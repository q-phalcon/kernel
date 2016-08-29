<?php

namespace Qp\Kernel;

use Qp\Kernel\Config;

/**
 * QP框架核心模块：异常处理模块
 *
 * 程序入口通过catch所有异常，凡是遇到异常的情况都将终止操作！因为这是一个良好的实践经验！
 */
class Exception
{
    /**
     * 捕捉异常的类型
     *
     * @var int
     */
    private $exception_type = E_ALL;

    /**
     * 致命错误类型
     *
     * @var array
     */
    private $fatal_error = [
        E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE
    ];

    /**
     * 构造器
     */
    public function __construct()
    {
        ini_set("display_errors", 1);
        error_reporting($this->exception_type);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * set_error_handler() 方法的回调函数
     *
     * @param   int             $level      错误等级
     * @param   string          $message    错误消息
     * @param   string          $file       错误的文件名
     * @param   int             $line       错误的代码行数
     * @throws  \ErrorException
     */
	public function handleError(int $level, string $message, string $file, int $line)
    {
        if (error_reporting() && $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * set_exception_handler() 方法的回调函数
     *
     * @param   \Exception|\Throwable  $ex  异常对象
     * @throws  \ErrorException
     */
    public function handleException($ex)
    {
        if ($ex instanceof \Exception || $ex instanceof \Throwable) {
            throw new \ErrorException($ex->getMessage(), $ex->getCode(), E_ALL, $ex->getFile(), $ex->getLine());
        }
    }

    /**
     * register_shutdown_function() 方法的回调函数
     */
    public function handleShutdown()
    {
        if (is_null($error = error_get_last()) || ! $this->isFatal($error['type'])) {
            return;
        }

        if (Config::getEnv("app.debug")) {
            echo "<pre>";
            echo "Exception : " , $error['message'] , "<br>";
            echo "Catch position: " . $error['file'] . " : " . $error['line'];
        } else {
            echo strval(Config::getEnv("app.prod_tip"));
        }

        exit;
    }

    /**
     * QP框架异常处理方式
     *
     * @param   \Exception|\Throwable   $ex
     */
    public function fatalHandler($ex)
    {
        if (Config::getEnv("app.debug")) {
            echo "<pre>";
            echo "Exception : " , $ex->getMessage() , "<br>";
            echo "Catch position : " . $ex->getFile() . " : " . $ex->getLine() . "<br><br>";
            echo $ex->getTraceAsString();
        }else{
            echo strval(Config::getEnv("app.prod_tip"));
        }

        exit;
    }

    /**
     * 判断传入的参数是否是PHP的致命错误
     *
     * 返回true表示致命错误，false表示非致命错误
     *
     * @param	int		$type		Php Error Level
     * @return	bool
     */
    protected function isFatal(int $type)
    {
        return in_array($type, $this->fatal_error);
    }
}
