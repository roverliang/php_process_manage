<?php

/**
 *  +----------------------------------------------------------------------
 *  | 进程控制服务
 *  +----------------------------------------------------------------------
 *  | Create Time : 2017年5月25日 10:57
 *  +----------------------------------------------------------------------
 *  | Author: roverliang <mr.roverliang@gmail.com>
 *  +----------------------------------------------------------------------
 */
class Process
{

    /**
     * 通过进程配置文件启动进程
     *
     * @param $process_config  进程配置文件
     */
    public static function StartProcessByProcessConfig($process_config)
    {
        self::KillProcessByProcessConfig($process_config);
        $message = "【run new process】";
        Colors::notice($message);

        foreach ($process_config as $process) {
            if (!$process['process_state']) {
                continue;
            }
            //先获取原有的进程列表
            $process_file = SCRIPT_PATH.$process['process_file'];
            Server::startProcessByRoot($process_file, $process['process_num']);
            $processArr = Server::getProcessIDs($process_file);

            if (!is_array($processArr) || empty($processArr)) {
                continue;
            }

            foreach ($processArr as $pid) {
                $message = "start {$process_file} Success, Process ID is: {$pid}";
                Colors::success($message);
            }
        }

        return true;
    }


    /**
     * 通过进程配置文件杀死进程
     *
     * @param $process_config  进程配置文件
     */
    public static function KillProcessByProcessConfig($process_config)
    {
        self::checkProcessConfig($process_config);

        foreach ($process_config as $process) {
            if (!$process['process_state']) {
                continue;
            }

            //先获取原有的进程列表
            $process_file    = SCRIPT_PATH.$process['process_file'];
            $process_old_arr = Server::getProcessIDs($process_file);

            if (!is_array($process_old_arr) || empty($process_old_arr)) {
                continue;
            }

            //杀死老进程
            foreach ($process_old_arr as $processid) {
                $return_var = Server::killProcessByProcessId($processid);
                if (!$return_var) {
                    $msg = "kill {$process_file} Failure, Process ID is: {$processid}";
                    Colors::error($msg);
                } else {
                    $msg = "kill {$process_file} Success, Process ID is: {$processid}";
                    Colors::success($msg);
                }
            }
        }


        return true;
    }

    /*
     * 主控进程。进程全部启动后。启动主控进程
     * 进程启动后，放进进程启动配置项中。如果监测到进程退出，则启动进程。
     */
    public static function startMonitoringProcess($monitoring_script, $mask)
    {
        if (!file_exists($monitoring_script)) {
            return false;
        }

        $param = "-s {$mask}";
        Server::startProcessByRoot($monitoring_script, 1, $param);
        $pidArr = Server::getProcessIDs($monitoring_script);

        if ($pidArr) {
            $msg = "【start monitoring process succes】";
            Colors::notice($msg);
        } else {
            $msg = "【start monitoring process failure】";
            Colors::error($msg);
        }
    }


    /**
     * 监控进程
     *
     * @param $process_config
     *
     * @return bool
     */
    public static function monitoringProcess($process_config)
    {
        if (!is_array($process_config) || empty($process_config)) {
            return false;
        }

        while (true) {
            foreach ($process_config as $configarr) {
                $absolute_script_path = SCRIPT_PATH.$configarr['process_file'];
                $runpidArr            = Server::getProcessIDs($absolute_script_path);  //监测到的进程

                //如果配置中的进程数量和实际的进程数量一致，则跳过后续监测
                if ($configarr['process_num'] == count($runpidArr)) {
                    continue;
                }

                //启动新的进程
                Server::startProcessByRoot($absolute_script_path, $configarr['process_num']);

                //如果监测到的进程为空，则无需杀死进程;
                if (!is_array($runpidArr) || empty($runpidArr)) {
                    continue;
                }

                //杀死原有的进程
                foreach ($runpidArr as $pid) {
                    $return_var = Server::killProcessByProcessId($pid);
                    if (!$return_var) {
                        echo "kill {$absolute_script_path} PID: {$pid} failure".PHP_EOL;
                    }
                }
            }
            sleep(MONITORING_PROCESS_SLEEP_TIME);  //3秒监测一次
        }
    }

    /*
     * 杀掉所有的监控进程
     */
    public static function KillMonitoringProcess($monitoring_script)
    {
        if (!file_exists($monitoring_script)) {
            return false;
        }

        $pidArr = Server::getProcessIDs($monitoring_script);

        if (!is_array($pidArr) || !$pidArr) {
            return false;
        }

        //防止多个监控进程形成死循环
        foreach ($pidArr as $pid) {
            for ($i = 0; $i < 10; $i++) {
                $return_var = Server::killProcessByProcessId($pid);
                if ($return_var) {
                    $msg = "【stop monitoring process success】";
                    Colors::notice($msg);
                    break;
                } else {
                    $msg = "【stop monitoring process failure】";
                    Colors::error($msg);
                }
            }
        }
    }


    /**
     *
     * @param $process_config  进程配置统计
     *
     * @return array
     */
    public static function processCount($process_config)
    {
        $config = 0;
        $run    = 0;
        foreach ($process_config as $processarr) {
            if (!$processarr['process_state']) {
                continue;
            }

            $process_file = SCRIPT_PATH.$processarr['process_file'];
            $runarr       = Server::getProcessIDs($process_file);
            $config       += $processarr['process_num'];
            $run          += (int)count($runarr);
        }
        return array('config' => $config, 'run' => $run);
    }


    /*
     * 进程配置列表检查
     */
    private static function checkProcessConfig($process_config)
    {
        if (!is_array($process_config) || empty($process_config)) {
            Colors::error('process config file is empty, please check it');
            die();
        }
    }

}