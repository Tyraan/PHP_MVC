<?php
/**
 *  分类module
 */

/**
 *  分类module
 */
class CategoryModule extends LibModule
{

    /**
     *  定义主键
     */
    protected function getPk (){
    	return 'id';
    }

    /**
     *  定义表明
     */
    protected function getTableName() {
        return 'category';
    }

    /**
     *  获取字段映射
     */
    protected function getFiledsMap() {
    	return array(
    		'name'      => PDO::PARAM_STR,        // 分类名称
    		'desc'      => PDO::PARAM_STR,        // 分类描述
    		'product_count' => PDO::PARAM_INT,   // 分类下商品数量 （允余字段)
    	    'symbol'    => PDO::PARAM_STR,     // 分类标识
    	    'order'     =>  PDO::PARAM_INT,         // 排序顺序
    	    'create_at' =>  PDO::PARAM_INT,       // 建立时间
    	    'modify_at' =>  PDO::PARAM_INT,       // 修改时间
    	);
    } 
}