#!/bin/sh

if [ $# -lt 1 ]; then
	echo "*** Insufficient parameters." >&2
	exit 1
fi

ipip_info=`php /usr/local/share/17monipdb/ipip.php "$1" 2>/dev/null`
cz88_info=`ipsc "$1"`
carrier=""

for tag in 'BGP' 'bgp' '电信' '联通' '移动' '铁通' \
	'鹏博士' '电信通' '长城宽带' '长宽' '方正宽带' '教育网' \
	'CERNET' 'cernet' '赛尔'; do
	if expr "$cz88_info" : ".*$tag" >/dev/null; then
		carrier="$tag"
		break
	fi
done

if [ -n "$carrier" ]; then
	echo "$ipip_info,$carrier"
else
	echo "$ipip_info"
fi

