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

	/**
	 * The identifier of the cache that is used to store the generated PDFs.
	 *
	 * @const
	 */
	const CACHE_IDENTIFIER = 'tx_webkitpdf_pdf';

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
	 */
	protected $cache;

	/**
	 * Initializes the cache frontend.
	 */
	public function __construct() {
		$this->cache = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class)->getCache(static::CACHE_IDENTIFIER);
	}

	/**
	 * Returns TRUE when there is a cache entry available for the given URLs.
	 *
	 * @param array $urls Comma seperated list of URLs that were used to generate the PDF.
	 * @return bool
	 */
	public function isInCache(array $urls) {
		return $this->cache->has($this->getEntryIdentifier($urls));
	}

	/**
	 * Stores the contents of the given file in the cache.
	 *
	 * When page IDs are provided the cache entry will be tagged with these page IDs to make
	 * sure the PDF cache is flushed when the page changes.
	 *
	 * @param array $urls Array of the URLs that should be retrieved by the PDF generator.
	 * @param string $fileIdentifier The file identifier of the generated PDF file.
	 * @param array $pageIds Array containing the page UIDs for which the PDF was generated.
	 */
	public function store(array $urls, $fileIdentifier, $pageIds = array()) {

		// Convert the page UIDs to cache tags.
		array_walk($pageIds, function (&$tag) {
			$tag = 'pageId_' . intval($tag);
		});

		$this->cache->set($this->getEntryIdentifier($urls), $fileIdentifier, $pageIds);
	}

	/**
	 * Fetches the file identifier of the PDF document.
	 *
	 * @param array $urls Array of the URLs that should be retrieved by the PDF generator.
	 * @return string The file identifier.
	 */
	public function get(array $urls) {
		return $this->cache->get($this->getEntryIdentifier($urls));
	}

	/**
	 * Removes the entry for the given URLs from the cache.
	 *
	 * @param array $urls Array of the URLs that should be retrieved by the PDF generator.
	 * @return string The PDF file contents.
	 */
	public function remove(array $urls) {
		$this->cache->remove($this->getEntryIdentifier($urls));
	}

	/**
	 * Builds the cache entry identifiert for the given URLs.
	 *
	 * @param array $urls Array of the URLs that should be retrieved by the PDF generator.
	 * @return string The cache identifier that should be used for the given URL array.
	 */
	protected function getEntryIdentifier(array $urls) {
		return sha1(implode(', ', $urls));
	}
}