<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

use Tx\Webkitpdf\Utility\CacheManager as WebkitpdfCacheManager;

// Unserializing the configuration so we can use it here
$_EXTCONF = unserialize($_EXTCONF);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['cacheLifetime'] = isset($_EXTCONF['cacheLifetime']) ? intval($_EXTCONF['cacheLifetime']) : 2592000;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['debug'] = isset($_EXTCONF['debug']) ? boolval($_EXTCONF['debug']) : FALSE;

$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][WebkitpdfCacheManager::CACHE_IDENTIFIER] = array(
	'frontend' => 'TYPO3\CMS\Core\Cache\Frontend\VariableFrontend',
	'backend' => 'TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend',
	'options' => array(
		'defaultLifetime' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['cacheLifetime']
	),
	'groups' => array('pages', 'all')
);