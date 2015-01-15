<?php
require_once ('../apps/config/config.php');
require_once ('../apps/common.php');

// 数据库实现 ，与controller 交互
class Model {
	// 数据库配置
	public $dbconfig;
	// 模型表名
	public $tablename;
	// 最后插入id
	public $lastInsertId = null;
	// 数据库交互实例
	public $mysqlpdo;
	/*
	 * 构造函数
	 * @param string
	 */
	public function __construct($tablename) {
		global $G_CONF;
		$this->dbconfig = $G_CONF ['db'];
		$this->tablename = $tablename;
		$this->mysqlpdo = new MysqlPdo ( $this->dbconfig );
		unset ( $this->dbconfig );
	}
	/*
	 * 插入记录
	 * @access public
	 * @param array
	 * @return integer
	 */
	public function add($dataArray = array()) {
		if (! empty ( $dataArray )) {
			$this->mysqlpdo->add ( $dataArray, $this->tablename );
			$this->lastInsertId = Mysqlpdo::getLastInsId ();
			return $this->lastInsertId;
		}
		throw new Exception ( 'empty dataArray' );
	}
	/*
	 * 获得表所有记录
	 * @access public
	 * @param array
	 * @return array
	 */
	public function getAll($order = null) {
		$byOrder = "";
		if (isset ( $order )) {
			$byOrder = MysqlPdo::parseOrder ( $order );
		}
		
		$sql = "select * from $this->tablename $byOrder";
		return $this->mysqlpdo->getAll ( $sql );
	}
	
	/*
	 * 按 主键id 获得 字段名
	 * @access public
	 * @param array
	 * @param string
	 * @param string
	 * @return array
	 */
	public function getNamesById($idData, $field, $key = 'id') {
		$result = false;
		if (! empty ( $idData ) && ! empty ( $field )) {
			if (is_array ( $idData ))
				$idStr = implode ( ',', $idData );			
			$where = "id in ($idStr) ";
			$result = $this->mysqlpdo->find ( $this->tablename, $where, 'name' );
		}		 		
		return $result;
	}
	/*
	 * 按字段名查询关系表
	 * @access public
	 * @param string
	 * @param string
	 * @return array
	 */
	public function getRelation($idData, $field) {
		if (! empty ( $idData ) && in_array ( $field, array (
				'productid',
				'categoryid' 
		) )) {
			
			if (is_array ( $idData ) && isset ( $idData )) {
				$idData = implode ( ',', array_column ( id, $field ) );
			}
			$column = $field == 'productid' ? 'categoryid' : 'productid';
			$whereStr = " $column = $idData ";
			$result = $this->mysqlpdo->find ( $this->tablename, $whereStr, $field );
		}
		return $result;
	}
	/*
	 * 按id查询字段
	 * @access public
	 * @param array
	 * @param string
	 * @param string
	 * @return array
	 */
	public function getFields($iddata, $field, $key) {
		if (! empty ( $iddata ) && ! empty ( $field )) {
			if (is_array ( $iddata )) {
                $id = implode(',',$iddata);
				$whereStr = "$key in ( $id )";
			} elseif (is_string ( $iddata )) {
				$whereStr = "$key = $iddata ";
			}
			$result = $this->mysqlpdo->find ( $this->tablename, $whereStr, $field );
		}
		return $result; // ? $result:null;
	}
	
	/*
	 * 按id删除记录
	 * @access public
	 * @param string
	 * @return integer
	 */
	public function deleteById($id) {
		if (isset ( $id )) {
			$id = "id = $id ";
			$this->mysqlpdo->remove ( $id, $this->tablename );
			$this->lastInsertId = Mysqlpdo::getLastInsId ();
			return $this->lastInsertId;
		}
		throw new Exception ( 'empty param' );
	}
}

// 数据库交互类
class MysqlPdo {
	// statement对象
	public static $PDOStatement = null;
	/**
	 * 数据库的连接参数配置
	 * 
	 * @var array
	 * @access public
	 */
	public static $config = array ();
	
	/**
	 * 错误信息
	 * 
	 * @var string
	 * @access public
	 */
	public static $error = '';
	/**
	 * 单件模式,保存Pdo类唯一实例,数据库的连接资源
	 * 
	 * @var object
	 * @access public
	 */
	protected static $link;
	/**
	 * 是否已经连接数据库
	 * 
	 * @var bool
	 * @access public
	 */
	public static $connected = false;
	
	/**
	 * 当前SQL语句
	 * 
	 * @var string
	 * @access public
	 */
	public static $queryStr = '';
	/**
	 * 最后插入记录的ID
	 * 
	 * @var integer
	 * @access public
	 */
	public static $lastInsertId = null;
	/**
	 * 返回影响记录数
	 * 
	 * @var integer
	 * @access public
	 */
	public static $numRows = 0;
	
	/**
	 * 构造函数，
	 * 
	 * @param
	 *        	$dbconfig
	 */
	public function __construct($dbConfig = '') {
		if (! class_exists ( 'PDO' ))
			throw new Exception ( "unsupport :PDO" );
			// 若没有传输任何参数，则使用默认的数据定义
		if (! is_array ( $dbConfig ) || empty ( $dbConfig ['hostname'] )) {
			throw new Exception ( 'invalid args' );
		}
		
		self::$config = $dbConfig;
		if (empty ( self::$config ['params'] ))
			self::$config ['params'] = array ();
		if (! isset ( self::$link )) {
			$configs = self::$config;
			try {
				self::$link = new PDO ( $configs ['dsn'], $configs ['username'], $configs ['password'], $configs ['params'] );
			} catch ( PDOException $e ) {
				throw_exception ( $e->getMessage () );
				// exit('连接失败:'.$e->getMessage());
			}
			if (! self::$link) {
				throw_exception ( 'PDO CONNECT ERROR' );
				return false;
			}
			self::$link->exec ( 'SET NAMES utf8' );
			// 标记连接成功
			self::$connected = true;
			// 注销数据库连接配置信息
			unset ( $configs );
		}
		return self::$link;
	}
	/**
	 * 释放查询结果
	 * 
	 * @access function
	 */
	static function free() {
		self::$PDOStatement = null;
	}
	
	/**
	 * 获得所有的查询数据
	 * 
	 * @access function
	 * @return array
	 */
	static function getAll($sql = null) {
		self::query ( $sql );
		// 返回数据集
		$result = self::$PDOStatement->fetchAll ( constant ( 'PDO::FETCH_ASSOC' ) );
		return $result;
	}
	
	/**
	 * 获得一条查询结果
	 * 
	 * @access function
	 * @param string $sql
	 *        	SQL指令
	 * @param integer $seek
	 *        	指针位置
	 * @return array
	 */
	static function getRow($sql = null) {
		self::query ( $sql );
		// 返回数组集
		$result = self::$PDOStatement->fetch ( constant ( 'PDO::FETCH_ASSOC' ), constant ( 'PDO::FETCH_ORI_NEXT' ) );
		return $result;
	}
	/**
	 * 执行sql语句，自动判断进行查询或者执行操作
	 * 
	 * @access function
	 * @param string $sql
	 *        	SQL指令
	 * @return mixed
	 */
	static function doSql($sql = '') {
		if (self::isMainIps ( $sql )) {
			return self::execute ( $sql );
		} else {
			return self::getAll ( $sql );
		}
	}
	/**
	 * 根据指定ID查找表中记录(仅用于单表操作)
	 * 
	 * @access function
	 * @param integer $priId
	 *        	主键ID
	 * @param string $tables
	 *        	数据表名
	 * @param string $fields
	 *        	字段名
	 * @return ArrayObject 表记录
	 */
	static function findById($tabName, $priId, $fields = '*', $key = 'id') {
		$sql = "SELECT %s FROM %s WHERE $key=%d";
		return self::getRow ( sprintf ( $sql, self::parseFields ( $fields ), $tabName, $priId ) );
	}
	/**
	 * 查找记录
	 * 
	 * @access function
	 * @param string $tables
	 *        	数据表名
	 * @param mixed $where
	 *        	查询条件
	 * @param string $fields
	 *        	字段名
	 * @param string $order
	 *        	排序
	 * @return ArrayObject
	 */
	static function find($tables, $where = "", $fields = '*', $order = null) {
		$sql = 'SELECT ' . self::parseFields ( $fields ) . ' FROM ' . $tables . self::parseWhere ( $where ) . self::parseOrder ( $order );
		
		$dataAll = self::getAll ( $sql );
		
		return $dataAll;
	}
	/*
	 * 插入（单条）记录
	 * @access function
	 * @param mixed $data 数据
	 * @param string $table 数据表名
	 * @return false | integer
	 */
	static function add($data, $table) {
		// 过滤提交数据
		// $data=self::filterPost($table,$data);
		foreach ( $data as $key => $val ) {
			if (is_array ( $val ) && strtolower ( $val [0] ) == 'exp') {
				$val = $val [1]; // 使用表达式 ???
			} elseif (is_scalar ( $val )) {
				$val = self::fieldFormat ( $val );
			} else {
				// 去掉复合对象
				continue;
			}
			$data [$key] = $val;
		}
		$fields = array_keys ( $data );
		$fieldsStr = implode ( ',', $fields );
		$values = array_values ( $data );
		$valuesStr = implode ( ',', $values );
		$sql = 'INSERT INTO ' . $table . ' (' . $fieldsStr . ') VALUES (' . $valuesStr . ')';
		return self::execute ( $sql );
	}
	/**
	 * 更新记录
	 * 
	 * @access function
	 * @param mixed $sets
	 *        	数据
	 * @param string $table
	 *        	数据表名
	 * @param string $where
	 *        	更新条件
	 * @param string $limit        	
	 * @param string $order        	
	 * @return false | integer
	 */
	static function update($sets, $table, $where, $order = '') {
		$sets = self::filterPost ( $table, $sets );
		$sql = 'UPDATE ' . $table . ' SET ' . self::parseSets ( $sets ) . self::parseWhere ( $where ) . self::parseOrder ( $order );
		return self::execute ( $sql );
	}
	/**
	 * 保存某个字段的值
	 * 
	 * @access function
	 * @param string $field
	 *        	要保存的字段名
	 * @param string $value
	 *        	字段值
	 * @param string $table
	 *        	数据表
	 * @param string $where
	 *        	保存条件
	 * @param boolean $asString
	 *        	字段值是否为字符串
	 * @return void
	 */
	static function setField($field, $value, $table, $condition = "", $asString = false) {
		// 如果有'(' 视为 SQL指令更新 否则 更新字段内容为纯字符串
		if (false === strpos ( $value, '(' ) || $asString)
			$value = '"' . $value . '"';
		$sql = 'UPDATE ' . $table . ' SET ' . $field . '=' . $value . self::parseWhere ( $condition );
		return self::execute ( $sql );
	}
	/**
	 * 删除记录
	 * 
	 * @access function
	 * @param mixed $where
	 *        	为条件Map、Array或者String
	 * @param string $table
	 *        	数据表名
	 * @param string $limit        	
	 * @param string $order        	
	 * @return false | integer
	 */
	static function remove($where, $table, $order = '') {
		$sql = 'DELETE FROM ' . $table . self::parseWhere ( $where ) . self::parseOrder ( $order );
		return self::execute ( $sql );
	}
	
	/**
	 * 获取最近一次查询的sql语句
	 * 
	 * @access function
	 * @param        	
	 *
	 * @return String 执行的SQL
	 */
	static function getLastSql() {
		$link = self::$link;
		if (! $link)
			return false;
		return self::$queryStr;
	}
	/**
	 * 获取最后插入的ID
	 * 
	 * @access function
	 * @param        	
	 *
	 * @return integer 最后插入时的数据ID
	 */
	static function getLastInsId() {
		$link = self::$link;
		if (! $link)
			return false;
		return self::$lastInsertId;
	}
	
	/**
	 * 取得数据表的字段信息
	 * 
	 * @access function
	 * @return array
	 */
	static function getFields($tableName) {
		// 获取数据库联接
		$link = self::$link;
		list ( $tableName ) = explode ( ' ', $tableName );
		$sql = 'SHOW COLUMNS FROM `' . $tableName . '`';
		echo $sql;
		$sth = $link->prepare ( $sql );
		$sth->excute ();
		$result = $sth->fetchAll ();
		$info = array ();
		if ($result) {
			echo "result!!";
			foreach ( $result as $key => $val ) {
				if (\PDO::CASE_LOWER != $this->_linkID->getAttribute ( \PDO::ATTR_CASE )) {
					$val = array_change_key_case ( $val, CASE_LOWER );
				}
				$info [$val ['field']] = array (
						'name' => $val ['field'],
						'type' => $val ['type'],
						'notnull' => ( bool ) ($val ['null'] === ''), // not null is empty, null is yes
						'default' => $val ['default'],
						'primary' => (strtolower ( $val ['key'] ) == 'pri'),
						'autoinc' => (strtolower ( $val ['extra'] ) == 'auto_increment') 
				);
			}
		}
		
		// 有错误则抛出异常
		self::haveErrorThrowException ();
		return $info;
	}
	/**
	 * 关闭数据库
	 * 
	 * @access function
	 */
	static function close() {
		self::$link = null;
	}
	/**
	 * SQL指令安全过滤
	 * 
	 * @access function
	 * @param string $str
	 *        	SQL指令
	 * @return string
	 */
	static function escape_string($str) {
		return addslashes ( $str );
	}
	
	/* 内部操作方法 */
	/**
	 * 有出错抛出异常
	 * 
	 * @access function
	 * @return
	 *
	 */
	static function haveErrorThrowException() {
		$obj = empty ( self::$PDOStatement ) ? self::$link : self::$PDOStatement;
		$arrError = $obj->errorInfo ();
		$arrayError = array_unique ( $arrError );
		$arrayInfo = array ();
		foreach ( $arrError as $errObj ) {
			if (! $errObj [0] == '00000') {
				$arrayInfo [] = $errObj;
			}
		}
		if (count ( $arrayInfo ) > 2) { // 有错误信息
		                           // $this->rollback();
			self::$error = implode ( ',', $arrError ) . "<br/><br/> [ SQL statement ] : " . self::$queryStr;
			return false;
		}
		// 主要针对execute()方法抛出异常
		if (self::$queryStr == '') {
			throw new Exception ( 'Query was empty<br/><br/>[ SQL语句 ] :' );
		}
	}
	/**
	 * where分析
	 * 
	 * @access function
	 * @param mixed $where
	 *        	查询条件
	 * @return string
	 */
	static function parseWhere($where) {
		$whereStr = '';
		if (is_string ( $where ) || is_null ( $where )) {
			$whereStr = $where;
		}
		return empty ( $whereStr ) ? '' : ' WHERE ' . $whereStr;
	}
	/**
	 * order分析
	 * 
	 * @access function
	 * @param mixed $order
	 *        	排序
	 * @return string
	 */
	static function parseOrder($order) {
		$orderStr = '';
		if (is_array ( $order ))
			$orderStr .= ' ORDER BY ' . implode ( ' ', $order );
		else if (is_string ( $order ) && ! empty ( $order ))
			$orderStr .= ' ORDER BY ' . $order;
		return $orderStr;
	}

	/**
	 * fields分析
	 * 
	 * @access function
	 * @param mixed $fields        	
	 * @return string
	 */
	static function parseFields($fields) {
		if (is_array ( $fields )) {
			array_walk ( $fields, array (
					$this,
					'addSpecialChar' 
			) );
			$fieldsStr = implode ( ',', $fields );
		} else if (is_string ( $fields ) && ! empty ( $fields )) {
			if (false === strpos ( $fields, '`' )) {
				$fields = explode ( ',', $fields );
				$fieldsStr = implode ( ',', $fields );
			} else {
				$fieldsStr = $fields;
			}
		} else
			$fieldsStr = '*';
		return $fieldsStr;
	}
	/**
	 * sets分析,在更新数据时调用
	 * 
	 * @access function
	 * @param mixed $values        	
	 * @return string
	 */
	private function parseSets($sets) {
		$setsStr = '';
		if (is_array ( $sets )) {
			foreach ( $sets as $key => $val ) {
				$key = self::addSpecialChar ( $key );
				$val = self::fieldFormat ( $val );
				$setsStr .= "$key = " . $val . ",";
			}
			$setsStr = substr ( $setsStr, 0, - 1 );
		} else if (is_string ( $sets )) {
			$setsStr = $sets;
		}
		return $setsStr;
	}
	/**
	 * 字段格式化
	 * 
	 * @access function
	 * @param mixed $value        	
	 * @return mixed
	 */
	static function fieldFormat(&$value) {
		if (is_int ( $value )) {
			$value = intval ( $value );
		} else if (is_float ( $value )) {
			$value = floatval ( $value );
		} else if (preg_match ( '/^\(\w*(\+|\-|\*|\/)?\w*\)$/i', $value )) {
			// 支持在字段的值里面直接使用其它字段
			// 例如 (score+1) (name) 必须包含括号
			$value = $value;
		} else if (is_string ( $value )) {
			$value = '\'' . self::escape_string ( $value ) . '\'';
		}
		return $value;
	}
	/**
	 * 字段和表名添加` 符合
	 * 保证指令中使用关键字不出错 针对mysql
	 * 
	 * @access function
	 * @param mixed $value        	
	 * @return mixed
	 */
	static function addSpecialChar(&$value) {
		if ('*' == $value || false !== strpos ( $value, '(' ) || false !== strpos ( $value, '.' ) || false !== strpos ( $value, '`' )) {
			// 如果包含* 或者 使用了sql方法 则不作处理
		} elseif (false === strpos ( $value, '`' )) {
			$value = '`' . trim ( $value ) . '`';
		}
		return $value;
	}
	
	/**
	 * 去掉空元素
	 * 
	 * @access function
	 * @param mixed $value        	
	 * @return mixed
	 */
	static function removeEmpty($value) {
		return ! empty ( $value );
	}
	/**
	 * 执行查询 主要针对 SELECT, SHOW 等指令
	 * 
	 * @access function
	 * @param string $sql
	 *        	sql指令
	 * @return mixed
	 */
	static function query($sql = '') {
		// 获取数据库联接
		$link = self::$link;
		if (! $link)
			return false;
		self::$queryStr = $sql;
		// 释放前次的查询结果
		if (! empty ( self::$PDOStatement ))
			self::free ();
		self::$PDOStatement = $link->prepare ( self::$queryStr );
		$bol = self::$PDOStatement->execute ();
		// 有错误则抛出异常
		self::haveErrorThrowException ();
		return $bol;
	}
	
	/**
	 * 执行语句 针对 INSERT, UPDATE 以及DELETE
	 * 
	 * @access function
	 * @param string $sql
	 *        	sql指令
	 * @return integer
	 */
	static function execute($sql = '') {
		// 获取数据库联接
		$link = self::$link;
		if (! $link)
			return false;
		self::$queryStr = $sql;
		// 释放前次的查询结果
		if (! empty ( self::$PDOStatement ))
			self::free ();
		$result = $link->exec ( self::$queryStr );
		// 有错误则抛出异常
		self::haveErrorThrowException ();
		if (false === $result) {
			return false;
		} else {
			self::$numRows = $result;
			self::$lastInsertId = $link->lastInsertId ();
			return self::$numRows;
		}
	}
	
	/**
	 * 用于非自动提交状态下面的查询提交
	 * 
	 * @access function
	 * @return boolen
	 */
	static function commit() {
		$link = self::$link;
		if (! $link)
			return false;
		if (self::$transTimes > 0) {
			$result = $link->commit ();
			self::$transTimes = 0;
			if (! $result) {
				throw_exception ( self::$error () );
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 事务回滚
	 * 
	 * @access function
	 * @return boolen
	 */
	public function rollback() {
		$link = self::$link;
		if (! $link)
			return false;
		if (self::$transTimes > 0) {
			$result = $link->rollback ();
			self::$transTimes = 0;
			if (! $result) {
				throw_exception ( self::$error () );
				return false;
			}
		}
		return true;
	}
}