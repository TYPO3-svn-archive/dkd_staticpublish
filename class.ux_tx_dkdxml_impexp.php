<?php
/*
 *  CVS Versioning: $Id$
 */

/***************************************************************
*  Copyright notice
*
*  (c) 2004-2005 Thorsten Kahler (thorsten.kahler@dkd.de)
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



require_once( t3lib_extMgm::extPath('dkd_xmlimport', 'mod1/class.tx_dkdxml_impexp.php') );

class ux_tx_dkdxml_impexp extends tx_dkdxml_impexp {




	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function modMenu()	{

		return array (
			'function' => Array (
				'3' => $GLOBALS['LANG']->getLL('function3'),
				'1' => $GLOBALS['LANG']->getLL('function1'),
				'2' => $GLOBALS['LANG']->getLL('function2')
			)
		);
	}


	/**
	 * Generates the module content
	 */
	function moduleContent()	{

		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content = $this->importXML();
				$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('importXML_header'),$content,0,1);
			break;
			case 2:
				$content = $this->importPics();
				$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('importPics_header'),$content,0,1);
			break;
			case 3:
				$GLOBALS['BE_USER']->pushModuleData( 'web_txdkdxmlimportM1', array('function' => 1) );
				$location = t3lib_div::resolveBackPath( t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $GLOBALS['BACK_PATH'] . t3lib_extMgm::extRelPath('dkd_staticpublish') );
				$location .= 'cm1/index.php?id=' . $this->id .'&SET[function]=2';
				header( 'Location: ' . $location );
			break;
		}
	}




	function importXML(){

		$content .= sprintf('<h4>%s</h4><p>%s</p>', $GLOBALS['LANG']->getLL('importXML_conf_header'), $GLOBALS['LANG']->getLL('importXML_conf_hint'));

		$configurations = array();
		foreach( $this->selections as $sel ) {
			$configurations[] = array( 'value' => $sel, 'label' => $sel );
		}
		$select = $this->selectBox( 'config', $configurations, $this->conf_selected );
		$content .= '<p>' .  $select . '</p>';

		if( ! $this->conf_selected ) {
			$content .= '<p><input type="submit" value="'. $GLOBALS['LANG']->getLL('importXML_buttonSelect') .'"></p>';

		} else {		// configuration is selected

			$this->config['pid'] = intval( $this->vars['page_id'] );
			$content .= '<p>'. $GLOBALS['LANG']->getLL('importXML_currentConfig') .':</p>';
			foreach($this->config as $key => $value){
				$value = ( t3lib_div::inList( 'http_user,http_password', $key ) ) ? '******' : $value;
				$content.='<p>'.$key.' => '.$value.' </p>';
			}

			if ( $this->config['url'] ) {

				$content .= sprintf ( '
						<p>%s:<br />
						<input type="text" maxlength="5" size="5" name="id" value="%s" readonly="readonly" /><br />
						<input type="submit" value="%s" /></p>',
					$GLOBALS['LANG']->getLL('importXML_pidMsg'),
					$this->id,
					$GLOBALS['LANG']->getLL('importXML_buttonImport')
				);
				$content .= $this->hiddenField('import', '1', 1);
				$content .= $this->hiddenField('config', $this->conf_selected, 1);

				if( $this->vars['import'] ) {

					if ( $this->extConf['backup'] && ! $this->importer->backupRecords( $this->config['table'], $this->config['pid'] ) ) {

						$this->importer->log('error', 'Could not backup records, so insert was skipped');

					} else {

						$proto = isset($this->config['http_protocol']) ? $this->config['http_protocol'] : 'http://';
						$auth = ( $this->config['http_user'] && $this->config['http_password'] ) ? sprintf('%s:%s@', $this->config['http_user'], $this->config['http_password'] ) : '';
						$url = $proto . $auth . $this->config['url'];

						if (! $assoc = $this->importer->fetchData($url) ) {
							$this->importer->log('error', 'The URL was not reachable: '.$url);
						} else {

							$table = $this->importer->assocArray2Table($assoc);

							if (is_array($this->config['preProcessing'])) {
								$this->importer->doProcessing( $this->config['preProcessing']['function'], $this->config['preProcessing']['params'] );
							}

							$log = $this->importer->insertRecords($table);
							if (is_array($this->config['postProcessing'])) {
								$this->importer->doProcessing( $this->config['postProcessing']['function'], $this->config['postProcessing']['params'] );
							}


							$this->logFiles['error'] = $this->extConf['log_file'] ? $this->extConf['log_file'] : 'html';

							if ( is_array($log) && ( $log['rows'] != $log['success'] + $log['failed'] ) )  {
								$msg = sprintf( 'Something fishy happened: There were %d records to insert, but %d were successful and %d failed', $log['rows'], $log['success'], $log['failed'] );
								$this->importer->log( 'error', $msg );
								$content .= sprintf( '<p class="bgColor">%s</p>', $msg );
							} elseif ( $log['failed'] ) {
								$msg = sprintf( 'An error occured during insertion of %d datasets: %d were successful but %d failed', $log['rows'], $log['success'], $log['failed'] );
								$this->importer->log( 'error' , $msg );
								$content .= sprintf( '<p class="bgColor">%s</p>', $msg );
							} else {
								$msg = sprintf( '%d rows were inserted successful', $log['rows'] );
								$this->importer->log( 'ok', $msg );
								$content .= sprintf( '<h3>%s</h3>', $msg );
									// reset configuration from tx_dkdstaticpublish_cm1
								$GLOBALS['BE_USER']->setAndSaveSessionData( 'dkd_staticpublish_XMLMenuURL', false );
								$GLOBALS['BE_USER']->setAndSaveSessionData( 'dkd_staticpublish_menuPid', false );
								$GLOBALS['BE_USER']->setAndSaveSessionData( 'dkd_staticpublish_pubID', false );
							}
						}
					}
				}

				$content .= $this->backLink( $GLOBALS['LANG']->getLL('importXML_backLink'), '<h4>|</h4>' );

			} else {
				$content .= sprintf( '<p class="perm-denied">%s</p>', $GLOBALS['LANG']->getLL('importXML_msg_noUrl') );
			}
		}

		return $content;
	}






	function importPics(){

		$content = '';

		$select = "\n".'<select name="'. $this->prefixId. '[config]">';
		foreach($this->selections as $sel){
			$found = ($this->conf_selected == $sel) ? ' selected="selected"' : '';
			$select .= sprintf('<option value="%s"%s>%s</option>', $sel, $found, $sel);
		}
		$select .= '</select>'."\n";
		$content .= sprintf('<h4>%s</h4><p>%s</p>', $GLOBALS['LANG']->getLL('importPics_conf_header'), $GLOBALS['LANG']->getLL('importPics_conf_hint'));
		$content .= '<p>'.$select.'<input type="submit" value="'. $GLOBALS['LANG']->getLL('form_select') .'"></p>';


		if ($this->conf_selected) {

			$current = ($this->conf_selected == $this->vars['config_sel']);

			if ( ! ( is_array($this->config['pictures']) && count($this->config['pictures']) ) ) {
				$content .= '<p><br>' . $GLOBALS['LANG']->getLL('importPics_noPics') . '</p>';
			} else {

				$content .= sprintf( '<h3>%s:</h3>', $GLOBALS['LANG']->getLL('importPics_currentSettings') );
				$content .= sprintf( $GLOBALS['LANG']->getLL('importPics_url'), $this->config['pictures_url'] );

					// select fields
				$pics_selected = '';
				$pics_selected_flag = false;
				foreach($this->config['pictures'] as $field => $pics){
					$content .= sprintf('<p>DB-%s: %s<br>', $GLOBALS['LANG']->getLL('create_table_field'), $field );

						// select picture variant
					if( count($pics) > 1 ) {
						foreach($pics as $i =>$pic) {
							$checked = ($this->vars['pics'][$field][$i]) ? ' checked' : '';
							$label = sprintf($pic, $GLOBALS['LANG']->getLL('importPics_placeholder_fieldVal') );
							$content .= sprintf('<input type="checkbox" name="%s[pics][%s][%s]" value="1"%s> %s<br />', $this->prefixId, $field, $i, $checked, $label);
							if ($current) {
								if ( $this->vars['pics'][$field][$i] ) {
									$pics_selected .= $this->hiddenField( sprintf('pics][%s][%s', $field, $i), '1', 1);
									$pics_selected_flag = true;
								} else {
									$pics_selected .= $this->hiddenField( sprintf('pics][%s][%s', $field, $i), '0', 1);
								}
							}
						}
					} else {
						$label = sprintf($pics[0], $GLOBALS['LANG']->getLL('importPics_placeholder_fieldVal') );
						$content .= sprintf('<input type="checkbox" name="%s[pics][%s][0]" value="1" checked="checked"> %s<br />', $this->prefixId, $field, $label);
						if ($current) {
							$pics_selected .= $this->hiddenField( sprintf('pics][%s][0', $field), '1', 1 );
							$pics_selected_flag = true;
						}
					}

					$content .= '</p>';

				}

					// select publication
				$content .= sprintf ('<h4>%s</h4>', $GLOBALS['LANG']->getLL('importPics_pids_head') );

				$content .= '<p>'. $this->sb_publications( $current ) .'</p>';
				$selectedPublication = $current ? $this->hiddenField( 'pubID', $this->vars['pubID'], 1) : '';

					// set text for submit button
				$confirm_button = $GLOBALS['LANG']->getLL('form_select');

				if ( $current && $pics_selected_flag && strlen($selectedPublication) ) {

						// switch to import mode
					$confirm_button = $GLOBALS['LANG']->getLL( $this->vars['import'] ? 'form_import' : 'form_confirm' );
					$content .= $this->hiddenField( 'import', '1', 1);

						// write hidden inputs to freeze selections
					$content .= $pics_selected;
					$content .= $selectedPublication;

						// set pointer for import step
					$this->vars['pointer'] = isset($this->vars['pointer']) ? $this->vars['pointer'] : 0;

						// set up log for file activities
					$this->log['file'] = array();
					$this->logFiles['file'] = $this->extConf['log_file'];

						// restrain runtime of costly code
					$time_start = $this->getmicrotime();
					$time_end = $time_start + intval( ini_get('max_execution_time') ) * $this->importer->runTimeFactor;

					$fields = array();				// which db fields should be read
					foreach( $this->vars['pics'] as $field => $variants ) {
							// is a variant selected?
						foreach ($variants as $index => $checked ) {
							if  ($checked) {
								$fields[$field][] = $this->config['pictures'][$field][$index];
								$variant_count++;
							}
						}
					}

					$select_fields = implode( ', ', array_keys($fields) );
					$from_table = $this->config['table'];
					$where_clause  = 'pid=' . $this->id;
					$where_clause .= " AND pub_id='" . $GLOBALS['TYPO3_DB']->quoteStr( $this->vars['pubID'], $this->config['table'] ) . "'";
					$where_clause .= t3lib_BEfunc::BEenableFields( $this->config['table'] );
					$where_clause .= t3lib_BEfunc::deleteClause( $this->config['table'] );
					$orderBy = 'uid';
					$limit = sprintf('%u,%u', intval( $this->vars['pointer'] ), 99999 );
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $from_table, $where_clause, '', $orderBy, $limit);

					$proto = isset($this->config['http_protocol']) ? $this->config['http_protocol'] : 'http://';
					$auth = ( $this->config['http_user'] && $this->config['http_password'] ) ? sprintf('%s:%s@', $this->config['http_user'], $this->config['http_password']) : '';
					$url = $proto . $auth . $this->config['picture_url'];
					$url = ( substr($url, -1) == '/' ) ? $url : $url.'/';

						// alle nötigen Infos sind eingesmammelt
					if ($this->vars['import']) {

						$timeout = false;				// Flag: weitere Import-Schritte nötig?
						$pic_count = 0;				// Counter: wieviele Bilder wurden kopiert
						while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
							foreach ($row as $field => $pic) {
								if ($pic != '') {
									foreach ($fields[$field] as $template) {
										$file = $url . sprintf($template, $pic);
										$err = $this->importer->copy($file, $this->config['picture_dir']);

										if (! $err) {
											$pic_count++;
										} else {
											$this->logFile['error'] = 'html';
											switch ((string)$err) {
												case '3' :
													break;
												case '1' :
												case '2' :
												case '4' :
												default  :
													break 4;
											}
										}
									}
								}
							}
							$this->vars['pointer']++;
								// Laufzeit checken
							if ($this->getmicrotime() >= $time_end) {
								$timeout = true;
								$this->vars['timeout'] = true;
// 								header( 'http://tycon3.gimli.dkd.de' . t3lib_div::linkThisScript( $this->vars ) );
								break;
							}
						}
					}
					$content .= $this->hiddenField('pointer', $this->vars['pointer'], 1);
					$content .= $this->hiddenField('pic_count', $this->vars['pic_count'] + $pic_count, 1);
				}
				if ( (! $this->vars['pointer']) || $timeout ) {
						// processing active
					if (isset($timeout)) {
						$this->importer->log('file', sprintf('** imported %s pictures up to record %s', $this->vars['pic_count'], $this->vars['pointer']) );
						$reset_button = '';
					} else {
						$reset_button = sprintf( '<input type="reset" value="%s">', $GLOBALS['LANG']->getLL('form_reset') );
					}
					$msg = sprintf( $GLOBALS['LANG']->getLL('importPics_pic_count'), $pic_count);
					$this->importer->log('file', $msg);
					$content .= sprintf('<p>%s</p>', $msg);
					$content .= sprintf( '<br><p>%s %s</p>', $GLOBALS['LANG']->getLL('importPics_step'),  $this->vars['pointer'] );
					$content .= $this->hiddenField( 'config_sel', $this->conf_selected, 1);
					$content .= sprintf('<p><input type="submit" value="%s"> %s</p>', $confirm_button, $reset_button );
				} else {
						// processing finished
					$msg = sprintf( $GLOBALS['LANG']->getLL('importPics_completed_msg'), $this->vars['pointer'], $this->vars['pic_count'] + $pic_count );
					$this->importer->log('ok', $msg);
					$this->importer->log('file', $msg);
					$content .= '<h3>' . $msg . '</h3>';
				}

			}

			$content .= $this->backLink( $GLOBALS['LANG']->getLL('importPics_backLink'), '<h4>|</h4>' );
		}
		return $content;
	}


	/**
	 * Create HTML for Select Box (drop down)
	 *
	 * @param	string	$param name of the piVar (used for name attrib of select box and to pick the current value of this var)
	 * @param	array	$options assoc array of value/label pairs which are available in the select box
	 * @param	bool	$selected (optional) flag: indicates whether a value was previously selected
	 * @return	string	HTML code
	 */
	function selectBox ($param, $options, $selected=false) {

		$found = false;
		if (substr( $this->doc->docType, 0, 5) == 'xhtml') {
			$selectedAttribute = ' selected="selected"';
			$disabledAttribute = ' disabled="disabled"';
		} else {
			$selectedAttribute = ' selected';
			$disabledAttribute = ' disabled';
		}
		$optTags = array();

		foreach ($options as $row) {
			$sel = '';
			if($selected && !$found && ($row['value'] == $this->vars[$param])) {
				$found = true;
				$sel = $selectedAttribute;
			}
			$optTags[]= sprintf( '<option value="%s"%s>%s</option>', $row['value'], $sel, $row['label'] );
		}

		$selTag = sprintf(
			'<select name="%s[%s]"%s>',
			$this->prefixId,
			$param,
			$found ? $disabledAttribute : ''
		);

		return $selTag . implode( chr(10), $optTags ) . '</select>';
	}

	/**
	 * Generate select box with publications of the current page
	 *
	 * @param	bool	$selected (optional) flag: indicates whether a publication was previously selected
	 *
	 */
	function sb_publications( $selected=false ) {
			// select publication
		$publications = array();
		$dateFormat = $GLOBALS['LANG']->getLL('pubID_dateFormat');

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'pub_id, count(uid) as whole, (count(uid) - sum(hidden)) as avail',
			$this->config['table'],
			'pid='. $this->id . t3lib_BEfunc::deleteClause( $this->config['table'] ),
			'pub_id',
			'pub_id DESC'
		);

		while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
			$publications[] = array(
				'value' => $row['pub_id'],
				'label' => strftime( $dateFormat, $row['pub_id'] ) . sprintf(' (%u/%u)', $row['avail'], $row['whole'] )
			);
		}

		return $this->selectBox( 'pubID', $publications, $selected );
	}



}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dkd_staticpublish/class.ux_tx_dkdxml_impexp.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dkd_staticpublish/class.ux_tx_dkdxml_impexp.php']);
}


?>