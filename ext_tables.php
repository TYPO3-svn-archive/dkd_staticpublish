<?php
/**
 *$Id$
 */	

if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_func',		
		'tx_dkdstaticpublish_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_dkdstaticpublish_modfunc1.php',
		'LLL:EXT:dkd_staticpublish/locallang_db.php:moduleFunction.tx_dkdstaticpublish_modfunc1',
		'wiz'	
	);
}


if (TYPO3_MODE=='BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][]=array(
		'name' => 'tx_dkdstaticpublish_cm',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_dkdstaticpublish_cm.php'
	);
}

$TCA['tx_dkdstaticpublish_urls'] = Array (
    'ctrl' => Array (
        'title' => 'LLL:EXT:dkd_staticpublish/locallang_db.php:tx_dkdstaticpublish_urls',
        'label' => 'pub_id',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
		'delete' => 'deleted',
		'enablecolumns' => Array (		
			'disabled' => 'hidden'
		),
        'default_sortby' => 'ORDER BY crdate',
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile' => 'pages.gif',
        'rootLevel' => 0,
        'readOnly' => 1
    ),
    'feInterface' => Array (
        'fe_admin_fieldList' => '',
    )
);


t3lib_extMgm::addPlugin(Array('LLL:EXT:dkd_staticpublish/locallang_db.php:tt_content.menu_type_xmlmenu', $_EXTKEY.'_pi_xmlmenu'),'menu_type');
?>