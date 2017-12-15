<?php
/**
 * File: Helper.php
 * Functionality: Model, function loader, raiseError, generateSign, response
 * Author: 大眼猫
 * Date: 2013-5-8
 */

abstract class Helper {

	private static $obj;

	/**
	 * Import function
	 *
	 * @param string file to be imported
	 * @return null
	 */
	public static function import($file) {
		$function = 'F_'.ucfirst($file);
		$f_file   = FUNC_PATH.'/'.$function.'.php';

		if(file_exists($f_file)){
			Yaf_Loader::import($f_file);
			unset($file, $function, $f_file);
		}else{
			$traceInfo = debug_backtrace();
			$error = 'Function '.$file.' NOT FOUND !';
			self::raiseError($traceInfo, $error);
		}
	}
	
	/**
	 * Load model
	 * <br />After loading a model, the new instance will be added into $obj immediately,
	 * <br />which is used to make sure that the same model is only loaded once per request !
	 *
	 * @param string => model to be loaded
	 * @return new instance of $model or raiseError on failure !
	 */
	public static function load($model) {
		$path = '';

		//分组功能
		if(strpos($model, '/') !== FALSE){
			list($category, $model) = explode('/', $model);
			$path = '/'. $category;
		}
		
		$hash = md5($path . $model);

		if(isset(self::$obj[$hash])) {
			return self::$obj[$hash];
		}

		$default = FALSE;
		$file = MODEL_PATH .$path .'/M_'.ucfirst($model).'.php';
		
		if(!file_exists($file)) {
			// 加载默认模型, 减少没啥通用方法的模型
			$default = TRUE;
			$table   = strtolower($model);
			$model   = 'M_Default';
			$file    = MODEL_PATH.'/'.$model.'.php';
		}

		if(PHP_OS == 'Linux'){
			Yaf_Loader::import($file);
		}else{
			require_once $file;
		}

		try{
			if($default){
				self::$obj[$hash] = new $model($table);
			}else{
				$model = 'M_'.$model;
				self::$obj[$hash] = new $model;	
			}
			
			unset($model, $default, $table, $file, $path, $category);
			return self::$obj[$hash];
		}catch(Exception $error) {
			$traceInfo = debug_backtrace();
			$error = 'Load model '.$model.' FAILED !';
			Helper::raiseError($traceInfo, $error);
		}
	}

	/**
	 * Generate sign
	 * @param array $parameters
	 * @return new sign
	 */
	public static function generateSign($parameters){
		$signPars = '';
		foreach($parameters as $k => $v) {
			if(isset($v) && 'sign' != $k) {
				$signPars .= $k . '=' . $v . '&';
			}
		}

		$signPars .= 'key='.API_KEY;
		return strtolower(md5($signPars));
	}
	
	
	/**
	 * Response
	 * 
	 * @param string $format : json, xml, jsonp, string
	 * @param array $data: 
	 * @param boolean $die: die if set to true, default is true
	 */
	public static function response($data, $format = 'json', $die = TRUE) {
		switch($format){
			default:
			case 'json':
				$file = FUNC_PATH.'/F_String.php';
				Yaf_Loader::import($file);
				if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){ 
					$data = JSON($data);
				}else if(isset($_REQUEST['ajax'])){
					$data = JSON($data);
				}else{
					//pr($data); die; // URL 测试打印数组出来
					echo json_encode($data, JSON_UNESCAPED_UNICODE); die;
				}
			break;
			
			case 'jsonp':
				$data = $_GET['jsoncallback'] .'('. json_encode($data) .')';
			break;
			
			case 'string':
			break;
		}

		echo $data;
		
		if($die){
			die;
		}
	}


	/**
	 * Raise error and halt if it is under DEV
	 *
	 * @param string debug back trace info
	 * @param string error to display
	 * @param string error SQL statement
	 * @return null
	 */
	public static function raiseError($trace, $error, $sql = '') {
		// YOF 自定义错误编号
		$errno   = 9999; 
		$errFile = $trace[0]['file'];
		$errLine = $trace[0]['line'];

		// Call yofErrorHandler to show error
		self::yofErrorHandler($errno, $error, $errFile, $errLine, $sql);
	}

	public static function getConfig($file){
		$f = APP_PATH.'/conf/'.$file;
		if(file_exists($f)){
			return include $f;
		}else{
			$traceInfo = debug_backtrace();
			$error = 'File '.$f.' NOT FOUND ';
			self::raiseError($traceInfo, $error);
		}
	}

	/*
	 * DEV 下我们使用自定义输出错误, 这样能更好的 debug
	 * PRODUCT 下则报 500, 记录错误至指定日志
	 * 注: 由于这些不能输出至 html, 使用了比较恶心的处理方式. 
	 *     若有更好办法, 请告知, 谢谢!
	 */
	public static function yofErrorHandler($errno, $errstr, $errfile, $errline, $sql = ''){
		if(ENV != 'DEV'){
			file_put_contents(LOG_FILE, CUR_DATETIME.' '.$errno.PHP_EOL,   FILE_APPEND);
			file_put_contents(LOG_FILE, CUR_DATETIME.' '.$errstr.PHP_EOL,  FILE_APPEND);
			file_put_contents(LOG_FILE, CUR_DATETIME.' '.$errfile.PHP_EOL, FILE_APPEND);
			file_put_contents(LOG_FILE, CUR_DATETIME.' '.$errline.PHP_EOL, FILE_APPEND);
			
			header('HTTP/1.1 500 Internal Server Error');
			$html = '<html>
				<head><title>500 Internal Server Error</title></head>
				<body bgcolor="white">
				<center><h1>500 Internal Server Error</h1></center>
				<hr>
				</body>
			</html>';
			die($html);
		}else{
			$error = '<link href="CSS_PATH/bootstrap.min.css" rel="stylesheet">
				<link href="CSS_PATH/bootstrap-responsive.min.css" rel="stylesheet">
				<link href="CSS_PATH/docs.css" rel="stylesheet">
				<script src="JS_PATH/jquery-1.7.min.js"></script>';
			
			$error .= '<style>
					body{
							font-family:"ff-tisa-web-pro-1","ff-tisa-web-pro-2","Lucida Grande","Helvetica Neue",Helvetica,Arial,"Hiragino Sans GB","Hiragino Sans GB W3","Microsoft YaHei UI","Microsoft YaHei","WenQuanYi Micro Hei",sans-serif;
							padding: 10px;
						}
					</style>';
			$error .= '<link href="CSS_PATH/prettify.css" rel="stylesheet">';
			$error .= "<script> 
					  $(function(){
						$('#errorTab a').click(function(e){
							e.preventDefault();
							$('#errorTab a').parent().removeClass('active'); 
							$(this).parent().addClass('active');

							// 切换 DIV
							$('.tab-content div').removeClass('active');
							var id = $(this).attr('val');
							$('#'+id).addClass('active');
						}) 
					  }) 
					</script>";
			$error .= '<h4>Error : [ERROR_DESC]</h4>
						<ul class="nav nav-tabs" id="errorTab"> 
						  <li class="active"><a val="general" href="#general">General</a></li> 
						  <li><a val="request" href="#request">Request</a></li> 
						  <li><a val="router" href="#router">Router</a></li>
						  <li><a val="modules" href="#modules">Modules</a></li>
						  <li><a val="config" href="#config">Config</a></li> 
						  <li><a val="get" href="#get">GET</a></li>
						  <li><a val="post" href="#post">POST</a></li> 
						  <li><a val="cookie" href="#cookie">COOKIE</a></li> 
						  <li><a val="session" href="#session">SESSION</a></li> 
						  <li><a val="server" href="#server">SERVER</a></li>
						  <li><a val="sql" href="#sql">SQL</a></li>
						</ul>';

			$error .= '<div class="tab-content">
						  <div class="tab-pane active" id="general">[GENERAL_ERR]</div> 
						  <div class="tab-pane" id="request">[REQUEST_ERR]</div> 
						  <div class="tab-pane" id="router">[ROUTER_ERR]</div> 
						  <div class="tab-pane" id="modules">[MODULES_ERR]</div>
						  <div class="tab-pane" id="config">[CONFIG_ERR]</div>
						  <div class="tab-pane" id="get">[GET_ERR]</div>
						  <div class="tab-pane" id="post">[POST_ERR]</div>
						  <div class="tab-pane" id="cookie">[COOKIE_ERR]</div>
						  <div class="tab-pane" id="session">[SESSION_ERR]</div>
						  <div class="tab-pane" id="server">[SERVER_ERR]</div>
						  <div class="tab-pane" id="sql">[SQL_ERR]</div>
						</div>';

			$search  = array('CSS_PATH', 'JS_PATH');
			$replace = array(CSS_PATH, JS_PATH);
			$error = str_replace($search, $replace, $error);

			// Environ
			$environ = Yaf_Application::app()->environ();

			// General
			$generalErr = '<ul>';
			$generalErr .= '<li>Environ: '.$environ.'</li>';
			$generalErr .= '<li>Error NO: '.$errno.'</li>';
			$generalErr .= '<li>Error: '.$errstr.'</li>';
			$generalErr .= '<li>File: '.$errfile.'</li>';
			$generalErr .= '<li>Line: '.$errline.'</li>';
			$generalErr .= '<li>URL: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'</li>';
			$generalErr .= '</ul>';

			$error = str_replace('[ERROR_DESC]',  $errstr, $error);
			$error = str_replace('[GENERAL_ERR]', $generalErr, $error);

			// Request
			$request = Yaf_Dispatcher::getInstance()->getRequest();
			$requestErr = '<ul>';
			$requestErr .= '<li>Module: '.$request->module.'</li>';
			$requestErr .= '<li>Controller: '.$request->controller.'</li>';
			$requestErr .= '<li>Action: '.$request->action.'</li>';
			$requestErr .= '<li>URI: '.$request->getRequestUri().'</li>';
			$requestErr .= '</ul>';

			$error = str_replace('[REQUEST_ERR]', $requestErr, $error);

			// Routers
			$router = Yaf_Dispatcher::getInstance()->getRouter();
			$routers = $router->getRoutes();

			// TODO: Convert each route to array !
			// if($routers){
			//     Helper::import('Array');
			//     foreach($routers as $key => $val){
			//         //$val = array($val);
			//         //pr($key);
			//         pr($val); continue;
			//     }
			// }

			// Current Router
			$currentRouter = $router->getCurrentRoute();
			$routerErr = '<ul>';
			$routerErr .= '<li>Current Router: '.$currentRouter.'</li>';
			$routerErr .= '</ul>';
			$error = str_replace('[ROUTER_ERR]', $routerErr, $error);

			// Modules
			$modules = Yaf_Application::app()->getModules();

			$moduleErr = '<ul>';
			foreach($modules as $val){
				$moduleErr .= '<li>'.$val.'</li>';
			}
			$moduleErr .= '</ul>';

			$error = str_replace('[MODULES_ERR]', $moduleErr, $error);

			// Config
			$config = Yaf_Application::app()->getConfig();
			$configErr = '<ul>';
			foreach($config as $key => $val){
				if($key != 'application'){
					// Hide PSWD of MySQL
					if(strpos($key, 'PSWD') !== FALSE){
						$val = '******';
					}   
					$configErr .= '<li>'.$key. ' => '.$val.'</li>';
				}
			}
			$configErr .= '</ul>';
			$error = str_replace('[CONFIG_ERR]', $configErr, $error);

			// $_GET
			$getErr = '<ul>';
			foreach($_GET as $key => $val){
				$getErr .= '<li>'.$key. ' => '.$val.'</li>';
			}
			$getErr .= '</ul>';
			$error = str_replace('[GET_ERR]', $getErr, $error);

			// $_POST
			$postErr = '<ul>';
			foreach($_POST as $key => $val){
				$postErr .= '<li>'.$key. ' => '.$val.'</li>';
			}
			$postErr .= '</ul>';
			$error = str_replace('[POST_ERR]', $postErr, $error);

			// $_COOKIE
			$cookieErr = '<ul>';
			foreach($_COOKIE as $key => $val){
				$cookieErr .= '<li>'.$key. ' => '.$val.'</li>';
			}
			$cookieErr .= '</ul>';
			$error = str_replace('[COOKIE_ERR]', $cookieErr, $error);

			// $_SESSION
			$sessionErr = '<ul>';
			if($_SESSION){
				foreach($_SESSION as $key => $val){
					$sessionErr .= '<li>'.$key. ' => '.$val.'</li>';
				}
			}
			$sessionErr .= '</ul>';
			$error = str_replace('[SESSION_ERR]', $sessionErr, $error);

			// $_SERVER
			$serveErr = '<ul>';
			foreach($_SERVER as $key => $val){
				$serveErr .= '<li>'.$key. ' => '.$val.'</li>';
			}
			$serveErr .= '</ul>';
			$error = str_replace('[SERVER_ERR]', $serveErr, $error);

			// SQL
			if($sql){
				$sqlErr = '<ul>';
				$sqlErr .= '<li>'.$sql.'</li>';
				$sqlErr .= '</ul>';
				$error = str_replace('[SQL_ERR]', $sqlErr, $error);
			}else{
				$error = str_replace('[SQL_ERR]', '', $error);
			}

			echo $error; die;
		}
	}

}
