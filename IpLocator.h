/************************************************************
 * 纯真IP数据库查询工具（GNU C）
 * Author: rssn
 * Email : rssn@163.com
 * QQ    : 126027268
 * Blog  : http://blog.csdn.net/rssn_net/
 ************************************************************/

#ifndef __IPLOCATOR_H
#define __IPLOCATOR_H

#define STR_BUFF_SIZE 128

//IP查询结果结构体
typedef struct _IpLocation
{
	unsigned int IpStart;
	unsigned int IpEnd;
	char * Country;
	char * City;
} IpLocation;
#define TRUE  1
#define FALSE 0
//IP字符串 -> IP数值
extern unsigned int IpStringToInt(const char * ipStr);

//IP数值 -> IP字符串
extern char * IntToIpString(unsigned int ipv);

//查询IP段和所在地
extern IpLocation GetIpLocation(const char * fnData, unsigned int ipv);

//解压数据
extern void DataCompress(const char * fnData, const char * fnOut);

#endif /*__IPLOCATOR_H*/
