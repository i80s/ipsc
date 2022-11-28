<?php

error_reporting(0);

$time_start = getmicrotime();

$IPIP_PHP_INCLUDED = 1;
include("/usr/local/lib/ipsc/ipsc.php");

if (phpversion() >= 7.0) {
	function ereg($pattern, $text) {
		return preg_match("/$pattern/", $text);
	}
	function eregi($pattern, $text) {
		return preg_match("/$pattern/i", $text);
	}
}

function getmicrotime()
{
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

//获取脚本运行时间
function get_runtime()
{
	global $time_start;
	$time_end = getmicrotime();
	return round(($time_end-$time_start)*1000,3);
}

//用户浏览器
function get_user_browser($agent) 
{
	$expser=""; $expserver="";
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
	//$agent = $_SERVER['HTTP_USER_AGENT'];
	if (eregi('win',$agent)) {
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
	} else {
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

$search_ip = @$_GET['p'];

main();

//主函数入口
function main()
{
	switch (@$_GET['type']) {
	case "css":
		html_css();
		break;
	case "js":
		ips_js();
		break;
	default:
		if (strlen(@$_GET['p'])>0) {
			html_search();
		} else {
			html_default();
		}
	}
}

//输出JS格式的IP查询结果
function ips_js()
{
	global $search_ip;
	echo "document.write(\"" . get_ip_geoinfo($search_ip) . "\");";
}

//默认页：显示用户网络环境信息
function html_default()
{
	$server_ip = $_SERVER['SERVER_ADDR'];
	if (preg_match('/:/', $server_ip))
		$server_ip = "[$server_ip]";
	$server_port = $_SERVER['SERVER_PORT'];
	$remote_ip = $_SERVER['REMOTE_ADDR'];
	if (preg_match('/:/', $remote_ip))
		$remote_ip = "[$remote_ip]";
	$remote_port = $_SERVER['REMOTE_PORT'];
	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	$x_fwd_for_ip = '';
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$x_fwd_for_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if (isset($_SERVER['HTTP_X_REAL_IP'])) {
		$x_fwd_for_ip = $_SERVER['HTTP_X_REAL_IP'];
	}

	if (preg_match('/mozilla|w3m/i', $user_agent)) {
		/* From a web browser */
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
	<thead>
		<tr><th colspan="2">您的网络环境信息</th></tr>
	</thead>
	<tbody>
		<tr class="normal">
			<th>操作系统</th>
			<td><?= get_user_os($user_agent) ?></td>
		</tr>
		<tr class="hlight">
			<th>浏览器</th>
			<td><?= get_user_browser($user_agent) ?></td>
		</tr>
		<tr class="normal">
			<th>IP地址</th>
			<td><?= $remote_ip ?>:<?= $remote_port ?><?= $x_fwd_for_ip ? " ($x_fwd_for_ip)" : '' ?></td>
		</tr>
		<tr class="hlight">
			<th>IP所在地</th><td><?= get_ip_geoinfo($remote_ip) ?></td>
		</tr>
		<tr class="normal">
			<th>服务器</th><td><?= $server_ip ?>:<?= $server_port ?></td>
		</tr>
		<tr class="hlight">
			<th>User-Agent</th><td><?= $user_agent ?></td>
		</tr>
		<tr class="normal">
			<form action="" method="get">
				<td colspan="2">
					<input type="text" name="p" value="<?= $remote_ip ?>" size="20" />
					<input type="submit" value="查询IP所在地" />
				</td>
			</form>
		</tr>
	</tbody>
</table>
<div id="runtime">
	页面执行时间：<font color="red"><?= get_runtime() ?></font>毫秒
</div>
</body>
</html>
<?php
	} else {
		/* From wget, curl, ... */
		printf("OS: %s\n", get_user_os($user_agent));
		printf("Browser: %s\n", get_user_browser($user_agent));
		printf("IP address: %s:%d%s\n", $remote_ip, $remote_port, $x_fwd_for_ip ? " ($x_fwd_for_ip)" : '');
		printf("Server: %s:%s\n", $server_ip, $server_port);
		printf("Location: %s\n", get_ip_geoinfo($remote_ip));
		printf("User-Agent: %s\n", $user_agent);
	}
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
	<?php html_css(); ?>
</head>
<body>
<table id="info">
	<thead>
		<tr><th colspan="2">IP查询结果</th></tr>
	</thead>
	<tbody>
		<tr class="normal">
			<th>查询IP</th><td><?= $search_ip ?></td>
		</tr>
		<tr class="hlight">
			<th>IP所在地</th><td><?= get_ip_geoinfo($search_ip) ?> ( <?= $__ip_num ?> )</td>
		</tr>
		<tr class="normal">
			<form action="?" method="get">
				<td colspan="2">
					<input type="text" name="p" value="<?= $search_ip ?>" size="20" /> <input type="submit" value="查询IP所在地" />
				</td>
			</form>
		</tr>
	</tbody>
</table>
<div id="runtime">页面执行时间：<font color="red"><?= get_runtime() ?></font>毫秒</div>
</body>
</html>
<?php
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
