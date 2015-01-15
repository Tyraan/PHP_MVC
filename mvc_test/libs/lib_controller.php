<?php

/**
 *  主控制器基类
 */
class LibController
{
	/**
     *  渲染视图
     * @param $data array 绑定进去的变量
     * @param $view 视图
     */
	public function renderView($data,$view) 
	{
        echo $this->fetchView($data, $view);
	}

    /**
     *  获取视图内容
     *
     *  @param $data array 导入到视图的变量
     *  @param $view 视图
     */
	public function fetchView($data, $view)
	{
		return loadView($data,$view);
	}

}