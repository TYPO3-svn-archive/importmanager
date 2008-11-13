<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Pascal Hinz <hinz@elemente.ms>
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


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');
require_once(PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once(PATH_t3lib.'class.t3lib_extfilefunc.php');
require_once(t3lib_extMgm::extPath('rs_userimp').'mod1/class.tx_rsuserimp.php');

$LANG->includeLLFile('EXT:importmanager/mod1/locallang.xml');
# require_once(PATH_t3lib.'class.t3lib_scbase.php');
require_once(PATH_t3lib.'class.t3lib_svbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]


/**
 * Class for the csv template download.
 *
 * @author Pascal Hinz <hinz@elemente.ms>
 * @package TYPO3
 * @subpackage tx_importmanager
 */
class tx_importmanager_csvtemplate extends t3lib_svbase  {
	var $pageinfo;
	
	
	/**
	 * Initializes the Module
	 * 
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		parent::init();
	}
	
	
	/**
	 * Build CSV and send it as header to you.
	 * 
	 * @return void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access && t3lib_div::_GET('uid')) || !$this->id)	{

			$c = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['importmanager']);
			
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('dbtable,dbmapping','tx_importmanager_mapping','uid='.t3lib_div::_GET('uid'));
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			
			if($row) {
				
				# $t3lib_svbase = t3lib_div::makeInstance('t3lib_svbase');
				$t3lib_basicFileFunctions = t3lib_div::makeInstance('t3lib_basicFileFunctions');
				
				$CSV_COLUMNS = array();
				$dbmapping = unserialize($row['dbmapping']);
				foreach($dbmapping as $VALUE) {
					if($VALUE['MapType']==1 && in_array($c['fieldEncaps'].$VALUE['Mapping'].$c['fieldEncaps'],$CSV_COLUMNS)==false) {
						 $CSV_COLUMNS[] = $c['fieldEncaps'].$VALUE['Mapping'].$c['fieldEncaps'];
					}
				}

				// Convert data into file charset
				$t3lib_cs = t3lib_div::makeInstance("t3lib_cs");
				$t3lib_cs->convArray($CSV_COLUMNS,$c['dbCharset'],$c['fileCharset']);
				
				// Build CSV file
				$CSV_FILE = $this->writeFile(implode($c['fieldDelimiter'],$CSV_COLUMNS),PATH_site.'uploads/tx_importmanager/'.$row['dbtable'].'.csv');
				$CSV_INFO = $t3lib_basicFileFunctions->getTotalFileInfo($CSV_FILE);
				
				// Build header for download
				header('Content-Description: File Transfer'); 
				header('Content-Disposition: attachment; filename="'.$CSV_INFO['filebody'].'-'.time().'.csv"');
				header('Content-type: text/'.$CSV_INFO['fileext'].'; charset='.$c['fileCharset']);
				header('Content-Length: '. filesize($CSV_FILE));
				readfile($CSV_FILE);
				exit;
				
			}
			
		}
	}
	
}

/* No XCLASS needed?!
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/importmanager/mod1/class.tx_importmanager_csvtemplate.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/importmanager/mod1/class.tx_importmanager_csvtemplate.php']);
} */

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_importmanager_csvtemplate');
$SOBE->init();

// Include files?
// foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

// Run it
$SOBE->main();

?>