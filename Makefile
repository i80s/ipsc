src/ipsc:
	make -C src
clean: 
	make -C src clean

install: src/ipsc
	mkdir -p /usr/local/lib/ipsc
	cp -av libs/* /usr/local/lib/ipsc/
	cp -av oi src/ipsc /usr/local/bin/
	[ -f /www/ips.php ] && cp -v ips.php /www/ || :

