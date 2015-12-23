CC ?= $(CROSS_COMPILE)gcc
HEADERS := IpLocator.h
prefix ?= /usr/local

all: ipsc

ipsc: ipsc.o IpLocator.o
	$(CC) -o $@ $^
##-liconv

%.o: %.c $(HEADERS)
	$(CC) $(CFLAGS) -c -o $@ $<

clean: 
	rm -f *.o ipsc

install: all
	[ -f QQWry.Dat ] || wget http://s.rssn.cn:1080/QQWry.Dat -O QQWry.Dat
	mkdir -p $(prefix)/bin $(prefix)/share/ipsc $(prefix)/share/17monipdb
	cp QQWry.Dat $(prefix)/share/ipsc/
	[ -d /www ] && cp ips.php QQWry.Dat /www/ || :
	cp ipip.php $(prefix)/share/17monipdb/
	cp ipsc ipip.sh overlay-ipinfo.sh tracert.sh $(prefix)/bin/

