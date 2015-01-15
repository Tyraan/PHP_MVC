<?php
/**
 *  商品管理控制器
 */
require_once ('../libs/lib_dbmodel.php');


class ProductController extends LibController {
	/*
	 * 从product_show接受post参数
	 * 连接数据库product表并插入记录
	 */
	public function add() {
		if (! empty ( $_POST )) {
			
			$proModel = new Model ( 'products' );
			$productData = array (
					'name' => $_POST ['productName'],
					'price' => $_POST ['productPrice'],
					'uptime' => time (),
					'description' => $_POST ['productDescription'] 
			)
			;
			$lastInsertId = $proModel->add ( $productData );
			$relationModel = new Model ( 'relationship' );
			$relationData = $_POST ['category'];
			if ($lastInsertId) {
				foreach ( $relationData as $key => $categoryid ) {
					if (is_numeric ( $categoryid )) {
						$relation = intval ( $categoryid, 10 );
					} else {
						throw new Exception ( 'categroy id must be integer' );
					}
					$relationData = array (
							'categoryid' => $categoryid,
							'productid' => $lastInsertId 
					);
					$relationModel->add ( $relationData );
				}
			}
		}
		return $this->show ( array (
				'result' => $result 
		) );
	}
	
	/*
	 * 向数据库查询，向product_show传递查询结果。
	 */
	public function show() {
		/*
		 * 确定有无升降序变量byOrder
		 * 没有则按id排序
		 */
		if (! empty ( $_GET ['byOrder'] )) {
			$byOrder = explode ( ',', $_GET ['byOrder'] );
			if (in_array ( $byOrder [0], array (
					'uptime',
					'price' 
			) ) && in_array ( $byOrder [1], array (
					'ASC',
					'DESC' 
			) )) {
				$byOrder = " $byOrder[0] $byOrder[1]";
			} else {
				($byOrder = null);
			}
		}
		// 建立模型 ，查询记录
		$proModel = new Model ( 'products' );
		$catModel = new Model ( 'category' );
		$relModel = new Model ( 'relationship' );
		$dataArray = $proModel->getAll ( $byOrder );
		$catData = $catModel->getAll ();
		foreach ( $dataArray as &$data ) {
			$idData = $relModel->getRelation ( $data ['id'], 'categoryid' );
			// 按照关系表中的记录取出对应的category name
			if (! empty ( $idData )) {
				$idData = array_column ( $idData, 'categoryid' );
				$names = $catModel->getNamesById ( $idData, 'name' );
				$data ['category'] = array_column ( $names, 'name' );
			}
		}
		// 返回数据
		$_GET ['products'] = $dataArray;
		$_GET ['catArray'] = $catData;
		$this->renderView ( array (), 'product_manage' );
	}
	/*
	 * 接受从products_show页面传来的参数
	 * 向products表中删除对应id记录
	 */
	public function delete() {
		// 获得 要delete操作的目标id
		if (! empty ( $_POST )) {
			if (isset ( $_POST ['deleteProduct'] )) {
				$id = $_POST ['deleteProduct'];
				$proModel = new Model ( 'products' );
				$relModel = new Model ( 'relationship' );
				$proModel->deleteById ( $id );
				$relModel->deleteById ( $id );
			}
		}
		return $this->show ();
	}
}
