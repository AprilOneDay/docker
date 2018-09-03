#!/bin/bash
#
# 打包脚本执行
# 
# dos2unix /docker/jenkins/src/workspace/pakage.sh

export LANG=en_US.UTF-8
export LANGUAGE=en_US.UTF-8

# 默认值
if [ ! $3 ]; then  
       $3=0  
fi  

project=$1 # kluser
project_path=$2 # /docker/src/kluser
is_send=$3 # 是否发送钉钉机器人消息
TMP_PAKAGE=tmp_${project}_pakage

cd ${JENKINS_HOME}/workspace



if [ ! -d "pakage" ]; then
	mkdir pakage
fi

if [ ! -d "${TMP_PAKAGE}" ]; then
	mkdir ${TMP_PAKAGE}
fi

rm -rf ${TMP_PAKAGE}/*

/usr/bin/php /${JENKINS_HOME}/workspace/MakeAutoPk.php -v${BUILD_NUMBER} -n${JOB_NAME} -d${project_path} -a0

if [ ! "`ls -A ${TMP_PAKAGE}`" = "" ]; then
	cd ${TMP_PAKAGE}
	find . -type d -name ".svn"|xargs rm -rf
    
	zip -rq ${JENKINS_HOME}/workspace/pakage/${JOB_NAME}_${BUILD_NUMBER}_${SVN_REVISION}.zip ./*
	cp -r ${JENKINS_HOME}/workspace/pakage/${JOB_NAME}_${BUILD_NUMBER}_${SVN_REVISION}.zip ${WORKSPACE}
fi