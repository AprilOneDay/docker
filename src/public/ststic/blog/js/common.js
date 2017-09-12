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
	//console.log(rightHeight);
})