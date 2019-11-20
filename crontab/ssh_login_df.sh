#!/usr/bin/expect -f
set host     [lindex $argv 0]
set password [lindex $argv 1]

set timeout 10

spawn ssh $host df -h | grep -w ^/dev

expect {
    "(yes/no)?" {
        send "yes\r";
        expect "password:";
        send "${password}\r";
        exp_continue;
    }
    "password:" {
        send "${password}\r";
        exp_continue;
    }
}
interact
