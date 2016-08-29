<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/* @var string $_EXTKEY */

// Add static file for plugin
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/', 'WebKit PDF');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,pages,select_key';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    ['LLL:EXT:webkitpdf/Resource/Private/Language/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY . '_pi1'],
    'list_type'
);

