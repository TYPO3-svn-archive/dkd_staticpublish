<?php
/**
 *$Id$
 */

/***************************************************************
*  Copyright notice
*
*  (c) 2004 Thorsten Kahler (thorsten.kahler@dkd.de)
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
 * Plugin 'XML Submenu' for the 'dkd_staticpublish' extension.
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_dkdstaticpublish_pi_xmlmenu extends tslib_pibase {
	var $prefixId = "tx_dkdstaticpublish_xmlmenu";		// Same as class name
	var $scriptRelPath = "pi_xmlmenu/class.tx_dkdstaticpublish_xmlmenu.php";	// Path to this script relative to the extension dir.
	var $extKey = "dkd_staticpublish";	// The extension key.

	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{

		$this->conf = $conf;

			// send HTTP header for content-type?
		if( $this->conf['sendXMLHeader'] ) {
			header('Content-type: text/xml');
		}

			// Get the PID from which to make the menu.
			// If a page is set as reference in the 'Startingpoint' field, use that
			// Otherwise use the current page's id-number from TSFE
		$menuPid = trim($this->piVars['menuPid']) == intval($this->piVars['menuPid']) ? intval($this->piVars['menuPid']) : $GLOBALS['TSFE']->id;

			// Now, get an array with all the subpages to this pid:
			// which pages shall be retrieved
		$menuItems = array();

		$levels = 0;
		if ( is_numeric( $this->piVars['scope'] ) ) {
			$levels = (int) $this->piVars['scope'];
			$this->piVars['scope'] = 'all';
		} else {
			$levels = (int) $this->conf['maxLevels'];
		}

		$levels = t3lib_div::intInRange( $levels, 0, 100);	// 100 should be sufficient for "infinite"
		switch( strval( $this->piVars['scope'] ) ) {
			case 'sub' :
				$menuItems = $this->getSubpages( $menuPid, $levels );
				break;
			case 'all' :
				$menuItems[] = $GLOBALS['TSFE']->sys_page->getPage($menuPid);
				$menuItems = array_merge( $menuItems, $this->getSubpages( $menuPid, $levels ) );
				break;
			case 'single' :
			default :
				$menuItems[] = $GLOBALS['TSFE']->sys_page->getPage($menuPid);
				break;
			}

			// which page types shall be used
		if( $this->piVars['pageTypes'] == 'all' ) {
				// pageTypes: "all" types defined in setup
			$pageTypes = array_keys($GLOBALS['TSFE']->tmpl->setup['types.']);
		} else {
				// pageTypes: default=0, restricted to types defined in setup
			$pageTypes = $this->getPiVarsArray( 'pageTypes', ',', array(0), array_keys($GLOBALS['TSFE']->tmpl->setup['types.']) );
		}

			// remove page type used for generation of this menu
		$pageTypes = array_diff( $pageTypes, array( $this->conf['type_self'] ) );


			// which languages shall be used
		$defaultLanguages = t3lib_div::trimExplode( ',', $this->conf['langList'] );
		if( $this->piVars['languages'] == 'all' ) {
			$all_languages = true;		// set a flag: generate link for every _existing_ language
		} else {
			$selectedLanguages = $this->getPiVarsArray( $languages, ',', $defaultLanguages );
		}

			// Traverse menuitems:
		$pages = array();		// the "page" elements
		foreach( $menuItems as $pages_row )	{
			$languages = array();		// the selected and existing languages
			$urls = array();		// array containing generated URLs
			$urlList = '';		// formatted XML string containing the generated URLs
			$title = $pages_row['title']; // content of the title field

			if( ! is_array( $pages_row) ) {
				break;
			}

				// check which translations of the page should be used
			if( $all_languages ) {
				$languages +=  $this->getTranslationsOfPage($pages_row);
			} else {
				$languages += array_intersect( $selectedLanguages, $this->getTranslationsOfPage($pages_row) );
			}

				// check accessibility
			$access = $this->checkPageAccess($pages_row) ? 1 : 0;

				// Firs add a link to the default page type and language
			$urls[] = htmlspecialchars( $this->pi_getPageLink( $pages_row['uid'] ) );

				// checking for quotes in page title
			$titleAttribute = sprintf( 'title="%s"', htmlspecialchars($title) );

				// Travere page types
			foreach( $pageTypes as $typeNum ) {
				$typeNum = intval($typeNum);
					// Traverse languages
				if ( isset( $languages[0] ) ) {
					foreach ( $languages as $lang) {
						$lang = intval($lang);
						$urls[] = htmlspecialchars(
							$this->pi_getPageLink(
								$pages_row['uid'] . ',' . $typeNum,
								$pages_row['target'],
								array( 'L' => $lang )
							)
						);
					}
				}else{
					$urls[] = htmlspecialchars(
						$this->pi_getPageLink(
							$pages_row['uid'] . ',' . $typeNum,
							$pages_row['target']
						)
					);
				}
			}


			$urls = array_unique($urls);		// remove duplicates
			$urlList = "\n\t\t<url>". implode( "</url>\n\t<url>", $urls ) . "</url>\n\t";

			$pages[]= sprintf(
				"\t".'<page id="%d" %s fe_display="%s">%s</page>',
				$pages_row['uid'],
				$titleAttribute,
				$access,
				$urlList
			);

		}

		$totalMenu = implode( "\n\t", $pages );
		return sprintf( "\n<pages>\n%s\n</pages>\n", $totalMenu );
	}


	/**
	 * This function accumulates recursively all pages within a branch
	 *
	 * @params	int	$pid identifies the branch root
	 * @params	int	$levels	restricts the recursion depth
	 * @return	array	array of data sets from table "pages"
	 */
	function getSubpages($pid, $levels) {

		$pages = array();

			// (Function getMenu() is found in class.t3lib_page.php)
		if( $levels > 0 ) {
			$pages = $GLOBALS['TSFE']->sys_page->getMenu($pid);
			if( count($pages) > 0 ) {
				$temp_pages = array();
				foreach( $pages as $p ) {
					$temp_pages = array_merge( $temp_pages, $this->getSubPages( $p['uid'], $levels-1 ) );
				}
				$pages = array_merge( $pages, $temp_pages );
			}
		}

		return $pages;
	}




	/**
	 * This method checks if a page is "accessible" for normal FE users
	 *
	 * @param	Array	the "pages" record to check
	 * @return	bool	page accessiblity
	 */
	function checkPageAccess($pageArray) {

		$access = false;

			// just a simple check at the moment, could be enhanced via TS
		$doktypes = $GLOBALS['TYPO3_CONF_VARS']['FE']['content_doktypes'].',4';
		if( t3lib_div::inList( $doktypes, $pageArray['doktype'] ) ) {
			$access = true;
		}

		return $access;
	}


	/**
	 *	Search for translations of a page
	 *
	 *	@param	array	record from "pages" table
	 *	@return	array	array of language IDs
	 */
	function getTranslationsOfPage($page) {
		$languages = array();
		$queryArray = array(
			'SELECT' => 'distinct sys_language_uid l',
			'FROM' => 'pages_language_overlay',
			'WHERE' => 'pid='. intval( $page['uid'] ) . $GLOBALS['TSFE']->sys_page->enableFields('pages_language_overlay'),
			'ORDERBY' => 'l',
		);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray( $queryArray );
		while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
			$languages[] = $row['l'];
		}
		return $languages;
	}


	/**
	 * Parse a piVar and try to return them as an array
	 *
	 * With this function it's possible to use arrays as well as
	 * comma/semicolon/...-separated values as piVar
	 * You may optionally set a list of default values to add ( as far as it
	 * isn't already done by tslib_pibase::pi_setPiVarDefaults() ). Further
	 * you can restrict the piVars to a given set.
	 *
	 * @params	string	$varName name of the piVar
	 * @params	string	$separator used to explode strings
	 * @params	array	$default array of default values
	 * @params	array	$constraint restricts piVars to a set of possible values
	 */
	function getPiVarsArray( $varName, $separator=',', $default='', $constraint='' ) {

		$array = array();		// array of parsed and valid piVar values
		$tempArray = array();		// array for temporary storage of values

			// if there's no piVar of this name, return an empty array
		if( ! isset( $this->piVars[$varName] ) ) {
			if ( is_array($default) ) {
				return $default;
			} else {
				return array();
			}
		}

			// change string to array if necessary
		if( ( ! is_array( $this->piVar[$varName] ) && ( $separator != '' ) ) ) {
			$tempArray = t3lib_div::trimExplode( $separator, $this->piVars[$varName] );
		} else {
			$tempArray = $this->piVars[$varName];
		}

		if ( is_array( $default ) ) {
			$tempArray = array_unique( $default + $tempArray  );
		}

			// check restriction
		if( is_array($constraint) ) {
			foreach( $constraint as $c ) {
				if ( in_array( $c, $tempArray  ) ) {
					$array[] = $c;
				}
			}
		} else {
			$array = $tempArray;
		}

		return $array;
	}

}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_staticpublish/pi_xmlmenu/class.tx_dkdstaticpublish_pi_xmlmenu.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_staticpublish/pi_xmlmenu/class.tx_dkdstaticpublish_pi_xmlmenu.php"]);
}

?>