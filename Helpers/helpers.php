<?php
declare(strict_types = 1);

if (! function_exists('array_get')) {
    /**
     * 从数组中获取值，如果不存在，则返回指定的默认值
     *
     * @param   array           $arr        数组
     * @param   string|integer  $key        Key
     * @param   mixed           $default    指定默认值，null
     * @return  mixed
     */
    function array_get(array $arr, string $key, $default = null)
    {
        if (is_null($key)) {
            return $arr;
        }

        if (isset($arr[$key])) {
            return $arr[$key];
        }

        return $default;
    }
}

if (! function_exists('array_first')) {
    /**
     * 获取数组的第一个元素，如果数组为空，返回null
     *
     * @param   array   $needle     参数数组
     * @return  mixed
     */
    function array_first(array &$needle)
    {
        $re = null;
        foreach ($needle as $v) {
            $re = $v;
            break;
        }
        return $re;
    }
}

if (! function_exists('array_first_key')) {
    /**
     * 获取数组的第一个元素的key，如果数组为空，返回null
     *
     * @param   array   $needle     参数数组
     * @return  mixed
     */
    function array_first_key(array &$needle)
    {
        $re = null;
        foreach ($needle as $k => $v) {
            $re = $k;
            break;
        }
        return $re;
    }
}

if (! function_exists('dd')) {
    /**
     * 输出传入的数据，并结束请求
     */
    function dd()
    {
        echo "<pre>";
        $arg_list = func_get_args();
        foreach ($arg_list as $value) {
            var_dump($value);
            echo "<br>";
        }
        exit;
    }
}

if (! function_exists('dump')) {
    /**
     * 输出传入的数据，带有Html格式的样式
     */
    function dump($var)
    {
        echo "<pre>";
        $arg_list = func_get_args();
        foreach ($arg_list as $value) {
            var_dump($value);
            echo "<br>";
        }
    }
}

if (! function_exists('config')) {
    /**
     * 优先从开发环境中读取配置
     *
     * @param   mixed   $key        配置项
     * @return  mixed
     */
    function config(string $key = '')
    {
        return \Qp\Kernel\Config\BaseConfig::getEnv($key);
    }
}