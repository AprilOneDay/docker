# 修改密码
find / -name redis-cli 
cd reids根目录
./redis-cli
get name
config get requirepass
config set requirepass "新密码"
auth "新密码"
info
