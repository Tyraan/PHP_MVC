<?php

/**
 *  数据库连接类
 */
class LibDbConnect
{	/**
	 * @var 连接字符串
	 */
    public $connectionString;
    /*
     * @var 数据库用户名
     */
    public $username;
    /**
     * @var 数据库密码
     */
    public $password;
    // 是否是活动连接
	public $active = false;
	// pdo 实例
	private $_pdo;
    // pdo 的statement
    private $_statement;
    // 查询语句
    private $_sql;
    private static $connection;
    private $params = array();
    /**
     *  获取当前连接
     */
    public static function getConnection()
    {
       global $G_CONF;
       if(!empty(self::$connection) && self::$connection->active) {
            return self::$connection;
       }
       self::$connection = new LibDbConnect($G_CONF['db']['dsn'], $G_CONF['db']['username'], $G_CONF['db']['password']);
       return self::$connection;
    }

    /**
     *  数据库连接器的构造函数
     */
	public function __construct($dsn = '',$username='',$password='')
	{
		$this->connectionString = $dsn;
		$this->username = $username;
		$this->password = $password;
	}
    /**
     *  连接数据库
     */
	public function open()
	{ 
        if($this->_pdo===null)
		{
			if(empty($this->connectionString))
				throw new Exception('LibConnection.connectionString cannot be empty.');
			try
			{
				$this->_pdo=$this->createPdoInstance();
				$this->active=true;
			}catch(PDOException $e){
                die($e->getMessage().$e->getCode().$e->errorInfo);
			}
		}
		return $this->_pdo;
	}
	/*
	 *  关闭数据库
	 */
    public function close()
    {
        $this->_pdo=null;
		$this->_active=false;
    }

    /**
     *  建立pdo 实例
     */
	private function createPdoInstance(){
        $pdoClass = 'PDO';  // 这里使用变量方便以后扩展其他PDO类
        if(!class_exists($pdoClass)) 
        {
        	// 有些环境没有安装 PDO
        	die('unable to find PDO class');
        }
        $instance=new $pdoClass($this->connectionString , $this->username, $this->password);
        if(!$instance) {
        	die(' failed to open the DB connection.');
        }
        $instance->exec('SET NAMES utf8');
        return $instance;
	}
    public function setSql($sql){
    	$this->_sql = $sql;
    }
    public function getSql() {
    	return $this->_sql;
    }
	/*
	 *  prepare statement.
	 */
	private function prepare($sql = null)
	{
		if($this->_statement == null)
		{
			try
			{	if(empty($sql)){
					$sql = $this->getSql();
				}
				$this->_statement=$this->open()->prepare($sql);
			}
			catch(Exception $e)
			{
				throw new Exception('can\'t prepare sql'.$e);
			}
		}
	}
    /*
     *  绑定参数
     */
	public function bindParam($name, &$value, $dataType = null){
       $this->prepare();
       if($dataType===null){
		   $this->_statement->bindParam($name, $value, $this->getPdoType(gettype($value)));
	   }elseif($value===null){
			$this->_statement->bindParam($name,$value,$dataType);
	   }
	}
    /**
     *  绑定值
     */
	public function bindValue($name, $value, $dataType = null) {
       $this->prepare();
       if($dataType===null)
			$this->_statement->bindValue($name,$value,$this->getPdoType(gettype($value)));
	   else
			$this->_statement->bindValue($name,$value,$dataType);
		return $this;
	}
    /*
     *  获取pdo类型
     */
	public function getPdoType($type)
	{
		static $map=array
		(
			'boolean'=>PDO::PARAM_BOOL,
			'integer'=>PDO::PARAM_INT,
			'string'=>PDO::PARAM_STR,
			'resource'=>PDO::PARAM_LOB,
			'NULL'=>PDO::PARAM_NULL,
		);
		return isset($map[$type]) ? $map[$type] : PDO::PARAM_STR;
	}
    public function execute($sql = null)
    {
       if(empty($sql)) {
     	  $this->prepare();
	   }else{
		   $this->prepare($sql);
	   }
		$result=$this ->_statement->execute() ;
		// 有错误则抛出异常
		if ( false === $result) {
			return false ;
		} else {
			return $result;

		}
    }
    /*
     *  执行sql
     */
    public function getAll($params=array())
    {
    	// 执行查询
		$this->prepare();
        $params=array_merge($this->params,$params);

        if($params===array()){
			$ret = $this->_statement->execute();
		}
		else{
			$ret = $this->_statement->execute($params);
		}
		//$n=$this->_statement->rowCount();
        if($ret){
            $data = $this->_statement->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }
        return array();
    }
}