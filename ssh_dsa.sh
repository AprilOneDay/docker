[root@zenghui expect]# cat ssh_dsa.exp
#!/usr/bin/expect
set ip [lindex $argv 0]
set pass [lindex $argv 1]
set port [lindex $argv 2]
set user [lindex $argv 3]
set id_dsa [lindex $argv 4]
 
if { $id_dsa == "1" } {set id "id_dsa_wdzj";set i "-i"
	spawn ssh $i $id -p $port $user@$ip "df -hP;free -m;uptime"
	expect {
	"*(yes/no)?"         {send  "yes\r"; exp_continue}
	"*password:"      { send "$pass\r"}
	"*id_dsa_wdzj':" { send "$pass\r"}
	}
}
if { $id_dsa == "0" } {
	spawn ssh -p $port $user@$ip "df -hP;free -m;uptime"
	expect {
	"*(yes/no)?"         {send  "yes\r"; exp_continue}
	"*password:"      { send "$pass\r"}
	}
}
 
expect eof