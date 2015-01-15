<?php
/**
 *  主页面控制器
 */

class IndexController extends LibController
{
	/**
	 *  显示主页面
	 */
	public function main()
	{

		$this->renderView(array('title'=>'简单测试程序'),'main');
	}
}