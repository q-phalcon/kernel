<?php
declare(strict_types = 1);

namespace Qp\Kernel\Database;

use Qp\Kernel\StackTrace;

/**
 * QP框架核心模块：Database链接模块 - 基础模块
 */
class QpDB extends BaseDB
{
    /**
     * 获取默认的数据库匹配模式
     *
     * @return  int
     */
    public static function getDefaultDBFetch()
    {
        return DBConfig::getDefaultFetch();
    }

    /**
     * 获取数据库连接对象
     *
     * @param   string  $connection_name    连接名
     * @return  \Phalcon\Db\Adapter\Pdo\Mysql|\Phalcon\Db\Adapter\Pdo\Oracle|\Phalcon\Db\Adapter\Pdo\Postgresql|\Phalcon\Db\Adapter\Pdo\Sqlite
     * @throws  \ErrorException
     */
    public static function getConnection(string $connection_name = '')
    {
        if (self::$conn_list === null) {
            self::initConnection();
        }

        if (! isset(self::$conn_list[$connection_name])) {
            $debug_msg = "数据库链接 - '$connection_name' 没有定义！" . StackTrace::getCode(3);
            throw new \InvalidArgumentException($debug_msg);
        }

        return self::connect($connection_name);
    }

    /**
     * 获取连接对象的列表
     *
     * @return  array
     */
    public static function getConnectionList()
    {
        if (self::$conn_list === null) {
            self::initConnection();
        }
        return self::$conn_list;
    }

    /**
     * 获取连接名的集合
     *
     * @return  array
     */
    public static function getConnectionNameList()
    {
        if (self::$conn_name_list === null) {
            self::initConnection();
        }
        return self::$conn_name_list;
    }
}
