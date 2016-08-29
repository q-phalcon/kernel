<?php
declare(strict_types = 1);

namespace Qp\Kernel;

use Qp\Kernel\Http\Response\QpResponse as Base;

/**
 * QP框架核心模块：Http模块 - 响应模块
 */
class Response
{
    /**
     * 发送HTTP响应信息
     *
     * @param   string  $message    响应消息
     * @param   int     $status     响应状态码
     */
    public static function send(string $message = '', int $status = 200)
    {
        Base::send($message, $status);
    }

    /**
     * 获取响应信息
     *
     * @return  \Phalcon\Http\Response
     */
    public static function response()
    {
        return Base::getResponse();
    }
}
