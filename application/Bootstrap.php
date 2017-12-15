<?php
/**
 * @name Bootstrap
 * @author ljc
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

	public function _initConfig() {
		//把配置保存起来
		$arrConfig = Yaf_Application::app()->getConfig();
		Yaf_Registry::set('config', $arrConfig);
	}

	public function _initPlugin(Yaf_Dispatcher $dispatcher) {
		//注册一个插件
		$objSamplePlugin = new SamplePlugin();
		$dispatcher->registerPlugin($objSamplePlugin);
	}

	public function _initRoute(Yaf_Dispatcher $dispatcher) {
		//在这里注册自己的路由协议,默认使用简单路由
		$router = Yaf_Dispatcher::getInstance()->getRouter();
		if (Yaf_Registry::get("config")->routes) {
			$router->addConfig(Yaf_Registry::get("config")->routes);
		}

		// 添加一个名为regex的路由协议
		// $regex = new Yaf_Route_Regex(
		// 	'/list\/([a-zA-Z-_0-9]+)/',
		// 	array(
		// 		'controller' => 'list',
		// 		'action' => 'index'
		// 	),
		// 	array(
		// 		1=>"var"
		// 	)
		// );
		// $router->addRoute('regex', $regex);

		// // 添加一个名为rewrite的路由协议
		// $rewrite = new Yaf_Route_Rewrite(
		// 	'product/:ident',
		// 	array(
		// 		'controller' => 'products',
		// 		'action' => 'view'
		// 	)
		// );
		// $router->addRoute('rewrite', $rewrite);

		// 添加一个名为map的路由协议
		// $route = new Yaf_Route_Map(false, '_');
		// $router->addRoute("map", $route);
		
		// // // 添加一个名为supervar的路由协议
		// $supervar = new Yaf_Route_Supervar("r");
		// $router->addRoute("supervar", $supervar);

		// // // 添加一个名为simple的路由协议
		// $simple = new Yaf_Route_Simple("m", "c", "a");
		// $router->addRoute("simple", $simple);
	}
	
	public function _initView(Yaf_Dispatcher $dispatcher) {
		//在这里注册自己的view控制器 - smarty
		$smarty = new Smarty_Adapter(null, Yaf_Registry::get("config")->get("smarty"));
		$dispatcher->setView($smarty);
	}
}
