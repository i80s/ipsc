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
	@[ -f qqwry.dat ] || wget http://s.rssn.cn:1080/qqwry.dat -O qqwry.dat
	mkdir -p /usr/bin /usr/lib/ipsc
	@cp -v ipip.php qqwry.dat /usr/lib/ipsc/
	@cp -v ipsc ipip.sh overlay-ipinfo.sh tracert.sh /usr/bin/
	@[ -d /www ] && cp -v ips.php /www/ || :

