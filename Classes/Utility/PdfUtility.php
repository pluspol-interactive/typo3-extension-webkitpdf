<?php
namespace Tx\Webkitpdf\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Dev-Team Typoheads <dev@typoheads.at>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility function for the PDF generation.
 */
class PdfUtility implements SingletonInterface {

	/**
	 * Make sure that host of the URL matches TYPO3 host or one of allowed hosts given.
	 *
	 * @param string $url The URL to be sanitized
	 * @param array $allowedHosts
	 * @throws \Exception
	 * @return string The sanitized URL
	 */
	public function sanitizeURL($url, $allowedHosts) {

		$parts = parse_url($url);
		if ($parts['host'] !== GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')) {
			if (($allowedHosts && !in_array($parts['host'], $allowedHosts)) || !$allowedHosts) {
				throw new \Exception('Host "' . $parts['host'] . '" does not match TYPO3 host.');
			}
		}

		return $url;
	}

	/**
	 * Appends information about the FE user session to the URL.
	 * This is used to be able to generate PDFs of access restricted pages.
	 *
	 * @param   string $url The URL to append the parameters to
	 * @return  string  The processed URL
	 */
	public function appendFESessionInfoToURL($url) {
		/** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontendController */
		$frontendController = $GLOBALS['TSFE'];
		$uParts = parse_url($url);
		$params = '&FE_SESSION_KEY=' .
			rawurlencode(
				$frontendController->fe_user->id . '-' .
				md5(
					$frontendController->fe_user->id . '/' .
					$frontendController->TYPO3_CONF_VARS['SYS']['encryptionKey']
				)
			);
		// Add the session parameter ...
		$url .= ($uParts['query'] ? '' : '?') . $params;
		return $url;
	}

	/**
	 * Writes log messages to devLog
	 *
	 * Acts as a wrapper for t3lib_div::devLog()
	 * Additionally checks if debug was activated
	 *
	 * @param    string $title : title of the event
	 * @param    int $severity : severity of the debug event
	 * @param    array $dataVar : additional data
	 * @return    void
	 */
	public function debugLogging($title, $severity = -1, $dataVar = array()) {
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['webkitpdf']['debug'] === 1) {
			GeneralUtility::devlog($title, 'webkitpdf', $severity, $dataVar);
		}
	}

	/**
	 * Generates a nice looking filename without special chars.
	 *
	 * @param string $fileName
	 * @return string
	 */
	public function sanitizeFilename($fileName) {

		// Workaround to remove special chars from the filenames.
		$utf8FileSystemBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['UTF8filesystem'];
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['UTF8filesystem'] = FALSE;

		// TODO: use non deprecated function, see also: https://forge.typo3.org/issues/54357
		$fileUtility = GeneralUtility::makeInstance(BasicFileUtility::class);
		$fileName = $fileUtility->cleanFileName($fileName);

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['UTF8filesystem'] = $utf8FileSystemBackup;

		return $fileName;
	}
}
