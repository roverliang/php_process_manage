<?php
/**
 *  +----------------------------------------------------------------------
 *  | 进程监控启动器
 *  +----------------------------------------------------------------------
 *  | Create Time : 2017年5月26日 15:20
 *  +----------------------------------------------------------------------
 *  | Author: roverliang <mr.roverliang@gmail.com>
 *  +----------------------------------------------------------------------
 */

define('PROCESS_MANAGE_ROOT_PATH', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);  //项目跟路径
define('SCRIPT_PATH', PROCESS_MANAGE_ROOT_PATH.'cron'.DIRECTORY_SEPARATOR);          //要执行的脚本路径
define('MONITORING_PROCESS_SLEEP_TIME', 3);                                          //监控进程休眠时间


include PROCESS_MANAGE_ROOT_PATH.'config'.DIRECTORY_SEPARATOR.'cron.cfg.php';        //加载进程配置文件
spl_autoload_register('my_autoloader');                                              //注册类加载器


/**
 * 类加载器
 * @param $class
 */
function my_autoloader($class)
{
    try {
        $class_file = dirname(__FILE__).DIRECTORY_SEPARATOR.strtolower($class).'.class.php';
        if (!file_exists($class_file)) {
            throw new Exception("auto loader 加载的{$class}文件不存在");
        }
        include $class_file;
    } catch (Exception $exc) {
        die($exc->getMessage().PHP_EOL);
    }
}


