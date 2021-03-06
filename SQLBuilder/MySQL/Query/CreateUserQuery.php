<?php
namespace SQLBuilder\MySQL\Query;
use Exception;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\MySQL\Syntax\UserSpecification;
use SQLBuilder\MySQL\Traits\UserSpecTrait;

/**

MYSQL CREATE USER SYNTAX
=========================

CREATE USER user_specification [, user_specification] ...

user_specification:
    user
    [
      | IDENTIFIED WITH auth_plugin [AS 'auth_string']
        IDENTIFIED BY [PASSWORD] 'password'
    ]


When using auth plugin, we need to specify the password later.

The 'old_passwords' global variable is for the hash algorithm.

There are two mysql auth plugin:
    mysql_native_password
    mysql_old_password

CREATE USER 'jeffrey'@'localhost' IDENTIFIED WITH mysql_native_password;
SET old_passwords = 0;
SET PASSWORD FOR 'jeffrey'@'localhost' = PASSWORD('mypass');


@see http://dev.mysql.com/doc/refman/5.5/en/create-user.html
@see http://dev.mysql.com/doc/refman/5.5/en/server-system-variables.html#sysvar_old_passwords
*/
class CreateUserQuery implements ToSqlInterface
{
    use UserSpecTrait;

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $specSql = array();
        foreach($this->userSpecifications as $spec) {
            $specSql[] = $spec->toSql($driver, $args);
        }
        return 'CREATE USER ' . join(', ', $specSql);
    }
}

