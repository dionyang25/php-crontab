<?php
/**
 * Created by PhpStorm.
 * User: yanghaonan
 * Date: 2018/8/14
 * Time: 上午11:59
 */
//require __DIR__ . "/../vendor/autoload.php";
//$loop = \React\EventLoop\Factory::create();
//$loop->addPeriodicTimer(1,function (){
//    echo "Tick!\n";
//});
//$stream = new \React\Stream\ReadableResourceStream(
//    fopen('/tmp/file.txt','r'),$loop
//);
//$loop->run();
//class AAA
//{
//    /**
//     * 数据配置
//     * @var array
//     */
//    private static $configData = null;
//
//    public static function set(array $config){
//        self::$configData = $config;
//    }
//
//    public static function get($item){
//        return isset(self::$configData[$item])?self::$configData[$item]:null;
//    }
//}
//AAA::set(['a'=>1,'b'=>2]);
//echo AAA::get('b');
file_put_contents('/tmp/file.txt','50');