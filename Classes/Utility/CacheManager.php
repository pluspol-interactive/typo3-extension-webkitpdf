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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Cache handling for generated PDF documents.
 */
class CacheManager {

	protected $conf;

	protected $isEnabled;

	public function __construct($conf = array()) {
		$this->conf = $conf;
		$this->isEnabled = TRUE;
		$minutes = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['webkitpdf']['cacheThreshold'];
		if (intval($minutes) === 0) {
			$this->isEnabled = FALSE;
		}
		if (intval($this->conf['disableCache']) === 1) {
			$this->isEnabled = FALSE;
		}
	}

	public function clearCachePostProc(&$params, &$pObj) {
		$now = time();

		//cached files older than x minutes.
		$minutes = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['webkitpdf']['cacheThreshold'];
		$threshold = $now - $minutes * 60;

		$res = $this->getDatabaseConnection()->exec_SELECTquery('uid,crdate,filename', 'tx_webkitpdf_cache', 'crdate<' . $threshold);
		if ($res && $this->getDatabaseConnection()->sql_num_rows($res) > 0) {
			$filenames = array();
			while (($row = $this->getDatabaseConnection()->sql_fetch_assoc($res)) !== FALSE) {
				$filenames[] = $row['filename'];
			}
			$this->getDatabaseConnection()->sql_free_result($res);
			$this->getDatabaseConnection()->exec_DELETEquery('tx_webkitpdf_cache', 'crdate<' . $threshold);
			foreach ($filenames as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}

			// Write a message to devlog
			if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['webkitpdf']['debug'] === 1) {
				GeneralUtility::devLog('Clearing cached files older than ' . $minutes . ' minutes.', 'webkitpdf', -1);
			}
		}
	}

	public function isInCache($urls) {
		$found = FALSE;
		if ($this->isEnabled) {
			$res = $this->getDatabaseConnection()->exec_SELECTquery('uid', 'tx_webkitpdf_cache', "urls='" . md5($urls) . "'");
			if ($res && $this->getDatabaseConnection()->sql_num_rows($res) === 1) {
				$found = TRUE;
				$this->getDatabaseConnection()->sql_free_result($res);
			}
		}
		return $found;
	}

	public function store($urls, $filename) {
		if ($this->isEnabled) {
			$insertFields = array(
				'crdate' => time(),
				'filename' => $filename,
				'urls' => md5($urls)
			);
			$this->getDatabaseConnection()->exec_INSERTquery('tx_webkitpdf_cache', $insertFields);
		}
	}

	public function get($urls) {
		$filename = FALSE;
		if ($this->isEnabled) {
			$res = $this->getDatabaseConnection()->exec_SELECTquery('filename', 'tx_webkitpdf_cache', "urls='" . md5($urls) . "'");
			if ($res && $this->getDatabaseConnection()->sql_num_rows($res) === 1) {
				$row = $this->getDatabaseConnection()->sql_fetch_assoc($res);
				$filename = $row['filename'];
				$this->getDatabaseConnection()->sql_free_result($res);
			}
		}
		return $filename;
	}

	public function isCachingEnabled() {
		return $this->isEnabled;
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		returN $GLOBALS['TYPO3_DB'];
	}
}