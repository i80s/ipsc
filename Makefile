CC ?= $(CROSS_COMPILE)gcc
HEADERS := IpLocator.h

SYNC_SERVERS := root@devx root@lax.rssn.cn root@hk.rssn.cn

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
	rsync -e 'ssh -p13022' ipip.php qqwry.dat 17monipdb.dat root@apix:/usr/lib/ipsc/ -av -z
	rsync -e 'ssh -p13022' oi ipsc ipip root@apix:/usr/bin/ -av -z

