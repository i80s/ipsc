CC ?= $(CROSS_COMPILE)gcc
HEADERS := IpLocator.h

ipsc: ipsc.o IpLocator.o
	$(CC) -o $@ $^
##-liconv

%.o: %.c $(HEADERS)
	$(CC) $(CFLAGS) -c -o $@ $<

clean: 
	rm -f *.o ipsc

install: ipsc
	mkdir -p /usr/lib/ipsc
	@cp -v ipip.php qqwry.dat 17monipdb.dat /usr/lib/ipsc/
	@cp -v oi ipsc ipip /usr/bin/
	@[ -f /www/ips.php ] && cp -v ips.php /www/ || :

