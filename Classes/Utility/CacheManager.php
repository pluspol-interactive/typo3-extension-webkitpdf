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

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
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
	 * Will be called by the garbage collection of the CacheDatabaseBackend
	 * and removes all PDF files from the directory that do not have a
	 * valid matching cache entry.
	 */
	public function collectGarbage() {
		foreach ($this->getCacheDirectory()->getFiles() as $file) {
			if ($file->getName() === '.htaccess') {
				continue;
			}
			if (!$this->cache->has($file->getName())) {
				$file->delete();
			}
		}
	}

	/**
	 * Fetches the file identifier of the PDF document.
	 *
	 * @param array $urls Array of the URLs that should be retrieved by the PDF generator.
	 * @return File The file identifier.
	 */
	public function get(array $urls) {

		$entryIdentifier = $this->getEntryIdentifier($urls);
		$cacheIsValid = $this->cache->has($entryIdentifier);
		$cachedPdf = $this->getCachedPdfForIdentifier($entryIdentifier, !$cacheIsValid);

		if (!isset($cachedPdf) && $cacheIsValid) {
			$this->cache->remove($entryIdentifier);
		}

		return $cachedPdf;
	}

	/**
	 * Returns TRUE when there is a cache entry available for the given URLs.
	 *
	 * @param array $urls Comma seperated list of URLs that were used to generate the PDF.
	 * @return bool
	 */
	public function isInCache(array $urls) {
		$cachedPdf = $this->get($urls);
		return isset($cachedPdf);
	}

	/**
	 * Removes the entry for the given URLs from the cache.
	 *
	 * @param array $urls Array of the URLs that should be retrieved by the PDF generator.
	 * @return string The PDF file contents.
	 */
	public function remove(array $urls) {
		$entryIdentifier = $this->getEntryIdentifier($urls);
		$this->getCachedPdfForIdentifier($entryIdentifier, TRUE);
		$this->cache->remove($this->getEntryIdentifier($urls));
	}

	/**
	 * Stores the given temporary PDF file in the cache directory.
	 *
	 * When page IDs are provided the cache entry will be tagged with these page IDs to make
	 * sure the PDF cache is flushed when the page changes.
	 *
	 * If caching is disabled a unique entry identifier will be used
	 * and it will not be stored in the cache backend.
	 *
	 * @param array $urls Array of the URLs that should be retrieved by the PDF generator.
	 * @param string $tempFile The temporary file created by the PDF generator.
	 * @param array $pageIds Array containing the page UIDs for which the PDF was generated.
	 * @param bool $cachingEnabled Use a unique file identifier and
	 * @return File
	 */
	public function store(array $urls, $tempFile, $pageIds = array(), $cachingEnabled) {

		$entryIdentifier = $this->getEntryIdentifier($urls);
		$conflictMode = 'replace';

		if (!$cachingEnabled) {
			$entryIdentifier = uniqid($entryIdentifier, TRUE);
			$conflictMode = 'changeName';
		}

		$file = $this->getCacheDirectory()->addFile($tempFile, $entryIdentifier, $conflictMode);

		if (!$cachingEnabled) {
			return $file;
		}

		// Convert the page UIDs to cache tags.
		array_walk($pageIds, function (&$tag) {
			$tag = 'pageId_' . intval($tag);
		});

		$this->cache->set($entryIdentifier, $entryIdentifier, $pageIds);
		return $file;
	}

	/**
	 * Makes sure a .htaccess file exists in the given folder that denies all access.
	 *
	 * @param \TYPO3\CMS\Core\Resource\Folder $folder
	 */
	protected function createHtaccessInCacheFolder($folder) {

		if ($folder->hasFile('.htaccess')) {
			return;
		}

		$tempHtaccess = GeneralUtility::tempnam(static::CACHE_IDENTIFIER, 'cache_htaccess');
		file_put_contents($tempHtaccess, 'Deny from all');
		$folder->addFile($tempHtaccess, '.htaccess');
		GeneralUtility::unlink_tempfile($tempHtaccess);
	}

	/**
	 * Makes sure the configured PDF document cache directory exists in the configured storage
	 * and returns the matching Folder object.
	 *
	 * @throws \InvalidArgumentException
	 * @return \TYPO3\CMS\Core\Resource\Folder
	 */
	protected function getCacheDirectory() {

		$outputStorage = ResourceFactory::getInstance()->getStorageObjectFromCombinedIdentifier($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['webkitpdf']['pdfCacheFolderIdentifier']);

		$folderIdentifierParts = GeneralUtility::trimExplode(':', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['webkitpdf']['pdfCacheFolderIdentifier']);
		if (empty($folderIdentifierParts[1])) {
			throw new \InvalidArgumentException('The pdfCacheFolderIdentifier extension config seems to be invalid. The folder identifier is empty.');
		}

		$folderIdentifier = $folderIdentifierParts[1];
		if (!$outputStorage->hasFolder($folderIdentifier)) {
			$outputStorage->createFolder($folderIdentifier);
		}

		$folder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['webkitpdf']['pdfCacheFolderIdentifier']);
		$this->createHtaccessInCacheFolder($folder);

		return $folder;
	}


	/**
	 * Returns a File instance to the cached PDF document if it exists and if $removeIfExists is FALSE.
	 * Otherwise NULL is returned.
	 *
	 * @param string $entryIdentifier The file idenifier (identical to the cache identifier).
	 * @param bool $removeIfExists If TRUE the file will be removed if it exists.
	 * @return File|NULL
	 */
	protected function getCachedPdfForIdentifier($entryIdentifier, $removeIfExists) {

		$cacheDirectory = $this->getCacheDirectory();
		if (!$cacheDirectory->hasFile($entryIdentifier)) {
			return NULL;
		}

		$file = ResourceFactory::getInstance()->getFileObjectFromCombinedIdentifier($cacheDirectory->getCombinedIdentifier() . $entryIdentifier);
		if ($removeIfExists) {
			$file->delete();
			return NULL;
		} else {
			return $file;
		}
	}

	/**
	 * Builds the cache entry identifiert for the given URLs.
	 *
	 * @param array $urls Array of the URLs that should be retrieved by the PDF generator.
	 * @return string The cache identifier that should be used for the given URL array.
	 */
	protected function getEntryIdentifier(array $urls) {
		return GeneralUtility::hmac(implode(', ', $urls), static::CACHE_IDENTIFIER);
	}
}