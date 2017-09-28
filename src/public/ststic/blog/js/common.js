$(function() {
	$('.news li').each(function(){
		var img = $(this).find('img');
		var imgShow = img.parent().find() 
		if(img.css('display') == 'block'){
			$(this).find('.desc p').css('height','6rem');
			$(this).find('.desc p').css('width',($(this).width()-img.width() - 20)+'px');
		}
		//console.log(img.css('display'));
		//console.log($(this).html());
	})

	$('.btn-search').click(function(){
		var keyword = $('input[name=keyword]').val();
		if(!keyword){ 
			//layer.msg('请输入搜索内容'); 
			window.location.href = '/';
		}else{
			$('#form').submit();
		}
	})

	//暂无内容居中
	var rightHeight = $('.right').height();
	var nd_news = $('.nd-news').find('p');
	if(nd_news && rightHeight){
		nd_news.css('margin-top',((rightHeight / 2) + 'px'));
	}

    //提交信息
    $('.btn-comply').click(function(){
        var form;
        if($('.form-horizontal').length == 1){
           form = $('.form-horizontal');
        }else{
           form = $(this).parent().parent().parent();
        }
        var data= form.serializeArray();
        var url = form.attr('action');

        $.post(url,data,function(reslut){
            layer.msg(reslut.msg);
            if(reslut.status){
                setTimeout(function(){
                    parent.location.reload();
                    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                    parent.layer.close(index);
                },1000);
            }
        })
    })
	
	//提交post信息
    $('.btn-ajax-post').click(function(){
        var attr = $(this).context.attributes;
        var url = $(this).attr('data-href');
        var data = new Object();
        for (var i = 0; i < attr.length; i++) {
            if(attr[i].localName.indexOf('data') !== -1 && attr[i].localName != 'data-href'){
                data[attr[i].localName.substr(5,attr[i].localName.length)] =  attr[i].value;
            }
        }

        $.post(url,data,function(reslut){
            layer.msg(reslut.msg);
            if(reslut.status){
               setTimeout(function(){location.reload();},1000);
            }
        })
    })

})