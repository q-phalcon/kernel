<?php

namespace Qp\Kernel\Task;

class TaskTime
{
    /**
     * 任务时间状态：
     *  0.不执行 1.新任务 2.执行中 3.本次即将执行
     *
     * @var int
     */
    private $status = 0;

    private $redis_key;

    private $runTime;

    public function __construct($redis_key, $status, $runTime)
    {
        $this->redis_key = $redis_key;
        $this->status = $status;
        $this->runTime = $runTime;
    }

    private function handleByTaskStatus($next_time)
    {
        switch ($this->status) {
            case 0 : // 不执行
                $diff = $next_time != $this->runTime;
                $absTime = $this->runTime - time();
                if ($diff && $absTime > 5) {
                    BaseTask::addIgnoreButUpdateTime($this->redis_key, $next_time);
                }
                break;
            case 1 : // 新任务
                BaseTask::addNewTask($this->redis_key, $next_time);
                break;
            case 2 : // 执行中
                break;
            case 3 : // 本次即将执行
                BaseTask::addHandleTaskTime($this->redis_key, $next_time);
                break;
            default :
                break;
        }
    }

    /**
     * 在指定的时刻，每天执行一次任务
     *
     * @param   string  $time   "H:i"格式的时间字符串
     */
    public function dailyAt($time)
    {
        $hm_arr = explode(':', strval($time));
        if (count($hm_arr) != 2) {
            throw new \InvalidArgumentException("定时任务定义错误：dailyAt接受的时间参数格式不合法");
        }
        $h = intval($hm_arr[0]);
        $m = intval($hm_arr[1]);
        if (! is_int($h) || $h < -1 || $h > 24) {
            throw new \InvalidArgumentException("定时任务定义错误：dailyAt接受的时间参数值不合法");
        }
        if (! is_int($m) || $m < 0 || $m > 60) {
            throw new \InvalidArgumentException("定时任务定义错误：dailyAt接受的时间参数值不合法");
        }

        $next_time = strtotime(date("Y-m-d {$time}:00"));
        if (time() > $next_time) {
            $next_time = strtotime(date("Y-m-d {$time}:00", strtotime("+1 day")));
        }
        $this->handleByTaskStatus($next_time);
    }

    public function everyMinute()
    {
        $next_time = strtotime(date('Y-m-d H:i:00',strtotime("+1 minute")));
        $this->handleByTaskStatus($next_time);
    }

    public function everyFiveMinutes()
    {
        $dt = date('i');
        $nd = $dt + 5 - $dt % 5;
        $next_time = strtotime(date("Y-m-d H:{$nd}:00"));
        $this->handleByTaskStatus($next_time);
    }

    public function everyHour()
    {
        $next_time = strtotime(date('Y-m-d H:00:00', strtotime("+1 hour")));
        $this->handleByTaskStatus($next_time);
    }

    public function everyDay()
    {
        $next_time = strtotime(date('Y-m-d 00:00:00', strtotime("+1 day")));
        $this->handleByTaskStatus($next_time);
    }

    public function everyMonth()
    {
        $next_time = strtotime(date('Y-m-01 00:00:00', strtotime("+1 month")));
        $this->handleByTaskStatus($next_time);
    }

}
