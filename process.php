<?php
/**
 *  +----------------------------------------------------------------------
 *  | 进程控制类
 *  | 负责启动作业进程、监控进程等
 *  +----------------------------------------------------------------------
 *  | Create Time : 2017年5月25日 09:58
 *  +----------------------------------------------------------------------
 *  | Author: roverliang <mr.roverliang@gmail.com>
 *  +----------------------------------------------------------------------
 */

require dirname(__FILE__).'/lib/autoloader.php';   //加载类自动加载器

/**
 * 启动进程
 */
$option = checkOptions();
$monitor_path       = __FILE__;             //监控脚本
$start_time         = microtime(true);

switch ($option) {
    case 'start':
        $process_count_info = restart($contab_config, $monitor_path);

        $end_time  = microtime(true);
        $cout_glue = str_repeat('+', 20)." Process Count Message ".str_repeat('+', 20).PHP_EOL;
        echo Colors::initColoredString($cout_glue, 'red', 'light_gray');
        $count_message = "config  process is : {$process_count_info['config']}".PHP_EOL;
        $count_message .= "running process is : {$process_count_info['run']}".PHP_EOL;
        $count_message .= "used time is :".($end_time - $start_time)." second".PHP_EOL;
        echo Colors::initColoredString($count_message, 'red', 'light_gray');
        break;

    case 'stop':
        stop($contab_config, $monitor_path); //杀死所有进程
        break;
    case 'monitor':
        Process::monitoringProcess($contab_config);  //启动监控进程。
        break;
}


/**
 * 进程启动器
 *
 * @param $contab_config  进程配置文件
 * @param $monitor_path   监控脚本路径
 *
 * @return array
 */
function restart($contab_config, $monitor_path)
{
    Process::KillMonitoringProcess($monitor_path);                           //杀死监控进程
    Process::StartProcessByProcessConfig($contab_config);                    //启动进程列表
    Process::startMonitoringProcess($monitor_path, 'monitor');               //启动监控进程
    $process_count_info = Process::processCount($contab_config);             //进程统计
    return $process_count_info;
}

/**
 * 关闭全部进程
 * @param $contab_config
 * @param $monitor_path
 */
function stop($contab_config, $monitor_path)
{
    Process::KillMonitoringProcess($monitor_path);                       //杀死监控进程
    $status = Process::KillProcessByProcessConfig($contab_config);       //杀死配置文件中的所有进程
    if (!$status) {
        printUsage();
    }
}

/**
 * 校验脚本参数
 * @return bool|mixed
 */
function checkOptions()
{
    $options = getopt('s:');
    if (empty($options) || !isset($options['s'])) {
        printUsage();
    }

    if ($options['s'] != 'start' && $options['s'] != 'stop' && $options['s'] != 'monitor') {
        printUsage();
    }

    return $options['s'];
}

/**
 * 打印帮助信息
 */
function printUsage()
{
    $usage_message = "Usage:".PHP_EOL;
    $usage_message .= "-s start    Start all processes by configuration file".PHP_EOL;
    $usage_message .= "-s stop     Stop  all processes by configuration file".PHP_EOL;
    echo Colors::initColoredString($usage_message, 'red', 'light_gray');
    die(PHP_EOL);
}





