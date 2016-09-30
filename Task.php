<?php

namespace Qp\Kernel;

use Qp\Kernel\Task\TaskStatus as Status;
use Qp\Kernel\Task\TaskTime as Time;
use Qp\Kernel\Task\BaseTask as Base;

/**
 * QP框架的定时任务类
 */
class Task
{
    /**
     * 任务调度
     *
     * @param   string      $no     任务编号
     * @param   \Closure    $fun    任务函数(闭包)
     * @return  Time
     */
    public static function call(string $no, $fun)
    {
        if (! is_callable($fun)) {
            throw new \InvalidArgumentException("定时任务定义错误：call方法接收的fun不是有效函数");
        }

        $redis_key = Base::getRedisKey($no);
        $taskInfo = Base::getTaskInfo($redis_key);
        $run_time = $taskInfo == '' ? 0 : $taskInfo['run_time'];
        $status = new Status();

        if (empty($taskInfo)) {
            $status->newTask();
            goto goto_return;
        }

        if ($taskInfo['is_running']) {
            $status->running();
            goto goto_return;
        }

        if (time() >= $run_time) {
            Base::addHandleTaskFun($redis_key, $fun);
            $status->execTask();
            goto goto_return;
        }

        goto_return:
        return new Time($redis_key, $status->status(), $run_time);
    }
}
