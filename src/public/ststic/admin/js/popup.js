$(function() {
	//打开弹出
    $('#btn-open').click(function() {

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

    //关闭弹窗
    $('#btn-close').click(function(){
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.close(index);
    });
})