#!/bin/bash
content='测试信息'
#curl -H "Content-Type:application/json" -d '{"msgtype": "text","text": {"content": "测试信息,  @15923882847"},"at": {"atMobiles": ["15923882847"], "isAtAll": false}}' https://oapi.dingtalk.com/robot/send?access_token=7a9e5d4da8e0c34f8d2d02b78891915a4f00ec294b42b16a9eadf06ffc740a59
curl -H "Content-Type:application/json" -d '{"msgtype": "text","text": {"content": "'${content}',  @15923882847"},"at": {"atMobiles": ["15923882847"], "isAtAll": false}}' https://oapi.dingtalk.com/robot/send?access_token=7a9e5d4da8e0c34f8d2d02b78891915a4f00ec294b42b16a9eadf06ffc740a59