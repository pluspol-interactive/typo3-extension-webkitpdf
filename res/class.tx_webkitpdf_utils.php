<?php

class tx_webkitpdf_utils {
	
	/**
	 * Escapes a URI resource name so it can safely be used on the command line.
	 *
	 * @param   string  $inputName URI resource name to safeguard, must not be empty
	 * @return  string  $inputName escaped as needed
	 */
	static public function wrapUriName($inputName) {
		return escapeshellarg($inputName);
	}

	/**
	 * Checks if the given URL's host matches the current host
	 * and sanitizes the URL to be used on command line.
	 *
	 * @param string $url The URL to be sanitized
	 * @param array $allowedHosts
	 * @throws Exception
	 * @return string The sanitized URL
	 */
	static public function sanitizeURL($url, $allowedHosts) {
		
		//Make sure that host of the URL matches TYPO3 host or one of allowed hosts set in TypoScript.
		$parts = parse_url($url);
		if($parts['host'] !== t3lib_div::getIndpEnv('TYPO3_HOST_ONLY')) {
			if(($allowedHosts && !in_array($parts['host'], $allowedHosts)) || !$allowedHosts) {
				throw new Exception('Host "' . $parts['host'] . '" does not match TYPO3 host.');
			}
		}
		$url = self::wrapUriName($url);
		return $url;
	}
	
	/**
	 * Appends information about the FE user session to the URL.
	 * This is used to be able to generate PDFs of access restricted pages.
	 *
	 * @param   string  $url The URL to append the parameters to
	 * @return  string  The processed URL
	 */
	static public function appendFESessionInfoToURL($url) {
		if(strpos($url, '?') !== FALSE) {
			$url .= '&';
		} else {
			$url .= '?';
		}
		
		$url .= 'FE_SESSION_KEY=' . 
				rawurlencode(
					$GLOBALS['TSFE']->fe_user->id . 
					'-' . 
					md5(
						$GLOBALS['TSFE']->fe_user->id . 
						'/' . 
						$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
					)
				)
		;
		return $url;
	}
	
	/**
	 * Writes log messages to devLog
	 *
	 * Acts as a wrapper for t3lib_div::devLog()
	 * Additionally checks if debug was activated
	 *
	 * @param	string		$title: title of the event
	 * @param	string		$severity: severity of the debug event
	 * @param	array		$dataVar: additional data
	 * @return	void
	 */
	static public function debugLogging($title, $severity = -1, $dataVar = array()) {
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['webkitpdf']['debug'] === 1) {
			t3lib_div::devlog($title, 'webkitpdf', $severity, $dataVar);
		}
	}
	
	/**
	 * Makes sure that given path has a slash as first and last character
	 *
	 * @param	string		$path: The path to be sanitized
	 * @return	Sanitized path
	 */
	static public function sanitizePath($path, $trailingSlash = TRUE) {
		
		// slash as last character
		if($trailingSlash && substr($path, (strlen($path) - 1)) !== '/') {
			$path .= '/';
		}
		
		//slash as first character
		if(substr($path, 0, 1) !== '/') {
			$path = '/' . $path;
		}
		
		return $path;
	}
	
	/**
	 * Generates a random hash
	 *
	 * @return	The generated hash
	 */
	static public function generateHash(){
		$result = '';
		$charPool = '0123456789abcdefghijklmnopqrstuvwxyz';
		for($p = 0; $p < 15; $p++) {
			$result .= $charPool[mt_rand(0, strlen($charPool) - 1)];
		}
		return sha1(md5(sha1($result)));
	}
}

?>