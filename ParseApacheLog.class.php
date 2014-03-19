<?php
/**
 * Class ParseApacheLog
 * apache日志解析
 * @author beacya
 * email  beacya@163.com
 * @Date: 14-3-19
 * @Time: 下午3:25
 */
class ParseApacheLog{
    public  $formatString   = ''; //格式数组
    public  $AliasArr       = array(); //别名数组

    function setFormat($formatString) {
        if($formatString){
            $this->formatString = $formatString;
        }else{
           return false;
        }

    }

    //输出按格式分类的日志数组
    public function output($logString){
        $result = $this->parse($logString);
        if($this->AliasArr){
            foreach($this->AliasArr  as $key=>$val){
                if($result[$key]&&$key!=$val){
                    $result[$val] = $result[$key];
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }

    //设置别名
    public function setAlias($key,$alias){
            $this->AliasArr[$key] = $alias;
    }

    //解析日志
    private function parse($logString){
        $formatArr = explode(' ',$this->formatString);
        $pattern = "/";
        $otherNum = 1;
        $formatNum = count($formatArr);
        foreach($formatArr as $key=>$val){
            //根据参数 匹配数据
            if(preg_match("/%t/",$val)){
              $breakArr = explode("%t",$val);
              if($breakArr[0]&&$breakArr[1]){
                  $pattern .= $breakArr[0];
                  $pattern .= "\[(.*?)\]";
                  $pattern .= $breakArr[1];
              }else{
                  $pattern .= "\[(.*?)\]";
              }
              $resultArr[] = '%t';
            }elseif(preg_match("/%r/",$val)){
                $breakArr = explode("%r",$val);
                if($breakArr[0]&&$breakArr[1]){
                    $pattern .= $breakArr[0];
                    $pattern .= "([GET|POST].*?[HTTPS?|S?FTP]\/\S{1,3})";
                    $pattern .= $breakArr[1];
                }else{
                    $pattern .= "([GET|POST].*?[HTTPS?|S?FTP]\/\S{1,3})";
                }
                $resultArr[] = '%r';
            }elseif(preg_match("/%a/",$val)||preg_match("/%A/",$val)||preg_match("/%h/",$val)){
                $breakArr = explode("%a",$val);
                if($breakArr[0]&&$breakArr[1]){
                    $pattern .= $breakArr[0];
                    $pattern .=  "(\d+.\d+.\d+.\d+)";
                    $pattern .= $breakArr[1];
                }else{
                    $pattern .=  "(\d+.\d+.\d+.\d+)";
                }
                if(preg_match("/%a/",$val)){
                    $resultArr[] = '%a';
                }elseif(preg_match("/%A/",$val)){
                    $resultArr[] = '%A';
                }elseif(preg_match("/%h/",$val)){
                    $resultArr[] = '%h';
                }
            }elseif(preg_match("/%u/",$val)){
                $breakArr = explode("%u",$val);
                if($breakArr[0]&&$breakArr[1]){
                    $pattern .= $breakArr[0];
                    $pattern .=  "(\S*)";
                    $pattern .= $breakArr[1];
                }else{
                    $pattern .=  "(\w+.\w+.\w+)";
                }
                $resultArr[] = '%u';
            }elseif(preg_match("/%{Referer}i/",$val)){
                $breakArr = explode("%{Referer}i",$val);
                if($breakArr[0]&&$breakArr[1]){
                    $pattern .= $breakArr[0];
                    $pattern .=  "(https?:\/\/[^\/]+.*?)";
                    $pattern .= $breakArr[1];
                }else{
                    $pattern .=  "(https?:\/\/[^\/]+.*?)";
                }
                $resultArr[] = '%{Referer}i';
            }elseif(preg_match("/%{User-Agent}i/",$val)){
                $breakArr = explode("%{User-Agent}i",$val);
                if($breakArr[0]&&$breakArr[1]){
                    $pattern .= $breakArr[0];
                    $pattern .= "(.*?)";
                    $pattern .= $breakArr[1];
                }else{
                        $pattern .= "(Mozilla\/[1-5].0.*?)";//没有加引号且不在末尾的user-agent
                }
                $resultArr[] = '%{User-Agent}i';
            }elseif(preg_match("/%>s/",$val)){
                $breakArr = explode("%>s",$val);
                if($breakArr[0]&&$breakArr[1]){
                    $pattern .= $breakArr[0];
                    $pattern .=  "(\d+)";
                    $pattern .= $breakArr[1];
                }else{
                    $pattern .=  "(\d+)";
                }
                $resultArr[] = '%>s';
            }elseif(preg_match("/%b/",$val)){
                $breakArr = explode("%b",$val);
                if($breakArr[0]&&$breakArr[1]){
                    $pattern .= $breakArr[0];
                    $pattern .=  "(\d+)";
                    $pattern .= $breakArr[1];
                }else{
                    $pattern .=  "(\d+)";
                }
                $resultArr[] = '%b';
            }elseif(preg_match("/%l/",$val)){
                $breakArr = explode("%l",$val);
                if($breakArr[0]&&$breakArr[1]){
                    $pattern .= $breakArr[0];
                    $pattern .=  "(\S*)";
                    $pattern .= $breakArr[1];
                }else{
                    $pattern .=  "(\S*)";
                }
                $resultArr[] = '%l';
            }elseif(preg_match("/%other/",$val)){
                $breakArr = explode("%other".$otherNum,$val);
                if($breakArr[0]&&$breakArr[1]){
                    $pattern .= $breakArr[0];
                    $pattern .=  "(.*?)";
                    $pattern .= $breakArr[1];
                }else{
                    $pattern .=  "(\S*)";
                }
                $resultArr[] = "%other".$otherNum;
                $otherNum++;
            }
            if($key!=($formatNum-1)){
                $pattern .= " ?";
            }
        }
        $pattern .= "/i";
        preg_match_all($pattern,$logString,$match);
        foreach($resultArr as $key=>$val){
            $result[$val] = $match[$key+1][0];
        }
        if($result['%r']){
            $RequestArr = explode(' ',$result['%r']);
            if(preg_match('/%m/',$this->formatString)){
                $result['%m'] = $RequestArr[0];
            }
            if(preg_match('/%H/',$this->formatString)){
                $result['%H'] = $RequestArr[2];
            }
            if(preg_match('/%U/',$this->formatString)){
                $result['%U'] = array_shift(explode("?",$RequestArr[1]));
            }
            if(preg_match('/%f/',$this->formatString)){
                preg_match_all('/\w+.\w+/',array_shift(explode("?",$RequestArr[1])),$match);
                $result['%f']  = $match[0][0];
            }
        }
        return $result;
    }
}

