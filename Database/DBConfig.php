<?php

namespace Qp\Kernel\Database;

use Qp\Kernel\Config;

/**
 * QP框架核心模块：Database链接模块 - 配置模块
 */
class DBConfig
{
    /**
     * 可以使用的DB匹配模式
     *
     * @var array
     */
    private static $fetch_allow = [
        5, 1, 2, 3, 4, 6, 7, 8, 9, 10, 65536, 196608, 12, 262144, 524288, 1048576, 11,
    ];

    /**
     * 默认的DB匹配模式
     *
     * @var null|int
     */
    private static $default_fetch = null;

    /**
     * 获取默认的数据库匹配模式
     *
     * @return  int
     */
    public static function getDefaultFetch()
    {
        if (self::$default_fetch === null) {
            self::initDefaultFetch();
        }

        return self::$default_fetch;
    }

    /**
     * 初始化定义默认的数据库匹配模式和常量
     */
    private static function initDefaultFetch()
    {
        $fetch = Config::get('database.fetch');
        if (! is_int($fetch) || ! in_array($fetch, self::$fetch_allow)) {
            throw new \InvalidArgumentException("'database'配置文件中的'fetch'配置项无效：请参照PDO类的常量");
        }

        if (! defined('QP_DB_FETCH_MODE')) {
            define('QP_DB_FETCH_MODE', $fetch);
        }

        if (! defined('BIND_PARAM_INT')) {
            define('BIND_PARAM_INT', \Phalcon\Db\Column::BIND_PARAM_INT);
        }
        if (! defined('BIND_PARAM_STR')) {
            define('BIND_PARAM_STR', \Phalcon\Db\Column::BIND_PARAM_STR);
        }
        if (! defined('BIND_PARAM_STR')) {
            define('BIND_PARAM_DECIMAL', \Phalcon\Db\Column::BIND_PARAM_DECIMAL);
        }

        self::$default_fetch = $fetch;
    }

    /**
     * 获取所有数据库链接的配置对象
     *
     * @return  array
     */
    public function getAllConnection()
    {
        $config = Config::getEnv('database.connection');

        if (empty($config)) {
            throw new \InvalidArgumentException("'database'配置文件中的'connection'配置项无效：该配置必须是非空数组");
        }

        $conn = [];

        foreach ($config as $key => $value) {

            if (! is_string($key) || empty($key)) {
                throw new \InvalidArgumentException("'database'配置文件中的'connection'配置项无效：'{$key}'链接名必须是非空字符串");
            }

            if (empty($value)) {
                throw new \InvalidArgumentException("'database'配置文件中的'connection'配置项无效：'{$key}'必须是非空数组");
            }

            $driver_str = isset($value->driver) ? $value->driver : null;
            $driver = strtolower($driver_str);
            unset($value->driver);

            $value = (array) $value;
            $value['options'] = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_STRINGIFY_FETCHES => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => $this->getDefaultFetch(),
            ];

            $conn[$key] = new ConnectionObj($key, $driver, $value);
        }

        return $conn;
    }

    /**
     * 返回默认的数据库链接对象
     *
     * @return  ConnectionObj
     */
    public function getDefaultConnection()
    {
        $config = Config::getEnv('database.default');

        if (empty($config)) {
            throw new \InvalidArgumentException("'database'配置文件中的'default'配置项无效：该配置必须是非空数组");
        }

        $driver_str = isset($config->driver) ? $config->driver : null;
        $driver = strtolower($driver_str);
        unset($config->driver);

        $config = (array) $config;
        $config['options'] = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_DEFAULT_FETCH_MODE => $this->getDefaultFetch(),
        ];

        return new ConnectionObj('db', $driver, $config);
    }
}
