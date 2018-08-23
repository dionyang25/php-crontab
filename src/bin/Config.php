<?php
/**
 * Created by PhpStorm.
 * User: yanghaonan
 * Date: 2018/8/14
 * Time: 上午11:35
 */

namespace Dion\Crontab\bin;

class Config
{
    /**
     * 数据配置
     * @var array
     */
    private static $configData = null;
    private static $configPath = null;

    /**
     * 任务数据
     * @var Unit[]
     */
    private static $taskData = [];


    public static function set(array $config = []){
        if(empty($config)){
            self::$configData = require self::$configPath;
        }else{
            self::$configData = $config;
        }
        //加载任务配置
        self::loadTask();
    }

    public static function setPath($configPath){
        self::$configPath = $configPath;
    }

    public static function get($item){
        return isset(self::$configData[$item])?self::$configData[$item]:null;
    }


    /**
     * 读取task的任务配置
     * @return void
     */
    public static function loadTask()
    {
        $configTaskList = self::get('task');
        //重置任务
        self::$taskData = [];
        foreach ($configTaskList as $taskName => $task) {
            list($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek) = \explode(' ', $task['daemon']);
            $unit = new Unit($taskName, $task['filename'], $task['params'], $execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek);
            $task['standard_ouput'] = isset($task['standard_ouput'])?$task['standard_ouput']:null;
            $task['error_output'] = isset($task['error_output'])?$task['error_output']:null;
            $unit->setSingle($task['single']);
            $unit->setOuput($task['standard_ouput'], $task['error_output']);
            self::$taskData[] = $unit;
        }
    }

    /**
     * 查找需要执行的任务
     * @param int $execSecond 执行的秒
     * @param int $execMintue 执行的分钟
     * @param int $execHour 执行的小时
     * @param int $execDay 执行的日期
     * @param int $execMonth 执行的月份
     * @param int $execWeek 执行的一周的某天
     * @return Unit[]
     */
    public static function find($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)
    {
        foreach (self::$taskData as $task) {
            if ($task->match($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek)) {
                yield $task;
            }
        }
    }
}