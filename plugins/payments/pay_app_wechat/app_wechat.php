<?php
/**
 * @class app_wechat
 * @brief APP微信支付
 * @date 2016/12/10 0:37:45
 * @author nswe
 */
include_once(dirname(__FILE__)."/../common/wechatBase.php");
class app_wechat extends wechatBase
{
	//支付插件名称
    public $name = '微信APP支付';

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
		$return['out_trade_no']     = $payment['M_OrderNO']."_APP";
		$return['total_fee']        = $payment['M_Amount']*100;
		$return['spbill_create_ip'] = IClient::getIp();
		$return['notify_url']       = $this->serverCallbackUrl;
		$return['trade_type']       = 'APP';

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
				$resultArray['partnerid']= $payment['mch_id'];
				$resultArray['appid']    = $payment['appid'];
				$resultArray['key']      = $payment['key'];
				$resultArray['order_no'] = $payment['M_OrderNO'];
				$resultArray['order_id'] = $payment['M_OrderId'];
				return $resultArray;
			}
			else
			{
			    IError::show($resultArray['return_msg']);
			}
		}
		else
		{
		    IError::show($result);
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
			$return = [];

			//基本参数
			$return['appid']     = $sendData['appid'];
			$return['partnerid'] = $sendData['mch_id'];
			$return['prepayid']  = $sendData['prepay_id'];
			$return['package']   = "Sign=WXPay";
			$return['noncestr']  = md5(rand(100000,999999));
			$return['timestamp'] = time();

			//除去待签名参数数组中的空值和签名参数
			$para_filter = $this->paraFilter($return);

			//对待签名参数数组排序
			$para_sort = $this->argSort($para_filter);

			//生成签名结果
			$mysign = $this->buildMysign($para_sort, $sendData['key']);

			//签名结果与签名方式加入请求提交参数组中
			$return['sign'] = $mysign;
			$failUrl        = IUrl::getHost().IUrl::creatUrl('/errors/error/message/#message#');
			$successUrl     = IUrl::getHost().IUrl::creatUrl('/ucenter/order_detail/id/'.$sendData['order_id']);

			include(dirname(__FILE__).'/template/pay.php');
		}
		else
		{
			$message = $sendData['err_code_des'] ? $sendData['err_code_des'] : '微信下单API接口失败';
			IError::show($message);
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
			'appid'     => '应用ID',
			'appsecret' => '应用Secret',
		);
		return $result;
	}
}