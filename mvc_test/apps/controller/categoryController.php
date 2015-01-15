<?php
/**
 * Created by PhpStorm.
 * User: Tyraan
 * Date: 2014/12/26
 * Time: 11:26
 */
require_once('../libs/lib_dbconnect.php');
require_once('../libs/lib_dbmodel.php');

/*
 * 商品分类控制函数
 */
class CategoryController extends LibController{
	/*
	 * 添加 商品分类记录
	 * @access public
	 */
    public function add(){
    	$result = false;
        if (!empty($_POST)) {
        	$catMode = new Model('category');        	           
        	$result = $catMode->add(array('name'=>$_POST['catName']));      
           
            }
          return $this->showCat(array('result'=>$result));
    }
    /*
     * 删除商品记录
     * @access public
     */
    public function delete()
    {	
    	$result=false;
    	
		if (! empty ( $_POST )) {
			if (isset ( $_POST ['deleteCat'] )) {
				$catid = $_POST ['deleteCat'];
				if (is_numeric ( $catid )) {
					// category表中删除对应id
					$catModel = new Model ( 'category' );
					$catModel->deleteById ( $catid );
					// relationship表中删除对应id
					$relatModel = new Model ( 'relationship');
					$relatModel->deleteById ( $catid );
					$result = true;
				}
			}
			
		}
		return $this->showCat (array('result'=>$result));
	}
	/*
	 * 展示商品分类记录
	 * @access public
	 * @param array	 * 
	 */
	public function showCat($arr=array()) {
		/*
		 * 插入category表查询
		 */
		$catModel = new Model ( 'category' );
		$relatModel = new Model ( 'relationship' );
		$productsModel = new Model ( 'products' );
		$catdata = $catModel->getAll ();
				
		foreach ( $catdata as &$cat ) {
			$catid = $cat ['id'];
			$proid = $relatModel->getRelation ( $catid, 'productid' );
			
			/*
			 * 按照关系表的结果，取每个category中对应的product记录
			 */
			
			if (! empty ( $proid )) {
				$idData = array_column ( $proid, 'productid' );
				$names = $productsModel->getNamesById ( $idData, 'name' );				
				if (! empty ( $names )) {
					$cat ['products'] = array_column ( $names, 'name' );
				}
			}
		}
		$_GET ['catArray'] = $catdata;
		$this->renderView ( array (), 'category_manage' );
	}
    

}
?>
