<?php
/**
 * @name FooController
 * @author ljc
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class FooController extends Yaf_Controller_Abstract {

	/** 
	 * 默认动作
	 * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
	 */
	public function barAction() {
		$dummy = $this->getRequest()->getParam("dummy");
		$this->getView()->assign("router", "foo/bar?dummy=".$dummy);
		return TRUE;
	}
}
