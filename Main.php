<?php

namespace Qp\Kernel;

use Qp\Kernel\Http\Router\QpRouter as QR;
use Qp\Kernel\Session\QpSession as QS;

/**
 * QP框架的入口类：启动程序
 */
class Main
{
    public function __construct()
    {
        define('QP_ROOT_PATH' , dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
        define('QP_APP_PATH' , QP_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);
        define('QP_VIEW_PATH' , QP_ROOT_PATH . 'app_view' . DIRECTORY_SEPARATOR);
        define('QP_CONFIG_PATH', QP_ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);
        define('QP_TMP_PATH', QP_ROOT_PATH . 'tmp' . DIRECTORY_SEPARATOR);

        require_once "Helpers/helpers.php";
    }

    /**
     * 启动程序 - 常规HTTP请求
     */
    public function start()
    {
        $QpException = new Exception();
        try {

            // 1.加载配置文件 - /config目录下的php文件
            Config\BaseConfig::init(['app', 'database', 'session']);
            Config\BaseConfig::initEnv();

            // 2.设置默认时区 - 从配置中读取
            date_default_timezone_set(Config::getEnv("app.timezone"));

            // 3.定义日志目录路径 - 从配置中读取
            $this->setLogPath();

            // 4.记录请求日志
            Log\SystemLog::request_start_log();

            // 5.注册命名空间
            $this->setNamespace();

            // 6.加载路由模块
            $router = $this->handleRouter();

            // 7.定义Phalcon的DI
            $di = new \Phalcon\DI\FactoryDefault();

            // 8.预加载数据库链接
            $this->handleDBConnection($di);

            // 9.设置Redis数据库连接
            $this->handleRedis($di);

            // 10.设置会话 - 防止跨域攻击
            $this->handleSession($di);

            // 11.处理中间件
            $this->handleMiddleware();

            // 12.设置请求
            $this->setRequest($di, $router);

            // 13.开始请求，并处理响应
            $this->handleRequestAndEnd($di);

        } catch (\Exception $ex) {
            Log\SystemLog::error_log($ex);
            $QpException->fatalHandler($ex);
        } catch (\Throwable $ex) {
            Log\SystemLog::error_log($ex);
            $QpException->fatalHandler($ex);
        }
    }

    /**
     * 定时任务脚本入口
     */
    public function task()
    {
        $QpException = new Exception();
        try {

            // 1.加载配置文件 - /config目录下的php文件
            Config\BaseConfig::init(['app', 'database', 'session']);
            Config\BaseConfig::initEnv();

            // 2.设置默认时区 - 从配置中读取
            date_default_timezone_set(Config::getEnv("app.timezone"));

            // 3.定义日志目录路径 - 从配置中读取
            $this->setLogPath();

            // 4.注册命名空间
            $this->setNamespace();

            // 5.定义Phalcon的DI
            $di = new \Phalcon\DI\FactoryDefault();

            // 6.预加载数据库链接
            $this->handleDBConnection($di);

            // 7.设置Redis数据库连接
            $this->handleRedis($di);

            // 8.处理任务
            $this->handleTask($di);

        } catch (\Exception $ex) {
            Log\SystemLog::error_log($ex);
            $QpException->fatalHandler($ex);
        } catch (\Throwable $ex) {
            Log\SystemLog::error_log($ex);
            $QpException->fatalHandler($ex);
        }
    }

    /**
     * 定时任务刷新入口
     */
    public function task_refresh()
    {
        $QpException = new Exception();
        try {

            // 1.加载配置文件 - /config目录下的php文件
            Config\BaseConfig::init(['app', 'database', 'session']);
            Config\BaseConfig::initEnv();

            // 2.设置默认时区 - 从配置中读取
            date_default_timezone_set(Config::getEnv("app.timezone"));

            // 3.定义日志目录路径 - 从配置中读取
            $this->setLogPath();

            // 4.注册命名空间
            $this->setNamespace();

            // 5.定义Phalcon的DI
            $di = new \Phalcon\DI\FactoryDefault();

            // 6.预加载数据库链接
            $this->handleDBConnection($di);

            // 7.设置Redis数据库连接
            $this->handleRedis($di);

            // 8.处理刷新任务
            $this->handleTaskRefresh($di);

        } catch (\Exception $ex) {
            Log\SystemLog::error_log($ex);
            $QpException->fatalHandler($ex);
        } catch (\Throwable $ex) {
            Log\SystemLog::error_log($ex);
            $QpException->fatalHandler($ex);
        }
    }

    /**
     * 定义日志目录
     *
     * @throws \ErrorException
     */
    private function setLogPath()
    {
        $config_log_dir = Config::getEnv("app.log_dir");
        if (! is_string($config_log_dir)) {
            throw new \ErrorException("配置项app.log_dir必须是字符串格式");
        }

        $log_dir = QP_ROOT_PATH . str_replace(['/','\\'], DIRECTORY_SEPARATOR, $config_log_dir) . DIRECTORY_SEPARATOR;

        define('QP_LOG_PATH' , $log_dir);
    }

    /**
     * 注册命名空间：除了app目录外，还需要注册用户定义的命名空间
     */
    private function setNamespace()
    {
        // 注册app目录，使其成为prs-4标准注册命名空间
        $loader = new \Phalcon\Loader();
        $loader->registerDirs(array(
            QP_APP_PATH,
        ));

        // 注册用户自定义命名空间
        $ns_config = (array) Config::get('app.namespace');

        $ns = ['App' => QP_APP_PATH];

        foreach ($ns_config as $key => $value) {
            if ($key == "App") {
                continue;
            }
            $ns[$key] = QP_ROOT_PATH . $value;
        }

        $loader->registerNamespaces($ns)->register();
    }

    /**
     * 处理用户请求的路由
     * 匹配失败直接抛出异常，成功则返回设置过的Phalcon路由对象
     *
     * @return  \Phalcon\Mvc\Router     Phalcon的路由对象
     * @throws  \ErrorException
     */
    private function handleRouter()
    {
        require_once QP_APP_PATH . "routers.php";

        if (QR::hasMatched() == false) {
            echo (new \App\Controllers\IndexController)->notFoundAction();
            exit;
        }

        $matched_router_data = QR::getMatchedData();

        $ns = $matched_router_data['namespace'];
        $ctrl = $matched_router_data['controller'];
        $m = QR::getMethod();

        if (! method_exists(($ns . "\\" . $ctrl . "Controller"), $m . "Action")) {
            echo (new \App\Controllers\IndexController)->notFoundAction();
            exit;
        }

        $router = new \Phalcon\Mvc\Router();
        $router->setDefaults([
            "namespace" => $ns,
            "controller" => $ctrl,
            "action" => $m,
        ]);

        return $router;
    }

    /**
     * 设置DI的数据库连接
     *
     * @param   \Phalcon\DI\FactoryDefault  $di     Phalcon的DI类
     */
    private function handleDBConnection(&$di)
    {
        foreach (Database\QpDB::getConnectionNameList() as $connection_name) {
            $di->set($connection_name, function () use ($connection_name) {
                return  DB::connection($connection_name);
            });
        }
    }

    /**
     * 预加载Redis数据库连接
     *
     * @param   \Phalcon\DI\FactoryDefault  $di     Phalcon的DI类
     */
    private function handleRedis(&$di)
    {
        foreach (Redis\PhalconRedis\PhalconRedis::getNameList() as $conn_name) {
            $di->set($conn_name, function () use ($conn_name) {
                return Redis\PhalconRedis\PhalconRedis::connection($conn_name);
            });
        }
    }

    /**
     * 启动Session并注入到DI
     *
     * @param   \Phalcon\DI\FactoryDefault  $di     Phalcon的DI类
     */
    private function handleSession(&$di)
    {
        if (! QS::isOpen()) {
            return;
        }

        QS::startSession();

        $di->set('session', function () {
            return QS::getSessionObject();
        });
    }

    /**
     * 处理中间件
     */
    private function handleMiddleware()
    {
        Http\Middleware\QpMiddleware::handleMiddleware();
    }

    /**
     * 设置请求和DI注入服务
     *
     * @param   \Phalcon\DI\FactoryDefault  $di         Phalcon的DI类
     * @param   \Phalcon\Mvc\Router         $router     Phalcon路由对象
     */
    private function setRequest(&$di, &$router)
    {
        $di->set('router', $router);

        $di->set('url', function () {
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri(QP_ROOT_PATH);
            return $url;
        });

        $di->set('view', function () {
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir(QP_VIEW_PATH);
            return $view;
        });
    }

    /**
     * 终结请求：处理请求、会话、发送响应
     *
     * @param   \Phalcon\DI\FactoryDefault  $di     Phalcon的DI类
     */
    private function handleRequestAndEnd(&$di)
    {
        $response = Http\Response\QpResponse::getResponse();
        $response->setContent(
            (new \Phalcon\Mvc\Application($di))->handle()->getContent()
        );
        $response->send();
    }

    /**
     * 开始处理任务
     *
     * @param   \Phalcon\DI\FactoryDefault  $di     Phalcon的DI类
     */
    private function handleTask(&$di)
    {
        $router = new \Phalcon\Mvc\Router();
        $router->setDefaults([
            "namespace" => 'App\Task',
            "controller" => 'QpTask',
            "action" => 'kernel',
        ]);

        $di->set('router', $router);

        $di->set('url', function () {
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri(QP_ROOT_PATH);
            return $url;
        });

        $di->set('view', function () {
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir(QP_VIEW_PATH);
            return $view;
        });

        Task\BaseTask::initData();
        (new \Phalcon\Mvc\Application($di))->handle();
        Task\BaseTask::handleTask();
    }

    /**
     * 开始刷新任务
     *
     * @param   \Phalcon\DI\FactoryDefault  $di     Phalcon的DI类
     */
    private function handleTaskRefresh(&$di)
    {
        $router = new \Phalcon\Mvc\Router();
        $router->setDefaults([
            "namespace" => 'App\Task',
            "controller" => 'QpTask',
            "action" => 'kernel',
        ]);

        $di->set('router', $router);

        $di->set('url', function () {
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri(QP_ROOT_PATH);
            return $url;
        });

        $di->set('view', function () {
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir(QP_VIEW_PATH);
            return $view;
        });

        Task\BaseTask::flushTask();
        Task\BaseTask::initData();
        (new \Phalcon\Mvc\Application($di))->handle();
        Task\BaseTask::handleTaskRefresh();
    }
}
