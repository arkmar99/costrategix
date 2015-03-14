<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initTestAutoLoader(){

		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace('Keyword_');

	}

	public function _initConfig()
	{
		$registry = new Zend_Registry(array(), ArrayObject::ARRAY_AS_PROPS);
		Zend_Registry::setInstance($registry);

		//$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
		$config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/application.xml');

		$registry->notkeywords 			= $config->keyword->notkeywords;

	}
}

