/************************************************************
 * 纯真IP数据库查询工具（GNU C）
 * Author: rssn
 * Email : rssn@163.com
 * QQ    : 126027268
 * Blog  : http://blog.csdn.net/rssn_net/
 ************************************************************/

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#ifdef WIN32
	#include <windows.h>
	#define inline __inline
#else
	#include <arpa/inet.h>
	#include <iconv.h>
#endif
#include "IpLocator.h"

#define letobel(n)	(((((unsigned long)(n) & 0xff)) << 24) | \
					((((unsigned long)(n) & 0xff00)) << 8) | \
					((((unsigned long)(n) & 0xff0000)) >> 8) | \
					((((unsigned long)(n) & 0xff000000)) >> 24))
#define betolel(n)	letobel(n)

static inline unsigned long letohl(unsigned long n)
{
	n = letobel(n);
	return htonl(n);
}
static inline unsigned long betohl(unsigned long n)
{
	return htonl(n);
}

/*
//IP字符串 -> IP数值
unsigned int IpStringToInt(const char * ipStr)
{
	int t[4];
	unsigned int ipv;
	unsigned char * p=(unsigned char *)&ipv;
	sscanf(ipStr,"%d.%d.%d.%d",&t[0],&t[1],&t[2],&t[3]);
	p[0]=t[3]; p[1]=t[2]; p[2]=t[1]; p[3]=t[0];
	return ipv;
}
*/
//IP数值 -> IP字符串
char * IntToIpString(unsigned int ipv)
{
	static char ipStr[sizeof("xxx.xxx.xxx.xxx")];
	sprintf(ipStr,"%d.%d.%d.%d",
			(ipv >> 24) & 0xff,
			(ipv >> 16) & 0xff,
			(ipv >> 8) & 0xff,
			(ipv >> 0) & 0xff );
	return ipStr;
}
// 采用“二分法”搜索索引区, 定位IP索引记录位置
int getIndexOffset(FILE * fp, int fo, int lo, unsigned int ipv)
{
	int mo;    //中间偏移量
	unsigned int mv;    //中间值
	unsigned int fv,lv; //边界值
	unsigned int llv;   //边界末末值
	fseek(fp,fo,SEEK_SET);
	fread(&fv,4,1,fp); fv=letohl(fv);
	fseek(fp,lo,SEEK_SET);
	fread(&lv,4,1,fp); lv=letohl(lv);
	//临时作它用,末记录体偏移量
	mo=0; fread(&mo,3,1,fp); mo=letohl(mo); mo&=0xffffff;
	fseek(fp,mo,SEEK_SET);
	fread(&llv,sizeof(int),1,fp); llv=letohl(llv);
	//printf("%d %d %d %d %d %d %d \n",fo,lo,mo,ipv,fv,lv,llv);
	//边界检测处理
	if(ipv<fv)
		return -1;
	else if(ipv>llv)
		return -1;
	//使用"二分法"确定记录偏移量
	do
	{
		mo=fo+(lo-fo)/7/2*7;
		fseek(fp,mo,SEEK_SET);
		fread(&mv,sizeof(int),1,fp); mv=letohl(mv);
		if(ipv>=mv)
			fo=mo;
		else
			lo=mo;
		if(lo-fo==7)
			mo=lo=fo;
	} while(fo!=lo);
	return mo;
}
// 读取IP所在地字符串
char * __getString(char * strBuf, size_t strBufSz, FILE * fp)
{
	//byte Tag;
	//int Offset;
	//Tag=fp.ReadByte();
	char tag;
	int so;
	fread(&tag,1,1,fp);  // 无需转换字节序
	
	if(tag==0x01)   // 重定向模式1: 城市信息随国家信息定向
	{
		so=0; fread(&so,3,1,fp); so=letohl(so); so&=0xffffff;
		fseek(fp,so,SEEK_SET);
		return __getString(strBuf, strBufSz, fp);
	}
	else if(tag==0x02)   // 重定向模式2: 城市信息没有随国家信息定向
	{
		int tmo;
		so=0; fread(&so,3,1,fp); so=letohl(so); so&=0xffffff;
		//记下文件当前读位置
		tmo=ftell(fp);
		fseek(fp,so,SEEK_SET);
		__getString(strBuf, strBufSz, fp);
		fseek(fp,tmo,SEEK_SET);
		return strBuf;
	}
	else   // 无重定向: 最简单模式
	{
		fseek(fp,-1,SEEK_CUR);
		//读取字符串
		fread(strBuf,1,STR_BUFF_SIZE,fp);
		//修正文件指针
		fseek(fp,(long)strlen(strBuf)+1-STR_BUFF_SIZE,SEEK_CUR);
		return strBuf;
	}
}
char * getString(char * strBuf, size_t strBufSz, FILE * fp)
{
	char *rv;
#ifndef WIN32
	iconv_t cd;
	char *inBuf, *outBuf;
	size_t inLen, outLen;
	char dbuf[STR_BUFF_SIZE];
	int ret;
#endif
	rv = __getString(strBuf, strBufSz, fp);
#ifndef WIN32
	if((cd = iconv_open("UTF-8", "GB2312")) != (iconv_t)-1)
	{
		inBuf = rv; outBuf = dbuf;
		inLen = strlen(rv); outLen = STR_BUFF_SIZE;
		if(iconv(cd, &inBuf, &inLen, &outBuf, &outLen) == 0)
		{
			*outBuf = '\0';
			strcpy(rv, dbuf);
		}
		iconv_close(cd);
	}
#endif
	return rv;
}

//查询IP段和所在地
IpLocation GetIpLocation(const char * fnData, unsigned int ipv)
{
	static IpLocation sl = { 0, 0, (char *)"Unknown", (char *)"Unknown", };
	int fo,lo;   //首末索引偏移量
	int rcOffset;
	IpLocation ipl;
	
	FILE * fp=fopen(fnData,"rb");
	if(!fp)
	{
		return sl;
	}
	fread(&fo,4,1,fp); fo=letohl(fo);
	fread(&lo,4,1,fp); lo=letohl(lo);
	//获取索引记录偏移量
	rcOffset=getIndexOffset(fp,fo,lo,ipv);
	fseek(fp,rcOffset,SEEK_SET);
	if(rcOffset>=0)
	{
		int ro;   //记录体偏移量
		char strBuf[STR_BUFF_SIZE];
		fseek(fp,rcOffset,SEEK_SET);
		//读取开头IP值
		fread(&ipl.IpStart,sizeof(int),1,fp); ipl.IpStart=letohl(ipl.IpStart);
		//转到记录体
		ro=0; fread(&ro,3,1,fp); ro=letohl(ro); ro&=0xffffff;
		fseek(fp,ro,SEEK_SET);
		//读取结尾IP值
		fread(&ipl.IpEnd,sizeof(int),1,fp); ipl.IpEnd=letohl(ipl.IpEnd);
		getString(strBuf, STR_BUFF_SIZE, fp);
		ipl.Country = strdup(strBuf);  //new char[strlen(strBuf+1)];
		getString(strBuf, STR_BUFF_SIZE, fp);
		ipl.City = strdup(strBuf);  //new char[strlen(strBuf+1)];
	}
	else
	{
		//没找到
		ipl.IpStart=0;
		ipl.IpEnd=0;
		ipl.Country = (char *)"Unknown";
		ipl.City = (char *)"Unknown";
	}
	fclose(fp);
	return ipl;
}

//解压数据
void DataCompress(const char * fnData, const char * fnOut)
{
	static IpLocation sl = { 0, 0, (char *)"Unknown", (char *)"Unknown", };
	int fo,lo,mo,ro;
	unsigned int ipStart,ipEnd;
	char country[STR_BUFF_SIZE];
	char city[STR_BUFF_SIZE];
	char sStart[sizeof("xxx.xxx.xxx.xxx")];
	char sEnd[sizeof("xxx.xxx.xxx.xxx")];
	int rCount;   //记录计数
	FILE *fp, *fpo;
	
	fp=fopen(fnData,"rb");
	if(!fp)
	{
		fprintf(stderr, "Opening data file [%s] failed.", fnData);
		return;
	}
	fpo=fopen(fnOut,"w");
	//如果没有打开文件则输出到屏幕
	if(!fpo)
		fpo=stdout;
	fread(&fo,4,1,fp); fo=letohl(fo);
	fread(&lo,4,1,fp); lo=letohl(lo);
	rCount=0;   //记录计数
	for(mo=fo;mo<=lo;mo+=7)
	{
		fseek(fp,mo,SEEK_SET);
		fread(&ipStart,sizeof(int),1,fp); ipStart=letohl(ipStart);
		ro=0; fread(&ro,3,1,fp); ro=letohl(ro); ro&=0xffffff;
		fseek(fp,ro,SEEK_SET);
		fread(&ipEnd,sizeof(int),1,fp); ipEnd=letohl(ipEnd);
		getString(country, STR_BUFF_SIZE, fp);
		getString(city, STR_BUFF_SIZE, fp);
		//将IP值转化为字符串
		strcpy(sStart,IntToIpString(ipStart));
		strcpy(sEnd,IntToIpString(ipEnd));
		fprintf(fpo,"%s - %s  %s %s\n",sStart,sEnd,country,city);
		rCount++;
	}
	fprintf(fpo,"\nTotal records: %d\n",rCount);
	fclose(fp);
	if(fpo!=stdout)
		fclose(fpo);
}

