<?php
/*
 *  CVS Versioning: $Id$
 */

if( ! ( defined('TYPO3_MODE') && TYPO3_MODE == 'BE' ) ) {
	die ('Access denied.');
}

$conf = array (

	'Static Publish' => array (
			// Es ist Pfui, aber es sollte gehen:
		'url' => $GLOBALS['BE_USER']->getSessionData('dkd_staticpublish_XMLMenuURL'),
		'http_protocol' => '',
		'table' => 'tx_dkdstaticpublish_urls',
		'clear_table' => false,
		'xml_dir' => 'static_publish',
		'mapping' => array (
			'pid' => array(
				'function' => array( 
					create_function( '$array', 'return ($array[0]);' ),
					array( $GLOBALS['BE_USER']->getSessionData('dkd_staticpublish_menuPid') )
				)
			),
			'pub_id' => array(
				'function' => array( 
					create_function( '$array', 'return $array[0];' ),
					array( $GLOBALS['BE_USER']->getSessionData('dkd_staticpublish_pubID') )
				)
			),
			'tstamp' => array(
				'function' => array( 'time', array()
				)
			),
			'crdate' => array(
				'function' => array( 'time', array()
				)
			),
			'cruser_id' => array(
				'function' => array( 
					create_function( '$array', 'return $array[0];' ),
					array( $GLOBALS['BE_USER']->user['uid'] )
				)
			),
			'hidden' => array(
				'xml_fieldname' => 'fe_display',
				'function' => array(
					create_function( '$array', 'return ($array[0]) == 1 ? 0 : 1;' ),
					array( 'THIS' )
				)
			)
		),
		'scope' => 'pid',
		'postProcessing' => array ('function' => array('tx_dkd_staticpublish_procs', 'process_afterStaticPublish'), 'params' => array() ),
		'pictures' => array(
			'url' => array('%s')
		),
		'picture_url' => t3lib_div::getIndpEnv('TYPO3_SITE_URL'),
		'picture_dir' =>
			( $GLOBALS['TYPO3_CONF_VARS']['FE']['publish_dir'] ) ?
			$GLOBALS['TYPO3_CONF_VARS']['FE']['publish_dir'] :
			array_search(
				'publish_path',
				array_flip( unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dkd_staticpublish'] ) )
			)
	)
);

?>