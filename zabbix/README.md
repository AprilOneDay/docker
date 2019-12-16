docker run -itd --name zabbix-web-nginx-mysql --network es-network --ip 172.19.0.8   \
      -e DB_SERVER_HOST="172.19.0.3" \
      -e MYSQL_DATABASE="zabbix" \
      -e MYSQL_USER="siyue" \
      -e MYSQL_PASSWORD="ihbdTjWY0xNQHXCmT41k2Ryxk3kMnQOJ" \
      -e MYSQL_ROOT_PASSWORD="ihbdTjWY0xNQHXCmT41k2Ryxk3kMnQOJ" \
      -p 8080:80 \
      zabbix/zabbix-web-nginx-mysql:latest 

docker run -itd --name zabbix-server-mysql --network es-network --ip 172.19.0.9 \
      -e DB_SERVER_HOST="172.19.0.3" \
      -e MYSQL_DATABASE="zabbix" \
      -e MYSQL_USER="siyue" \
      -e MYSQL_PASSWORD="ihbdTjWY0xNQHXCmT41k2Ryxk3kMnQOJ" \
      -e MYSQL_ROOT_PASSWORD="ihbdTjWY0xNQHXCmT41k2Ryxk3kMnQOJ" \
      -p 10051:10051 \
      zabbix/zabbix-server-mysql:latest