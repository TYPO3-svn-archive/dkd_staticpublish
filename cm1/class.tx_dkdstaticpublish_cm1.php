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
 * dkd_staticpublish module cm1
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */



require_once ($BACK_PATH.'template.php');

require_once (PATH_t3lib.'class.t3lib_scbase.php');
require_once (PATH_t3lib.'class.t3lib_tstemplate.php');
require_once (PATH_t3lib.'class.t3lib_page.php');

class tx_dkdstaticpublish_cm1 extends t3lib_SCbase {
	
	var $prefixId = 'tx_dkdstaticpublish_cm1';
	var $modVars = array();		// variables from this module
	var $sysPage = '';		// page selector obj
	var $tmpl = '';		// TS template obj
		// Extension that will be used for writing the files
	var $importExtension = array( 'extKey' => 'dkd_xmlimport', 'mod' => 'mod1', 'mode' => 'SET[function]=1' );
		// list for permission bit mask
	var $permissions = array( 'show' => '1', 'edit' => '2', 'delete' => '4', 'new' => '8', 'content' => '16' );
	var $publish_permsClause = '';		// part of SQL where-statement


	/**
	 * Initialisation
	 */

	/**
	 * Initializes the backend module by setting internal variables, initializing the menu.
	 *
	 * @return	void
	 * @see menuConfig()
	 */
	function init() {
		parent::init();

			// set the permissions clause publishing function
		$permission_bitmask = ( $this->permissions['show'] | $this->permissions['content'] );
		$this->publish_permsClause = $GLOBALS['BE_USER']->getPagePermsClause( $permission_bitmask );
		
			// initialize the page selector
		$this->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$this->sys_page->init(true);

			// initialize the TS template
		$this->tmpl = t3lib_div::makeInstance('t3lib_TStemplate');
		$this->tmpl->init();
		$rootline = $this->sys_page->getRootLine( $this->id );
		$this->tmpl->forceTemplateParsing = 1;
		$this->tmpl->start($rootline);
		
		$this->modVars = $this->fetchVars();

	}



	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig() {
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $GLOBALS['LANG']->getLL('function1'),
				'2' => $GLOBALS['LANG']->getLL('function2')
			)
		);
		parent::menuConfig();
	}



	/**
	 * Fetch the module variables
	 *
	 * @return	array	the modVars
	 */
	function fetchVars() {
			// initialize the vars
		$modVars = array(
			'pageTypes' => array(),
			'languages' => array(),
			'ready' => false,
			'scope' => '',
			'delRelease' => array(),
			'view' => '',
			'maxDepth' => 99		// should be sufficient for "infinite"
		);
		
			// set the modVars without checking the input
		$vars = t3lib_div::GPVar( $this->prefixId );
		
		if( is_array($vars) ) {
			foreach($vars as $var => $val) {
				$modVars[$var] = $val;
			}
		}
		
			// restrict maxDepth to setting in TS
		if ( isset( $this->tmpl->setup['plugin.']['tx_dkdstaticpublish_pi_xmlmenu.']['maxLevels'] ) ) {
			$ts_maxLevels = intval( $this->tmpl->setup['plugin.']['tx_dkdstaticpublish_pi_xmlmenu.']['maxLevels'] );
			if ( ( $ts_maxLevels > 0 ) && ( $ts_maxLevels < $modVars['maxDepth'] ) ) {
				$modVars['maxDepth'] = $ts_maxLevels;
			}
		} 

		return $modVars;
	}




	/**
	 * Main function
	 */

	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main() {
			// Draw the header.
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->docType = 'xhtml_trans';
		$this->doc->form='<form action="index.php" method="POST">';

			// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
		';
			// import additional stylesheet
		$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath('dkd_staticpublish') . '/res/styles.css';

		$this->content.=$this->doc->startPage($GLOBALS['LANG']->getLL('title'));
		$this->content.=$this->doc->header($GLOBALS['LANG']->getLL('title'));
		$this->content.=$this->doc->spacer(5);

		$this->pageinfo = t3lib_BEfunc::readPageAccess( $this->id, $this->permissions['read'] );

		$page = t3lib_BEfunc::getRecord( 'pages', $this->id, '*', ' AND '.$this->publish_permsClause );

		$access = ( is_array( $this->pageinfo )  && is_array( $page ) );

		if (! ($this->id && $access )) {
			$this->content .= $this->_showAcessError();
		} else {

			$headerSection = $this->doc->getHeader(
				'pages'
				,$this->pageinfo
				,$this->pageinfo['_thePath']
			);
			$headerSection .= '<br>'
				. $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.path')
				. ': ' . t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'], 50);
			
			$this->content .= $this->doc->section(
				''
				,$this->doc->funcMenu(
					$headerSection
					,t3lib_BEfunc::getFuncMenu(
						$this->id
						,'SET[function]'
						,$this->MOD_SETTINGS['function']
						,$this->MOD_MENU['function']
					)
				)
			);
			$this->content .= $this->doc->divider(5);

				// Render content:
			$this->moduleContent();

				// Shortcut
			if ($GLOBALS['BE_USER']->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

		}

		$this->content.=$this->doc->spacer(10);
	}



	/**
	 *
	 */

	/**
	 * Output completed document
	 *
	 * @return void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	


	/**
	 * Functionality
	 */

	/**
	 * Dispatch functions
	 *
	 * @return void
	 */
	function moduleContent()	{
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content = $this->f_publishSubpages();
				$this->content.=$this->doc->section( $GLOBALS['LANG']->getLL( 'header_config'),$content,0,1);
			break;
			case 2:
				$content = $this->f_previousReleases();
				$this->content.=$this->doc->section( $GLOBALS['LANG']->getLL( 'PR_header'),$content,0,1);
			break;
			default:
			break;
		} 
	}
	
	
	/**
	 * Wrapper function for task "publishSubpages"
	 *
	 * @return	string	content of BE form
	 */
	function f_publishSubpages() {
		$content = '';

		$hiddenFields = array( 
			'id' => array( 'type' => 'hidden', 'name' => 'id', 'value' => $this->id ),
		);
		$content .= $this->form_input( $hiddenFields['id'] );

		$content .= $this->configForm();

			// if configuration is ready, set a link to another BE module
		if( $this->modVars['ready'] ) {
				// restrict depth of XMLMenu to value set in TS
			$scope = ( $this->modVars['maxDepth'] && t3lib_div::inList( 'sub,all', $this->modVars['scope'] ) ) ? $this->modVars['maxDepth'] : $this->modVars['scope'];

			$exportLinkAttributes = array(
				'tx_dkdstaticpublish_xmlmenu[pageTypes]' => implode( ',', array_keys( $this->modVars['pageTypes'] ) ),
				'tx_dkdstaticpublish_xmlmenu[languages]' => implode( ',', array_keys( $this->modVars['languages'] ) ),
				'tx_dkdstaticpublish_xmlmenu[menuPid]' => $this->id,
				'tx_dkdstaticpublish_xmlmenu[scope]' => $scope
			);
			$exportLink = $this->makeExportLink(
				$this->id,
				$this->tmpl->setup['plugin.']['tx_dkdstaticpublish_pi_xmlmenu.']['type_self'],
				$exportLinkAttributes
			);
			
				// generating a link to the import module
			$modParams = array( 
				$this->importExtension['mode'],
				'tx_dkdxml_impexp[config]=Static Publish',
				'tx_dkdxml_impexp[page_id]=' .$this->id,
				'tx_dkdxml_impexp[import]=1',
			);
				

			$modLink = sprintf(
				'%s%s%s/index.php?id=%u&%s',
				t3lib_div::getIndpEnv('TYPO3_SITE_URL'),
				t3lib_extMgm::siteRelPath( $this->importExtension['extKey'] ),
				$this->importExtension['mod'],
				$this->id,
				implode( '&', $modParams )
			);

			$GLOBALS['BE_USER']->setAndSaveSessionData( 'dkd_staticpublish_XMLMenuURL', $exportLink );
			$GLOBALS['BE_USER']->setAndSaveSessionData( 'dkd_staticpublish_menuPid', $this->id );
			$GLOBALS['BE_USER']->setAndSaveSessionData( 'dkd_staticpublish_pubID', time() );
			$content .= $this->doc->sectionHeader( $GLOBALS['LANG']->getLL( 'header_switchToMod' ), 1 );
			$content .= sprintf(
				'<p>%s <a href="%s" title="%s">%s</a></p>',
				$GLOBALS['LANG']->getLL( 'msg_switchToMod' ),
				$modLink,
				$GLOBALS['LANG']->getLL( 'header_switchToMod' ),
				$GLOBALS['LANG']->getLL( 'msg_switchToModLink' )
			);
		}
		
		return $content;
	}



	
	/**
	 * Wrapper function for task "Previous Releases"
	 *
	 * @return	string	content of BE form
	 */
	function f_previousReleases() {
		$content = '';

		$hiddenFields = array( 
			'id' => array( 'type' => 'hidden', 'name' => 'id', 'value' => $this->id ),
		);
		$content .= $this->form_input( $hiddenFields['id'] );

		if( count( $this->modVars['delRelease'] ) > 0 ) {
			$delMessage = $this->deleteReleases( $this->modVars['delRelease'] );
		} else {
			$delMessage = '';
		}
		
		$content .= '<h4>' . $GLOBALS['LANG']->getLL( 'PR_comment' ) . '</h4>';
		
		$table = '<table cellpadding="0" cellspacing="0" border="0" class="releaseList">';
		$table .= sprintf( '<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>',
			$GLOBALS['LANG']->getLL( 'PR_tblHead_date' ),
			$GLOBALS['LANG']->getLL( 'PR_tblHead_sum' ),
			$GLOBALS['LANG']->getLL( 'PR_tblHead_del' ),
			$GLOBALS['LANG']->getLL( 'PR_tblHead_view' )
		);
		
		$releases = $this->getReleases( $this->id );
		$trAttributes = '';
		$cbAttributes = array(
			'type' => 'checkbox',
			'name' =>  $this->prefixId.'[delRelease][]',
			'checked' => ''
		);
		
		$singleLink = sprintf( '%s?id=%u&SET[function]=%u&%s[view]', t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT'), $this->id, $this->MOD_SETTINGS['function'], $this->prefixId );
		foreach( $releases as $rel ) {
			$table .= sprintf(
				'<tr%s><td>%s</td><td>%s</td><td>%s</td><td><a href="%s=%u">%s</a></td></tr>',
				$rowAttributes,
				date( 'd.m.Y, H:i:s', $rel['datetime'] ),
				$rel['sum'],
				$this->form_input( array_merge ( $cbAttributes, array( 'value' => $rel['datetime'] ) ) ),
				$singleLink,
				$rel['datetime'],
				$GLOBALS['LANG']->getLL( 'PR_singleLink' )
			);
		}
		
		$table .= '</table>';
		
		$content .= $table;
		
		$submitButton = array( 'type' => 'submit', 'value' => $GLOBALS['LANG']->getLL( 'PR_delButton' ), 'class' => 'translucent' );
		$content .= '<p>' . $this->form_input( $submitButton ) . '</p>';
		
		$content .= '<p>' . $delMessage . '</p>';
		
		if( $this->modVars['view'] ) {
			$content .= '<hr />' . $this->viewReleaseDetails( $this->modVars['view'] );
		}
		
		return $content;
	}



	/**
	 * Helper functions
	 */
	
	/**
	 * Generate configuration form to trigger PI XMLMenu
	 *
	 * @return	string	HTML code for form
	 */
	function configForm() {
		$content = '';
		$pageTypes = array();
		$languages = array();

		$pt = $this->tmpl->setup['types.'];
		foreach( $pt as $num => $label ) {
			if(  $num != $this->tmpl->setup['plugin.']['tx_dkdstaticpublish_pi_xmlmenu.']['type_self'] ) {
				$pageTypes[$num] = array(
					'typeNum' => $num,
					'label' => sprintf( '%s (%s)', $label, $num ),
					'selected' => false
				);
			}
		}
		if( is_array( $this->modVars['pageTypes'] ) ) {
			foreach( array_keys( $this->modVars['pageTypes'] ) as $type )
				$pageTypes[$type]['selected'] =  true;
		}
		unset($pt);
		ksort($pageTypes);

		$languages = $this->getLanguagesInDb();
		if( is_array( $this->modVars['languages'] ) ) {
			foreach( array_keys( $this->modVars['languages'] ) as $langID ) {
				$languages[$langID]['sel'] = true;
			}
		}
		
			// config for $this->form_checkBoxes()
		$conf_pageTypes = array(
			'value' => 'typeNum',
			'label' => 'label',
			'checked' => 'selected'
		);
		$conf_languages = array(
			'value' => 'langID',
			'label' => 'langTitle',
			'checked' => 'sel'
		);
		$wrap = '<li>%2$s%1$s</li>';
		
		$cbName = $this->prefixId.'[pageTypes]';
		$cb_pageTypes = $this->form_checkBoxes( $cbName, $pageTypes, $conf_pageTypes, $wrap );

		$cbName = $this->prefixId.'[languages]';
		$cb_languages = $this->form_checkBoxes( $cbName, $languages, $conf_languages, $wrap );

			// insert checkboxes into content
		$cbWrap= '<h4>%s:</h4><ul class="selection-list">%s</ul>';
		$content .= sprintf( $cbWrap, $GLOBALS['LANG']->getLL( 'header_cbPageTypes' ), $cb_pageTypes );
		$content .= sprintf( $cbWrap, $GLOBALS['LANG']->getLL( 'header_cbLanguages' ), $cb_languages );
		
		$content .= sprintf( '<h4>%s:</h4>', $GLOBALS['LANG']->getLL( 'header_scope' ) );
			// which pages should be published? (single(default), sub, all)
		$scopes = t3lib_div::trimExplode( ',', 'single, sub, all' );
		foreach( $scopes as $s ) {
			$scopeOptions[]= array(
				'value' => $s,
				'label' => $GLOBALS['LANG']->getLL( 'lbl_scope_'. $s ),
				'selected' => ( $this->modVars['scope']  == $s )
			);
		}
			
		$ddName = $this->prefixId.'[scope]';
		$content .= $this->form_dropDown($ddName, $scopeOptions);
			// preview selection
		$content .= $this->showTree( $this->id, $this->modVars['scope'], $this->modVars['maxDepth'] );

			// set checkbox to switch to next step
		if( is_array( $this->modVars['pageTypes'] ) ) {
			$cb_ready = array( 'type' => 'checkbox', 'name' => $this->prefixId.'[ready]' );
			$content .= sprintf(
				'<h4>%s</h4><p>%s%s</p>',
				$GLOBALS['LANG']->getLL( 'header_ready' ),
				$GLOBALS['LANG']->getLL( 'msg_ready' ),
				$this->form_input( $cb_ready )
			 );
		}

		$submitButton = array( 'type' => 'submit', 'value' => 'Send' );
		$content .= '<p>'. $this->form_input( $submitButton ) .'</p>';
		
		return $content;
	}



	/**
	 * Generate link to "exported XML"
	 *
	 * @param	int	$id page id of publication
	 * @param	int	$type page type for XML generation (XMLMenu)
	 * @param	array	$attributes used to adjust XML generation
	 * @return	string	link to "XML file"
	 */
	function makeExportLink( $id, $type, $attributes ) {
		$link = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		$link .= 'index.php?id=' . intval($id);
		$link .= '&type=' . $type;
		foreach($attributes as $key => $value) {
			$link .= '&'. $key .'='. $value;
		}
		
		return $link;
		
	}



	 
	/**
	 * Display a tree of pages
	 * Makes use of class t3lib_pagetree
	 *
	 * @param	int	$treeStartingPoint root of the page tree part
	 * @param	string	$scope determines scope of the page tree part; three options: single, sub, all
	 * @param	int	$depth number of sublevels
	 * @return	string	HTML code
	 */
	function showTree( $treeStartingPoint, $scope='', $depth='' ) {
		require_once(PATH_t3lib.'class.t3lib_pagetree.php');
		
			// adjusting scope if depth = 0
		if( $depth === 0 ) {
			$scope = 'single';
		}

			// Initialize starting point of page tree:

		$treeStartingRecord = t3lib_BEfunc::getRecord( 'pages', $treeStartingPoint );

			// Initialize tree object:
		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->init('AND '.$GLOBALS['BE_USER']->getPagePermsClause(1));
		
			// Creating top icon; the current page
		$HTML = t3lib_iconWorks::getIconImage('pages', $treeStartingRecord, $GLOBALS['BACK_PATH'],'align="top"');
		
		switch($scope) {
			case 'all' :
				$tree->tree[] = array(
					'row' => $treeStartingRecord,
					'HTML'=>$HTML
				);
			case 'sub' :
					// Create the tree from starting point:
				$tree->getTree($treeStartingPoint, $depth, '');
			break;
			case 'single' :
			default :
				$tree->tree[] = array(
					'row' => $treeStartingRecord,
					'HTML'=>$HTML
				);
			break;
		}
		
			// Put together the tree HTML:
		$output = '';

		foreach($tree->tree as $data)    {
			$output.='
				<tr>
					<td nowrap="nowrap">'.$data['HTML'].htmlspecialchars($data['row']['title']).'</td>
				</tr>';
		}
		
		$output = '<table border="0" cellspacing="0" cellpadding="0" class="pageTree">'.$output.'</table>';
		
		return $output;
	}
	
	
	/**
	 * Renders output in case of an access error
	 * 
	 * @return	string	HTML code with error message
	 * @since	2010-05-25
	 */
	protected function _showAcessError() {
						// access/rights error
		$content = $this->doc->sectionHeader(
			$GLOBALS['LANG']->getLL( 'error_header' )
			,1
		);
		if( $this->id == 0 ){
				// no publishing from the root page
			$msg = $GLOBALS['LANG']->getLL( 'error_rootpage' );
		} else {
				// user has insufficient rights to publish this page
			$msg = $GLOBALS['LANG']->getLL( 'error_access' );
		}
		$content .= '<p class="err">' . $msg . '</p>';
		$content.=$this->doc->divider(5);
		$content .= '<a href="javascript:history.back();" title="cancel" alt="cancel"><img src="/' . TYPO3_mainDir . 'gfx/closedok.gif" width="21" height="16" class="c-inputButton" title="Cancel" alt="" /></a>';
		
		return $content;
	}


	/**
	 * Functions related to DB operations
	 */

	/**
	 * Fetch publications from cache table
	 *
	 * @return	array	assoc array of publications with count of pages and date of publication ("sum"/"datetime")
	 */
	function getReleases() {
		
		$releases = array();

		$queryArray = array(
			'SELECT' => 'count(uid) sum, pub_id datetime',
			'FROM' => 'tx_dkdstaticpublish_urls',
			'WHERE' => 'pid='. $this->id . t3lib_BEfunc::deleteClause('tx_dkdstaticpublish_urls'),
			'ORDERBY' => 'datetime',
			'GROUPBY' => 'datetime',
		);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray( $queryArray );
		while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
			$releases[] = $row;
		}

		return $releases;
		
	}


	/**
	 * Delete publication from cache table
	 *
	 * @param	array	$relIDs list of publications
	 * @return	string	result message
	 */
	function deleteReleases( $relIDs ) {

		$releases = array();
		$queryArray = array(
			'SELECT' => 'count(uid) sum, pub_id datetime',
			'FROM' => 'tx_dkdstaticpublish_urls',
			'WHERE' => 'pub_id in('. implode( ',', $relIDs ) .')',
			'GROUPBY' => 'datetime',
		);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray( $queryArray );
		while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
			$releases[] = $row;
		}
		if( count($releases) != count($relIDs) ) {

			return '<span class="err">Some error happened! No entries deleted.</span>';
		} else {
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_dkdstaticpublish_urls',
				'pub_id in('. implode( ',', $relIDs ) .')',
				array( 'deleted' => 1 )
			 );
		}
		
		return count($relIDs) . ' have been deleted.';	
		
	}



	/**
	 * Generate HTML table with info about specific publication
	 *
	 * @param	int	$releaseID id of the publicatoin to display
	 * @return	string	HTML table with details of publication $releaseID
	 */
	function viewReleaseDetails($releaseID) {
		$content = '';

			// display header
		$day = strftime( $GLOBALS['LANG']->getLL( 'PR_singleView_dateFormat' ), $releaseID );
		$time = strftime( $GLOBALS['LANG']->getLL( 'PR_singleView_timeFormat' ), $releaseID );
		$header = sprintf( $GLOBALS['LANG']->getLL( 'PR_singleView_header' ), $day, $time );
		$content .= $this->doc->sectionHeader( $header, 1 );
		
			// get release details from DB
		$queryArray = array(
			'SELECT' => 'title, url',
			'FROM' => 'tx_dkdstaticpublish_urls',
			'WHERE' => 'pub_id='. $releaseID . t3lib_BEfunc::deleteClause('tx_dkdstaticpublish_urls'),
		);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray( $queryArray );
		while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
			$urls[] = $row;
		}
		
			// display table
		$content .= '<table cellpadding="0" cellspacing="0" border="0" class="releaseList">';
		$content .= sprintf( '<tr><th>%s</th><th>%s</th></tr>',
			$GLOBALS['LANG']->getLL( 'PR_tblHead_pageTitle'),
			$GLOBALS['LANG']->getLL( 'PR_tblHead_url' )
		);
		foreach( $urls as $url ) {
			$content .= sprintf( '<tr><td>%s</td><td>%s</td></tr>', $url['title'], $url['url'] );
		}
		$content .= '</table>';

		return $content;
	}



	/**
	 *	Search for language labels in DB
	 *
	 *	@return	array	array of languages
	 */
	function getLanguagesInDb() {
		$languages = array();
		$languages[0] = array( 'langID' => 0, 'langTitle' => 'default' );

		$queryArray = array(
			'SELECT' => 'distinct plo.sys_language_uid langID, sl.title langTitle',
			'FROM' => 'pages, pages_language_overlay plo, sys_language sl',
			'WHERE' => sprintf( 'pages.uid=plo.pid AND sl.uid=plo.sys_language_uid %s', $this->sys_page->where_hid_del ),
			'ORDERBY' => 'langID',
		);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray( $queryArray );
		while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
			$languages[] = $row;
		}
		return $languages;
	}



	/**
	 * Form Generation Functions
	 *
	 */


	/**
	 * Generates HTML for an <input>-tag
	 * @params	array	$attributes	the attributes of the <input>-tag
	 * @return	string	the resulting HTML code
	 */
	function form_input($attributes) {
		$attribString = ' ';
		$trailingSlash = ( substr( $this->doc->docType, 0, 5 ) == 'xhtml' ) ? ' /' : '';

				// TODO: Sonderbehandlung von z.B. checked

// types: text, password, checkbox, radio, submit, reset, file, hidden, image, button
// attributes: accept,accesskey,align,alt,checked,datafld,datasrc,dataformatas,disabled,ismap,maxlength,name,onblur,onchange,onfocus,onselect,readonly,size,src,tabindex,type,usemap,value
/*
? datafld
? datasrc
? dataformatas
size
*/
			// common attributes for <input>-tags
		$attribs = 'type, accesskey, alt, maxlength, name, tabindex, value';
		$attribs_empty = 'disabled, readonly';
		$attribs_deprecated = 'align';
		$attribs_events = 'onblur, onchange, onfocus, onselect';
			// adding universal attributes
		$attribs .= ', class, id, style, title, dir, lang';
		$attribs_events = ', onclick, ondblclick, onmousedown, onmouseup, onmouseover, onmousemove, onmouseout, onkeypress, onkeydown, onkeyup';

			switch ( strtolower( $attributes['type']) ) {
				case 'text':
				case 'password':
					$attribs .= ''; 
					$attribs_empty .= '';
					$attribs_deprecated .= '';
					$attribs_events .= '';
					break;
				case 'checkbox' :
					$attribs .= '';
					$attribs_empty .= ', checked';
					$attribs_deprecated .= '';
					$attribs_events .= '';
					break;
				case 'radio':
					$attribs .= ''; 
					$attribs_empty .= ', checked';
					$attribs_deprecated .= '';
					$attribs_events .= '';
					break;
				case 'submit':
					$attribs .= ''; 
					$attribs_empty .= '';
					$attribs_deprecated .= '';
					$attribs_events .= '';
					break;
				case 'reset':
					$attribs .= ''; 
					$attribs_empty .= '';
					$attribs_deprecated .= '';
					$attribs_events .= '';
					break;
				case 'file' :
					$attribs .= ', accept';
					$attribs_empty .= '';
					$attribs_deprecated .= '';
					$attribs_events = '';
					break;
				case 'hidden':
					$attribs .= ''; 
					$attribs_empty .= '';
					$attribs_deprecated .= '';
					$attribs_events .= '';
					break;
				case 'image' :
					$attribs .= ', src, usemap';
					$attribs_empty .= ', ismap';
					$attribs_deprecated .= '';
					$attribs_events .= '';
					break;
				case 'button':
					$attribs .= ''; 
					$attribs_empty .= '';
					$attribs_deprecated .= '';
					$attribs_events .= '';
					break;
				default :
					break;
			}

			// change the lists to arrays, so a search is faster
		$attribs = t3lib_div::trimExplode( ',', $attribs );
		$attribs_empty = t3lib_div::trimExplode( ',', $attribs_empty );
		$attribs_deprecated = t3lib_div::trimExplode( ',', $attribs_deprecated );
		$attribs_events = t3lib_div::trimExplode( ',', $attribs_events );
		

		foreach( $attributes as $key => $value ) {
			if( in_array( $key, $attribs ) ) {
				$attribString .= sprintf( ' %s="%s"', $key, $value );

			} elseif ( in_array( $key, $attribs_empty ) && ($value) ) {
				$attribString .= ($trailingSlash) ? sprintf( ' %1$s="%1$s"', $key ) : ' '.$key;

			} elseif ( in_array( $key, $attribs_deprecated ) && ! ($trailingSlash) ) {
				$attribString .= sprintf( ' %s="%s"', $key, $value);

				// is encoding needed for JS?
			} elseif ( in_array( $key, $attribs_events ) ) {
				$attribString .= sprintf( ' %s="%s"', $key, $value );
			}
		}

		return sprintf( '<input%s%s>', rtrim($attribString), $trailingSlash );
	}
	


	/**
	 * Generate HTML code for a select box
	 *
	 * @param	array	$attributes	array of attributes for the <selectt>-tatg
	 * @param	array	$options	array of options and their attributes for the <option>-tags
	 * @return	string	HTML code for a dropdown box
	 */
	function form_select($attributes, $options) {

		$attribString = '';
		$trailingSlash = ( substr( $this->doc->docType, 0, 5 ) == 'xhtml' ) ? ' /' : '';

				// TODO: Sonderbehandlung von z.B. checked


// attributes: datafld, datasrc, dataformatas, disabled, multiple, name, onblur, onchange, onfocus, size, tabindex
/*
? datafld
? datasrc
? dataformatas
size
*/
			// attributes for <select>-tags
		$attribs = ' name, size, tabindex';
		$attribs_empty = 'disabled, multiple';
		$attribs_events = 'onblur, onchange, onfocus';

			// attributes for <option>-tags
			// "label" is allowed
		$attribsO = 'label, value';
		$attribsO_empty = 'disabled, selected';

			// adding universal attributes for <select>-tags
		$attribs .= ', class, id, style, title, dir, lang';
		$attribs_events = ', onclick, ondblclick, onmousedown, onmouseup, onmouseover, onmousemove, onmouseout, onkeypress, onkeydown, onkeyup';
			// adding universal attributes for <option>-tags
		$attribsO .= ', class, id, style, title, dir, lang';

		
			// change the lists to arrays, so a search is faster
		$attribs = t3lib_div::trimExplode( ',', $attribs );
		$attribs_empty = t3lib_div::trimExplode( ',', $attribs_empty );
		$attribs_events = t3lib_div::trimExplode( ',', $attribs_events );
		$attribsO = t3lib_div::trimExplode( ',', $attribsO );
		$attribsO_empty = t3lib_div::trimExplode( ',', $attribsO_empty );
		

		foreach( $attributes as $key => $value ) {
			if( in_array( $key, $attribs ) ) {
				$attribString .= sprintf( ' %s="%s"', $key, $value );

			} elseif ( in_array( $key, $attribs_empty ) && ($value) ) {
				$attribString .= ' ' . $key . ( $trailingSlash ? sprintf( '="%s"', $key ) : '') ;

				// is encoding needed for JS code?
			} elseif ( in_array( $key, $attribs_events ) ) {
				$attribString .= sprintf( ' %s="%s"', $key, $value );
			}
		}
		$selectTag = sprintf( '<select%s%s>', rtrim($attribString), $trailingSlash );

		foreach( $options as $option ) {
			$attribString = '';
			$label = '';
			foreach( $option as $key => $value ) {
				if( in_array( $key, $attribsO ) ) {
					$attribString .= sprintf( ' %s="%s"', $key, $value );
				} elseif ( in_array( $key, $attribsO_empty ) && ($value) ) {
					$attribString .= ' ' . $key . ( $trailingSlash  ? "=\"$key\"" : '' ) ;
				}
			}
			$label = htmlspecialchars( $option['label'] ? $option['label'] : $option['value'] );
			$optionTags[] = sprintf( '<option%s>%s</option>', $attribString, $label );
		}
		
		return $selectTag . implode( "\n", $optionTags ) . '</select>';
	}




	/**
	 * Generate HTML code for a set of checkboxes
	 *
	 * @param	string	$name	name attribute of checkboxes
	 * @param	array	$data	configuration data
	 * @param	array	$mapping	array with mapping config data->checkbox attributes
	 * @param	string	$wrap	format string for sprintf; implements the label as first argument and the <input>-tag as second argument
	 * @return	string	HTML code for multiple checkboxes
	 */
	function form_checkBoxes( $name, $data, $mapping, $wrap ) {
		$boxes = array();
		foreach( $data as $cb ) {
			$attributes = array(
				'type' => 'checkbox',
				'name' => strval( ($name != '') ? $name . '[' . $cb[$mapping['value']] . ']' : $cb[$mapping['name']] ),
//				'value' => $cb[$mapping['value']],
				'checked' => ( $cb[$mapping['checked']] ) ? 'checked' : ''
			);

			$boxes[] = sprintf( $wrap, $cb[$mapping['label']], $this->form_input($attributes) );
		}
		return implode( "\n", $boxes );
	}




	/**
	 * Generate HTML code for a dropdown box
	 *
	 * @param	string	$name	name attribute of the select box
	 * @param	array	$opt	data for the option tags
	 * @param	array	$selAttributes	optional additional attributes for the <select>-tag
	 * @return	string	HTML code for a dropdown box
	 */
	function form_dropDown($name, $opt, Array $selAttributes=array() ) {
		$dd = '';

			// use the additional attributes and overwrite them
		$selAttributes['name'] = $name;
		$selAttributes['size'] = 1;

		if( substr( $this->doc->docType, 0, 5 ) == 'xhtml' ) {
			$selAttributes['id'] = str_replace(
				array( '[', ']' ),
				array( '_', '_' ),
				$name
			);
		}
		
		return $this->form_select( $selAttributes, $opt );
	}

	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dkd_staticpublish/cm1/class.tx_dkdstaticpublish_cm1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dkd_staticpublish/cm1/class.tx_dkdstaticpublish_cm1.php']);
}

?>