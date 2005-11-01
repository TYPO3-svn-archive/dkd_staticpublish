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



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ("conf.php");
require ($BACK_PATH."init.php");


	// ....(But no access check here...)
	// DEFAULT initialization of a module [END] 


require_once( t3lib_extMgm::extPath('dkd_staticpublish') . 'cm1/class.tx_dkdstaticpublish_cm1.php' );


	// TimeTrack'ing is needed e.g. by t3lib_TStemplate
require_once(PATH_t3lib.'class.t3lib_timetrack.php');
$TT = new t3lib_timeTrack;
$TT->start();
$TT->push('','Script start (dkd_staticpublish/cm1)');

$GLOBALS['LANG']->includeLLFile("EXT:dkd_staticpublish/cm1/locallang.php");

// Make instance:
$SOBE = t3lib_div::makeInstance("tx_dkdstaticpublish_cm1");
$SOBE->init();


$SOBE->main();
$SOBE->printContent();

$TT->pull();

?>