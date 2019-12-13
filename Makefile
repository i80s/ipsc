none:

clean:

install:
	mkdir -p /usr/local/lib/ipsc
	cp -rv libs/* /usr/local/lib/ipsc/
	[ -f /www/ips.php ] && cp -v ips.php /www/ || :

up: root@hk.rssn.cn root@lax.rssn.cn root@dev.ikuai8.com

root@%:
	rsync libs/ $@:/usr/local/lib/ipsc/ -rvz
	rsync ips.php $@:/www/ -rv

