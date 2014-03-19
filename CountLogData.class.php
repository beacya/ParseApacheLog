<?php
/* *
 *  Class CountLogData
 * 统计数据
 * @author beacya
 * email  beacya@163.com
 * Date: 14-3-19
 * @Time: 下午3:25
 */
abstract class CountLogData extends ParseApacheLog {
    //读取日志文件
    public function readLog($LogFile){
        if(file_exists($LogFile)){
            $fp = fopen($LogFile, "r");
            $i = 1;
            while(!feof($fp)&&$i<100000){
                $log = fgets($fp);
                $logdata = $this->output($log);
                $this->countData($this->formatData($logdata));
                $i++;
            }
            fclose ($fp);
        }else{
            exit('日志文件路径错误！');
        }

    }

    //通过参数格式化数据
    abstract function formatData($logdata);

    //通过格式后的数据统计结果
    abstract function countData($formatData);
}

//根据时间统计IP
class CountIpByHour extends CountLogData{
    public $result = array();
    public function formatData($logdata){
        //IP
        if($this->AliasArr['%a']){
            $data['remoteip'] = $logdata[$this->AliasArr['%a']];
        }elseif($this->AliasArr['%h']){
            $data['remoteip'] = $logdata[$this->AliasArr['%h']];
        }else{
            if($logdata['%a']){
                $data['remoteip'] = $logdata['%a'];
            }else{
                $data['remoteip'] = $logdata['%h'];
            }
        }
        //time
        if($this->AliasArr['%t']){
            $data['time'] = $logdata[$this->AliasArr['%t']];
        }else{
            $data['time'] = $logdata['%t'];
        }
        //domain
        if($this->AliasArr['%r']){
            $data['request'] = $logdata[$this->AliasArr['%r']];
        }else{
            $data['request'] = $logdata['%r'];
        }
        return $data;
    }

    public function countData($formatData){
        if($formatData['remoteip']&&$formatData['time']&&$formatData['request']){
            //匹配域名
            preg_match_all('/utmac=(.*?)&/',$formatData['request'],$match);
            $domain = $match[1][0];
            //分域名按每个小时统计
            $HourTime = date("YmdH",strtotime($formatData['time']));
            //IP去重 写法
            //$this->result[$domain][$HourTime]['remoteip'] = 1;
            if($this->result[$domain][$HourTime]){
                $this->result[$domain][$HourTime] ++;
            }else{
                $this->result[$domain][$HourTime] = 1;
            }
        }
    }

    public function apply(){
        //结果写入文件
        //file_put_contents('result',serialize($this->result));
        //返回统计数据
        return $this->result;
    }
}
