<?php

namespace Qp\Kernel\Config;

/**
 * QP框架核心模块：配置模块 -> 基础模块
 */
class BaseConfig
{
    /**
     * 配置数组 - 动态数组
     *
     * @var array
     */
    protected static $settings = [];

    /**
     * 开发环境配置数据
     *
     * @var array
     */
    protected static $env_setting = [];

    /**
     * 默认配置目录路径
     *
     * @var string
     */
    private static $default_dir = "";

    /**
     * 初始化配置
     *
     * @param   array   $init_config_files  需要初始化的文件名(不包括后缀)
     * @throws  \ErrorException
     */
    public static function init($init_config_files)
    {
        self::$settings = [];
        self::$default_dir = QP_CONFIG_PATH;

        foreach ($init_config_files as $filename) {
            self::addConfigFromFile($filename);
        }
    }

    /**
     * 通过指定的KEY，获取配置数据
     *
     * @param   string  $key    参数KEY(文件名.数组索引[.数组索引...])
     * @return  null|mixed      参数值
     * @throws  \ErrorException
     */
    public static function get($key = '')
    {
        if ($key === '') {
            return null;
        }

        $arr = explode('.', $key);

        $config_file = $arr[0];
        if (! isset(self::$settings[$config_file])) {
            self::addConfigFromFile($config_file);
        }

        $config_key_first = isset($arr[1]) ? $arr[1] : '';
        if ($config_key_first === '') {
            return null;
        }

        $value = isset(self::$settings[$config_file]->$config_key_first)
            ? self::$settings[$config_file]->$config_key_first : null;

        if (is_null($value)) {
            return null;
        }

        for ($i = 2; $i < count($arr); $i++) {
            $config_key = $arr[$i];
            if ($config_key == '') {
                return null;
            }
            $value = isset($value->$config_key) ? $value->$config_key : null;
            if (is_null($value)) {
                return null;
            }
        }

        return $value;
    }

    /**
     * 读取文件，增加配置内容
     *
     * @param   string  $filename       文件名(不包括后缀)
     * @throws  \ErrorException
     */
    private static function addConfigFromFile($filename)
    {
        $file_path = self::$default_dir . $filename . ".php";

        if (! file_exists($file_path)) {
            $err_msg = "The file '" . str_replace(['\\','/'], DIRECTORY_SEPARATOR, $file_path) . "' is not found!";
            throw new \Exception($err_msg);
        }

        self::$settings[$filename] = new \Phalcon\Config\Adapter\Php($file_path);
    }

    /**
     * 优先从开发环境中获取配置项
     *
     * @param   string      $key    配置项
     * @return  mixed|null
     */
    public static function getEnv($key = '')
    {
        if (self::$env_setting === null) {
            return self::get($key);
        }

        $arr = explode('.', $key);

        $value = null;
        foreach ($arr as $kv) {
            if (empty($kv)) {
                $value = null;
                break;
            }
            if ($value === null) {
                if (isset(self::$env_setting->$kv)) {
                    $value =  self::$env_setting->$kv;
                    continue;
                }
                $value = null;
                break;
            } else {
                if (isset($value->$kv)) {
                    $value =  $value->$kv;
                    continue;
                }
                $value = null;
                break;
            }
        }

        if ($value === null) {
            return self::get($key);
        }
        return $value;
    }

    /**
     * 初始化ENV配置文件
     */
    public static function initEnv()
    {
        $file_path = QP_ROOT_PATH . ".php";

        if (! file_exists($file_path)) {
            self::$env_setting = null;
            return;
        }

        self::$env_setting = new \Phalcon\Config\Adapter\Php($file_path);
    }
}
