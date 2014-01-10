<?php
namespace View;

  class View{
	
	private static $instance = null;
	private $storage = array();
	
	private function __construct(){}
	
	public static function getInstance(){
		if(is_null(self::$instance))
			self::$instance = new View;
		return self::$instance;
	}
	
	public function __set($key, $value){
		$this->storage[$key] = $value;
	}
	public function __get($key){
		return $this->storage[$key];
	}
	function render($tpl){
		ob_start();
		require_once('header.php');
		require_once($tpl);	
		require_once('footer.php');
		echo ob_get_clean();

	}
	function simpleRender($tpl){
		ob_start();
		require_once($tpl);
		echo ob_get_clean();
	}
    public function meta($pageTitle, $url, $shopId = "", $isSubPage = false){
        $this->pageTitle = $pageTitle;
        $this->url = $url;
        $this->shopId = $shopId;
        $this->isSubPage = $isSubPage;
    }
}