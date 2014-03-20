<?php
/* *
 * 示例用法
 * @author beacya
 * @email  beacya@163.com
 * Date: 14-3-19
 * @Time: 下午3:25
 */
require_once('ParseApacheLog.class.php');
require_once('CountLogData.class.php');
//apachelog参数 根据你的配置来拼
$format = "%a %u %l %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" \"%other1\" \"%other2\"";
$UrchinParse = new CountIpByHour();
$UrchinParse->setFormat($format);
$UrchinParse->readLog('logFileName');
$result = $UrchinParse->apply();
var_dump($result);



