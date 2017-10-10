#!/bin/sh
zcDATE=\$(date +%Y%m%d)
mysqldump -hlocalhost -usiyuework -cheng6251 --databases blog > /usr/local/work/blog.sql