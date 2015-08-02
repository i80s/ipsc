
#CROSS_COMPILE ?= arm-linux-
CC		:= $(CROSS_COMPILE)gcc
CXX		:= $(CROSS_COMPILE)g++
HEADERS	:= *.h
prefix	?= /usr/local

all: ipsc

ipsc: ipsc.o IpLocator.o
	$(CC) -o $@ $^
##-liconv

#Rules.make
%.o: %.c $(HEADERS)
	$(CC) $(CFLAGS) -c -o $@ $<

clean: 
	rm -f *.o ipsc

install: all
	mkdir -p $(DESTDIR)$(prefix)/bin $(DESTDIR)$(prefix)/share/ipsc
	cp -f ipsc tracert.sh $(DESTDIR)$(prefix)/bin/
	[ -e QQWry.Dat ] || wget http://rssn.cn/QQWry.Dat -O QQWry.Dat
	cp -f QQWry.Dat $(DESTDIR)$(prefix)/share/ipsc/
	[ -d /www ] && cp ips.php QQWry.Dat /www/ || :
