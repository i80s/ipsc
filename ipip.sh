#!/bin/sh

if [ $# -lt 1 ]; then
	echo "*** Insufficient parameters." >&2
	exit 1
fi

exec php /usr/lib/ipsc/ipip.php "$1"

