<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Traits\KeyTrait;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Exception\UnsupportedDriverException;

class AlterTableRenameTable implements ToSqlInterface
{
    protected $toTable;

    public function __construct($toTable) {
        $this->toTable = $toTable;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        return 'RENAME TO ' . $driver->quoteIdentifier($this->toTable);
    }
}




