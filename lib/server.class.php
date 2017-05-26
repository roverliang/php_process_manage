<?php
/**
 *  +----------------------------------------------------------------------
 *  | 进程服务类
 *  +----------------------------------------------------------------------
 *  | Create Time : 2017年5月25日 11:55
 *  +----------------------------------------------------------------------
 *  | Author: roverliang <mr.roverliang@gmail.com>
 *  +----------------------------------------------------------------------
 */

class Server
{
    const COMMOND_BIN = '/usr/local/bin/php';  //脚本路径
    const LOG_OUTPUT  = '/dev/null';  //日志输入目录
    const SIGTERM     = 15;   //优雅的停止进程
    const SIGKILL     = 9;    //立即停止进程

    /**
     * 获取进程IDs
     *
     * @param $process
     */
    public static function getProcessIDs($scriptname)
    {
        $script  = "'".self::COMMOND_BIN." ".trim($scriptname)."'";
        $command = "ps ax |grep {$script} |grep -v grep|awk '{print $1}'";
        exec($command, $output, $return_var);

        //命令执行出错
        if ($return_var != 0 || empty($output) || !is_array($output)) {
            return false;
        }
        return $output;
    }


    /**
     * $scriptname 脚本名
     * $num  进程数量
     * 通过Root 用户来启动进程
     */
    public static function startProcessByRoot($scriptname, $num = 1, $param = '')
    {
        if ((int)$num <= 0) {
            return false;
        }

        $command = self::COMMOND_BIN." ".$scriptname." {$param} >> ".self::LOG_OUTPUT." &";

        for ($i = 0; $i < $num; $i++) {
            //进程启动失败重试，最多重试3次。
            for ($j = 1; $j <= 3; $j++) {
                exec($command, $output, $return_var);
                if ($return_var != 0) {
                    continue;
                }
                break;
            }
        }
    }


    /**
     * 根据进程ID,杀死一个进程。
     * SIGKILL 立即杀死
     * SIGTERM 优雅杀死
     *
     * @param $processid
     * return boolean
     */
    public static function killProcessByProcessId($processid, $kill_type = self::SIGTERM)
    {
        $command = "kill -s {$kill_type} ".$processid;
        exec($command, $output, $return_var);
        if ($return_var != 0) {
            return false;
        }

        return true;
    }


    /**
     * 重启
     */
    public static function restart()
    {

    }


    /**
     * 获取CPU 的个数
     */
    private static function getCpuNum()
    {

    }

    /**
     * 获取CPU的核心数
     */

    private static function getCpuCores()
    {

    }
}