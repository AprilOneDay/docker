$(function() {

    init();
    //监听product-nav-scene的宽度变化
    $(".product-nav-scene").bind("DOMNodeInserted",function(e){
        $('.content-main').width(($(document).width() - $('.sidebar-inner').width() - $('.product-nav-scene').width()) );
        return true;
    })
    //监听content-main的宽度变化
    $(".content-main").bind("DOMNodeInserted",function(e){
        $('.content-main').width(($(document).width() - $('.sidebar-inner').width() - $('.product-nav-scene').width()) -2);
        return true;
    })

    //监听出现滚动条
    $(window).scroll(function () {
        $('.content-main').width(($(document).width() - $('.sidebar-inner').width() - $('.product-nav-scene').width()) );
        return true;
    })

    //主页容器固定高宽
    function init(){
        $('.sidebar-inner').height($(document).height() - $('.border-top').height() - 2);
        $('.product-nav-scene').height($(document).height() - $('.border-top').height() - 2)
        $('.content-main').height($(document).height() - $('.border-top').height() - 2);
        if($('.product-nav-scene').css('display') == 'block'){
            $('.content-main').width(($(document).width() - $('.sidebar-inner').width() - $('.product-nav-scene').width()) - 2);
        }else{
            $('.content-main').width(($(document).width() - $('.sidebar-inner').width()) - 2 );
        }

    }

    //收缩一级导航
    $('.sidebar-fold').click(function() {
        var width = $(this).width();
        if (width > 38) {
            $('.sidebar-inner').width(38);
        } else {
            $('.sidebar-inner').width(180);
        }

    });

    //导航展开下级菜单
    $('.sidebar-inner ul li').click(function() {
        //初始化
        $('.sidebar-inner ul li dl').css('display', 'none');
        $('.sidebar-inner ul li').each(function() {
            if ($(this).children().children().eq(0).attr('class') == 'glyphicon glyphicon-triangle-bottom') {
                $(this).children().children().eq(0).attr('class', 'glyphicon glyphicon-triangle-right');
            }
        });

        //展开/收缩
        var ico = $(this).children().children(':first').attr('class');
        if (ico == 'glyphicon glyphicon-triangle-right') {
            $(this).children().children(':first').attr('class', 'glyphicon glyphicon-triangle-bottom');
            $(this).find('dl').css('display', 'block');
        } else if (ico == 'glyphicon glyphicon-triangle-bottom') {
            $(this).children().children(':first').attr('class', 'glyphicon glyphicon-triangle-right');
            $(this).find('dl').css('display', 'none');
        }

    })


    //一级高亮
    $('.sidebar-inner dd').click(function() {
        $('.sidebar-inner dd').each(function() {
            $(this).find('a').removeClass();
        })
        $(this).find('a').addClass('cur');
    });

    //绑定初试信息
    $('select').each(function() {
        var data = $(this).attr('data-selected');
        if (data) {
            $(this).val(data);
        }
    });

    //绑定radio值
    $(".radio").each(function(){
        var data = $(this).attr('data-radio');
        $(this).find('input[type=radio]').each(function(){
            if($(this).attr('value') == data){
                $(this).attr("checked","checked");
            }
        })
    })

    //绑定checkbox
    $('.checkbox').each(function(){
        var data  = $(this).val();
        var value = $(this).attr('data-checked');
        if(value != ''){
            value =  jQuery.parseJSON(value);
        }

        if($.inArray(data,value) >= 0){
            $(this).attr("checked","checked");
        }
    })

   //打开弹出
    $('.btn-open').click(function() {
        var href = $(this).attr('data-href');
        var title = $(this).attr('data-title');
        var width = $(this).attr('data-width');
        var height = $(this).attr('data-height');

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
        var form = $(this).parents('.form-horizontal');
        var data = form.serializeArray();
        var url  = form.attr('action');
        if(data.length < 1){
            return layer.msg('请上传参数');
        }


        $.post(url,data,function(reslut){
            layer.msg(reslut.msg);
            if(reslut.status){
                setTimeout(function(){
                    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                    if(index){
                        parent.location.reload();
                        parent.layer.close(index);
                    }else{
                        location.reload();   
                    }
                },1000);
            }
        })
    })

    //提交post信息
    $('.btn-ajax-post').click(function(){
        var tips = $(this).attr('data-tips');
        var attr = $(this).context.attributes;
        var url = $(this).attr('data-href');
        var data = new Object();
        for (var i = 0; i < attr.length; i++) {
            if(attr[i].localName.indexOf('data') !== -1 && attr[i].localName != 'data-href'){
                data[attr[i].localName.substr(5,attr[i].localName.length)] =  attr[i].value;
            }
        }

        if(tips){
            layer.confirm(tips, {
              btn: ['确定','取消'] //按钮
            }, function(){
               $.post(url,data,function(reslut){
                    layer.msg(reslut.msg);
                    if(reslut.status){
                   setTimeout(function(){location.reload();},1000);
                    }
                })
            }, function(){});
        }else{
            $.post(url,data,function(reslut){
                layer.msg(reslut.msg);
                if(reslut.status){
               setTimeout(function(){location.reload();},1000);
                }
            })
        }

    })

    //get提交
    $('.btn-ajax-get').click(function(){
        var url = $(this).attr('data-url');
        $.get(url,function(reslut){
            layer.msg(reslut.msg);
            if(reslut.status){
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
        var content = '<input type="file" style="display:none;" id="'+name+'" multiple="multiple"><div class="img-list" style="margin-top:20px;"><ul></ul></div>';
        var value   = $(this).attr('data-value');
        if(value != ''){
            value =  jQuery.parseJSON(value);
        }
            
        if(maxNum == 1){
            var ablum   = '';
        }else{
            var ablum   = new Array();
        }
       
        $(this).parent().append(content);

        //渲染初始图片
        for(var i=0;i<value.length;i++){
            var content = '<li style="float:left;width:150px;height:100px;margin-left:10px;"><img src="'+value[i]+'" width="150" height="100" style="border:1px solid #ccc;"> <a style="float:right;margin-top:-100px;margin-right:2px;cursor: pointer;" class="btn-del-img"><i class="glyphicon glyphicon-remove"></i></a></li>';
            $('.img-list ul').append(content);
        }


        //上传
        $(this).click(function(){
            $('input[type=file]').wrap('<form>').closest('form').get(0).reset();
            $('input[type=file]').trigger('click');
        })

        //转换图片url
        $('#'+name).change(function(e){
            var imgLength = $('.img-list').find('img').length;
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
                            var content = '<li style="float:left;width:150px;height:100px;margin-left:10px;"><img src="'+result.data+'" width="150" height="100" style="border:1px solid #ccc;"> <a style="float:right;margin-top:-100px;margin-right:2px;cursor: pointer;" class="btn-del-img"><i class="glyphicon glyphicon-remove"></i></a></li>';
                            $('.img-list ul').append(content);  
                        }

                        //删除照片
                        $('.btn-del-img').click(function(){
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
            $('.img-list').find('img').each(function(){
                var path  = $(this).attr('src');
                path  = path.substring(path.lastIndexOf("/")+1,path.length);
                if(path != 'nd.jpg'){
                    data[data.length] = path.substring(path.lastIndexOf("/")+1,path.length);
                }
                
            })
            var content = '<input type="hidden" name="'+name+'" value="'+data.join(',')+'" />';
            $('input[name='+name+']').remove();
            $('.btn-ablum').parent().append(content);
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
        var id      = $(this).attr('id');
        var time    = $(this).val();                    //int
        var min     = $(this).attr('data-min');         // string int
        var max     = $(this).attr('data-max');         // string int
        var format    = $(this).attr('data-format');
        var type    = $(this).attr('data-type');        //year month date time datetime


        if(!format){ format  = 'yyyy-MM-dd'; }
        if(!min){ min = '1900-1-1';}
        if(!max){ max = '2099-12-31';}
        if(!type){ type = 'date';}

        laydate.render({
          elem: '#'+id, //指定元素
          value:new Date(time),
          format:format,
          type:type,
          min:min,
          max:max,
        });
    })

    //关闭弹窗
    $('#btn-close').click(function(){
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.close(index);
    });
})
