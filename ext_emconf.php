<?php

########################################################################
# Extension Manager/Repository config file for ext: "dkd_staticpublish"
# 
# Auto generated 01-11-2005 17:04
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'd.k.d Static Publish',
	'description' => 'A BE module (in context menu) to generate static HTML. Needs and uses working RealUrl- or simulateStatic-configuration (not tested with AliasPro etc.). Requires extension dkd_xmlimport.',
	'category' => 'module',
	'author' => 'Thorsten Kahler',
	'author_email' => 'thorsten.kahler@dkd.de',
	'shy' => '',
	'dependencies' => 'cms,dkd_xmlimport',
	'conflicts' => '',
	'priority' => '',
	'module' => 'cm1',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => 'd.k.d Internet Service GmbH',
	'private' => '',
	'download_password' => '',
	'version' => '0.6.1',	// Don't modify this! Managed automatically during upload to repository.
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'dkd_xmlimport' => '0.6.0-0.0.0',
			'php' => '5.2.1-0.0.0',
			'typo3' => '4.1.0-4.3.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => '',
);

?>