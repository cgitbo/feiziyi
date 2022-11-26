<!DOCTYPE html>
<html>
<head>
<title>微信支付</title>
<script type="text/javascript" src="//res.wx.qq.com/open/js/jweixin-1.3.2.js"></script>
<script type='text/javascript'>
//传参数到小程序页面
function payNow()
{
	wx.miniProgram.reLaunch({"url":"index?<?php echo http_build_query($return);?>"});
}
payNow();
</script>
</head>

<body>
	正在支付......
</body>

</html>