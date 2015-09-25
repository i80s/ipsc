<?php

error_reporting(0);

$dat_path = 'QQWry.Dat';

//$mysql=array("localhost","root","adobe","coralip_2007","珊瑚虫 2007.2.20");
//$ipconn;

/*
//连接IP数据库
function ipdbconn()
{
	global $ipconn,$mysql;
	if($ipconn)
	{
		return false;
	}
	else
	{
		$ipconn=mysql_connect($mysql[0],$mysql[1],$mysql[2]);
		mysql_select_db($mysql[3],$ipconn);
		return true;
	}
}
//关闭IP数据库连接
function ipdbclose()
{
	global $ipconn;
	if(!$ipconn)
	{
		return false;
	}
	else
	{
		mysql_close($ipconn);
		$ipconn=false;
		return true;
	}
}
*/

//用户浏览器
function get_user_browser($agent) 
{
	$expser="";$expserver="";
	//$agent = $GLOBALS["HTTP_USER_AGENT"];
	if (ereg("Mozilla",$agent) && ereg("MSIE",$agent))
	{
		$temp = explode("(", $agent); $anc=$temp[1];
		$temp = explode(";",$anc); $anc=$temp[1];
		$temp = explode(" ",$anc);$expserver=$temp[2];
		$expserver =preg_replace("/([\d\.]+)/","\\1",$expserver);
		$expserver = " $expserver";
		$expser = "Internet Explorer";
	}
	elseif (ereg("Mozilla",$agent) && !ereg("MSIE",$agent))
	{
		$temp =explode("(", $agent); $anc=$temp[0];
		$temp =explode("/", $anc); $expserver=$temp[1];
		$temp =explode(" ",$expserver); $expserver=$temp[0];
		$expserver =preg_replace("/([\d\.]+)/","\\1",$expserver);
		$expserver = " $expserver";
		if (eregi('Opera',$agent))
			$expser = "Opera";
		elseif (eregi('Netscape',$agent))
			$expser = "Netscape";
		elseif (eregi('Firefox',$agent))
		{
			//$expser = "Firefox";
			$expserver = '';
			$expser = str_replace('/', ' ', substr($agent, strpos(strtolower($agent), 'firefox')));
		}
		elseif (eregi('rv:',$agent))
			$expser = "rv:";
		else
			$expser = "Netscape Navigator";
	}
	if ($expser!="")
		$expseinfo = "{$expser}{$expserver}";
	else
		$expseinfo = "Unknown";
	return $expseinfo;
}

//用户操作系统
function get_user_os($agent)
{
	$sys="Unknown";
	//$agent = $_SERVER["HTTP_USER_AGENT"];
	if (eregi('win',$agent))
	{
		$sys="Windows ";
		if(eregi('nt 5\.1',$agent))
			$sys.="XP";
		elseif (ereg('98',$agent))
			$sys.="98";
		elseif (eregi('nt 5\.0',$agent))
			$sys.="2000";
		elseif (eregi('9x',$agent) && strpos($agent, '4.90'))
			$sys.="ME";
		elseif (strpos($agent, '95'))
			$sys.="95"; 
		elseif (eregi('nt 5\.2',$agent))
			$sys.="2003";
		elseif (eregi('nt 4\.0',$agent))
			$sys.="NT";
		elseif (ereg('32',$agent))
			$sys.="32";
		elseif (eregi('nt 6\.0',$agent))
			$sys.="Vista";
	}
	else
	{
		if (eregi('linux',$agent))
			$sys="Linux";
		elseif (eregi('unix',$agent))
			$sys="Unix";
		elseif (eregi('ibm',$agent) && eregi('os',$agent))
			$sys="IBM OS/2";
		elseif (eregi('NetBSD',$agent))
			$sys="NetBSD";
		elseif (eregi('BSD',$agent))
			$sys="BSD";
		elseif (eregi('FreeBSD',$agent))
			$sys="FreeBSD";
	}
	return $sys;
}

function get_ip_addr($ip)
{
	global $ipconn;
	//ipdbconn();
	//$ipv=get_ip_value($ip);
	//$sql="SELECT `city` FROM `ip` WHERE `ip1`<={$ipv} AND `ip2`>={$ipv}";
	////echo $sql;
	//$rs=mysql_query($sql,$ipconn);
	//if(!$row=mysql_fetch_array($rs))
	//{
	//	return "未知地址";
	//}
	//else
	//{
	//	return $row["city"];
	//}
	return iconv("GB2312", "UTF-8", convertip($ip));
}
function get_ip_value($ip)
{
	$ip_array=explode(".",get_real_ip($ip));
	return ($ip_array[0]*16777216+$ip_array[1]*65536+$ip_array[2]*256+$ip_array[3]);
}
function get_real_ip($ip)
{
	$ip_array=explode(".",$ip);
	for($i=0;$i<4;$i++)
	{
		if(!is_numeric($ip_array[$i])||$ip_array<0)
			$ip_array[$i]=0;
		elseif($ip_array[$i]>255)
			$ip_array[$i]=255;
		$ipv.=$ip_array[$i];
		if($i!=3) $ipv.=".";
	}
	return $ipv;
}
function get_ip_ver()
{
	//global $mysql;
	//return $mysql[4];
	return iconv("GB2312", "UTF-8", convertip('255.255.255.255') );
}


//===================================
//
// 功能：IP地址获取真实地址函数
// 参数：$ip - IP地址
// 作者：[Discuz!] (C) Comsenz Inc.
//
//===================================
function convertip($ip) {
	//IP数据文件路径
	global $dat_path;

	//检查IP地址
	if(!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
		return 'IP Address Error';
	}
	//打开IP数据文件
	if( !($fd = @fopen($dat_path, 'rb')) ){
		return 'IP date file not exists or access denied';
	}

	//分解IP进行运算，得出整形数
	$ip = explode('.', $ip);
	$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

	//获取IP数据索引开始和结束位置
	$DataBegin = fread($fd, 4);
	$DataEnd = fread($fd, 4);
	$ipbegin = implode('', unpack('L', $DataBegin));
	if($ipbegin < 0) $ipbegin += pow(2, 32);
	$ipend = implode('', unpack('L', $DataEnd));
	if($ipend < 0) $ipend += pow(2, 32);
	$ipAllNum = ($ipend - $ipbegin) / 7 + 1;
	
	$BeginNum = 0;
	$EndNum = $ipAllNum;

	//使用二分查找法从索引记录中搜索匹配的IP记录
	while($ip1num>$ipNum || $ip2num<$ipNum) {
		$Middle= intval(($EndNum + $BeginNum) / 2);

		//偏移指针到索引位置读取4个字节
		fseek($fd, $ipbegin + 7 * $Middle);
		$ipData1 = fread($fd, 4);
		if(strlen($ipData1) < 4) {
			fclose($fd);
			return 'System Error';
		}
		//提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
		$ip1num = implode('', unpack('L', $ipData1));
		if($ip1num < 0) $ip1num += pow(2, 32);
		
		//提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
		if($ip1num > $ipNum) {
			$EndNum = $Middle;
			continue;
		}
		
		//取完上一个索引后取下一个索引
		$DataSeek = fread($fd, 3);
		if(strlen($DataSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
		fseek($fd, $DataSeek);
		$ipData2 = fread($fd, 4);
		if(strlen($ipData2) < 4) {
			fclose($fd);
			return 'System Error';
		}
		$ip2num = implode('', unpack('L', $ipData2));
		if($ip2num < 0) $ip2num += pow(2, 32);

		//没找到提示未知
		if($ip2num < $ipNum) {
			if($Middle == $BeginNum) {
				fclose($fd);
				return 'Unknown';
			}
			$BeginNum = $Middle;
		}
	}

	//下面的代码读晕了，没读明白，有兴趣的慢慢读
	$ipFlag = fread($fd, 1);
	if($ipFlag == chr(1)) {
		$ipSeek = fread($fd, 3);
		if(strlen($ipSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
		fseek($fd, $ipSeek);
		$ipFlag = fread($fd, 1);
	}

	if($ipFlag == chr(2)) {
		$AddrSeek = fread($fd, 3);
		if(strlen($AddrSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return 'System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}

		while(($char = fread($fd, 1)) != chr(0))
			$ipAddr2 .= $char;

		$AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
		fseek($fd, $AddrSeek);

		while(($char = fread($fd, 1)) != chr(0))
			$ipAddr1 .= $char;
	} else {
		fseek($fd, -1, SEEK_CUR);
		while(($char = fread($fd, 1)) != chr(0))
			$ipAddr1 .= $char;

		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return 'System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}
		while(($char = fread($fd, 1)) != chr(0)){
			$ipAddr2 .= $char;
		}
	}
	fclose($fd);

	//最后做相应的替换操作后返回结果
	if(preg_match('/http/i', $ipAddr2)) {
		$ipAddr2 = '';
	}
	$ipaddr = "$ipAddr1 $ipAddr2";
	$ipaddr = preg_replace('/CZ88.Net/is', '', $ipaddr);
	$ipaddr = preg_replace('/^s*/is', '', $ipaddr);
	$ipaddr = preg_replace('/s*$/is', '', $ipaddr);
	if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
		$ipaddr = 'Unknown';
	}

	return $ipaddr;
}


////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////

/*
if($_SERVER["PHP_AUTH_USER"]!="admin")
{
	header("WWW-Authenticate: Basic relm=\"用户登录\"");
	header("HTTP/1.0 401 Unauthorized");
	exit();
}
*/

$time_start=getmicrotime();
$search_ip=$_REQUEST["p"];


main();
//ipdbclose();

//主函数入口
function main()
{
	switch($_GET["type"])
	{
		case "css": html_css(); break;
		case "js": ips_js(); break;
		default:
		{
			if(strlen($_GET["p"])>0) html_search();
			else
				html_default();
		}
	}
}

//输出JS格式的IP查询结果
function ips_js()
{
	echo "document.write(\"".get_ip_addr($search_ip)."\");";
}

//默认页：显示用户网络环境信息
function html_default()
{
	$user_ip=$_SERVER["REMOTE_ADDR"];
	$user_agent=$_SERVER["HTTP_USER_AGENT"];
?>
<html>
<head>
<title>显示您的网络环境信息</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="user-scalable=no, width=device-width, target-densityDpi=device-dpi,initial-scale=0.5, maximum-scale=0.5, minimum-scale=0.5;" />
<?php html_css();?>
</head>
<body>
<table id="info">
	<thead><tr><th colspan="2">您的网络环境信息</th></tr></thead>
	<tbody>
		<tr class="normal"><th>操作系统:</th><td><?=get_user_os($user_agent)?></td></tr>
		<tr class="hlight"><th>浏览器:</th><td><?=get_user_browser($user_agent)?></td></tr>
		<tr class="normal"><th>ＩＰ地址:</th><td><?=$_SERVER['REMOTE_ADDR'] ?> （服务器：<b><?=$_SERVER['SERVER_ADDR'] ?></b>）</td></tr>
		<tr class="hlight"><th>ＩＰ所在地:</th><td><?=get_ip_addr($user_ip)?></td></tr>
		<tr class="normal"><th>User-Agent:</th><td><?=$user_agent?></td></tr>
		<!-- <tr class="hlight"><th>Referrer:</th><td><?=$_SERVER['HTTP_REFERER'] ?></td></tr> -->
	<form action="?" method="get">
		<tr class="hlight"><td colspan="2">
<input type="text" name="p" value="<?=$_SERVER['REMOTE_ADDR'] ?>" size="20" />&nbsp;
<input type="submit" value="查询IP所在地" />
		</td></tr>
	</form>
	</tbody>
</table>
<div id="runtime">IP数据库：<font color="blue"><?=get_ip_ver()?></font> 页面执行时间：<font color="red"><?=get_runtime()?></font>毫秒</div>
</body>
</html>
<?php
}

//IP查询页
function html_search()
{
	global $search_ip;

	$__ip = explode('.', $search_ip);
	$__ip_num = $__ip[0] * 16777216 + $__ip[1] * 65536 + $__ip[2] * 256 + $__ip[3];

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="user-scalable=no, width=device-width, target-densityDpi=device-dpi,initial-scale=0.5, maximum-scale=0.5, minimum-scale=0.5;" />
<title>IP查询</title>
<?php html_css();?>
</head>
<body>
<table id="info">
	<thead><tr><th colspan="2">IP查询结果</th></tr></thead>
	<tbody>
		<tr class="normal"><th>查询ＩＰ：</th><td><?=get_real_ip($search_ip)?></td></tr>
		<tr class="hlight"><th>ＩＰ所在地：</th><td><?=get_ip_addr($search_ip)?> ( <?=$__ip_num?> )</td></tr>
	<form action="?" method="get">
		<tr class="normal"><td colspan="2">
	<input type="text" name="p" value="<?=get_real_ip($search_ip)?>" size="20" />&nbsp;<input type="submit" value="查询IP所在地" />
		</td></tr>
	</form>
	</tbody>
</table>
<div id="runtime">IP数据库：<font color="blue"><?=get_ip_ver()?></font> 页面执行时间：<font color="red"><?=get_runtime()?></font>毫秒</div>
</body>
</html>
<?php
}
//获取脚本运行时间
function get_runtime()
{
	global $time_start;
	$time_end=getmicrotime();
	return round(($time_end-$time_start)*1000,3);
}
function getmicrotime()
{
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

//CSS
function html_css()
{
?>
<style type="text/css">
a,body,td,th,table,div,input {font-family:Verdana,Arial,"宋体";font-size:12px;}
body {text-align:center;margin:0;padding:10;}
table#info {border-collapse: collapse;border:1px solid #6595D6;text-align:left;}
tr.normal {background:#FFFFFF;}
tr.hlight {background:#EEEEEE;}
td,th {padding:3px;border:1px solid #6595D6;}
thead th{padding:4px;background-color:#427FBB;color:#FFFFFF;text-align:center;}
tbody th{padding-left:10px;color:#000000;font-weight:normal;width:90px;}
tbody td{width:340px;}
input {height:20px;}
#runtime {margin-top:10px;}
#runtime a {color:blue;text-decoration:none;}
#runtime a:hover {text-decoration:underline;}
</style>
<?php
}
?>
