$(function(){
	$('.search').on('click', function(){
		$('.viewport').animate({scrollTop:0},200);
	})
	// 设置当前页面标题以及返回路径 开始
	var gobackBtn = $(".header_goback");
	var pageInfo = $("#pageInfo"),
		pageInfoTitle = pageInfo.data('title'),
		pageInfoGoback = pageInfo.data('goback');
	if (pageInfoTitle) {
		$(".header_title").html(pageInfoTitle);
	};
	gobackBtn.on('click',function() {
		if (pageInfoGoback) {
			gourl(pageInfoGoback);
		} else{
            history.go(-1);
		};
	});
	// 设置当前页面标题以及返回路径 结束
    // 将内部的底部按钮挪动到body下面，解决ios的兼容性问题
	var $vB = $("#viewport_bottom");
	var $lB = $("#layout_bottom");
	if ($vB) {
		var str = $vB.html();
		$lB.html(str);
		$vB.html('');
	}
});
// 跳转函数
function gourl(url){
	window.location.href = url;
}
// 获取url参数函数
function getUrlParam(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r!=null) return unescape(r[2]); return null;
}
// 返回上一页JS
var historyUtils = {
    add : function (url) {
        var historyArray = historyUtils.getLocal();
        if (!historyArray) {
            historyArray = [];
        }
        var currentPage = historyArray.pop();
        if (currentPage && currentPage == url) {
            //do nothing
        } else if (currentPage){
            historyArray.push(currentPage); //历史里面没有现在传入的url，在加回去
        }
        historyArray.push(url);
        historyUtils.saveLocal(historyArray);
    },
    back : function() {
        var historyArray = historyUtils.getLocal();
        var currentPage = historyArray.pop();//去掉当前页面，pop取最后，类似stack
        var history = historyArray.pop();
        if (!history) {//没有历史页面
            historyUtils.add(currentPage);//将当前页面加入回数组中
            return;
        }
        historyUtils.saveLocal(historyArray);
        window.location.href = history;
    },
    getLocal : function() {
        var result = window.sessionStorage.getItem(historyUtils.key);
        if (!result) {
            return null;
        }
        return JSON.parse(result);
    },
    saveLocal : function(data) {
        window.sessionStorage.setItem(historyUtils.key, JSON.stringify(data));
    },
    init : function() {
        historyUtils.saveLocal([]);
    },
    key : "_history_"
}
historyUtils.add(window.location.href);
