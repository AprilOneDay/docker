# Nginx 负载均衡的
在http节点下，添加upstream节点
upstream linuxidc { 
  	ip_hash;
  	server 10.0.0.85:8980 weight=10 max_fails=2 fail_timeout=60s; 
  	server 10.0.6.108:7080 weight=5;
}

upstream linuxidc { 
  	ip_hash;
  	server 10.0.0.85:8980 weight=10 max_fails=2 fail_timeout=60s; 
  	server 10.0.6.108:7080 weight=5;
}

ip_hash 每个请求按访问ip的hash结果分配，这样每个访客固定访问一个后端服务器，可以解决session的问题

upstream 还可以为每个设备设置状态值，这些状态值的含义分别如下：
down 表示单前的server暂时不参与负载.
weight 默认为1.weight越大，负载的权重就越大。
max_fails ：允许请求失败的次数默认为1.当超过最大次数时，返回proxy_next_upstream 模块定义的错误.
fail_timeout : max_fails次失败后，暂停的时间。
backup： 其它所有的非backup机器down或者忙的时候，请求backup机器。所以这台机器压力会最轻。


server {
  listen       80;
  server_name  www.68hn.cn m.68hn.cn 68hn.cn;
  location / {
    proxy_set_header Host $host:$server_port;
    proxy_pass http://68hn;
    proxy_next_upstream http_502 http_503 http_504;
    proxy_next_upstream_tries 2;
  }
}

# docker run -d  --name=nginx -p 80:80 -p 443:443 -p 81:81 --privileged -v /home/src/:/var/www/html/ -v /home/nginx/conf/nginx.conf:/etc/nginx/nginx.conf  -v /home/nginx/conf/conf.d/:/etc/nginx/conf.d/ -v /home/nginx/conf/ssl/:/etc/nginx/ssl/  -v /home//nginx/log/:/var/log/nginx/  --link=php:php --link=php7.1:php7.1 --restart=always home_nginx:latest