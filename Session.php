<?php
declare(strict_types = 1);

namespace Qp\Kernel;

use Qp\Kernel\Session\QpSession as Base;

/**
 * QP框架核心模块：Session模块
 *
 * 该模块提供Phalcon会话处理方法
 */
class Session
{
    /**
     * 获取Session中指定的值
     *
     * @param   string  $index          键
     * @param   mixed   $default_value  默认值
     * @return  mixed
     * @throws  \ErrorException
     */
    public static function get(string $index, $default_value = null)
    {
        Base::checkOpen();
        return Base::getSessionObject()->get($index, $default_value);
    }

    /**
     * 获取Session中所有得值
     * 注意：如果在此之前就已经使用过set或setBatch方法，该方法获取的值
     * 可能不是最新Session数据
     *
     * @return  mixed
     * @throws  \ErrorException
     */
    public static function getAll()
    {
        Base::checkOpen();
        if (Base::driver() == "file") {
            return $_SESSION;
        }
        $session = Base::getSessionObject();
        $data_str = $session->read($session->getId());
        if ($data_str === null) {
            return [];
        }
        return unserialize($data_str);
    }

    /**
     * 设置Session中指定的值
     *
     * @param   string  $index  键名
     * @param   mixed   $value  数据
     * @throws  \ErrorException
     */
    public static function set(string $index, $value)
    {
        Base::checkOpen();
        Base::getSessionObject()->set($index, $value);
    }

    /**
     * 批量设置Session的值
     *
     * @param   array   $data
     * @throws  \ErrorException
     */
    public static function setBatch(array $data)
    {
        Base::checkOpen();
        if (Base::driver() == "file") {
            $_SESSION = $data;
            return;
        }
        self::removeAll();
        $session = Base::getSessionObject();
        foreach ($data as $index => $value) {
            $session->set($index, $value);
        }
    }

    /**
     * 关闭Session
     *
     * @return  bool|void
     * @throws  \ErrorException
     */
    public static function close()
    {
        Base::checkOpen();
        if (Base::driver() == 'file') {
            if (session_status() == PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            return true;
        }
        return Base::getSessionObject()->close();
    }

    /**
     * 开启Session
     *
     * @return  bool|void
     * @throws  \ErrorException
     */
    public static function start()
    {
        Base::checkOpen();
        if (Base::driver() == 'file') {
            if (session_status() != PHP_SESSION_ACTIVE) {
                session_start();
            }
            return true;
        }
        return Base::getSessionObject()->start();
    }

    /**
     * 获取SessionID
     *
     * @return  string
     * @throws  \ErrorException
     */
    public static function getId()
    {
        Base::checkOpen();
        return Base::getSessionObject()->getId();
    }

    /**
     * 获取Session过期时间
     *
     * @return  int
     * @throws  \ErrorException
     */
    public static function getLifetime()
    {
        Base::checkOpen();
        if (Base::driver() == 'file') {
            return intval(ini_get('session.cookie_lifetime'));
        }
        return Base::getSessionObject()->getLifetime();
    }

    /**
     * 获取Session配置项
     *
     * @throws  \ErrorException
     */
    public static function getOptions()
    {
        Base::checkOpen();
        return Base::getSessionObject()->getOptions();
    }

    /**
     * 判断Session中是否有指定的键
     *
     * @param   string  $index  键名
     * @return  bool
     * @throws  \ErrorException
     */
    public static function has(string $index)
    {
        Base::checkOpen();
        return Base::getSessionObject()->has($index);
    }

    /**
     * 注册会话ID
     *
     * @param   bool    $deleteOldSessionId     是否同时删除旧会话
     * @throws  \ErrorException
     */
    public static function regenerateId(bool $deleteOldSessionId = true)
    {
        Base::checkOpen();
        if (Base::driver() == 'file') {
            session_regenerate_id($deleteOldSessionId);
            return true;
        }
        if ($deleteOldSessionId) {
            self::removeAll();
        }
        $random = new \Phalcon\Security\Random();
        session_write_close();
        session_id(str_replace('-', '', $random->uuid()));
        session_start();
        return true;
    }

    /**
     * 从Session中移除指定的键
     *
     * @param   string  $index  键名
     * @throws  \ErrorException
     */
    public static function remove(string $index)
    {
        Base::checkOpen();
        Base::getSessionObject()->remove($index);
    }

    /**
     * 移除所有Session数据
     */
    public static function removeAll()
    {
        Base::checkOpen();
        if (Base::driver() == 'file') {
            $_SESSION = [];
            return;
        }
        $data = self::getAll();
        $session = Base::getSessionObject();
        foreach ($data as $index => $value) {
            $session->remove($index);
        }
    }
}
