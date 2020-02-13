none:

clean:

install:
	mkdir -p /usr/local/lib/ipsc
	cp -rv libs/* /usr/local/lib/ipsc/
	cp -rv oi /usr/local/bin/
	[ -f /www/ips.php ] && cp -v ips.php /www/ || :

root@%:
	rsync libs/ $@:/usr/local/lib/ipsc/ -rvz
	rsync ips.php $@:/www/ -rv

