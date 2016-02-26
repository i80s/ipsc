/************************************************************
 * 纯真IP数据库查询工具（GNU C）
 * Author: Justin Liu
 * Email : rssn@163.com
 * QQ    : 126027268
 ************************************************************/

#include <stdio.h>
#include <string.h>
//#include <conio.h>
#ifdef WIN32
	#include <winsock2.h>
	#include <windows.h>
	#pragma comment(lib ,"ws2_32.lib")
#else
	#include <sys/socket.h>
	#include <netinet/in.h>
	#include <arpa/inet.h>
	#include <netdb.h>
#endif

#include "IpLocator.h"

static unsigned int domain_atoul(const char * s)
{
	int u[4];
	unsigned int rv;
	int i;
	if(sscanf(s, "%d.%d.%d.%d", &u[0], &u[1], &u[2], &u[3]) == 4)
	{
		for(i=0, rv=0; i<4; i++)
		{
			rv <<= 8;
			rv |= u[i] & 0xff;
		}
		return rv;
	}
	else
	{
		struct hostent * he;
		he = (struct hostent *)gethostbyname(s);
		if(he)
			return ntohl(((struct in_addr *)he->h_addr_list[0])->s_addr);
		else
			return 0xffffffff;
	}
}


//IP所在地查询程序
void IpLocating(const char * fnData, const char * ipStr)
{
	unsigned int ipv;
	IpLocation ipl;
	char ipStart[sizeof("xxx.xxx.xxx.xxx")];
	char ipEnd[sizeof("xxx.xxx.xxx.xxx")];
	
	ipv=domain_atoul(ipStr);
	ipl=GetIpLocation(fnData,ipv);
	strcpy(ipStart,IntToIpString(ipl.IpStart));
	strcpy(ipEnd,IntToIpString(ipl.IpEnd));
	printf("IP Section : %s - %s\n",ipStart,ipEnd);
	printf("IP Location: %s %s\n",ipl.Country,ipl.City);
}

void PrintLocating(const char *fnData, const char *ipStr)
{
	unsigned int ipv;
	IpLocation ipl;
	
	ipv=domain_atoul(ipStr);
	ipl=GetIpLocation(fnData,ipv);
	printf("%s %s\n", ipl.Country, ipl.City);
}

//帮助信息
void printHelp(int argc, char * argv[])
{
	printf("Examples:\n");
	printf("    > %s 202.206.68.128\n",argv[0]);
	printf("    > %s -c CoralWry.txt\n",argv[0]);
	//printf("    > %s -s 河北科技大学\n",argv[0]);
}

int main(int argc, char * argv[])
{
	int i;
	//数据文件名
#ifdef WIN32
	const char * fnData="qqwry.dat";
    WSADATA wsaData;
    WORD wVersionRequested;
	wVersionRequested = MAKEWORD(2, 2);
    WSAStartup(wVersionRequested, &wsaData);
#else
	const char * fnData="/usr/lib/ipsc/qqwry.dat";
#endif

	if(argc>1)
	{
		char opcode='s';  //操作码 's'为查询号码, 'c'为解压数据
		const char *val="";    //参数值
		//获取操作码和参数值
		for(i=1;i<argc;i++)
		{
			if(argv[i][0]=='-')
				opcode=argv[i][1];
			else
				val=argv[i];
		}
		//操作选择
		switch(opcode)
		{
		case 's':
			//查询IP所在地
			PrintLocating(fnData,val);
			break;
		case 'c':
			//解压数据
			DataCompress(fnData,val);
			break;
		case 'h':
			//帮助信息
			printHelp(argc,argv);
			break;
		default:
			//无操作
			break;
		}
	}
	else
	{
		//直接双击运行
		char inputBuf[32];
		size_t len;
		for(;;)
		{
			printf("IP Address : ");
			//如果到文件尾则退出
			if(fgets(inputBuf,32,stdin) == NULL)
				break;
			len = strlen(inputBuf);
			if(inputBuf[len-1]=='\n')
				inputBuf[(len--)-1]='\0';
			
			IpLocating(fnData,inputBuf);
			printf("\n");
		}

	}
	
#ifdef WIN32
	WSACleanup();
#endif
	return 0;

}
