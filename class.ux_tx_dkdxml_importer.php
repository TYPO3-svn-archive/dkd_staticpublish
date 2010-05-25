<?php
/*
 *  CVS Versioning: $Id$
 */

/***************************************************************
*  Copyright notice
*
*  (c) 2004 Maryna Sigayeva (maryna.sigayeva@dkd.de)
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
 * BE class providing functionality for the 'dkd_xmlimport' extension.
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */


class ux_tx_dkdxml_importer extends tx_dkdxml_importer {


	function assocArray2Table($assoc){

		$table = array();

		if( is_array( $assoc['pages'] ) && count( $assoc['pages'] ) ) {

			foreach ($assoc['pages'] as $page) {
				$rows = array();
				if( is_array( $page ) ) {
					$commonAttributes = array();
					$keys = array_keys ($page);
						// extract language variants and attach common properties to them
					foreach ($keys as $key) {
						if(is_int($key)) {	// language variant
							$tag = $page[$key];
							$rows[] = array( $tag['tag'] => $tag['value'] );
							unset($page[$key]);
						}
					}
					foreach ($rows as $row) {
						$table[] = array_merge($page, $row);
					}
				}
			}

		}

		return $table;
	}



	/**
	 * fetch file from URL and save it to local directory
	 *
	 * @param	string	$from	the URL of the file
	 * @param	string	$to	the local directory
	 * @return	int	error-codes: 0=ok; 1=could not create dir; 2=could not write to dir; 3=could not read from URL; 4=could not write to file;
	 */
	function copy($from, $to) {

			// change path type from relative to absolute if necessary
		$dir = t3lib_div::fixWindowsFilePath($to);
		$dir = t3lib_div::isAbsPath($dir) ? $dir : PATH_site.$dir;

			// check R/W rights
		if ( ! is_dir($dir) ) {
			if (! t3lib_div::mkdir($dir) ) {
				$this->log('error', sprintf('Could not create directory %s! Please check file permissions.', $to) );
				return 1;
			}
		}
		if ( ! is_writable($dir) ) {
			$this->log('error', sprintf('Directory %s was not writable! Please check file permissions.', $to) );
			return 2;
		}

			// fetch file
		if ( ! $content = @t3lib_div::getURL($from) ) {
			$this->log('error', sprintf('Could not read %s!', $from) );
			return 3;
		}

			// append trailing slash
		$dir .= (substr($dir, -1) == '/') ? '' : '/';
			// write file
		$pathName = substr( $from, strlen( t3lib_div::getIndpEnv('TYPO3_SITE_URL') ) );		// strip of current website URL

		$filename = array_pop( t3lib_div::trimExplode( '/', $pathName) );
		$path = substr( $pathName, 0, - strlen($filename) );

		if( $path != '' ) {
			$pathError = $this->forcePathExistence( $dir, $path );
			if( $pathError ) {
				return $pathError;
			} else {
				$dir .= $path . '/' ;
			}
		}

		if (! t3lib_div::writeFile($dir.$filename, $content) ) {
			$this->log( 'error', sprintf('Could not write file "%s" to directory %s', $filename, $to) );
			return 4;
		}
	
			// write log
		$this->log('file', sprintf('File %s mirrored to dir %s', $from, $dir.$filename) );
		return 0;
	}


	/**
	 * fetch file from URL and save it to local directory
	 *
	 * @param	string	$dir start at this dir
	 * @param	string	$path force existence of this path
	 * @return	int	error-codes: 0=ok; 1=could not create path; 
	 */
	function forcePathExistence( $dir, $path ) {

		$pathParts = explode( '/', $path );

		foreach( $pathParts as $p ) {
			$dir .= '/' . $p;
				// check R/W rights
			if ( ! is_dir($dir) ) {
				if (! t3lib_div::mkdir($dir) ) {
					$this->log('error', sprintf('Could not create directory %s! Please check file permissions.', $dir) );
					return 1;
				}
			}
			if ( ! is_writable($dir) ) {
				$this->log('error', sprintf('Directory %s was not writable! Please check file permissions.', $dir) );
				return 2;
			}
		}

			// if all path parts exist:
		return 0;
		
	}


}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_staticpublish/class.ux_tx_dkdxml_importer.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_staticpublish/class.ux_tx_dkdxml_importer.php"]);
}


?>