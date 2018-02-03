$(function() {
    //如果某个接口返回失败了 或存在BUG哦
    var btnBlock = true; //ajax提交堵塞 ture可提交 false堵塞中不可提交

    //判断通道是否堵塞 如果堵塞 返回提示文案 反之则变成堵塞
    function checkBtnBlock(msg = '请勿重复提交'){
        if(!btnBlock){
            layer.msg(msg); 
            return false; 
        }else{
            btnBlock = false;
        }

        return true;
    }

    //绑定初试信息
    $('select').each(function() {
        let data = $(this).attr('data-selected');
        if (data) {
            $(this).val(data);
        }
    });

    //绑定radio值
    $(".radio").each(function(){
         //不进行渲染
        let native = $(this).attr('config-native');
        if(native){
            return true;
        }

        let data = $(this).attr('data-radio');
        $(this).find('input[type=radio]').each(function(){
            if($(this).attr('value') == data){
                $(this).attr("checked","checked");
            }
        })
    })

    //tips提示
    $('[config-tooltip]').mouseover(function(){
        let msg = $(this).attr('config-tooltip');
        layer.tips(msg, this, {
          tips: [1, '#3595CC'],
          time: 10000
        });
    })
    $('[config-tooltip]').mouseout(function(){
        layer.closeAll('tips');
    })

    //绑定checkbox
    $('.checkbox').each(function(){
        //不进行渲染
        let native = $(this).attr('config-native');
        if(native){
            return true;
        }

        let data  = $(this).val();
        let value = $(this).attr('config-checked');
        let checkedValue = '';
        if(typeof(value) != 'undefined' && value != ''){
            checkedValue  = value.split(",");
        }

        if(checkedValue){
            if(jQuery.inArray(data,checkedValue) >= 0){
                $(this).attr("checked","checked");
            }
        }
        
    })

    //checkBox单选
    $('.btn-checkbox-radio').each(function(){
        let _this = this;
        let name = $(this).attr('name');
        $(this).click(function(){
            $('input[name="'+name+'"]').prop('checked',false);
            $(this).prop('checked',true);
        })
    });

    //前置触发事件
    $('.btn-before').on('mousedown',function(){
        let eventString = $(this).attr('config-event'); 
        console.log(eventString);
        $('.form-horizontal').unbind();
        //eventString.preventDefault();
        return;
    });

    //下拉联动
    $('.btn-linkage').change(function(){
        let value = $(this).val();
        let href = $(this).attr('config-href');
        let el = $(this).attr('config-el');

        $.post(href,{value:value},function(result){
            if(!result.status){
                return  layer.msg(result.msg);
            }

            if($(el).css('display') == 'none'){
                $(el).css('display','block');
            }

            $(el).find('select').html('');

            for(let key in result.data){
                let content = '<option value="'+key+'">'+result.data[key]+'</option>';
                $(el).find('select').append(content);
            }
        },"json");
    })

   //打开弹出
    $('.btn-open').click(function() {
        let href = $(this).attr('config-href');
        let title = $(this).attr('config-title');
        let width = $(this).attr('config-width');
        let height = $(this).attr('config-height');

        if (!title) {
            title = $(this).text();
        }
        if (!width) {
            width = '80%';
        }
        if (!height) {
            height = '80%';
        }
        if (!href) {
            layer.msg('请设置data-href的值');
            return false;
        }
    
        //iframe层
        layer.open({
            type: 2,
            title: title,
            shadeClose: true,
            shade: 0.8,
            fixed:true,
            area: [width, height],
            content: [href] //iframe的url
        });
    })

    //提交信息
    $('.btn-comply').click(function(){
        let form     = $(this).parents('.form-horizontal');
        let url      = form.attr('action');
        let trueUrl  = $(this).attr('config-true-url'); //执行成功跳转地址
        let falseUrl = $(this).attr('config-false-url'); //执行失败跳转地址
        let before   = $(this).attr('config-before');
        let falseValue,trueValue,data;

        data     = form.serializeArray();
        if(data.length < 1){
            return layer.msg('请上传参数');
        }
        
        //处理通讯堵塞
        if(!checkBtnBlock()){
            return false;
        } 

        $.post(url,data,function(result){
           submitThen(result);
        },"json")

        function submitThen(result){
            btnBlock = true; //恢复通道
            
            //恢复checkbox 未选中的样式
            checkBoxChecked();

            layer.msg(result.msg);
            if(result.status){
                setTimeout(function(){
                    let index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                    if(index){
                        parent.location.reload();
                        parent.layer.close(index);
                    }else{
                        if(trueUrl){
                            window.location.href= trueUrl;
                        }else{
                            location.reload();   
                        }
                    }
                },1000);
            }else if(!result.status && falseUrl){
                setTimeout(function(){
                    window.location.href= falseUrl;
                },1000);
            }
        }

        //处理checked 未选中不传值的问题
        function checkBoxChecked(){
            $(form).find('input[type=checkbox]').each(function(){
                if($(this).attr('config-native')){
                    return true;
                }
                falseValue = typeof($(this).attr('config-false-value')) != 'undefined' ?  $(this).attr('config-false-value') : 0;
                trueValue  = typeof($(this).attr('config-true-value')) != 'undefined' ?  $(this).attr('config-true-value') : 0;
                if(!$(this).prop('checked')){
                    $(this).prop('checked',true);
                    $(this).val(falseValue);
                }else{
                    $(this).val(trueValue);
                }
            })
        }
    })

    //提交post信息
    $('.btn-ajax-post').click(function(){
        let tagName = $(this).get(0).tagName;

        let attr        = $(this).context.attributes;  //获取执行参数
        let tips        = $(this).attr('config-tips');   //预先提示文案
        let url         = $(this).attr('config-href');   //执行地址
        let isReload    = $(this).attr('config-reload'); //是否刷新当前页面
        let trueUrl     = $(this).attr('config-true-url'); //执行成功跳转地址
        let falseUrl    = $(this).attr('config-false-url'); //执行失败跳转地址
        let data,inputName,inputType,inputValue;

        var data = new Object();
        for (let i = 0; i < attr.length; i++) {
            if(attr[i].localName.indexOf('data') !== -1 ){
                data[attr[i].localName.substr(5,attr[i].localName.length)] =  attr[i].value;
            }
        }

        //获取默认值
        if(tagName == 'INPUT' || tagName == 'SELECT'){
            inputName  = $(this).attr('name');
            if(tagName == 'INPUT'){
                inputType = $(this).attr('type');
                if(inputType == 'checkbox'){
                    if($(this).prop('checked') == true){
                        data[inputName] =  $(this).attr('config-true-value');
                    }else{
                        data[inputName] =  $(this).attr('config-false-value');
                    }
                    
                }else{
                    inputValue = $(this).val();
                    if(inputName){
                        data[inputName] = inputValue;
                    }   
                }
            }
        }

        if(tips){
            layer.confirm(tips, {
              btn: ['确定','取消'] //按钮
            }, function(){
                submit();
            }, function(){});
        }else{
            submit();
        }

        function submit(){
            //处理通讯堵塞
            if(!checkBtnBlock()){
                return false;
            } 

            $.post(url,data,function(result){
                submitThen(result);
            },"json")
        }

        function submitThen(result){
            btnBlock = true; //恢复通道
            layer.msg(result.msg);
            if(result.status){
                setTimeout(function(){
                    if(trueUrl){
                        window.location.href= trueUrl;
                    }else{
                        location.reload();
                    }
                },1000);
                
            }else if(!result.status && falseUrl){
                setTimeout(function(){
                    window.location.href= falseUrl;
                },1000);
            }

            if(isReload){
                setTimeout(function(){
                    location.reload();
                },1000);
            }   
        }

    })

    //get提交
    $('.btn-ajax-get').click(function(){
        let _this    = this;
        let tips     = $(this).attr('config-tips');   //预先提示文案
        if(tips){
            layer.confirm(tips, {
              btn: ['确定','取消'] //按钮
            }, function(){
                submit(); 
            }, function(){});  
        }else{
            submit();   
        }

        //提交处理
        function submit(){
            let isFrom   = Boolean($(_this).attr('config-from'));
            let isAsync  = Boolean($(_this).attr('config-async'));
            let form     = $(_this).parents('.form-horizontal');
            let url      = $(_this).attr('config-href');
            let falseValue,trueValue,param;
        
            //处理checked 未选中不传值的问题
            $(form).find('input[type=checkbox]').each(function(){
                if($(this).attr('config-native')){
                    return true;
                }

                falseValue = typeof($(this).attr('config-false-value')) != 'undefined' ?  $(this).attr('config-false-value') : 0;
                trueValue  = typeof($(this).attr('config-true-value')) != 'undefined' ?  $(this).attr('config-true-value') : 0;
                if(!$(this).prop('checked')){
                    $(this).prop('checked',true);
                    $(this).val(falseValue);
                }else{
                    $(this).val(trueValue);
                }
            })

            param     = form.serialize();
            url       +='?'+param;

            if(isAsync){
                $.get(url,function(result){
                    layer.msg(result.msg);
                    if(result.status){
                        layer.closeAll();
                    }
                },"json");
            }else{
                layer.closeAll();
                window.open(url);
            }
        }    
    })

    //渲染编辑器
    $('.ue-editor').each(function(){
        var id = $(this).attr('id');
        UE.getEditor(id);
    })

    //渲染时间插件
    $('.data-time').each(function(){
        var time       = $(this).val() * 1000;                    //int
        var min        = $(this).attr('data-min');         // string int
        var max        = $(this).attr('data-max');         // string int
        var format     = $(this).attr('data-format');
        var type       = $(this).attr('data-type');        //year month date time datetime
        var isNull     = $(this).attr('data-isnull');      //year month date time datetime

        if(!format){ format  = 'yyyy-MM-dd'; }
        if(!min){ min = '1900-1-1';}
        if(!max){ max = '2099-12-31';}
        if(!type){ type = 'date';}
        if(!time){ time = new Date();}
        if(!isNull){
            laydate.render({
              elem: this, //指定元素
              value:new Date(time),
              format:format,
              type:type,
              min:min,
              max:max,
            });
        }else{
            laydate.render({
              elem: this, //指定元素
              format:format,
              type:type,
              min:min,
              max:max,
            });
        }
        
    })

    //动态加载更多
    $('.btn-loadmore').on('click',function(){
        var url   = $(this).attr('config-href');
        var page  = $(this).attr('config-page');
        var elem  = $(this).attr('config-elem');
        var text  = $(this).attr('config-text');
        var _this = this;

        if(!url){
            return layer.msg('请绑定需要加载url');
        }

        if(!elem){
            return layer.msg('请绑定需要渲染对象');
        }

        $.post(url,{pageNo:page},function(result){
            if(result.length > 1){
                $(_this).attr('config-page',parseInt(page)+1);
                $(elem).append(result);
            }else{
                if(text){
                    $(_this).html(text);
                }
            }
        },'html')
    })

    //关闭弹窗
    $('#btn-close').on('click',function(){
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.close(index);
    });

    //渲染图片上传插件
    $('.btn-ablum').each(function(){
        var _this   = this;
        var name    = $(this).attr('data-name');
        var maxNum  = Math.max($(this).attr('data-max'),1);
        var path    = $(this).attr('data-path'); 
        var content = '<input type="file" style="display:none;" id="'+name+'"  multiple="multiple"><div class="img-list" style="margin-top:20px;"><ul></ul></div>';
        var progress = '<div class="progress" style="margin:0px; margin-top:5px;"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 1%;">1%</div></div>';
        var value   = $(this).attr('data-value');
        if(value != '' && typeof(value) != 'undefined'){
            value =  value.split(',');
        }

        if(maxNum == 1){
            var ablum   = '';
        }else{
            var ablum   = new Array();
        }
       
        $(_this).parent().append(content);

        //渲染初始图片
        for(var i=0;i<value.length;i++){
            value[i] = '/uploadfile/'+path+'/'+value[i]
            var content = '<li style="float:left;width:150px;height:100px;margin-left:10px;margin-top:10px;"><img src="'+value[i]+'" width="150" height="100" style="border:1px solid #ccc;"> <a style="float:right;margin-top:-100px;margin-right:2px;cursor: pointer;" class="btn-del-img"><i class="glyphicon glyphicon-remove"></i></a></li>';
            $(_this).parent().find('.img-list ul').append(content);
        }


        //上传
        $(_this).click(function(){
            $('input[id='+name+']').wrap('<form>').closest('form').get(0).reset();
            $('input[id='+name+']').trigger('click');
        })

        //转换图片url
        $('#'+name).change(function(e){
            var imgLength = $(_this).parent().find('.img-list ul img').length;
            var files = e.target.files || e.dataTransfer.files;
            if(maxNum  && maxNum < files.length + imgLength){
                 return layer.msg('最多只能传'+maxNum+'张图片');
            }

            for(var i=0;i<files.length;i++){

                var formData = new FormData();
                formData.append('file', files[i]);
                formData.append('path', path);
                formData.append('max_size', 100);
               
                //ajax异步上传  
                $.ajax({  
                    url: '/common/upload/up_file',  
                    type: 'POST',  
                    data: formData,
                    dataType: 'json',  
                    contentType: false, //必须false才会自动加上正确的Content-Type  
                    processData: false,  //必须false才会避开jQuery对 formdata 的默认处理  
                    xhr: function(){ //获取ajaxSettings中的xhr对象，为它的upload属性绑定progress事件的处理函数
                        //移除之前的上传进度条
                        $(_this).parent().find('.progress').remove();
                        //添加现在的进度条
                        layer.alert(progress);  
                        myXhr = $.ajaxSettings.xhr();  
                        if(myXhr.upload){ 
                            //检查upload属性是否存在  
                            //绑定progress事件的回调函数  
                
                            myXhr.upload.addEventListener('progress',progressHandlingFunction, false);   
                        }  
                        return myXhr; //xhr对象返回给jQuery使用  
                    },  
                    success: function(result){
                        if(result.status){
                            var url = '/uploadfile/'+path+'/'+result.data.name[0];
                            var content = '<li style="float:left;width:150px;height:100px;margin-left:10px;margin-top:10px;"><img src="'+url+'" width="150" height="100" style="border:1px solid #ccc;"> <a style="float:right;margin-top:-100px;margin-right:2px;cursor: pointer;" class="btn-del-img"><i class="glyphicon glyphicon-remove"></i></a></li>';
                            $(_this).parent().find('.img-list ul').append(content);  
                        }
                        bindValue();    
                        return layer.msg(result.msg);            
                    },  
                });  

                //上传进度回调函数：  
                function progressHandlingFunction(e) {
                    if (e.lengthComputable) {  
                        $('progress').attr({value : e.loaded, max : e.total}); //更新数据到进度条  
                        var percent = parseInt(e.loaded/e.total*100); 
                        $('.progress-bar').css('width',percent+'%');
                        $('.progress-bar').text(percent+'%'); 
                    }  
                } 
            }
        })
        //绑定初始值
        bindValue();

        //删除照片
        $('body').on('click','.btn-del-img',function(){
            $(this).parent().remove();
            bindValue();
        })
       
        function bindValue(){
            var data = new Array();
            $(_this).parent().find('.img-list').find('img').each(function(){
                var path  = $(this).attr('src');
                path  = path.substring(path.lastIndexOf("/")+1,path.length);
                if(path != 'nd.jpg'){
                    data[data.length] = path.substring(path.lastIndexOf("/")+1,path.length);
                }
            })

            var content = '<input type="hidden" name="'+name+'" value="'+data.join(',')+'" />';
            $('input[name='+name+']').remove();
            $(_this).parent().append(content);
        }
    })

    //上传文件
    $('.btn-files').each(function(){  
        var _this    = this;
        var name     = $(this).attr('data-name');
        var path     = $(this).attr('data-path'); 
        var content  = '<input type="file" style="display:none;" id="'+name+'"  multiple="multiple"><input type="hidden" name="'+name+'" >';
        var progress = '<div class="progress" style="margin:0px; margin-top:5px;"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 1%;">1%</div></div>';
        var value    = $(this).attr('data-value');
        var maxNum   = Number($(this).attr('data-max'));


        //DOM 显示文件
        var fileHtml = function(value){
            var  html = '<div class="form-inline" style="padding-bottom: 10px;"><input type="text" value="'+value+'" name="tmp'+name+'" class="form-control" style="width: 30rem;margin-left: 10px;margin-right: 10px;"> <a href="javascript:;" class="form-control-static del-files">删除</a></div>';
            return html;
        }

        //DOM 最终上传input
        var initValue = function(){
            let html = '';
            $(_this).parent().parent().find('input[name=tmp'+name+']').each(function(){
               html += ','+$(this).val();
            }) 
            
            html = html.substring(1,html.length);

            $('input[name='+name+']').val(html);
        }

        //删除已上传文件
        $('body').on('click','.del-files',function(){
            $(this).parent().find('input[name='+name+']').val();
            $(this).parent().remove();
            initValue();
        })

        //DOM上传保存信息 和上传插件
        if($('#'+name)){
            $(_this).parent().append(content);
        }

        if(value != ''){
           var  tmpValue =  value.split(',');
            for (var key in tmpValue){
                $(_this).parent().parent().find('.col-sm-8').append(fileHtml(tmpValue[key]));
                initValue();
            }
        }else{
            value = '';
        }


        //上传
        $(_this).click(function(){
            //判断是否达到最大上传数量
            if($('input[name=tmp'+name+']').length >= maxNum && maxNum > 0){
                return layer.msg('只可上传'+maxNum+'个文件');
            } 

            $('input[id='+name+']').wrap('<form>').closest('form').get(0).reset();
            $('input[id='+name+']').trigger('click');
        })

        $('#'+name).change(function(e){
            //获取资源
            var files = e.target.files || e.dataTransfer.files;
            //资源赋值
            var formData = new FormData();
            formData.append('file', files[0]);
            formData.append('path', path);
           
            //ajax异步上传  
            $.ajax({  
                url: '/common/upload/up_file',  
                type: 'POST',  
                data: formData,
                dataType: 'json',  
                contentType: false, //必须false才会自动加上正确的Content-Type  
                processData: false,  //必须false才会避开jQuery对 formdata 的默认处理  
                xhr: function(){ //获取ajaxSettings中的xhr对象，为它的upload属性绑定progress事件的处理函数
                    //移除之前的上传进度条
                    $(_this).parent().find('.progress').remove();
                    //添加现在的进度条
                    layer.alert(progress);  
                    myXhr = $.ajaxSettings.xhr();  
                    if(myXhr.upload){ 
                        //检查upload属性是否存在  
                        //绑定progress事件的回调函数  
            
                        myXhr.upload.addEventListener('progress',progressHandlingFunction, false);   
                    }  
                    return myXhr; //xhr对象返回给jQuery使用  
                },  
                success: function(result){
                    if(result.status){
                        var url = '/uploadfile/'+path+'/'+result.data.name[0];
                        value  = value +','+ url;
                        $(_this).parent().parent().find('.col-sm-8').append(fileHtml(url));
                        initValue();
                    }

                    return layer.msg(result.msg);               
                },  
            });  

            //上传进度回调函数：  
            function progressHandlingFunction(e) {
                if (e.lengthComputable) {  
                    $('progress').attr({value : e.loaded, max : e.total}); //更新数据到进度条  
                    var percent = parseInt(e.loaded/e.total*100); 
                    $('.progress-bar').css('width',percent+'%');
                    $('.progress-bar').text(percent+'%'); 
                }  
            } 

        });
        
    })
})
