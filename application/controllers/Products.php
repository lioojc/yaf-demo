<?php
/**
 * @name ProductController
 * @author ljc
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class ProductsController extends Yaf_Controller_Abstract {

	/** 
	 * 默认动作
	 * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
	 */
	public function viewAction() {
		//1. fetch query
		$ident = $this->getRequest()->getParam("ident");

		//2. assign
		$this->getView()->assign("ident", $ident);

		//3. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
		return TRUE;
	}
}
