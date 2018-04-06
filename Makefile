src/ipsc:
	make -C src
clean: 
	make -C src clean

install: src/ipsc
	mkdir -p /usr/local/lib/ipsc
	cp -rv libs/* /usr/local/lib/ipsc/
	cp -rv oi src/ipsc /usr/local/bin/
	[ -f /www/ips.php ] && cp -v ips.php /www/ || :

