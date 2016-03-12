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
	mkdir -p /usr/bin /usr/lib/ipsc
	@cp -v ipip.php /usr/lib/ipsc/
	@cp -v ipsc ipip.sh overlay-ipinfo.sh tracert.sh /usr/bin/
	@[ -d /www ] && cp -v ips.php /www/ || :
	@cp -v qqwry.dat 17monipdb.dat /usr/lib/ipsc/

