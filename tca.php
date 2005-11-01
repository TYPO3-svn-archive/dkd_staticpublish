<?php
/**
 *$Id$
 */


if (!defined ('TYPO3_MODE'))	 die ('Access denied.');

$TCA['tx_dkdstaticpublish_urls'] = Array (
	'ctrl' => $TCA['tx_dkdstaticpublish_urls']['ctrl'],	
	'interface' => Array (
		'showRecordFieldList' => 'pub_id,title,orig_pid,url'
	),
	'feInterface' => $TCA['tx_dkdstaticpublish_urls']['feInterface'],
	'columns' => Array (
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:dkd_staticpublish/locallang_db.php:tx_dkdstaticpublish_urls.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '1'
			)
		),
		'pub_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:dkd_staticpublish/locallang_db.php:tx_dkdstaticpublish_urls.pub_id',
			'config' => Array (
				'type' => 'input',
				'eval' => 'datetime',
				'size' => 10,
			)
		),
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:dkd_staticpublish/locallang_db.php:tx_dkdstaticpublish_urls.title',
			'config' => Array (
				'type' => 'none',
			)
		),
		'url' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:dkd_staticpublish/locallang_db.php:tx_dkdstaticpublish_urls.url',
			'config' => Array (
				'type' => 'none',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'pub_id;;;;1-1-1, title;;;;2-2-2, orig_pid;;;;3-3-3, url')
	),
	'palettes' => Array	(
		'1' => Array('showitem' => '')
	)
);
?>