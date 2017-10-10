#!/bin/sh
zcDATE=\$(date +%Y%m%d)
mysqldump -h127.0.0.1 -usiyue -prenyao748 --databases blog > /usr/local/work/blog.sql