<?php
/**
 * @copyright (c) 2019 aircheng.com
 * @file pullWord.php
 * @brief pullWord接口分词类
 * @author nswe
 * @date 2019/12/27 15:01:53
 * @version 5.6
 */
class pullWord extends pluginBase implements wordsPart_inter
{
	public static function name()
	{
		return "pullWord分词接口";
	}

	public static function description()
	{
		return "pullWord免费开源的分词技术";
	}

	/**
	 * @brief 获取提交按钮
	 * @return string
	 */
	public function getSubmitUrl()
	{
		return 'http://api.pullword.com/get.php';
	}

	/**
	 * @brief 运行分词
	 * @param string $content 要分词的内容
	 * @return array 词语
	 */
	public function run($content)
	{
		$postData = array(
			'source' => $content,
			'param1' => 0.8,//精确度0-1
			'param2' => 0,//调试模式 0关闭；1开启
			'json'   => 1,//json数据格式
		);
		$result = $this->curlSend($this->getSubmitUrl(),$postData);
		return $this->response($result);
	}

	/**
	 * @brief 发送curl组建数据
	 * @param string $url 提交的api网址
	 * @param array $post 发送的接口参数
	 * @return mixed 返回的数据
	 */
	private function curlSend($url,$postData)
	{
	    $url.= '?'.http_build_query($postData);
		$ch  = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => true,
		));

		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	/**
	 * @brief 处理规范统一的结果集
	 * @param string $result 要处理的返回值
	 * @return array 返回结果 array('result' => 'success 或者 fail','data' => array('分词数据'))
	 */
	public function response($result)
	{
		$resultArray = JSON::decode($result);
		if($resultArray)
		{
			$data = array();
			foreach($resultArray as $key => $val)
			{
			    $data[] = $val['t'];
			}
			return array('result' => 'success','data' => $data);
		}
		else
		{
			return array('result' => 'fail','data' => array());
		}
	}
}