<?php
/**
 * @interface transferInter
 * @brief 转账接口规范
 * @date 2021/2/18 7:38:38
 */
interface transferInter
{
	//执行转账
	public function run($data);
}