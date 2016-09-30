<?php

namespace Qp\Kernel\Task;

use Qp\Kernel\PhpRedis as Redis;

class BaseTask
{
    private static $task_no_list_key = "QP_Task_List";

    private static $task_no_prefix_key = "QP_Task_";

    private static $task_no_list = [];

    private static $handleTask = [];

    private static $newTask = [];

    private static $ignoreButUpdateTime = [];

    public static function initData()
    {
        $redis = Redis::connection('redis');

        $list = $redis->get(self::$task_no_list_key);

        if ($list == null) {
            self::$task_no_list = [];
        } else {
            self::$task_no_list = unserialize($list);
        }
    }

    public static function getRedisKey($no)
    {
        return self::$task_no_prefix_key . $no;
    }

    public static function getTaskInfo($key)
    {
        if (! in_array($key, self::$task_no_list)) {
            return '';
        }
        $data_str = Redis::connection('redis')->get($key);
        $data = unserialize($data_str);
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    public static function addHandleTaskFun($key, $fun)
    {
        self::$handleTask[$key]['fun'] = $fun;
    }

    public static function addHandleTaskTime($key, $time)
    {
        self::$handleTask[$key]['time'] = $time;
    }

    public static function addNewTask($key, $time)
    {
        self::$newTask[$key]['time'] = $time;
    }

    public static function addIgnoreButUpdateTime($key, $time)
    {
        self::$ignoreButUpdateTime[$key]['time'] = $time;
    }

    public static function handleTask()
    {
        $redis = Redis::connection('redis');

        // 将新任务加入到Redis中
        foreach (self::$newTask as $key => $value) {
            $data = serialize([
                'is_running' => false,
                'run_time' => $value['time']
            ]);
            $redis->set($key, $data);
            self::$task_no_list[] = $key;
        }
        $redis->set(self::$task_no_list_key, serialize(self::$task_no_list));

        // 更新忽略但是时间发生变化的任务
        foreach (self::$ignoreButUpdateTime as $key => $value) {
            $data = serialize([
                'is_running' => false,
                'run_time' => $value['time']
            ]);
            $redis->set($key, $data);
        }

        // 即将执行的任务实时置为忙碌状态，避免因为任务执行时间过长，导致下一分钟重复执行
        foreach (self::$handleTask as $key => $value) {
            $data = [
                'is_running' => true,
                'run_time' => $value['time'],
            ];
            $redis->set($key, serialize($data));
        }

        // 开始执行任务，任务执行完毕后置为闲置状态
        foreach (self::$handleTask as $key => $value) {
            $value['fun']();
            $data = [
                'is_running' => false,
                'run_time' => $value['time'],
            ];
            $redis->set($key, serialize($data));
        }
    }

    public static function flushTask()
    {
        $redis = Redis::connection('redis');

        foreach (self::$task_no_list as $key) {
            $redis->delete($key);
        }

        $redis->set(self::$task_no_list_key, serialize([]));
    }

    public static function handleTaskRefresh()
    {
        $redis = Redis::connection('redis');

        // 将新任务加入到Redis中
        foreach (self::$newTask as $key => $value) {
            $data = serialize([
                'is_running' => false,
                'run_time' => $value['time']
            ]);
            $redis->set($key, $data);
            self::$task_no_list[] = $key;
        }
        $redis->set(self::$task_no_list_key, serialize(self::$task_no_list));
    }

}
