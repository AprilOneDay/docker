$(function() {
    //主页容器固定高宽
    $('.sidebar-inner').height($(document).height() - $('.border-top').height());
    $('.product-nav-scene').height($(document).height() - $('.border-top').height())
    $('.content-main').width(($(document).width() - $('.sidebar-inner').width() - $('.product-nav-scene').width()) * 0.98);
    $('.content-main').height($(document).height() - $('.border-top').height());

    $(window).resize(function() {
        $('.sidebar-inner').height($(document).height() - $('.border-top').height());
        $('.product-nav-scene').height($(document).height() - $('.border-top').height())
        $('.content-main').width(($(document).width() - $('.sidebar-inner').width() - $('.product-nav-scene').width()) * 0.98);
        $('.content-main').height($(document).height() - $('.border-top').height());
    });

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

    //导航展开下级菜单
    $('.product-nav-scene ul li').click(function() {
        //初始化
        $('.product-nav-scene ul li dl').css('display', 'none');
        $('.product-nav-scene ul li').each(function() {
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

    //二级高亮
    $('.product-nav-scene dd').click(function() {
        $('.product-nav-scene dd').each(function() {
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

    //关闭弹窗
    $('#btn-close').click(function(){
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.close(index);
    });
})