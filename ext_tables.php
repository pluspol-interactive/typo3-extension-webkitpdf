<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Add static file for plugin
ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/', 'WebKit PDF');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,pages,select_key';

ExtensionManagementUtility::addPlugin(array('LLL:EXT:webkitpdf/Resource/Private/Language/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY . '_pi1'), 'list_type');

