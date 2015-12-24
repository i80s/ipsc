#!/bin/bash

text_ip_location()
{
	local line
	while read -r line; do
		# '[0-9]\+\.[0-9]\+\.[0-9]\+\.[0-9]\+'
		local ips=`echo "$line" | grep -o '\<[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}\>'`
		local ips_info="" ip
		for ip in $ips; do
			case "$ip" in
				10.*|172.16.*|172.17.*|172.18.*|172.19.*|172.2?.*|172.30.*|172.31.*| \
				192.168.*|127.*|0.*|224.*|225.*|226.*|227.*|228.*|229.*|23?.*|24?.*|25?.*)
					continue
					;;
			esac
			local info=`ipip.sh "$ip"`
			ips_info="$ips_info  $info"
		done


		if [ -n "$ips_info" ]; then
			echo "$line $ips_info"
		else
			echo "$line"
		fi
	done
}

if [ $# -eq 0 ]; then
	text_ip_location
else
	stdbuf -oL "$@" | text_ip_location
fi

