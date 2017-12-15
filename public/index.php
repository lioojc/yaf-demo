<?php
define("APP_PATH",  dirname(dirname(__FILE__)));
define("SMARTY_DIR", APP_PATH . "/application/library/Smarty/libs/" );
define('CUR_DATE', date('Y-m-d'));
define('TB_PK', 'id');  // 表的主键, 用于 SelectByID 等
define('ENV', ini_get('yaf.environ'));
define('CUR_DATETIME',  date('Y-m-d H:i:s'));
define('LOG_FILE', APP_PATH.'/log/'.CUR_DATE.'.log');

require_once(SMARTY_DIR . 'Smarty.class.php');

$app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
$app->bootstrap()  //call bootstrap methods defined in Bootstrap.php
	->run();