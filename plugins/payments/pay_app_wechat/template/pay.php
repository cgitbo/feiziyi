<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<title>微信支付</title>
	<meta charset="UTF-8">
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="expires" content="0">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
</head>

<body>
	正在支付......
</body>

<script type='text/javascript'>
function apiready()
{
    var wxPay = api.require('wxPay');
    wxPay.payOrder({
        apiKey: "<?php echo $return['appid']?>",
        orderId: "<?php echo $return['prepayid']?>",
        mchId: "<?php echo $return['partnerid']?>",
        nonceStr: "<?php echo $return['noncestr']?>",
        timeStamp: "<?php echo $return['timestamp']?>",
        package: "<?php echo $return['package']?>",
        sign: "<?php echo $return['sign']?>"
    },function(ret, err)
    {
        //支付成功
        if(ret.status)
        {
            window.location.href = "<?php echo $successUrl;?>";
        }
        else
        {
            var failUrl = "<?php echo $failUrl;?>";
            var message = {"-1":"发生错误","-2":"取消支付","1":"appid参数错误"};
            window.location.href = failUrl.replace("#message#",message[err.code]);
        }
    });
}
</script>
</html>