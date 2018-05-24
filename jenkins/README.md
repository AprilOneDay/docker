#!/bin/bash
#
# 打包脚本执行
# 

export LANG=en_US.UTF-8
export LANGUAGE=en_US.UTF-8
export LC_ALL=en_US.UTF-8

cd ..

if [ ! -d "pakage" ]; then
	mkdir pakage
fi


if [ ! -d "tmpPakage" ]; then
	mkdir tmpPakage
fi

rm -rf tmpPakage/*


/usr/bin/php /${JENKINS_HOME}/workspace/MakeAutoPk.php -v${BUILD_NUMBER} -n${JOB_NAME} -d/home/data/test


if [ ! "`ls -A tmpPakage`" = "" ]; then
	cd tmpPakage
	find . -type d -name ".svn"|xargs rm -rf
	zip -rq ${JENKINS_HOME}/workspace/pakage/${JOB_NAME}_${BUILD_NUMBER}.zip ./*
	cp ${JENKINS_HOME}/workspace/pakage/${JOB_NAME}_${BUILD_NUMBER}.zip ${WORKSPACE}
fi


#!/bin/bash
#
# 解压脚本执行
# 

export LANG=en_US.UTF-8
export LANGUAGE=en_US.UTF-8
export LC_ALL=en_US.UTF-8

project_path=/home/data/test

if [ ! -d "/home/data" ]; then
	mkdir /home/data
fi

if [ ! -d "${project_path}" ]; then
	mkdir ${project_path}
fi

cd /home/data

unzip -o ${JOB_NAME}_${BUILD_NUMBER}.zip -d ${project_path}

if [  -f "${project_path}/del.sh" ];then
	. ${project_path}/del.sh
	rm -rf ${project_path}/del.sh
fi