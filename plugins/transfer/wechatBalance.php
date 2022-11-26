<?php
/**
 * @class wechatBalance
 * @brief 微信余额转账方式
 * @note  需要利用微信插件和公众号支付方式
 * @date 2021/2/12 7:38:38
 */
include_once(dirname(__FILE__)."/common/transferInter.php");
class wechatBalance implements transferInter
{
	//名称
    public $name = '转账到微信余额';

	//wap_wechat支付接口在payment表中的ID
	const WAP_WECHAT_PAYMENT_ID = 12;

	//mini_wechat支付接口在payment表中的ID
	const MINI_WECHAT_PAYMENT_ID = 17;

	//构造函数
	public function __construct()
	{

	}

	/**
	 * @brief 提交URL路径
	 * @return string
	 */
	public function getSubmitUrl()
	{
		return 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
	}

	//发送可加密的xml的post请求
	protected function postXMLCurl($xml, $url)
    {
		$SSLCERT_PATH = dirname(__FILE__).'/../payments/common/key/apiclient_cert.pem';
		$SSLKEY_PATH  = dirname(__FILE__).'/../payments/common/key/apiclient_key.pem';

        $ch = curl_init();

        //设置基础设置
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, $SSLCERT_PATH);
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, $SSLKEY_PATH);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data)
        {
            curl_close($ch);
            return $data;
        }
        else
        {
            $error = curl_error($ch);
            curl_close($ch);
            if(stripos($error,'not found'))
            {
                $error .= "请拷贝微信退款证书在此目录下";
            }
            die("curl出错，错误信息:".$error);
        }
    }

	/**
	 * @brief 从array到xml转换数据格式
	 * @param array $arrayData
	 * @return xml
	 */
	protected function converXML($arrayData)
	{
		$xml = '<xml>';
		foreach($arrayData as $key => $val)
		{
			$xml .= '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
		}
		$xml .= '</xml>';
		return $xml;
	}

	/**
	 * @brief 从xml到array转换数据格式
	 * @param xml $xmlData
	 * @return array
	 */
	protected function converArray($xmlData)
	{
		$result = array();
		$xmlHandle = xml_parser_create();
		xml_parse_into_struct($xmlHandle, $xmlData, $resultArray);

		foreach($resultArray as $key => $val)
		{
			if($val['tag'] != 'XML' && isset($val['value']))
			{
				$result[$val['tag']] = $val['value'];
			}
		}
		return array_change_key_case($result);
	}

	/**
	 * 除去数组中的空值和签名参数
	 * @param $para 签名参数组
	 * return 去掉空值与签名参数后的新签名参数组
	 */
	protected function paraFilter($para)
	{
		$para_filter = array();
		foreach($para as $key => $val)
		{
			if($key == "sign" || $key == "sign_type" || $val == "")
			{
				continue;
			}
			else
			{
				$para_filter[$key] = $para[$key];
			}
		}
		return $para_filter;
	}

	/**
	 * 对数组排序
	 * @param $para 排序前的数组
	 * return 排序后的数组
	 */
	protected function argSort($para)
	{
		ksort($para);
		reset($para);
		return $para;
	}

	/**
	 * 生成签名结果
	 * @param $sort_para 要签名的数组
	 * @param $key 支付宝交易安全校验码
	 * @param $sign_type 签名类型 默认值：MD5
	 * return 签名结果字符串
	 */
	protected function buildMysign($sort_para,$key,$sign_type = "MD5")
	{
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkstring($sort_para);
		//把拼接后的字符串再与安全校验码直接连接起来
		$prestr = $prestr.'&key='.$key;
		//把最终的字符串签名，获得签名结果
		$mysgin = md5($prestr);
		return strtoupper($mysgin);
	}

	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	protected function createLinkstring($para)
	{
		$arg  = "";
		foreach($para as $key => $val)
		{
			$arg.=$key."=".$val."&";
		}

		//去掉最后一个&字符
		$arg = trim($arg,'&');

		//如果存在转义字符，那么去掉转义
		if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
		{
			$arg = stripslashes($arg);
		}

		return $arg;
	}

	/**
	 * @brief 开始执行支付
	 * @param array $data 支付数据
	 * @return data
	 */
	public function run($data)
	{
		$paymentConfig = Payment::getPaymentInfo($data['payment_id'],'bill');
		$sendData = [
			'mch_appid'        => $paymentConfig['appid'],
			'mchid'            => $paymentConfig['mch_id'],
			'nonce_str'        => rand(100000,999999),
			'partner_trade_no' => $data['partner_trade_no'],
			'openid'           => $data['openid'],
			'check_name'       => 'FORCE_CHECK',
			're_user_name'     => $data['re_user_name'],
			'amount'           => $data['amount']*100,
			'desc'             => $data['desc'],
		];

		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($sendData);

		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildMysign($para_sort, $paymentConfig['key']);

		//签名结果与签名方式加入请求提交参数组中
		$sendData['sign'] = $mysign;

		$url = $this->getSubmitUrl();
		$xml = $this->converXML($sendData);
		$result = $this->postXMLCurl($xml, $url);

		//日志记录
	    $logObj = new IFileLog('transfer/'.date('Y-m-d').'.log');
	    $logObj->write(['请求地址' => $url,'发送数据' => $xml, '返回数据' => $result]);

		//进行与支付订单处理
		$resultArray = $this->converArray($result);
		if(is_array($resultArray))
		{
			//处理正确
			if(isset($resultArray['return_code']) && $resultArray['return_code'] == 'SUCCESS')
			{
				if($resultArray['result_code'] == 'SUCCESS')
				{
					/**
					 * partner_trade_no
					 * payment_no
					 * payment_time
					 */
					return $resultArray;
				}
				else
				{
					return "微信转账：".$resultArray['err_code_des'];
				}
			}
			else
			{
				return "微信转账：".$resultArray['return_msg'];
			}
		}
		else
		{
			return "微信转账：".$result;
		}
	}
}