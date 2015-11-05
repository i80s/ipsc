#!/bin/bash

#############################################################
# CZ88 IP Address Locating Tool (GNU C)
#  Author: rssn
#  Email : rssn@163.com
#  QQ    : 126027268
#  Blog  : http://blog.csdn.net/rssn_net/
#############################################################

cd `dirname $0`

x_requires="which traceroute awk iconv ./ipsc"
for i in $x_requires; do
	which $i >/dev/null 2>/dev/null || { echo -e "*** Error: The program '$i' is required!"; exit 1; }
done

do_overlay_text()
{
	# Nothing to do with the first line:
	read m; echo $m
	while read m; do
		echo -ne "$m     "
		ip=`echo -n "$m" |grep -o '[0-9]\+\.[0-9]\+\.[0-9]\+\.[0-9]\+' |head -1`
		[ ! -z "$ip" ] && { echo -ne "\033[55G  "; ./ipsc "$ip"; } || echo
	done
}

do_traceroute()
{
	traceroute $* | ( do_overlay_text )
}

case "$1" in
	overlay) do_overlay_text;;
	*) do_traceroute "$@"
esac

