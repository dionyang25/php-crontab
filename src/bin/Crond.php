<?php
/**
 * Created by PhpStorm.
 * User: yanghaonan
 * Date: 2018/8/14
 * Time: 上午11:21
 */
namespace Dion\Crontab\bin;

use Monolog\Logger;
use React\EventLoop\Factory;
use Symfony\Component\Process\Process;

class Crond
{
    /**
     * 任务执行状态，进程没有执行
     * @var int
     */
    const TASK_NONE = 0;

    /**
     * 任务执行状态，进程正在运行
     * @var int
     */
    const TASK_EXEC = 1;

    /**
     * 进程执行列表
     * @var array
     */
    private $processList = [];

    /**
     * 日志记录器
     * @var Logger
     */
    private $logger;

    /**
     * 定时任务执行状态
     * @var boolean
     */
    private $running = true;


    public function __construct(array $config,$logger = '')
    {
        //写入配置
        Config::set($config);
        $this->logger = new Logger('cron');
    }

    /**
     * 创建pid文件
     * @param string $pidFileName pid文件路径
     * @throws \RuntimeException
     * @return void
     */
    private function createPidFile($pidFileName)
    {
        if (is_file($pidFileName)) {
            throw new \RuntimeException("pid file is exists, check the crond php is running or not!");
        }
        //将pid写入文件
        $pid = getmypid();
        if (!file_put_contents($pidFileName, $pid)) {
            throw new \RuntimeException("counldn't create pid file!");
        }
        //注册一个会在php中止时执行的函数
        register_shutdown_function(function($pidFileName){
            unlink($pidFileName);
        }, $pidFileName);

    }

    /**
     * 开始任务
     */
    public function start(){
        //读取配置
        Config::loadTask();
        //创建pid
        $this->createPidFile(Config::get('pid_file'));

        //记录启动日志
        $this->logger->info('php-crontab start');
        //主进程循环执行任务
        $loop = Factory::create();
        $that = $this;
        $loop->addPeriodicTimer(1, function($timer) use ($that,$loop){
            list($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek) = \explode(' ', date("s i H d m w"));
            //执行及具体任务
            $taskList = Config::find($execSecond, $execMintue, $execHour, $execDay, $execMonth, $execWeek);
            foreach ($taskList as $task) {

                //获取任务的唯一名称
                $taskUniqName = $task->getUniqTaskName();
                var_dump($taskUniqName);
                //判断是否single的任务 以及任务是否在执行
                if ($task->isSingle() && $that->checkProcess($taskUniqName) === Crond::TASK_EXEC) {
                    $that->logger->info('task ' . $task->getTaskName() . " is running");
                    continue;
                }

                //获取单元任务的参数
                list($processFilename, $params) = $task->getExec();
                //拼接命令
                $processCommand = $processFilename.' ' . \implode(' ', $params);
                //创建子进程
                $process = new Process($processCommand);
                //执行任务
                $process->start(function ($type, $buffer) use($task) {
                    //输出记录文件 默认到/dev/null
                    if ($type === Process::ERR) {
                        $filename = $task->getStderr();
                    } else {
                        $filename = $task->getStdout();
                    }
                    \file_put_contents($filename, $buffer, FILE_APPEND);
                });
                //日志
                $that->logger->info('task '.$task->getTaskName() . "[{$processCommand}] start");
                //记录
                $that->markProcess($taskUniqName, $process);
            }
            //信号处理
//            \pcntl_signal_dispatch();
            //信号处理结束
            if (!$that->alive()) {
                $loop->cancelTimer($timer);
            }
        });

        $loop->run();

        //等待所有子进程结束，结束进程
        while ($that->isTasksAlive()) {
            sleep(1);
        }
    }

   /**
    * 检查任务执行状态
    * @param string $taskUniqName 任务唯一名称
    * @return int 任务状态
    */
    private function checkProcess($taskUniqName)
    {
        if (!isset($this->processList[$taskUniqName])) {
            return self::TASK_NONE;
        }
        $process = $this->processList[$taskUniqName];
        if ($process->isRunning()) {
            return self::TASK_EXEC;
        } else {
            unset($this->processList[$taskUniqName]);
            return self::TASK_NONE;
        }
    }

    /**
     * 记录任务执行状态
     * @param string $taskUniqName 任务唯一名称
     * @param int $childPid 进程ID
     */
    private function markProcess($taskUniqName, Process $process)
    {
        $this->processList[$taskUniqName] = $process;
    }

    /**
     * 返回任务的执行状态
     * @return bool 如果正在执行，返回true，否则返回false
     */
    public function alive()
    {
        return $this->running;
    }

    /**
     * 检测是否有任务在执行
     * @return bool 如果有任务执行，返回true，否则返回false
     */
    public function isTasksAlive()
    {
        foreach ($this->processList as $process) {
            if ($process->isRunning()) {
                return true;
            }
        }
        return false;
    }

}