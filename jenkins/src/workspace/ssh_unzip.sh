#!/bin/bash
#
# 解压脚本执行
# 

export LANG=en_US.UTF-8
export LANGUAGE=en_US.UTF-8
export LC_ALL=en_US.UTF-8

# ${JOB_NAME} => 项目名称
# ${BUILD_NUMBER} => 构建版本号
# ${SVN_REVISION} => SVN版本号
# 压缩包存放路径
zip_path=/docker/src 
# 项目代码存放路径
project_path=/docker/src/klyzs/hanniu

if [ ! -d "${project_path}" ]; then
	mkdir ${project_path}
fi

cd ${zip_path}

unzip -o ${JOB_NAME}_${BUILD_NUMBER}_${SVN_REVISION}.zip -d ${project_path}

# rm -rf  ${JOB_NAME}_${BUILD_NUMBER}_${SVN_REVISION}.zip

if [  -f "${project_path}/del.sh" ];then
	. ${project_path}/del.sh
	rm -rf ${project_path}/del.sh
fi