#!/bin/bash

echo "current time is $(date -d "today" +"%Y-%m-%d-%H-%M-%S")"  >>/tmp/mem_auto_$(date +%Y%m%d).log
#系统分配的区总量 
mem_total=`free | grep "Mem:" |awk '{print $2}'` 
echo "mem_total is $mem_total " >>/tmp/mem_auto_$(date +%Y%m%d).log
#当前剩余的大小 
mem_free=`free | grep 'buffers/cache' | awk '{print $3}'`

echo "mem_free is $mem_free" >>/tmp/mem_auto_$(date +%Y%m%d).log
#当前已使用的used大小 
mem_used=`free -m | grep Mem | awk '{print  $3}'` 
echo "mem_used is $mem_used" >>/tmp/mem_auto_$(date +%Y%m%d).log
if (($mem_used != 0)); then 
 
#如果已被使用，则计算当前剩余free所占总量的百分比，用小数来表示，要在小数点前面补一个整数位0 
mem_per=0`echo "scale=2;$mem_free/$mem_total" ` 
echo "free percent is $mem_per" >>/tmp/mem_auto_$(date +%Y%m%d).log
DATA="$(date -d "today" +"%Y-%m-%d-%H-%M-%S") free percent is : $mem_per"
echo $DATA >> /tmp/mem_auto_$(date +%Y%m%d).log
echo $DATA >> /var/log/mem_detect.log
#设置的告警值为44%(即使用超过56%的时候告警)。 
mem_warn=0.4 
echo "mem_warn is $mem_warn"  >>/tmp/mem_auto_$(date +%Y%m%d).log
#当前剩余百分比与告警值进行比较（当大于告警值(即剩余44%以上)时会返回1，小于(即剩余不足44%)时会返回0 ） 
mem_now=`expr $mem_per \> $mem_warn` 
echo "剩余百分比与警告值比较 mem_now is $mem_now"  >>/tmp/mem_auto_$(date +%Y%m%d).log
echo "when mem_now is 1 , means mem is ok ! "  >>/tmp/mem_auto_$(date +%Y%m%d).log

echo "-----------------------------------" >>/tmp/mem_auto_$(date +%Y%m%d).log
#如果当前使用超过56%（即剩余小于44%，上面的返回值等于0），释放内存
if (($mem_now == 0)); then 
echo "but now the mem_now is 0 ,小于(即内存剩余不足44%)，所以清理内存, start to clear memery....." >>/tmp/mem_auto_$(date +%Y%m%d).log
sync
echo 1 > /proc/sys/vm/drop_caches
echo 2 > /proc/sys/vm/drop_caches
echo 3 > /proc/sys/vm/drop_caches
echo "---> start auto clear memery is OK ! $DATA , warn is $mem_warn ,小于(即内存剩余不足44%)，所以清理内存, " >>/tmp/mem_auto_$(date +%Y%m%d_%H).log

fi

#取当前空闲cpu百份比值（只取整数部分） 
cpu_idle=`top -b -n 1 | grep Cpu | awk '{print $4}' | cut -f 1 -d "."`
echo "cpu_idle percent is $cpu_idle，cpu 剩余量充足，警告值是当剩余不足30%时，自动清理内" >>/tmp/mem_auto_$(date +%Y%m%d).log
echo "================================================================" >>/tmp/mem_auto_$(date +%Y%m%d).log
#设置空闲cpu的告警值为30%，如果当前cpu使用超过70%（即剩余小于30%），立即发邮件告警,自动清理内存 
if (($cpu_idle < 30)); then
echo "cpu 剩余不足30% ，所以清理内存, start to clear memery....." >>/tmp/mem_auto_$(date +%Y%m%d).log
sync
echo 1 > /proc/sys/vm/drop_caches
echo 2 > /proc/sys/vm/drop_caches
echo 3 > /proc/sys/vm/drop_caches
echo "--->cpu used more than 70% ,so start auto clear memery is OK ! $DATA , warn is $mem_warn " >>/tmp/memstat_cpu_auto_$(date +%Y%m%d_%H).log

fi
fi
