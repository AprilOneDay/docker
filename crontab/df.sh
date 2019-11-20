#!/bin/bash
#
ssh[0]='root@47.97.220.239 fdfd123123'
ssh[1]='root@121.40.249.24 1b211d9144d556c14ae6c3aeb8b66c4b'
text='**服务器硬盘空间提示**\n>'
for item in "${ssh[@]}" ; do
	
	host=`echo $item | cut -d" " -f1`
	password=`echo $item | cut -d" " -f2`

	expect -f ~/ssh_login_df.sh $host $password >~/ssh_df.log

	log=`sed -n l ~/ssh_df.log | grep -w ^/dev`
	#log=($log)
	
	echo $log	

	#text={$text/%\>/ }"#### 服务器：$host\n"

	#for val in "${log[*]}" ; do
	#    text=$text"${val}\n"
	#done


	#dfAll=`cat ~/ssh_df.log | grep -w ^/dev | awk '{sum += $2};END{print sum}'`
	#useAll=`cat ~/ssh_df.log | grep -w ^/dev | awk '{sum += $3};END{print sum}'`

	#text=$text"#### 服务器：$host\n> 总空间：${dfAll}G 使用空间：${useAll}G\n"
	text=$text"#### 服务器：$host\n> ${log//r$/n>}"
	#text=${text%*>}
done

echo $text

curl -H "Content-Type:application/json" -d "{\"msgtype\": \"markdown\",\"markdown\": {\"title\": \"服务器硬盘空间提示\",\"text\": \"${text}\"}}" https://oapi.dingtalk.com/robot/send?access_token=77c18f3981cc6f411ccdfb4792f8fc4574e4fe3d70c37617d78301fe77fd6dbf

# curl 'https://oapi.dingtalk.com/robot/send?access_token=db8a210c99ead2cb6c05a6ea1aacadd16c703dd88ae495778a1fde117ff9def0' \
#    -H 'Content-Type: application/json' \
#    -d '{"msgtype": "markdown", 
#         "markdown": {
#              "title": "服务器硬盘空间提示",
#              "text": "'${text}'"
#         }
#       }'
