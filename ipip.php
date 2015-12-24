<?php
	include("IP.class.php");
	if (sizeof($argv) < 2)
		die("*** Insufficient parameters.\n");
	$ip = gethostbyname($argv[1]);
	$ips = new IP();
	$result = $ips::find($ip);

	$ip_info = "";
	foreach ($result as $r) {
		if (!$r)
			continue;
		if ($ip_info) {
			$ip_info .= ",$r";
		} else {
			$ip_info = "$r";
		}
	}
	echo "$ip_info\n";
?>
