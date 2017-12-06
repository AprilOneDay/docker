$(function() {
    //如果某个接口返回失败了 或存在BUG哦
    var btnBlock = true; //ajax提交堵塞 ture可提交 false堵塞中不可提交

    init();
    //监听product-nav-scene的宽度变化
    $(".product-nav-scene").bind("DOMNodeInserted",function(e){
        $('.content-main').width(($(document).width() - $('.sidebar-inner').width() - $('.product-nav-scene').width()) );
        return true;
    })
   
    $(window).resize(function(){
        if($('.product-nav-scene').css('display') == 'block'){
            $('.content-main').width(($(document).width() - 150 - 150));
        }else{
            $('.content-main').width(($(document).width() - 150));
        }
    });

   

    //主页容器固定高宽
    function init(){
        //一级栏目高度
        $('.sidebar-inner').height($(document).height() - $('.border-top').height() - 2);
        //二级栏目高度
        $('.product-nav-scene').height($(document).height() - $('.border-top').height() - 2)
        //主体显示高度
        $('.content-main').height($(document).height() - $('.border-top').height() - 2);
        //二级栏目如果可见
        if($('.product-nav-scene').css('display') == 'block'){
            $('.content-main').width(($(document).width() - 150 - 150));
        }else{
            $('.content-main').width(($(document).width() - 150));
        }

    }

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
        var data = $(this).attr('data-selected');
        if (data) {
            $(this).val(data);
        }
    });

    //绑定radio值
    $(".radio").each(function(){
         //不进行渲染
        var native = $(this).attr('data-native');
        if(native){
            return true;
        }

        var data = $(this).attr('data-radio');
        $(this).find('input[type=radio]').each(function(){
            if($(this).attr('value') == data){
                $(this).attr("checked","checked");
            }
        })
    })

    //tips提示
    $('[config-tooltip]').mouseover(function(){
        var msg = $(this).attr('config-tooltip');
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
        var native = $(this).attr('data-native');
        if(native){
            return true;
        }

        var data  = $(this).val();
        var value = $(this).attr('data-checked');
        if(typeof(value) != 'undefined' && value != ''){
            console.log(value)
            value =  jQuery.parseJSON(value);
        }

        if($.inArray(data,value) >= 0){
            $(this).attr("checked","checked");
        }
    })

    //checkBox单选
    $('.btn-checkbox-radio').each(function(){
        var _this = this;
        var name = $(this).attr('name');
        $(this).click(function(){
            $('input[name="'+name+'"]').prop('checked',false);
            $(this).prop('checked',true);
        })
    });

    //前置触发事件
    $('.btn-before').on('mousedown',function(){
        var eventString = $(this).attr('config-event'); 
        console.log(eventString);
        $('.form-horizontal').unbind();
        //eventString.preventDefault();
        return;
    });

   //打开弹出
    $('.btn-open').click(function() {
        var href = $(this).attr('config-href');
        var title = $(this).attr('config-title');
        var width = $(this).attr('config-width');
        var height = $(this).attr('config-height');

        if (!title) {
            title = $(this).text();
        }
        if (!width) {
            width = '890px';
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
        var form     = $(this).parents('.form-horizontal');
        var url      = form.attr('action');
        var trueUrl  = $(this).attr('config-true-url'); //执行成功跳转地址
        var falseUrl = $(this).attr('config-false-url'); //执行失败跳转地址
        var before   = $(this).attr('config-before');

        //处理checked 未选中不传值的问题
        $(form).find('input[type=checkbox]').each(function(){
            if($(this).attr('data-native')){
                return true;
            }
            var  falseValue = typeof($(this).attr('data-false-value')) != 'undefined' ?  $(this).attr('data-false-value') : 0;
            var  trueValue  = typeof($(this).attr('data-true-value')) != 'undefined' ?  $(this).attr('data-true-value') : 0;
            if(!$(this).prop('checked')){
                $(this).prop('checked',true);
                $(this).val(falseValue);
            }else{
                $(this).val(trueValue);
            }
        })

        var data     = form.serializeArray();
        if(data.length < 1){
            return layer.msg('请上传参数');
        }

        //处理通讯堵塞
        if(!checkBtnBlock()){
            return false;
        } 

        $.post(url,data,function(result){
            btnBlock = true; //恢复通道
            //恢复checkbox 未选中的样式
            $(form).find('input[type=checkbox]').each(function(){
                if($(this).attr('data-native')){
                    return true;
                }
                var  falseValue = typeof($(this).attr('data-false-value')) != 'undefined' ?  $(this).attr('data-false-value') : 0;
                var  trueValue  = typeof($(this).attr('data-true-value')) != 'undefined' ?  $(this).attr('data-true-value') : 0;
                if($(this).prop('checked',true) && $(this).val() == falseValue){
                    $(this).prop('checked',false);
                }
            })

            layer.msg(result.msg);
            if(result.status){
                setTimeout(function(){
                    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
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
        },"json")
    })

    //提交post信息
    $('.btn-ajax-post').click(function(){
        var attr     = $(this).context.attributes;  //获取执行参数
        var tips     = $(this).attr('config-tips');   //预先提示文案
        var url      = $(this).attr('config-href');   //执行地址
        var isReload = $(this).attr('config-reload'); //是否刷新当前页面
        var trueUrl  = $(this).attr('config-true-url'); //执行成功跳转地址
        var falseUrl = $(this).attr('config-false-url'); //执行失败跳转地址
        var data = new Object();
        for (var i = 0; i < attr.length; i++) {
            if(attr[i].localName.indexOf('data') !== -1 ){
                data[attr[i].localName.substr(5,attr[i].localName.length)] =  attr[i].value;
            }
        }

        if(tips){
            layer.confirm(tips, {
              btn: ['确定','取消'] //按钮
            }, function(){

                //处理通讯堵塞
                if(!checkBtnBlock()){
                    return false;
                } 

                $.post(url,data,function(result){
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
                },"json")
            }, function(){});
        }else{
            //处理通讯堵塞
            if(!checkBtnBlock()){
                return false;
            } 

            $.post(url,data,function(result){
                btnBlock = true; //恢复通道
                if(result.msg){
                    layer.msg(result.msg);
                }

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
            },"json")
        }

    })

    //get提交
    $('.btn-ajax-get').click(function(){
        var url = $(this).attr('config-href');
        $.get(url,function(result){
            layer.msg(result.msg);
            if(result.status){
                location.reload();
            }
        },"json");
    })

    

    //渲染图片上传插件
    $('.btn-ablum').each(function(){
        var _this   = this;
        var name    = $(this).attr('data-name');
        var maxNum  = Math.max($(this).attr('data-max'),1);
        var path    = $(this).attr('data-path'); 
        var content = '<input type="file" style="display:none;" id="'+name+'"  multiple="multiple"><div class="img-list" style="margin-top:20px;"><ul></ul></div>';
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
                var reader = new FileReader();
                reader.readAsDataURL(files[i]); 
                reader.onload = function(e){
                    $.post('/common/upload/up_base64_img',{data:e.target.result,path:path},function(result){
                        if(result.status){
                            var content = '<li style="float:left;width:150px;height:100px;margin-left:10px;margin-top:10px;"><img src="'+result.data+'" width="150" height="100" style="border:1px solid #ccc;"> <a style="float:right;margin-top:-100px;margin-right:2px;cursor: pointer;" class="btn-del-img"><i class="glyphicon glyphicon-remove"></i></a></li>';
                            $(_this).parent().find('.img-list ul').append(content);  
                        }

                        //删除照片
                        $('.btn-del-img').click(function(){
                            //console.log($(this).parent().html());
                            $(this).parent().remove();
                            bindValue();    
                        })

                        bindValue();    
                    },"json");
                }
            }
        })
        //绑定初始值
        bindValue();
        //删除照片
        $('.btn-del-img').click(function(){
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

    //渲染编辑器
    $('.ue-editor').each(function(){
        /*if($(this).index() == 0){
            $.getScript("/vendor/ueditor/ueditor.config.js"); 
            $.getScript("/vendor/ueditor/ueditor.all.js"); 
        }*/
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
                        value  = value +','+ result.data.name[0];
                        $(_this).parent().parent().find('.col-sm-8').append(fileHtml(result.data.name[0]));
                        initValue();
                    }

                    return layer.msg(result.msg);               
                },  
            });  

            //上传进度回调函数：  
            function progressHandlingFunction(e) {
                if (e.lengthComputable) {  
                    $('progress').attr({value : e.loaded, max : e.total}); //更新数据到进度条  
                    var percent = e.loaded/e.total*100; 
                    $('.progress-bar').css('width',percent+'%');
                    $('.progress-bar').text(percent+'%'); 
                }  
            } 

        });
        
    })
})
