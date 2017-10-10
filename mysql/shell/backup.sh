#!/bin/sh
zcDATE=\$(date +%Y%m%d)
mysqldump -hlocalhost -usiyue -prenyao748 --databases blog > /usr/local/work/blog.sql