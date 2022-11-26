<?php
/**
 * @class mini_wechat
 * @brief 微信小程序支付
 * @date 2018/2/26 22:25:04
 */
include_once(dirname(__FILE__)."/../common/wechatBase.php");
class mini_wechat extends wechatBase
{
	//支付插件名称
    public $name = '微信小程序支付';

	/**
	 * @see paymentplugin::getSendData()
	 */
	public function getSendData($payment)
	{
		$return = array();

		//基本参数
		$return['appid']            = $payment['appid'];
		$return['mch_id']           = $payment['mch_id'];
		$return['nonce_str']        = rand(100000,999999);
		$return['body']             = IWeb::$app->getController()->_siteConfig->name;
		$return['out_trade_no']     = $payment['M_OrderNO']."_mini";
		$return['total_fee']        = $payment['M_Amount']*100;
		$return['spbill_create_ip'] = IClient::getIp();
		$return['notify_url']       = $this->serverCallbackUrl;
		$return['trade_type']       = 'JSAPI';
		$return['openid']           = wechatMini::getPriData();

		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($return);

		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildMysign($para_sort, $payment['key']);

		//签名结果与签名方式加入请求提交参数组中
		$return['sign'] = $mysign;

		$xmlData = $this->converXML($return);
		$result  = $this->curlSubmit($xmlData);

		//进行与支付订单处理
		$resultArray = $this->converArray($result);
		if(is_array($resultArray))
		{
			//处理正确
			if(isset($resultArray['return_code']) && $resultArray['return_code'] == 'SUCCESS')
			{
				$resultArray['key']      = $payment['key'];
				$resultArray['order_no'] = $payment['M_OrderNO'];
				return $resultArray;
			}
			else
			{
				die("mini_wechat返回：".$resultArray['return_msg']);
			}
		}
		else
		{
			die("mini_wechat数据错误：".$result);
		}
		return null;
	}

	/**
	 * @see paymentplugin::doPay()
	 */
	public function doPay($sendData)
	{
		if(isset($sendData['prepay_id']) && $sendData['prepay_id'])
		{
			$return = array();

			//基本参数
			$return['appId']     = $sendData['appid'];
			$return['timeStamp'] = time();
			$return['nonceStr']  = rand(100000,999999);
			$return['package']   = "prepay_id=".$sendData['prepay_id'];
			$return['signType']  = "MD5";

			//除去待签名参数数组中的空值和签名参数
			$para_filter = $this->paraFilter($return);

			//对待签名参数数组排序
			$para_sort = $this->argSort($para_filter);

			//生成签名结果
			$mysign = $this->buildMysign($para_sort, $sendData['key']);

			//签名结果与签名方式加入请求提交参数组中
			$return['paySign']    = $mysign;
			$return['successUrl'] = IUrl::getHost().IUrl::creatUrl('/site/success/message/'.urlencode('支付成功！'));
			$return['failUrl']    = IUrl::getHost().IUrl::creatUrl('/errors/error/message/'.urlencode('支付失败！'));

			include(dirname(__FILE__).'/template/pay.php');
		}
		else
		{
			$message = $sendData['err_code_des'] ? $sendData['err_code_des'] : '微信下单API接口失败';
			die($message);
		}
	}

	/**
	 * @param 获取配置参数
	 */
	public function configParam()
	{
		$result = array(
			'mch_id'    => '商户号',
			'key'       => '商户支付密钥',
			'appid'     => '小程序AppID',
			'appsecret' => '小程序AppSecret',
		);
		return $result;
	}
}