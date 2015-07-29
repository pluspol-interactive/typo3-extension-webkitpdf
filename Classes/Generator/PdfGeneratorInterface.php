<?php
namespace Tx\Webkitpdf\Generator;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Intera GmbH
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

/**
 * Interface for PDF generators.
 */
interface PdfGeneratorInterface {

	/**
	 * Dispatches the PDF generation.
	 *
	 * @param array $urls The URLs that should be read for PDF generation.
	 * @param string $fileName Absolute filename to wich the generated PDF should be written.
	 * @return void
	 */
	public function generatePdf(array $urls, $fileName);

	/**
	 * Enables or disables debugging in the PDF generator.
	 *
	 * @param bool $isDebugEnabled
	 */
	public function setIsDebugEnabled($isDebugEnabled);

	/**
	 * Sets options for the PDF generator.
	 *
	 * @param array $options
	 * @return void
	 */
	public function setGeneratorOptions(array $options);

	/**
	 * Sets / overrides an option that is passed to the wkthmltopdf script.
	 *
	 * @param string $option The name of the option.
	 * @param mixed $value Can be a string with the option value or an array for options that accept multiple parameters.
	 * @return void
	 */
	public function setOption($option, $value);

	/**
	 * Setter for the path to the wkthmltopdf executable.
	 *
	 * @param string $path
	 * @return void
	 */
	public function setWebkitExecutablePath($path);
}