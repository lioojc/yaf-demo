<?php
/**
 * @name SampleModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author ljc
 */
class SampleModel extends Model {
	public function __construct() {
		$this->table = 'think_qcms';
		parent::__construct();
	}
	
	public function selectSample() {
		// return $this->Select();
		// return $this->Where(array("pid"=>"3700350985"))->Select();
		// return $this->Order("pid")->Limit(3)->Select();
		return $this->Field(array("pid", "path"))->Limit(5)->Select();
		// return $this->Total();
	}

	public function insertSample($arrInfo) {
		return true;
	}
}
