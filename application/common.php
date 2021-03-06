<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//use think\Loader;

//random() 函数返回随机整数。
function random($length = 6 , $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

//获取字符串中第一张图片的地址
function getThumbImg($str){
	$pattern="/<[img|IMG|Img].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/"; 
	preg_match_all($pattern,$str,$match); 
	if(count($match[1])>0){
		return $match[1][0];
	}else{
		return false;
	}
}

//返回截取一定长度后的字符串
function getContentText($str,$length){
	if(mb_strwidth($str, 'utf8')>$length){
		$str = mb_strimwidth($str, 0, $length, '...', 'utf8');
	}
	return $str;
}

//生成唯一的主键ID
function uuid($prefix = '')  
  {  
    $chars = md5(uniqid(mt_rand(), true));  
    $uuid  = substr($chars,0,8) . '-';  
    $uuid .= substr($chars,8,4) . '-';  
    $uuid .= substr($chars,12,4) . '-';  
    $uuid .= substr($chars,16,4) . '-';  
    $uuid .= substr($chars,20,12);  
    return $prefix . $uuid;  
  }    

//返回交易类型说明
function getTransactionDesc($transaction_type){
	switch($transaction_type){
		case 0:
			return "新用户注册奖励";
			break;
		case 1:
			return "问答悬赏冻结";
			break;
		case 2:
			return "问答佣金冻结";
			break;
		case 3:
			return "问答悬赏解冻";
			break;
		case 4:
			return "问答佣金解冻";
			break;
		case 5:
			return "问答悬赏支付";
			break;
		case 6:
			return "问答悬赏佣金扣除";
			break;
		case 7:
			return "问答收入佣金扣除";
			break;
		case 8:
			return "问答分享奖励";
			break;
		case 9:
			return "问答悬赏入账";
			break;
		case 10:
			return "问答仲裁处理解冻";
			break;
		case 11:
			return "问答仲裁处理支付";
			break;
		case 12:
			return "问答仲裁处理入账";
			break;
		case 13:
			return "提现冻结";
			break;
		case 14:
			return "提现成功";
			break;
		case 15:
			return "提现失败，解冻返回";
			break;
		case 16:
			return "邀请奖励";
			break;
		case 17:
			return "发布阅读数奖励";
			break;
		case 99:
			return "系统操作扣除";
			break;
	}
}

//获取当前时间精确到微妙
function udate($format = 'u', $utimestamp = null) {
	if (is_null($utimestamp))
	   $utimestamp = microtime(true);
	
	$timestamp = floor($utimestamp);
	$milliseconds = round(($utimestamp - $timestamp) * 1000000);
	
	return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
}

//获取流水号
function getSerialNumber (){ 
	$year_code = array ('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','I','S','T','U','V','W','X','Y','Z'); 
	return $year_code[intval(date('Y'))-2018]. 
	strtoupper(dechex (date('m'))).date('d'). 
	substr(time(),-5).substr(microtime(),2,5).sprintf('%02d',rand (0,99)); 
}

//获取两个日期的差值
function DateDiff($part, $begin, $end)
{
$diff = strtotime($end) - strtotime($begin);
switch($part)
{
case "y": $retval = bcdiv($diff, (60 * 60 * 24 * 365)); break;
case "m": $retval = bcdiv($diff, (60 * 60 * 24 * 30)); break;
case "w": $retval = bcdiv($diff, (60 * 60 * 24 * 7)); break;
case "d": $retval = bcdiv($diff, (60 * 60 * 24)); break;
case "h": $retval = bcdiv($diff, (60 * 60)); break;
case "n": $retval = bcdiv($diff, 60); break;
case "s": $retval = $diff; break;
}
return $retval;
}

//返回增加的日期时间
function DateAdd($part, $n, $date)
{
switch($part)
{
case "y": $val = date("Y-m-d H:i:s", strtotime($date ." +$n year")); break;
case "m": $val = date("Y-m-d H:i:s", strtotime($date ." +$n month")); break;
case "w": $val = date("Y-m-d H:i:s", strtotime($date ." +$n week")); break;
case "d": $val = date("Y-m-d H:i:s", strtotime($date ." +$n day")); break;
case "h": $val = date("Y-m-d H:i:s", strtotime($date ." +$n hour")); break;
case "n": $val = date("Y-m-d H:i:s", strtotime($date ." +$n minute")); break;
case "s": $val = date("Y-m-d H:i:s", strtotime($date ." +$n second")); break;
}
return $val;
}