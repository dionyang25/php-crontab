<?php
/**
 * Created by PhpStorm.
 * User: yanghaonan
 * Date: 2018/8/14
 * Time: 下午2:27
 */
require __DIR__ . "/../vendor/autoload.php";
$config = [
    //模式
    'model' => 'daemon',
    //PHP程序路径
    'php' => '/usr/bin/php',
    //主进程pid文件
    'pid_file' => "/tmp/crond.pid",
    //任务
    'task'=>[
        'process_a' => [
            'daemon' => '*/4 * * * * *',
            'filename' => '/usr/bin/php',
            'params' => ['./example/test.php'],
            'single' => true,
            'standard_ouput' => '/tmp/a.log',
            'error_output' => '/tmp/a.log',
        ]
    ]
];

$crond = new \Dion\Crontab\bin\Crond($config);
//注册重载配置信号
$crond->signalRegister(SIGUSR2,function () use ($crond){
    $config = [
        //模式
        'model' => 'daemon',
        //PHP程序路径
        'php' => '/usr/bin/php',
        //主进程pid文件
        'pid_file' => "/tmp/crond.pid",
        //任务
        'task'=>[
            'process_a' => [
                'daemon' => '*/1 * * * * *',
                'filename' => '/usr/bin/php',
                'params' => ['./example/test.php'],
                'single' => true,
                'standard_ouput' => '/tmp/a.log',
                'error_output' => '/tmp/a.log',
            ]
        ]
    ];
    $crond->reloadConfig($config);
});
//注册信号
$crond->start();

