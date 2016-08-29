<?php
declare(strict_types = 1);

namespace Qp\Kernel;

use Qp\Kernel\Config\BaseConfig;

/**
 * QP框架核心模块：配置模块
 *
 * 动态读取配置文件，并加入到全局配置数组中
 */
class Config
{
    /**
     * 通过指定的KEY，获取配置数据
     *
     * @param   string      $key    参数KEY(文件名.数组索引[.数组索引...])
     * @return  mixed               参数值
     */
    public static function get(string $key = '')
    {
        return BaseConfig::get($key);
    }

    /**
     * 通过指定的KEY，优先从ENV文件中读取配置，如果ENV中没有，再去config目录下获取
     *
     * @param   string      $key    参数KEY(文件名.数组索引[.数组索引...])
     * @return  mixed               参数值
     */
    public static function getEnv(string $key = '')
    {
        return BaseConfig::getEnv($key);
    }
}
