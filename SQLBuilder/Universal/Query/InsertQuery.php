<?php
namespace SQLBuilder\Universal\Query;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;

use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Syntax\Join;
use SQLBuilder\Universal\Syntax\IndexHint;
use SQLBuilder\Universal\Syntax\Paging;

use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\OptionTrait;
use SQLBuilder\Universal\Traits\WhereTrait;
use SQLBuilder\MySQL\Traits\PartitionTrait;

use SQLBuilder\Raw;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use Exception;
use InvalidArgumentException;

/**
 * > INSERT INTO tbl_name (a,b,c) VALUES (1,2,3),(4,5,6),(7,8,9);
 *
 *
 * @see MySQL Insert Statement http://dev.mysql.com/doc/refman/5.7/en/insert.html
 */
class InsertQuery implements ToSqlInterface
{
    use OptionTrait;
    use PartitionTrait;


    /**
     * insert into table
     *
     * @param string table name.
     */
    protected $intoTable;

    protected $values = array();

    /**
     * Should return result when updating or inserting?
     *
     * when this flag is set, the primary key will be returned.
     *
     * @var string
     */
    protected $returning;

    public function insert(array $values)
    {
        $this->values[] = $values;
        return $this;
    }

    public function into($table)
    {
        $this->intoTable = $table;
        return $this;
    }

    public function getColumnNames(BaseDriver $driver) {
        return array_map([$driver,'quoteColumn'], array_keys($this->values[0]));
    }

    public function returning($returningColumns) {
        if (is_array($returningColumns)) {
            $this->returning = $returningColumns;
        } else {
            $this->returning = func_get_args();
        }
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'INSERT';

        if (!empty($this->options)) {
            $sql .= $this->buildOptionClause();
        }

        $sql .= ' INTO ' . $driver->quoteTable($this->intoTable);

        // append partition clause if needed.
        $sql .= $this->buildPartitionClause($driver, $args);

        $valuesClauses = array();
        $varCnt = 1;

        // build columns
        $columns = $this->getColumnNames($driver);

        foreach ($this->values as $values) {
            $deflatedValues = array();
            foreach ($values as $key => $value) {
                $deflatedValues[] = $driver->deflate($value, $args);
            }
            $valuesClauses[] = '(' . join(',', $deflatedValues) . ')';
        }

        $sql .= ' (' . join(',',$columns) . ')'
                . ' VALUES ' . join(', ', $valuesClauses) ;

        // Check if RETURNING is supported
        if ($this->returning && ($driver instanceof PgSQLDriver) ) {
            $sql .= ' RETURNING ' . join(',', $driver->quoteColumns($this->returning));
        }
        return $sql;
    }
}




