<?php

// Define path to application directory

defined('DIR_BASE')  || define('DIR_BASE',realpath(dirname(__FILE__)));

defined('APPLICATION_PATH')  || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

defined('APPLICATION_UPLOADS_DIR') || define('APPLICATION_UPLOADS_DIR','resultData/');

defined('APPLICATION_LOGS_DIR') || define('APPLICATION_LOGS_DIR','Logs/');

defined('APPLICATION_DOWNLOADS_DIR') || define('APPLICATION_DOWNLOADS_DIR','resultData/');
    

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'staging'));

/*echo APPLICATION_PATH;
exit;*/
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
    realpath(dirname(__FILE__)),
	    realpath(dirname(__FILE__))."/public",
	    realpath(APPLICATION_PATH . '/controllers/helpers')
)));

/** Zend_Application */
require_once 'Zend/Application.php';

/** Zend_Loader_Autoloader */
require_once 'Zend/Loader/Autoloader.php';

/** Zend_Config_Ini */
require_once 'Zend/Config/Ini.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Keyword_');

$appConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
define('BASE_URL', $appConfig->baseurl);

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()->run();