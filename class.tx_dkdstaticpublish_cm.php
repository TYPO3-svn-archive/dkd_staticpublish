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
 * Addition of an item to the clickmenu 
 *
 * @author  Thorsten Kahler <thorsten.kahler@dkd.de>
 */ 

/** 
 *  
 *
 */ 
class tx_dkdstaticpublish_cm {
	
	var $writeDevLog = FALSE;       // flag: devLog enabled?
	var $preConf = array();     // array of configuration presets

	/** 
	 *  
	 *
	 */ 
	function main(&$backRef,$menuItems,$table,$uid) {
	
			// enable dev logging if set
		if( is_array( $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog'] ) || TYPO3_DLOG ) {
			$this->writeDevLog = TRUE;
		}

		$localItems = Array();
		if( ! $backRef->cmLevel ) { 
			$permission_bitmask = 17;       // BE User needs rights to show page and edit content
			$publish_permsClause = $GLOBALS['BE_USER']->getPagePermsClause( $permission_bitmask );
				// page is NULL, if BE User doesn't have sufficient rights
			$page = t3lib_BEfunc::getRecord( 'pages', $uid, '*', ' AND '.$publish_permsClause );
			if ( 
				$table != 'pages' ||        // Returns directly, because the clicked item was not from the pages table
				$page == NULL
				) { 
				return $menuItems;
			}

				// Adds the regular item:
			$LL = $this->includeLL();
			
				// initialize preset configurations 
			$this->initPreConf($uid);
				// Repeat this (below) for as many items you want to add!
				// Remember to add entries in the localconf.php file for additional titles. 
			
			if( count( $this->preConf ) ) { 
				$label = $GLOBALS['LANG']->getLLL( 'cm1_title',$LL );
	
				foreach( $this->preConf as $conf ) {
						// use $conf['label'] as linked text and throw it away afterwards
					if( isset( $conf['label'] ) && $conf['label'] != '' ) {
						$label = sprintf( ' - &quot; - (    %s )', $conf['label'] );
						unset( $conf['label'] );
					}
	
					$addParams = t3lib_div::implodeArrayForUrl( 'tx_dkdstaticpublish_cm1', $conf );
					$url = t3lib_extMgm::extRelPath('dkd_staticpublish').'cm1/index.php?id=' . $uid . '&SET[function]=1' . $addParams;
					$localItems[] = $backRef->linkItem( 
						$label, 
						$backRef->excludeIcon('<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . TYPO3_mainDir . 'gfx/savesnapshot.gif" width="17" height="12" border="0" align="top">'), 
						$backRef->urlRefForCM($url),
						1   // Disables the item in the top-bar. Set this to zero if you with the item to appear in the top bar!
					);
				}
			} else {
					$url = t3lib_extMgm::extRelPath('dkd_staticpublish').'cm1/index.php?id=' . $uid . '&SET[function]=1';
					$localItems[] = $backRef->linkItem( 
						$label, 
						$backRef->excludeIcon('<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . TYPO3_mainDir . 'gfx/savesnapshot.gif" width="17" height="12" border="0" align="top">'), 
						$backRef->urlRefForCM($url),
						1   // Disables the item in the top-bar. Set this to zero if you with the item to appear in the top bar!
					);
			}
			
			
				// Find position of 'delete' element:
			reset($menuItems);
			$c=0;
			$appendFlag = TRUE; 
			while(list($k)=each($menuItems)) {
				$c++;
				if (!strcmp($k,'delete')) { 
						// .. subtract two (delete item + divider line) 
					$c-=2;
						// ... and insert the items just before the delete element. 
					array_splice(
						$menuItems, 
						$c, 
						0,
						$localItems 
					);
					$appendFlag = FALSE;
					break;
				}
			}
			if( $appendFlag ) { 
				$menuItems = array_merge( $menuItems, $localItems );
			}
		}
		return $menuItems;
	} 



	/** 
	 * Includes the [extDir]/locallang.php and returns the $LOCAL_LANG array found in that file.
	 *
	 * @return  array   language labels 
	 */ 
	function includeLL()    {
		include(t3lib_extMgm::extPath('dkd_staticpublish').'locallang.php');
		return $LOCAL_LANG; 
	}


	/** 
	 * Reads configuration presets based on configuration in UserTSConfig
	 *
	 * @return  integer error code
	 */ 
	function initPreConf() {

		$TSConfig = $GLOBALS['BE_USER']->getTSConfigProp('xMOD_tx_dkdstaticpublish_cm1');
		if ( $this->writeDevLog ) {
			t3lib_div::devLog('User TSConfig', 'tx_dkd_staticpublish_cm', 0, $TSConfig);
		}

		$preConfOptions = array('label', 'pageTypes', 'languages', 'scope', 'maxDepth', 'ready' );
		
		if( is_array($TSConfig) ) {
			foreach( $TSConfig as $index => $alternative )  {
				if ( $this->writeDevLog ) {
					t3lib_div::devLog( 'Preconfiguration '.$index, 'tx_dkd_staticpublish_cm', 0, $TSConfig );
				}
				$i = intval( substr( $index, 0, -1 ) );
				foreach( $alternative as $key => $value ) {
					if( in_array( $key, $preConfOptions ) ) {
						if( $key == 'pageTypes' || $key == 'languages' ) {
							if( $value != '' ) {
								$options = t3lib_div::trimExplode( ',', $value );
								foreach( $options as $opt ) {
									$this->preConf[$i][$key][$opt] = 1;
								}
							}
						} else {
							$this->preConf[$i][$key] = $value;
						}
					}
				}
			}
		}

		if ( $this->writeDevLog ) {
			t3lib_div::devLog( 'Count Preconfiguration', 'tx_dkd_staticpublish_cm', 0, count($this->preConf) );
		}
		
		return 0;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dkd_staticpublish/class.tx_dkdstaticpublish_cm.php'])  {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dkd_staticpublish/class.tx_dkdstaticpublish_cm.php']); 
}

?>