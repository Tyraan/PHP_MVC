<?php
/**
 *  一些全局函数.
 */

if(!function_exists('loadController')) {
    /**
     *  加载控制器
     *
     *  @param string $name 要加载的类文件
     */
    function loadController($name)
    {
       global $G_LOADED;
       
       if( isset($G_LOADED[$name])) {
       	   return new $G_LOADED[$name]['class']();
       }
       // 简单加载，没做异常处理.
       $controllerFile = APP_ROOT . 'controller/' . lcfirst($name) . '.php';
       if(file_exists($controllerFile)) {
       	   $G_LOADED[$name] = array('class' => $name, 'path' => $controllerFile);
       	   require $controllerFile;
           return new $name();
       }
         die('can\'t init controller: '. $name);
    }
}

if(!function_exists('loadView')) {
	/*
	 *  加载视图文件内容
	 */
	function loadView($data,$viewname){
       $viewFile = VIEW_PATH . '/' . $viewname . '.php';
       if(file_exists($viewFile)) {
           // 把传入的数组放入作用域
           extract($data);
       	   ob_start();
       	   include $viewFile;
       	   $content = ob_get_contents();
       	   ob_end_clean();
       	   return $content;
       }
       die('can\'t find view '. $viewname);
	}
}

if(!function_exists('loadLib')) {
  /**
   *  加载系统库
   */
  function loadLib($name){
     global $G_LOADED;
     if(isset($G_LOADED['lib_' . $name])){
        return new $G_LOADED['lib_' . $name]['class']();
     }
     //包含
     $libFile = LIB_PATH . '/lib_' . $name. '.php';
     if(file_exists($libFile)) {
        $className = 'Lib'.$name;
        $G_LOADED['lib_'.$name] = array('class'=> 'Lib'.$name, 'path'=>$libFile);
        require $libFile;
        return new $className();
     }

     die('can\'t load lib'. $className);

  }
}

if(!function_exists('loadHelper')) {
  /**
   * ... 提供加载helper 的方法。
   */
  function loadHelper($name) {
     global $G_LOADED;
     if(isset($G_LOADED['helper_' . $name])) {
        return new $G_LOADED['helper_' . $name]['class']();
     }
     // 包含
     $helperFile = HELPER_PATH . '/helper_' . $name. '.php';
     if(file_exists($libFile)) {
        $className = 'Helper'.$name;
        $G_LOADED['helper_'.$name] = array('class'=> 'Helper'.$name, 'path'=>$helperFile);
        require $helperFile;
        return new $className();
     }
     die('can\'t load helper'. $className);   
  }
}

if(!function_exists('toUrl')) {
  /*
   * 根据controll和action构建url
   */
  function toUrl($controll, $action) {
     // 先最简单实现，以后根据 url_info 生成
     return "index.php?c={$controll}&a={$action}";  
  }


}
if(!function_exists('cmpByPrice')){
    function cmpByPrice($array1,$array2){
  if($array1['price'] == $array2['price']){
      return 0;
  }else{
      return $array1['price'] > $array2['price'] ? 1:-1;
  }
}
}
if(!function_exists('cmpByUptime')){
    function cmpByUptime($array1,$array2){
        if($array1['uptime'] == $array2['uptime']){
            return 0;
        }else{
            return $array1['uptime'] > $array2['uptime'] ? 1:-1;
        }
    }
}
function showVar(){
    echo '$_GET';
    var_dump($_GET);
    echo'<br/><hr></hr>';
    echo '$_POST';
    var_dump($_POST);
    echo'<br/><hr></hr>';

}
