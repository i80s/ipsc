<?php

include(__DIR__ . "/IpLocation.php");

use itbdw\Ip\IpLocation;

function get_ip_geoinfo_array($host) {
	$r = IpLocation::getLocation(gethostbyname($host));
	return array (@$r['country'], @$r['province'], @$r['city'], '', @$r['isp']);
}
function get_ip_geoinfo($host) {
	return implode(',', array_filter(get_ip_geoinfo_array($host)));
}


function __is_tty() {
	static $__cached_is_tty = null;

	if (is_null($__cached_is_tty)) {
		$pid = getmypid();
		$tty_file = readlink("/proc/$pid/fd/1");
		switch (substr($tty_file, 0, 8)) {
			case "/dev/pts":
			case "/dev/tty":
			case "/dev/pty":
				$__cached_is_tty = true;
				break;
			default:
				$__cached_is_tty = false;
		}
	}

	return $__cached_is_tty;
}

function __is_public_ip($ip) {
	$ip = explode('.', $ip);
	$n = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];
	if ($n < 16777216) return false;
	if ($n >= 167772160 && $n < 184549376) return false;
	if ($n >= 1681915904 && $n < 1686110208) return false;
	if ($n >= 2130706432 && $n < 2147483648) return false;
	if ($n >= 2886729728 && $n < 2887778304) return false;
	if ($n >= 3232235520 && $n < 3232301056) return false;
	if ($n >= 3758096384) return false;
	return true;
}

function overlay_ip_text() {
	while ($raw_text = fgets(STDIN)){
		$raw_text = rtrim($raw_text);
		if (preg_match_all('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $raw_text, $ip_arrays)) {
			$ip_text = "";
			foreach ($ip_arrays[0] as $ip) {
				if (__is_public_ip($ip))
					$ip_text .= get_ip_geoinfo($ip) . " ";
			}
			$ip_text = rtrim($ip_text);
			if (empty($ip_text)) {
				echo "$raw_text\n";
			} else {
				if (__is_tty()) {
					echo "$raw_text  \033[32m${ip_text}\033[0m\n";
				} else {
					echo "$raw_text  $ip_text\n";
				}
			}
		} else {
			echo "$raw_text\n";
		}
	}
}

if (!isset($IPIP_PHP_INCLUDED)) {
	if (!isset($argv[1])) {
		die("*** Insufficient parameters.\n");
	} else if ($argv[1] == "-o") {
		overlay_ip_text();
	} else {
		echo get_ip_geoinfo($argv[1]) . "\n";
	}
}

?>
