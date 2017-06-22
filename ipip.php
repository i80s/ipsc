<?php

/*
	全球 IPv4 地址归属地数据库(IPIP.NET 版)
	高春辉(pAUL gAO) <gaochunhui@gmail.com>
	Build 20141009 版权所有 IPIP.NET
	(C) 2006 - 2014 保留所有权利
	请注意及时更新 IP 数据库版本
	数据问题请加 QQ 群: 346280296
	Code for PHP 5.3+ only
*/

class IP
{
	private static $ip	 = NULL;

	private static $fp	 = NULL;
	private static $offset = NULL;
	private static $index  = NULL;

	private static $cached = array();

	public static function find($ip)
	{
		if (empty($ip) === TRUE)
		{
			return 'N/A';
		}

		$nip   = gethostbyname($ip);
		$ipdot = explode('.', $nip);

		if ($ipdot[0] < 0 || $ipdot[0] > 255 || count($ipdot) !== 4)
		{
			return 'N/A';
		}

		if (isset(self::$cached[$nip]) === TRUE)
		{
			return self::$cached[$nip];
		}

		if (self::$fp === NULL)
		{
			self::init();
		}

		$nip2 = pack('N', ip2long($nip));

		$tmp_offset = (int)$ipdot[0] * 4;
		$start = unpack('Vlen', self::$index[$tmp_offset] . self::$index[$tmp_offset + 1] . self::$index[$tmp_offset + 2] . self::$index[$tmp_offset + 3]);

		$index_offset = $index_length = NULL;
		$max_comp_len = self::$offset['len'] - 1024 - 4;
		for ($start = $start['len'] * 8 + 1024; $start < $max_comp_len; $start += 8)
		{
			if (self::$index{$start} . self::$index{$start + 1} . self::$index{$start + 2} . self::$index{$start + 3} >= $nip2)
			{
				$index_offset = unpack('Vlen', self::$index{$start + 4} . self::$index{$start + 5} . self::$index{$start + 6} . "\x0");
				$index_length = unpack('Clen', self::$index{$start + 7});

				break;
			}
		}

		if ($index_offset === NULL)
		{
			return 'N/A';
		}

		fseek(self::$fp, self::$offset['len'] + $index_offset['len'] - 1024);

		self::$cached[$nip] = explode("\t", fread(self::$fp, $index_length['len']));

		return self::$cached[$nip];
	}

	private static function init()
	{
		if (self::$fp === NULL)
		{
			self::$ip = new self();

			self::$fp = fopen(__DIR__ . '/17monipdb.dat', 'rb');
			if (self::$fp === FALSE)
			{
				throw new Exception('Invalid 17monipdb.dat file!');
			}

			self::$offset = unpack('Nlen', fread(self::$fp, 4));
			if (self::$offset['len'] < 4)
			{
				throw new Exception('Invalid 17monipdb.dat file!');
			}

			self::$index = fread(self::$fp, self::$offset['len'] - 4);
		}
	}

	public function __destruct()
	{
		//if (self::$fp !== NULL)
		//{
		//	fclose(self::$fp);
		//}
	}
}

?>
<?php
//===================================
//
// 功能：IP地址获取真实地址函数
// 参数：$ip - IP地址
// 作者：[Discuz!] (C) Comsenz Inc.
//
//===================================
function cz88_query($ip) {
	$dat_path = __DIR__ . '/qqwry.dat';

	//检查IP地址
	if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
		return 'IP Address Error';
	}
	//打开IP数据文件
	if ( !($fd = @fopen($dat_path, 'rb')) ){
		return 'IP date file not exists or access denied';
	}

	//分解IP进行运算，得出整形数
	$ip = explode('.', $ip);
	$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

	//获取IP数据索引开始和结束位置
	$DataBegin = fread($fd, 4);
	$DataEnd = fread($fd, 4);
	$ipbegin = implode('', unpack('L', $DataBegin));
	if ($ipbegin < 0)
		$ipbegin += pow(2, 32);
	$ipend = implode('', unpack('L', $DataEnd));
	if ($ipend < 0) $ipend += pow(2, 32);
	$ipAllNum = ($ipend - $ipbegin) / 7 + 1;
	
	$BeginNum = 0;
	$EndNum = $ipAllNum;

	$ip1num = 0; $ip2num = 0; $ipAddr1 = ''; $ipAddr2 = '';
	//使用二分查找法从索引记录中搜索匹配的IP记录
	while ($ip1num>$ipNum || $ip2num<$ipNum) {
		$Middle= intval(($EndNum + $BeginNum) / 2);

		//偏移指针到索引位置读取4个字节
		fseek($fd, $ipbegin + 7 * $Middle);
		$ipData1 = fread($fd, 4);
		if (strlen($ipData1) < 4) {
			fclose($fd);
			return 'System Error';
		}
		//提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
		$ip1num = implode('', unpack('L', $ipData1));
		if ($ip1num < 0) $ip1num += pow(2, 32);
		
		//提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
		if ($ip1num > $ipNum) {
			$EndNum = $Middle;
			continue;
		}
		
		//取完上一个索引后取下一个索引
		$DataSeek = fread($fd, 3);
		if (strlen($DataSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
		fseek($fd, $DataSeek);
		$ipData2 = fread($fd, 4);
		if (strlen($ipData2) < 4) {
			fclose($fd);
			return 'System Error';
		}
		$ip2num = implode('', unpack('L', $ipData2));
		if($ip2num < 0) $ip2num += pow(2, 32);

		//没找到提示未知
		if ($ip2num < $ipNum) {
			if($Middle == $BeginNum) {
				fclose($fd);
				return 'Unknown';
			}
			$BeginNum = $Middle;
		}
	}

	//下面的代码读晕了，没读明白，有兴趣的慢慢读
	$ipFlag = fread($fd, 1);
	if ($ipFlag == chr(1)) {
		$ipSeek = fread($fd, 3);
		if(strlen($ipSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
		fseek($fd, $ipSeek);
		$ipFlag = fread($fd, 1);
	}

	if ($ipFlag == chr(2)) {
		$AddrSeek = fread($fd, 3);
		if (strlen($AddrSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$ipFlag = fread($fd, 1);
		if ($ipFlag == chr(2)) {
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

		while (($char = fread($fd, 1)) != chr(0))
			$ipAddr2 .= $char;

		$AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
		fseek($fd, $AddrSeek);

		while (($char = fread($fd, 1)) != chr(0))
			$ipAddr1 .= $char;
	} else {
		fseek($fd, -1, SEEK_CUR);
		while (($char = fread($fd, 1)) != chr(0))
			$ipAddr1 .= $char;

		$ipFlag = fread($fd, 1);
		if ($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if (strlen($AddrSeek2) < 3) {
				fclose($fd);
				return 'System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}
		while (($char = fread($fd, 1)) != chr(0)){
			$ipAddr2 .= $char;
		}
	}
	fclose($fd);

	//最后做相应的替换操作后返回结果
	if (preg_match('/http/i', $ipAddr2)) {
		$ipAddr2 = '';
	}
	$geoinfo = "$ipAddr1 $ipAddr2";
	$geoinfo = preg_replace('/CZ88.Net/is', '', $geoinfo);
	$geoinfo = preg_replace('/^s*/is', '', $geoinfo);
	$geoinfo = preg_replace('/s*$/is', '', $geoinfo);
	if (preg_match('/http/i', $geoinfo) || $geoinfo == '') {
		$geoinfo = 'Unknown';
	}

	return iconv("GB2312", "UTF-8", $geoinfo);
}
?>
<?php

function get_ip_geoinfo($host)
{
	$isp_tags = array(
		"BGP"      => "BGP",
		"bgp"      => "BGP",
		"电信通"   => "鹏博士",
		"电信"     => "电信",
		"联通"     => "联通",
		"移动"     => "移动",
		"铁通"     => "铁通",
		"教育网"   => "教育网",
		"CERNET"   => "教育网",
		"cernet"   => "教育网",
		"鹏博士"   => "鹏博士",
		"长城宽带" => "鹏博士",
		"长宽"     => "鹏博士",
		"宽带通"   => "鹏博士",
		"方正宽带" => "方正宽带",
		"歌华有线" => "歌华有线",
		"光环新网" => "光环新网",
		"华数宽带" => "华数宽带",
		"华数传媒" => "华数宽带",
		"东方有线" => "东方有线",
		"中信网络" => "中信网络",
		"天威宽带" => "天威宽带",
		"天威视讯" => "天威宽带",
		);

	$ip = gethostbyname($host);

	/* Query IPIP.net for geo location */
	$ipip = new IP();
	$ipip_array = $ipip::find($ip);

	$ct = $ipip_array[0]; $st = $ipip_array[1];
	if (($ct == "中国" || $ct == "China") && ($st != "台湾" && $st != "香港" &&
		$st != "澳门" && $st != "Taiwan" && $st != "Hong Kong" && $st != "Macau")) {
		foreach ($isp_tags as $tag => $real_isp) {
			/* Query CZ88.net for ISP */
			$cz88_info = cz88_query($ip);
			if (strrpos($cz88_info, $tag)) {
				$ipip_array[4] = $real_isp;
				break;
			}
		}
	}

	/* Text format output */
	$ip_info_text = "";
	foreach ($ipip_array as $r) {
		if (!$r)
			continue;
		if ($ip_info_text) {
			$ip_info_text .= ",$r";
		} else {
			$ip_info_text = "$r";
		}
	}

	return $ip_info_text;
}

if (!isset($IPIP_PHP_INCLUDED)) {
	if (sizeof($argv) < 2)
		die("*** Insufficient parameters.\n");
	echo get_ip_geoinfo($argv[1]) . "\n";
}

?>
