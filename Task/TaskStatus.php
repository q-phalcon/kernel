<?php

namespace Qp\Kernel\Task;

class TaskStatus
{
    /**
     * 任务时间状态：
     *  0.不执行 1.新任务 2.执行中 3.本次即将执行
     *
     * @var int
     */
    private $task_status = 0;

    public function newTask()
    {
        $this->task_status = 1;
    }

    public function running()
    {
        $this->task_status = 2;
    }

    public function execTask()
    {
        $this->task_status = 3;
    }

    public function status()
    {
        return $this->task_status;
    }
}