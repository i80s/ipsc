<?php
	include("IP.class.php");
	if (sizeof($argv) < 2)
		die("*** Insufficient parameters.\n");
	$ip = gethostbyname($argv[1]);
	$ips = new IP();
	$result = $ips::find($ip);
	if ($result[3]) {
		echo "$result[0],$result[1],$result[2],$result[3]\n";
	} else {
		echo "$result[0],$result[1],$result[2]\n";
	}
?>
