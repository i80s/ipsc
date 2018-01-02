SYNC_SERVERS := root@hk root@lax root@lax2 root@dev.ikuai8.com

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

up: ipsc
	$(foreach h,$(SYNC_SERVERS),rsync ipip.php qqwry.dat 17monipdb.dat $(h):/usr/lib/ipsc/ -av -z;)
	$(foreach h,$(SYNC_SERVERS),rsync oi ipsc ipip $(h):/usr/bin/ -av -z;)
	$(foreach h,$(SYNC_SERVERS),scp ips.php $(h):/www/;)
