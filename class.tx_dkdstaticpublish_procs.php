<?php
/*
 *  CVS Versioning: $Id$
 */

/***************************************************************
*  Copyright notice
*
*  (c) 2004 Maryna Sigayeva (maryna.sigayeva@dkd.de)
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
 * container class for pre- and post_processing functions used by dkd_staticpublish extension.
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */

class tx_dkdstaticpublish_procs {

	function process_afterStaticPublish() {
			// delete publishing-ID after successfull import
		$GLOBALS['BE_USER']->setAndSaveSessionData( 'dkd_staticpublish_pubID', '' );
		return;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dkd_staticpublish/class.tx_dkdstaticpublish_procs.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']["ext/dkd_staticpublish/class.tx_dkdstaticpublish_procs.php']);
}