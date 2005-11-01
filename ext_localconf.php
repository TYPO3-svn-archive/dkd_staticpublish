<?php
/**
 *$Id$
 */	

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/*
t3lib_extMgm::addPageTSConfig('
	xMOD_tx_dkdstaticpublish_cm1 {
		pageTypes = 0 
		languages =
		scope = single
		maxDepth = 1
		ready = 0
	}
');
*/
t3lib_extMgm::addUserTSConfig('
	xMOD_tx_dkdstaticpublish_cm1.1 {
		pageTypes = 0 
		languages =
		scope = single
		maxDepth = 0
		ready = 1
	}
	xMOD_tx_dkdstaticpublish_cm1.2 {
		label = 3 Ebenen
		pageTypes = 0 
		languages =
		scope = all
		maxDepth = 3
		ready = 1
	}
');

$TYPO3_CONF_VARS['BE']['XCLASS']['ext/dkd_xmlimport/class.tx_dkdxml_importer.php'] = t3lib_extMgm::extPath( 'dkd_staticpublish', 'class.ux_tx_dkdxml_importer.php' );

$TYPO3_CONF_VARS['BE']['XCLASS']['ext/dkd_xmlimport/mod1/class.tx_dkdxml_impexp.php'] = t3lib_extMgm::extPath( 'dkd_staticpublish', 'class.ux_tx_dkdxml_impexp.php' );
$TYPO3_CONF_VARS['BE']['XLLfile']['EXT:dkd_xmlimport/mod1/locallang.php'] = 'EXT:dkd_staticpublish/res/dkdxmlimport_mod1_locallang.php';

t3lib_extMgm::addPItoST43($_EXTKEY,'pi_xmlmenu/class.tx_dkdstaticpublish_pi_xmlmenu.php','_pi_xmlmenu','menu_type',0);
?>