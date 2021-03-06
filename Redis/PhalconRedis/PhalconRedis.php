<?php

namespace Qp\Kernel\Redis\PhalconRedis;

/**
 * QP框架核心模块：Redis模块 - PhalconRedis模块 - 基础模块
 */
class PhalconRedis extends Base
{
    /**
     * 获取Phalcon的Redis对象
     *
     * @param   string  $name                   连接名
     * @return  \Phalcon\Cache\Backend\Redis
     */
    public static function connection($name)
    {
        if (self::$conn_list === null) {
            self::init();
        }

        $name = strval($name);

        if (! isset(self::$conn_list[$name])) {
            throw new \InvalidArgumentException("找不到连接名为'{$name}'的Redis配置！请检查配置项'database.redis'");
        }

        return self::getConnection($name);
    }

    /**
     * 获取Phalcon-Redis连接集合
     *
     * @return  array
     */
    public static function getList()
    {
        if (self::$conn_list === null) {
            self::init();
        }
        return self::$conn_list;
    }

    /**
     * 获取Phalcon-Redis连接名的集合
     *
     * @return  array
     */
    public static function getNameList()
    {
        if (self::$conn_name_list === null) {
            self::init();
        }
        return self::$conn_name_list;
    }
}
