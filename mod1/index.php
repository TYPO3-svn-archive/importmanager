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
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Import Manager' for the 'importmanager' extension.
 *
 * @author	Pascal Hinz <hinz@elemente.ms>
 * @package	TYPO3
 * @subpackage	tx_importmanager
 */
class  tx_importmanager_module1 extends t3lib_SCbase {
		var $pageinfo;


		/**
		 * Initializes the Module
		 *
		 * @return void
		 */
		function init()	{
			global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
			parent::init();
		}


		/**
		 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
		 * The menu is split into admin and editor sections.
		 *
		 * @return void
		 */
		function menuConfig()	{
			global $BE_USER,$LANG;
			if($BE_USER->user['admin']) {
			$this->MOD_MENU = Array (
				'function' => Array (
					'1' => $LANG->getLL('ExtFunction1'),
					// '2' => $LANG->getLL('ExtFunction2'),
					'3' => $LANG->getLL('ExtFunction3'),
				)
			);
			} else {
			$this->MOD_MENU = Array (
				'function' => Array (
					'1' => $LANG->getLL('ExtFunction1')
				)
			);
			}
			parent::menuConfig();
		}


		/**
		 * Main function of the module. Write the content to $this->content
		 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
		 *
		 * @return void
		 */
		function main()	{
			global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

			// Access check!
			// The page will show only if there is a valid page and if this page may be viewed by the user
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 : 0;

			if (($this->id && $access) || !$this->id)	{

				// Draw the header.
				$this->doc = t3lib_div::makeInstance('bigDoc');
				$this->doc->backPath = $BACK_PATH;
				$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath('importmanager').'mod/res/tx_importmanager_mod.css';
				$this->doc->form='<form action="" method="POST" enctype="multipart/form-data">';

				// JavaScript
				$this->doc->JScode = '
					<script language="javascript" type="text/javascript">
						script_ended = 0;
						function jumpToUrl(URL)	{
							document.location = URL;
						}
					</script>
				';
				$this->doc->postCode='
					<script language="javascript" type="text/javascript">
						script_ended = 1;
						if (top.fsMod) top.fsMod.recentIds["web"] = 0;
					</script>
				';

				$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

				$this->content.=$this->doc->startPage($LANG->getLL('ExtTitle'));
				$this->content.=$this->doc->header($LANG->getLL('ExtTitle'));
				$this->content.=$this->doc->spacer(5);
				$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
				$this->content.=$this->doc->divider(5);

				// Render content:
				if($BE_USER->user['admin']) {
					$this->moduleContentForAdmin();
				} else {
					$this->moduleContentForEditor();
				}

				// ShortCut
				if ($BE_USER->mayMakeShortcut()) {
					$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
				}

				$this->content.=$this->doc->spacer(10);
			} else {

				// If no access or if ID == zero
				$this->doc = t3lib_div::makeInstance('bigDoc');
				$this->doc->backPath = $BACK_PATH;
				// Build content
				$this->content.=$this->doc->startPage($LANG->getLL('ExtTitle'));
				$this->content.=$this->doc->header($LANG->getLL('ExtTitle'));
				$this->content.=$this->doc->spacer(5);
				$this->content.= '<p>'.$LANG->getLL('ExtNoPermission').'</p>';
				$this->content.=$this->doc->spacer(10);

			}
		}


		/**
		 * Prints out the module HTML
		 *
		 * @return void
		 */
		function printContent()	{

			$this->content.=$this->doc->endPage();
			echo $this->content;
		}


		/**
		 * Generates the module content only for Administrators
		 *
		 * @return void
		 */
		function moduleContentForAdmin()	{

			global $LANG,$FILEMOUNTS,$TCA,$TYPO3_CONF_VARS;

			switch((string)$this->MOD_SETTINGS['function'])	{
				case 1:
					$this->menuFunction1();
				break;
				case 2:
					$this->menuFunction2();
				break;
				case 3:
					$this->menuFunction3();
				break;
			}
		}


		/**
		 * Generates the module content only for Editors
		 *
		 * @return void
		 */
		function moduleContentForEditor() {
			switch((string)$this->MOD_SETTINGS['function'])	{
				case 1:
					$this->menuFunction1();
				break;
			}
		}


		/**
		 * This function is used to search in an specific table.
		 * The fieldArray holds all fields which should have an specified value
		 * if an record whith the defined combination is found, the field
		 * $lookupField will be returned, otherwise false
		 *
		 * @lookupTable	string	the table in which we should search for the
		 * record
		 * @lookupField string	the field name which value should be returned,
		 * if an record which matches the configuration in $fieldArray is found
		 * @fieldArray	array	multidimensional array which defines in which
		 * field we should search for which value; each item has to be an array,
		 * where the first is the fieldname, and the second the value to look
		 * for
		 */
		function searchRecord($lookupTable, $lookupField, $fieldArray) {
			if ('' == $lookupTable || '' == $lookupField || !is_array($fieldArray) || 0 == count($fieldArray)) { return false; }
			$query = array();
			foreach ($fieldArray as $k => $config) {
				// tx_artikelstammdaten_materialnummersap:MaterialNummer
				$query[] = $GLOBALS['TYPO3_DB']->quoteStr($config[0], $lookupTable).'="'.$GLOBALS['TYPO3_DB']->quoteStr($config[1], $lookupTable).'"';
			}
			$query[] = 'hidden = 0';
			$query[] = 'deleted=0';

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($GLOBALS['TYPO3_DB']->quoteStr($lookupField, $lookupTable),
				$lookupTable,
				implode(' AND ',$query));
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			if (false === $row || 0 == count($row)) {
				return false;
			}
			return $row[$lookupField];
		}

		/**
		 * Looks up in an foreign table which is connected via
		 * MM-Relation
		 *
		 * @params string $value	The value which should be looked for in
		 * field $field
		 * @params string $field	look up in that field
		 * @params string $table 	look up in the field of that table
		 * @params string $getField return the value of that field in the record
		 * which has beend found
		 * @params string $mmtable  MM-Table
		 * @params string $localTable
		 * @params string $foreignTable
		 *
		 * @return mixed returns False if no record is found, array if there is
		 * an record
		 */
		function lookupForMmRecord($value, $field = 'title', $table = '', $getField = 'uid', $mmtable = '', $localTable = '', $foreignTable = '') {
			if ('' == $value || '' == $field || '' == $table || '' == $getField  || '' == $mmtable || '' == $localTable || '' == $foreignTable) { return false; }
			// t3lib_div::debug("Value: $value<br>Field: $field<br>Table: $table<br>getField: $getField");
			$uid = $this->lookupForRecord($value, $field, $table, $getField);

			if (false === $uid) { return false; }
			$uid_local = '';
			$uid_foreign = '';
			if ($localTable == $table) {
				$uid_local = $uid;
				$insertInto = 'uid_foreign';
			} else {
				$uid_foreign = $uid;
				$insertInto = 'uid_local';
			}
			// vollständige Textfelder  	uid_local 	uid_foreign 	tablenames 	sorting
			// the array which should be inserted into table "table"
			$updateArray = array(
				'insertInto' => $insertInto, // in which field the uid of the imported record should be inserted
				'table' => $mmtable,
				'uid_local' => $uid_local,
				'uid_foreign' => $uid_foreign,
				'tablenames' => '',
			);
			// @TODO: check if this entry exists - so there is no need to insert it again...
			return $updateArray;
		}

		/**
		 * Looks in an foreign table
		 * searches for an record which has an special value in an field, and
		 * returns f.e. the uid of that record
		 *
		 * @params string $value	The value which should be looked for in
		 * field $field
		 * @params string $field	look up in that field
		 * @params string $table 	look up in the field of that table
		 * @params string $getField return the value of that field in the record
		 * which has beend found
		 *
		 * @return integer	The uid which has been found, false otherwise
		 */
		function lookupForRecord($value, $field = 'title', $table = '', $getField = 'uid') {
			if ('' == $value || '' == $field || '' == $table || '' == $getField) { return false; }
			if (isset($this->cache[$table][$field][$value][$getField])) {
				return $this->cache[$table][$field][$value][$getField];
			}
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($GLOBALS['TYPO3_DB']->quoteStr($getField, $table),$table,$GLOBALS['TYPO3_DB']->quoteStr($field, $table).'="'.$GLOBALS['TYPO3_DB']->quoteStr($value, $table).'" AND hidden=0 AND deleted=0');
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			if (0 == count($row)) {
				$this->cache[$table][$field][$value][$getField] = false;
				return false;
			}
			$this->cache[$table][$field][$value][$getField] = $row[$getField];
			return $row[$getField];
		}

		/**
		 * checks if the column exists in csv
		 *
		 * @return boolean returns true if the columnname exists in CSV, otherwise false
		 */
		function columnExistsInCSV($fieldname) {
			return isset($this->CSVcolumnToContent[$fieldname]);
		}
		

		/**
		 * Menu function 1; Build the steps for upload and import.
		 *
		 * @return void
		 */
		function menuFunction1() {

			global $GLOBALS,$TYPO3_CONF_VARS,$LANG;

			// Init Block
			$piVars = (array) t3lib_div::_POST('tx_importmanager');

			// Switch Block
			if(empty($piVars['action'])) {

				// Init action
				$this->content.= $this->SetFormAction('upload');

				$content.= '<p>'.$LANG->getLL('UploadStep1Description').'</p>';
				$content.= $this->BuildUploadForms();

				$this->content.= $this->doc->section($LANG->getLL('UploadStep1Title'), $content, 0, 1);

			} elseif ($piVars['action']=='upload') {

				$this->content.= $this->SetFormAction('import');
				$this->content.= $this->doc->section($LANG->getLL('UploadStep2Title'), '', 0, 1);

				$files = $this->CheckUpload();
				$t3lib_cs = t3lib_div::makeInstance("t3lib_cs");

				// Switch für die Files
				if(!empty($files)) {

					// Init Block
					$c = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['importmanager']);
					$mapper = t3lib_div::makeInstance("tx_rsuserimp");
					$mapper->fieldDelimiter = $c['fieldDelimiter'];
					$mapper->fieldEncaps = $c['fieldEncaps'];
					$mapper->CSVhasTitle = $c['CSVhasTitle'] ? TRUE: FALSE;

					$c['fileCharset'] = $t3lib_cs->parse_charset($c['fileCharset']);
					$c['dbCharset'] = $t3lib_cs->parse_charset($c['dbCharset']);
					// check if charset is known by TYPO3
					if (false === array_search($c['dbCharset'], $t3lib_cs->synonyms)) {
						die('Characterset for DB is unknown in TYPO3, check spelling: '.$c['dbCharset']);
					}
					if (false === array_search($c['fileCharset'], $t3lib_cs->synonyms)) {
						die('Characterset for FILE is unknown in TYPO3, check spelling: '.$c['fileCharset']);
					}

						// Für jede Datei die hochgeladen wurde, wird diese Schleife durchlaufen
					foreach ($files as $key => $value) {
							// Nur ausführen wenn auch wirklich eine Datei im Temp ordner liegt
						if (0 == mb_strlen($value)) {
							continue;
						}

							// init für jedes File
						$mapper->file = $value;
						$mapper->init();
						$mapper->CSV = $mapper->readCSV();
							// If CSV-Headers has Newline or tabs, we should ignore them
							// so it is possible to map suche fields too
						foreach ($mapper->columnNamesFromCSV as $tmpKey => $columnTitle) {
							$mapper->columnNamesFromCSV[$tmpKey] = str_replace(array("\n","\t","\r","(",")"), array('','','','',''), $columnTitle);
						}

							// Ändern des getColumnsFromDB
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,dbtitle,dbtable,dbmapping','tx_importmanager_mapping','uid='.(int)$piVars['upload'][$key]['uid'].' AND hidden=0 AND deleted=0');
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

						$map = unserialize($row['dbmapping']);
						$mapper->columnNamesFromDB = $GLOBALS['TYPO3_DB']->admin_get_fields($row['dbtable']);
						# converting content for database
						$t3lib_cs->convArray($mapper->columnNamesFromCSV,$c['fileCharset'],$c['dbCharset'], true);
						$t3lib_cs->convArray($mapper->CSV,$c['fileCharset'],$c['dbCharset'], true);
						$tmp = $mapper->columnNamesFromCSV;

						$mapper->CSVcolumnToContent = array_flip($tmp);
						// used to update f.e. mm-relation tables
						$updateLate = array();
						$updateLate['mm'] = array(); // MM-Relation Tables

						$counter = 0;
						// so we can use it in every method
						$this->CSVcolumnToContent = $mapper->CSVcolumnToContent;

						// Für jede CSV Zeile muss ein import durchgeführt werden
						foreach ($mapper->CSV as $content) { 
							$ignoreRecord = false; // some Records could be ignored by syntaxcheck etc
							// Für jedes Tabellenfeld wird geschaut welche Mapping Art
							// und das jeweilige Mapping hinzugefügt.
							foreach ($mapper->columnNamesFromDB as $key => $value) {
								// Mapping speichern
								$reg = $map[$key]['Mapping'];
								switch ($map[$key]['MapType']) {
									// uid-feld
									/*
									case 6: // sapnr,sprache

										Mapping (BE) : "sapnr,sprach"
										ich muss die beiden felder
										sapnr und sprache in einem datensatz suchen
										wenn ich den gefunden hab, nehme ich dessen uid
										andernfalls keine

										-> uid = sapnr,sprache
										-> per SQL suchen und setzen falls gefunden,
										ansonsten uid nicht setzen
									*/
									case 6: // SearchForValues
										// multiple lookup
										// found record which has contentA in columnA and contentb in columnB
										// take from an "table" the value of "field" where
										// "fieldA" has the same value as csvA
										// and "fieldB" as the same value in csvB
										// table.uid|fieldA:csvA, fieldB: csvB, fieldC:"staticcontent"
										// tx_commerce_products.uid|tx_artikelstammdaten_materialnummersap:MaterialNummer
										list($lookup, $fields) = t3lib_div::trimExplode('|',$reg,true);
										list($lookupTable, $lookupField) = t3lib_div::trimExplode('.',$lookup,true);

										$fieldArray = array();
										foreach (t3lib_div::trimExplode(',',$fields,true) as $fieldConfig) {
											list($field, $csvField ) = t3lib_div::trimExplode(':', $fieldConfig ,true);
											// makes possible to use static data instead of
											// fieldnames
											if (substr($csvField,0,1) == '"' && substr($csvField,-1,1) == '"') {
												$fieldArray[] = array($field, substr($csvField,1,-1));
											} else {
												$fieldArray[] = array($field, $content[$mapper->CSVcolumnToContent[$csvField]]);
											}

										}
										$foundValue = $this->searchRecord($lookupTable, $lookupField, $fieldArray);
										if ($foundValue) {
											$v[$counter][$key] = $foundValue;
										}
										// $ignoreRecord = true;
									break;

									// CSV-Feld
									case 1:
										// check if that column has been defined in the csv file
										// wenn $map[$key]['Mapping'] nicht in der CSV existiert
										if (!$this->columnExistsInCSV($reg)) {
											continue;
										}
										# Das htmlspecialchars(*) muss an einer anderen Stelle stehen!
										$v[$counter][$key] = (string) $content[array_search($reg,$mapper->columnNamesFromCSV)];
									break;
									// Funktion
									case 2:
										switch ($reg) {
											default:
												// Hinweis: mitzählen wieviele Ersetzungen es gab ist nicht hilfreich, weil eine ggf. nich vorhandene Spalte darin vorkommen könnte
												// daher gibt es die extra Suche nach den Feldern												
												$countMatches = preg_match_all('/\{([^}]*)\}/e',$reg,$matches);
												if ($countMatches > 0 ) {
													for ($i = 0; $i < $countMatches; $i++) {
														if (!$this->columnExistsInCSV($matches[1][$i])) {
															//t3lib_div::debug($countMatches.' Felder gefunden wobei '.$matches[1][$i]. ' nicht existiert.','columnExistsInCSV'.$i);
															continue 2;
														}
													}
												}
												// array_search("$1",$mapper->columnNamesFromCSV) returns false - which would be converted to 0 so we have to check it 
												$parsed = preg_replace('/\{([^}]*)\}/e','addslashes($content[array_search("$1",$mapper->columnNamesFromCSV)])', $reg);									
												$v[$counter][$key] = (string) eval('return '.$parsed.';'); // or die('eval error: '.$parsed.' | counter: '.$counter.' | key: '.$key);
											break;
										}
									break;
									// Text
									case 3:
										$v[$counter][$key] = (string) $reg;
									break;
									// Look Up Field
									case 4:
										// Syntax
										list($feld, $lookupField, $lookupTable, $returnField) = t3lib_div::trimExplode('|',$reg,true);
										
										if (!$this->columnExistsInCSV($feld)) {
											continue;
										}
										// $v[$counter][$key] =
										// echo "Look up for: ".$content[$mapper->CSVcolumnToContent['Serie']].'<br>';
										$foreign = $this->lookupForRecord($content[$mapper->CSVcolumnToContent[$feld]], $lookupField, $lookupTable, $returnField);
										$v[$counter][$key] = (string)$foreign;
										// TODO: Diesen Datensatz ignorieren und nicht mehr einlesen
										if (0 == (int)$foreign) { $ignoreRecord = true; }
									break;
									// LookUpMMField
									//
									// Darf erst hinzugefügt werden, wenn der Datensatz eingefügt
									// oder geupdated wurde, da sonst der Zusammenhang unklar ist
									//
									// Informationen werden in $updateLate gespeichert,
									// damit dann später entsprechend abgefragt werden kann
									//
									// Unterstütz die Möglichkeit, dass mehrere CSV-Felder für eine MM-Relation der
									// Datenbank steht, d.h. category1, category2, category3 sind Felder in der CSV-Datei
									// Es sollen nun alle drei Kategorie Felder in der gleichen mm-Tabelle gespeichert werden
									//
									//
									// Syntax:
									// felder|mm-Tabelle|lookupField|listInsteadCounter|localTable|foreignTable
									//
									// felder: CSV-Feldnamen in doppelten Anführungsstrichen, mit Semikolon getrennt, wenn mehrere CSV-Felder einem MM-Feld zugewiesen werden sollen
									// mm-Tabelle: Tabellenname mit der MM-Relation
									// lookupField: Tabelle.Feldname in der der CSV-Wert gesucht werden soll
									// listInsteadCounter: in den meisten MM-Relationen wird in der Lokalentabelle ein
									//                     Zähler geführt, mit der Anzahl der MM-Relationen, bei commerce z.B. wird dort aber eine Kommagetrennte Liste geführt.
									//                     1 erstellt eine komma getrennte Liste
									//                     0 zeigt den Counter der Relationen an
									// localTable:         Tabellenname der Tabelle in der die Daten eigentlich importiert werden
									// foreignTable:       Tabellenname der "fremden" Tabelle
									case 5:
										// "Katalog 2009Gruppierung1";"Katalog 2009Gruppierung2 ";"Serie"|tx_commerce_products_categories_mm|tx_commerce_categories.title|0|tx_commerce_products|tx_commerce_categories
										list($felder, $mm, $lookupField, $listInsteadCounter, $localTable, $foreignTable) = t3lib_div::trimExplode('|',$reg,true);
										list($lookupTable, $lookupField) = t3lib_div::trimExplode('.',$lookupField,true);
										$felder = t3lib_div::trimExplode(';',$felder,true);
										$v[$counter][$key] = 0;
										$lookup = array();
										foreach ($felder as $fieldName) {
											if ('"' == substr($fieldName,0,1) && '"' == substr($fieldName,-1,1)) {
												$fieldName = substr($fieldName,1,-1);
											}
											if (!$this->columnExistsInCSV($fieldName)) {
												continue 2;
											}
											
											// lookupForMmRecord($value, $field = 'title', $table = 'tx_commerce_categories', $getField = 'uid', $mmtable = ' tx_commerce_products_categories_mm', $localTable = 'tx_commerce_products', $foreignTable = 'tx_commerce_categories')
											$tmp = $this->lookupForMmRecord(
													$content[$mapper->CSVcolumnToContent[$fieldName]],
													$lookupField,
													$lookupTable,
													'uid',
													$mm,
													$localTable,
													$foreignTable
											);
											// @TODO: what happens if $tmp is false?
												// only one could be filled
												$lookup[] = $tmp['uid_local'].$tmp['uid_foreign'];
												if ('' != trim($tmp['uid_local'].$tmp['uid_foreign'])) {
													// if an entry is in more than one field, this should
													// lead to the same key, so only one relation will be inserted
													// into db
													$updateLate['mm'][$counter][$tmp['table']][$tmp['uid_local'].$tmp['uid_foreign']] = $tmp;
												}
											
											$v[$counter][$key]++;

										}
										if ($listInsteadCounter) {
											$v[$counter][$key] = t3lib_div::uniqueList(implode(',',$lookup));
										}
									break;
								}

							}
							if ($ignoreRecord) {
								unset($v[$counter]);
							} else {
								$counter++;
							}

						}

						// t3lib_div::debug($v);

						/***
						 * Wenn die Daten in Ordnung sind schreibe Sie in die Datenbank!
						 */
						$where = $ukeys = array();
						$wkey = array();
						$insertContent = $updateContent = 0;
						$dbKeysArr = $GLOBALS['TYPO3_DB']->admin_get_keys($row['dbtable']);
						if(!empty($dbKeysArr)) {
							foreach ($dbKeysArr as $dbKey) {
								if($dbKey['Non_unique']==0 && $map[$dbKey['Column_name']]['MapType']!=0) {
									$wkey[] = $dbKey['Column_name'];
									$ukeys[$dbKey['Column_name']] = $dbKey['Column_name'].' <img src="../mod/res/tx_importmanager_icon_'.(($dbKey['Key_name']=='PRIMARY') ? 'key':'unique').'.gif" width="16" height="16" />';
									$where[$dbKey['Column_name']].= $dbKey['Column_name'].' IN (';
									$k=FALSE;
									foreach ($v as $fields) {
										$where[$dbKey['Column_name']].= ($k==TRUE)?',':'';
										$where[$dbKey['Column_name']].= '"'.$GLOBALS['TYPO3_DB']->quoteStr($fields[$dbKey['Column_name']], $row['dbtable']).'"';
										$k=TRUE;
									}
									$where[$dbKey['Column_name']].= ') ';
								}
							}
							if($wkey) {
								$RES = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(implode(',',$wkey),$row['dbtable'],implode(' OR ',$where),'','','','');
								$updateContent = count($RES);
							}

						}
						$insertContent = count($v)-$updateContent;

						//
						// TODO: check when and why this could happen
						// No Data: continue with next file
						if (!is_array($v) || count($v) <= 0) {
							$this->content.= '<p>'.$LANG->getLL('UploadStep2UploadNotSuccessfullNoData').'</p>';
							$content.= $this->doc->divider(5);
							continue;
						}

						foreach ($v as $counter => $iufields) {
							$iufields = $GLOBALS['TYPO3_DB']->fullQuoteArray($iufields, $row['dbtable']);
							// $t3lib_cs->convArray($iufields,$c['fileCharset'],$c['dbCharset'], true);
							$doup = array();
							foreach($iufields as $u => $uv) {
								$doup[] = $u.'='.$uv;
							}

							$query = 'INSERT INTO '.$row['dbtable'].'
							(
								'.implode(',
								',array_keys($iufields)).'
							) VALUES (
								'.implode(',
								',$iufields).'
							) ON DUPLICATE KEY UPDATE '.implode(',',$doup);


							$res = $GLOBALS['TYPO3_DB']->sql_query ($query);
							if ($res) {
								// if this record was updated/inserted successfull
								$affected_rows = $GLOBALS['TYPO3_DB']->sql_affected_rows();
								// http://dev.mysql.com/doc/refman/5.0/es/mysql-affected-rows.html
								if (1 == $affected_rows) {
									// inserted
									$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
								} elseif(isset($iufields['uid']) &&  (int)substr($iufields['uid'],1,-1) > 0) {
									// If there is the field "uid" in $iufields with value > 0 we can use that
									// $iufields['uid'] has "'" around it.
									$uid = (int)substr($iufields['uid'],1,-1);
								} else {
									// t3lib_div::debug($iufields,'$iufields');
									// We need to look up the updated
									// record, because there is no possibility to get it
									// so we create an select query like it was updated/inserted
									// before
									$query = 'SELECT uid FROM '.$row['dbtable'].' WHERE ';
									$whereArray = array();
									foreach ($iufields as $field => $value) {
										$whereArray[] = $field.' = '.$value.'';
									}
									$query .= implode(' AND ',$whereArray);
									$res = $GLOBALS['TYPO3_DB']->sql_query ($query);
									$uid = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
									$uid = $uid['uid'];
									// TODO: Debug, if $uid < 1, can happen if mapping is incorrect
								}



						  		$hookObjectsArr = array();
								if (is_array ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['importmanager']['mod1/index.php']['beforeUpdateLate'])) {
										foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['importmanager']['mod1/index.php']['beforeUpdateLate'] as $classRef) {
												$hookObjectsArr[] = &t3lib_div::getUserObj($classRef);
										}
								}
								foreach($hookObjectsArr as $hookObj)	{
									if (method_exists($hookObj, 'beforeUpdateLate')) {
										$updateLate = $hookObj->beforeUpdateLate($this, $updateLate, $uid, $counter, $iufields, $row);
									}
								}


								// t3lib_div::debug($iufields);
								// t3lib_div::debug($updateLate['mm'][$counter]);
								if (is_array($updateLate['mm'][$counter])) {
									foreach ($updateLate['mm'][$counter] as $mmTable => $mmTableArray) {
										foreach ($mmTableArray as $mmTableValues) {
											$mmTableValues[$mmTableValues['insertInto']] = $uid;
											// t3lib_div::debug($mmTable);
											// t3lib_div::debug($mmTableValues);
											unset($mmTableValues['insertInto']);
											unset($mmTableValues['table']);
											// check first, if this table does not exists - do not update an existing mm-relation
											// check if there is that relation in there, then
											// we do not need to update again
											$where = array(); 
											foreach ($mmTableValues as $key => $value) {
												$where[] = ' '.$key.'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($value, $mmTable).' ';
											}
											$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*' ,$mmTable,implode(' AND ',$where),'','',1);
											if (false === $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
												// only insert, if not exists
												$GLOBALS['TYPO3_DB']->exec_INSERTquery($mmTable,$mmTableValues);
											}												
										}
									}
								}
							}
						}

						$content = ('
						<table>
							<tr>
								<td><img src="../mod/res/tx_importmanager_database_table.gif" width="16" height="16" /></td>
								<td>'.$LANG->getLL('UploadStep2InfoTable').'</td>
								<td>'.$row['dbtable'].'</td>
							</tr>
							<tr>
								<td><img src="../mod/res/tx_importmanager_database_key.gif" width="16" height="16" /></td>
								<td>'.$LANG->getLL('UploadStep2InfoKeys').'</td>
								<td>'.((empty($ukeys))?'No keys':implode(', ',$ukeys)).'</td>
							</tr>
							<tr>
								<td><img src="../mod/res/tx_importmanager_database_csv.gif" width="16" height="16" /></td>
								<td>'.$LANG->getLL('UploadStep2InfoFilePath').'</td>
								<td>'.$mapper->file.'</td>
							</tr>
							<tr>
								<td><img src="../mod/res/tx_importmanager_database_add.gif" width="16" height="16" /></td>
								<td>'.$LANG->getLL('UploadStep2InfoInserts').'</td>
								<td>'.$insertContent.'</td>
							</tr>
							<tr>
								<td><img src="../mod/res/tx_importmanager_database_refresh.gif" width="16" height="16" /></td>
								<td>'.$LANG->getLL('UploadStep2InfoUpdates').'</td>
								<td>'.$updateContent.'</td>
							</tr>
						</table>
						');

						$this->content.= '<fieldset>';
						$this->content.= $this->doc->section($LANG->getLL('UploadStep2InfoTitle').' '.$row['dbtitle'], $content, 0, 1);
						$this->content.= '</fieldset><br />';
					}
					$this->content.= $this->doc->divider(5);
					$this->content.= '&nbsp;';
					$this->content.= $this->doc->t3Button('window.history.back();', $LANG->getLL('Back'));

				} else {
					// Wenn keine Dateien hochgeladen wurden ...
					$this->content.= '<p>'.$LANG->getLL('UploadStep2UploadNotSuccessfull').'</p>';
					$content.= $this->doc->divider(5);
					$this->content.= $this->doc->t3Button('window.history.back();', $LANG->getLL('Back'));

				}

			} elseif($piVars['action']=='import') {

				// In work!
				// Not included
				$this->content.= 'Importiert!';

			}

		}


		/**
		 * Menu function 2; Not included yet
		 *
		 * @return void
		 */
		function menuFunction2() {
			$content='<div align=center><strong>Diese Funktion ist noch nicht implementiert.</strong></div>';
			$this->content.=$this->doc->section('Message #2:',$content,0,1);
		}


		/**
		 * Menu function 3; Build the steps for mapping
		 *
		 * @return void
		 */
		function menuFunction3() {

			// Init
			global $GLOBALS,$LANG;
			$_PIVARS_	= t3lib_div::_POST('tx_importmanager');


			$_MAPTABLE_ = '';
			list ($_MAPTABLE_, $uid) = explode(':',$_PIVARS_['mapTable']);

			$_MAP_		= (array) $_PIVARS_['MAP'];


			// Dieser switch soll für die einzelnen Steps des Mapping unterschiedliche
			// Formulare anzeigen.
			if($_MAPTABLE_ && !$_MAP_) {

				// Init Block
				$_CONTENT_ = '';
				$_FIELDS_  = $GLOBALS['TYPO3_DB']->admin_get_fields($_MAPTABLE_);
				$_JS_ = '';
				$_COUNTER_ = 0;
				$_MAPTYPE_ = ('
						<option value="0">'.$LANG->getLL('MappingStep2MapType0').'</option>
						<option value="1" style="background:#FFFF33;">'.$LANG->getLL('MappingStep2MapType1').'</option>
						<option value="2" style="background:#69A550">'.$LANG->getLL('MappingStep2MapType2').'</option>
						<option value="3" style="background:#B8D7F2">'.$LANG->getLL('MappingStep2MapType3').'</option>
						<option value="4" style="background:#B8D7F2">'.$LANG->getLL('MappingStep2MapType4').'</option>
						<option value="5" style="background:#B8D7F2">'.$LANG->getLL('MappingStep2MapType5').'</option>
						<option value="6" style="background:#B8D7F2">'.$LANG->getLL('MappingStep2MapType6').'</option>
				');

				// Get map if avaible
				// Holt alle bereits gemappten Tabellen aus der Datenbank
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_importmanager_mapping','dbtable="'.$GLOBALS['TYPO3_DB']->quoteStr($_MAPTABLE_,'tx_importmanager_mapping').'" AND uid="'.(int)$uid.'" AND hidden=0 AND deleted=0');
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$ser_arr = (!empty($row)) ? unserialize($row['dbmapping']): array();

				// Build Detail Form
				$_CONTENT_.= ('
				<dl>
					<dt><label>'.$LANG->getLL('MappingStep2DatabaseTitel').'</label></dt>
					<dd>
						<input type="text" name="tx_importmanager[MAP]['.$_MAPTABLE_.'][title]" value="'.$row['dbtitle'].'" size="50" />
						<input type="hidden" name="tx_importmanager[mapTable]" value="'.$_MAPTABLE_.'" />
						<input type="hidden" name="tx_importmanager[uid]" value="'.(int)$uid.'" />
					</dd>
					<dt><label>'.$LANG->getLL('MappingStep2DatabaseDescription').'</label></dt>
					<dd><textarea name="tx_importmanager[MAP]['.$_MAPTABLE_.'][description]" rows="6" cols="47">'.$row['dbdescription'].'</textarea></dd>
				</dl>
				');


				// Build Table Header
				// Holt zuerst alle Schlüssel aus dem $_FIELDS_ array
				// um in die 2 Ebene des Assoziativen Arrays zu kommen
				// Das Map Array ist wie folgt aufgebaut:
				// tx_importmanager[MAP][TABLE][FIELD][MAPTYPE/MAPPING] = VALUE
				$_TH_ = array_keys($_FIELDS_);
				$_TH_ = array_keys($_FIELDS_[$_TH_[0]]);
				$this->doc->table_TABLE.= '<tr>';
				foreach ($_TH_ as $_VALUE_) {
					$this->doc->table_TABLE.= '<th>'.$_VALUE_.'</th>';
				}
				$this->doc->table_TABLE.= '<th>MapType</th><th>Mapping</th></tr>';

				# t3lib_div::debug($_FIELDS_);

				foreach ($_FIELDS_ as $_KEY_ => $_VALUE_) {
					$_FIELDS_[$_KEY_]['MapType'] = '<select name="tx_importmanager[MAP]['.$_MAPTABLE_.'][FIELDS]['.$_KEY_.'][MapType]" id="tx_importmanager-select-'.$_COUNTER_.'" onchange="this.parentNode.parentNode.style.background=this.options[this.selectedIndex].style.background;">'.$_MAPTYPE_.'</select>';
				if(!empty($row)) {
						$_JS_ .= 'document.getElementById(\'tx_importmanager-select-'.$_COUNTER_.'\').selectedIndex='.(($ser_arr[$_KEY_]['MapType'])?$ser_arr[$_KEY_]['MapType']:0).';';
						$_JS_ .= 'document.getElementById(\'tx_importmanager-select-'.$_COUNTER_.'\').onchange();';
					}
					$_FIELDS_[$_KEY_]['Mapping'] = '<input type="text" name="tx_importmanager[MAP]['.$_MAPTABLE_.'][FIELDS]['.$_KEY_.'][Mapping]" value="'.htmlspecialchars($ser_arr[$_KEY_]['Mapping']).'" />';
					$_COUNTER_++;
				}


				// Build Table Formular
				$this->doc->tableLayout['defRow']['defCol'][1] = '&nbsp;</td>';
				$_CONTENT_.= $this->doc->table($_FIELDS_);

				// Check if some mapping is still avaible
				if(!empty($row)) {
					// Delete Mapping
					$_CONTENT_.= '<div class="warningbox">'.$this->doc->sectionBegin();
					$_CONTENT_.= $this->doc->sectionHeader($this->doc->icons(2).$LANG->getLL('ExtImportantNotice'), FALSE);
					$_CONTENT_.= '<p>'.$LANG->getLL('MappingStep2DeleteDescription').'</p>';
					$_CONTENT_.= '<input type="checkbox" name="tx_importmanager[delete]" id="tx_importmanager-checkbox-delete" value="1" />&nbsp;<label for="tx_importmanager-checkbox-delete">'.$LANG->getLL('MappingStep2Delete').'</label>';
					$_CONTENT_.= $this->doc->sectionEnd().'</div>';
				}
				// Build Content
				$this->content.= $this->doc->section($LANG->getLL('MappingStep2Title').' '.$_MAPTABLE_,$_CONTENT_,0,1);
				$this->content.= $this->doc->divider(5);
				$this->content.= $this->doc->t3Button('this.form.submit();', $LANG->getLL('MappingStep2Save'));
				$this->content.= '&nbsp;';
				$this->content.= $this->doc->t3Button('window.history.back();', $LANG->getLL('Back'));
				$this->content.= ('<script type="text/javascript">
							<!--
							'.$_JS_.'
							-->
							</script>');


			} elseif($_MAPTABLE_ && $_MAP_) {

				// Init Block
				$_SARR_MAP_ = serialize($_MAP_[$_MAPTABLE_]['FIELDS']);
				$_VALUES_   = array('tstamp' => time(), 'dbtable' => $_MAPTABLE_, 'dbtitle' => $_MAP_[$_MAPTABLE_]['title'], 'dbdescription' => $_MAP_[$_MAPTABLE_]['description'], 'dbmapping' => $_SARR_MAP_);

				// Insert or Update
				// Hier wird erstmal kontrolliert ob es bereits einen Eintrag für diese
				// Tabelle in der Datenbank gibt und danach entschieden ob ein
				// Insert oder update durchgeführt werden soll
				// $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_importmanager_mapping','dbtable="'.$_MAPTABLE_.'" AND hidden=0 AND deleted=0');
				// $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$row['uid'] = (int)$_PIVARS_['uid'];
				if  ($row['uid'] <= 0) { unset($row['uid']); }

				if(empty($row['uid']) && $_PIVARS_['delete']==0) {
				// Insert query

					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_importmanager_mapping',$_VALUES_);
					$this->content.= $LANG->getLL('MappingStep3SaveSuccessful');

				} elseif($_PIVARS_['delete']==0) {
				// Update query

					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_importmanager_mapping','uid='.$row['uid'],$_VALUES_);
					$this->content.= $LANG->getLL('MappingStep3UpdateSuccessful');

				} elseif($_PIVARS_['delete']==1) {
				// Delete query

					$_VALUES_ = array('deleted'=>'1');
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_importmanager_mapping','uid='.$row['uid'],$_VALUES_);
					$this->content.= $LANG->getLL('MappingStep3DeleteSuccessful');

				}

				$this->content.= $this->doc->divider(5);

			} else {

				// Init Block
				// TODO: check if that is more an MySQL 4 / MySQL 5 Issue?!
				/*
				 * Achtung! Die funktion admin_get_tables() liefert seit der T3 4.2.x Version
				 * ein multidimensionales Array zurück!
				 */
				$_TABLES_   = $GLOBALS['TYPO3_DB']->admin_get_tables();
				$_SELECTOR_ = '<select name="tx_importmanager[new_mapTable]" onchange="if(this.value!=\'NULL\'){document.getElementsByName(\'tx_importmanager[mapTable]\')[0].value=this.value;this.form.submit();}"><option value="NULL" selected="selected">&nbsp;</option><optgroup label="'.$LANG->getLL('MappingStep1DBTableWithoutMap').'">%s</optgroup></select>';
				$_mOPTIONS_ = $_sOPTIONS_  = '';
				$_CONTENT_  = '<p>'.$LANG->getLL('MappingStep1Description').'</p>';
				$_MAPS_ 	= array();
				$_ALL_MAPS	= array();

				// Get all maps
				// Holt alle bereits gemappten Tabellen aus der Datenbank
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, dbtable, dbtitle','tx_importmanager_mapping','hidden=0 AND deleted=0');
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$_MAPS_[$row['dbtable']] = true;
					$_ALL_MAPS[$row['dbtable']][] = $row;
				}

				// Typo3 4.2.1 Kompatibel
				if((float)TYPO3_version >= (float)'4.2.0') {

					// Für Typo3 4.2.x
					foreach ($_TABLES_ as $_TABLESKEY_ => $_VALUE_) {
						if($_MAPS_[$_TABLESKEY_]) {
							foreach ($_ALL_MAPS[$_TABLESKEY_] as $row) {
								$_mOPTIONS_.= '<option value="'.$_TABLESKEY_.':'.$row['uid'].'">'.$_TABLESKEY_.' ('.$row['dbtitle'].')</option>';
							}
						}
						$_sOPTIONS_.= '<option value="'.$_TABLESKEY_.'">'.$_TABLESKEY_.'</option>';
					}

				} else {
					t3lib_div::debug('not really tested yet for TYPO3 < 4.2');
					// Für Typo3 4.1.x
					// Content Block
					foreach ($_TABLES_ as $_VALUE_) {
						if($_MAPS_[$_VALUE_]) {
							foreach ($_ALL_MAPS[$_VALUE_] as $row) {
								$_mOPTIONS_.= '<option value="'.$_VALUE_.':'.$row['uid'].'">'.$_VALUE_.'</option>';
							}
						}
						$_sOPTIONS_.= '<option value="'.$_TABLES_[$_VALUE_].'">'.$_VALUE_.'</option>';
					}

				}

				// Build selector for avaible mappings
				$selectorForAvaibleMappings = '<select name="tx_importmanager[edit_mapTable]" size="10" onchange="if(this.value!=\'NULL\'){document.getElementsByName(\'tx_importmanager[mapTable]\')[0].value=this.value;this.form.submit();}"><optgroup label="'.$LANG->getLL('MappingStep1DBTableWithMap').'" style="background:#94C78D;">%m</optgroup></select><br />';
				$selectorForAvaibleMappings = str_replace('%m',$_mOPTIONS_,$selectorForAvaibleMappings);

				// Selector for databases
				$_SELECTOR_ = str_replace('%s',$_sOPTIONS_,$_SELECTOR_);

				$_CONTENT_ .= $selectorForAvaibleMappings;
				$_CONTENT_ .= '<p>'.$LANG->getLL('MappingStep1DescriptionPartII').'</p>';
				$_CONTENT_ .= $_SELECTOR_;

				// Hidden-field to set the mapTable
				$_CONTENT_ .= '<input type="hidden" name="tx_importmanager[mapTable]" value="" />';

				$this->content.= $this->doc->section($LANG->getLL('MappingStep1Title'),$_CONTENT_,0,1);
				$this->content.= $this->doc->divider(5);
				$this->content.= $this->doc->t3Button('this.form.submit();', $LANG->getLL('Next'));
				$this->content.= '&nbsp;';
				$this->content.= $this->doc->t3Button('window.history.back();', $LANG->getLL('Back'));

			}

		}


		/**
		 * Läd die Übergebenen Daten hoch und gibt sie anschließend zurück
		 *
		 * @todo 		Pascal Hinz: Diese Methode muss aufjedenfall nochmal überarbeitet werden!
		 * @return Array mit den Dateinamen der hochgeladenen CSV dateien
		 */
		function CheckUpload() {

			global $FILEMOUNTS,$TYPO3_CONF_VARS,$BE_USER,$LANG;


			// Nur CSV Dateien erlauben
			$c = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['importmanager']);
			$TYPO3_CONF_VARS['BE']['fileExtensions'] = array (
    			'webspace' => array('allow'=>$c['importFormats'], 'deny'=>'*'),
			);


			// Hole alle Upload-Felder und setzte das TARGET
			$file = t3lib_div::_POST('tx_importmanager');


			// Upload Einstellen
			$fileProcessor = t3lib_div::makeInstance('t3lib_extFileFunctions');
			$fileProcessor->init($FILEMOUNTS, $TYPO3_CONF_VARS['BE']['fileExtensions']);
			$fileProcessor->init_actionPerms($BE_USER->user['fileoper_perms']);
			$fileProcessor->dontCheckForUnique = 1;


			// Upload ausführen
			$fileProcessor->start($file);
			$newFile = array();
			for ($i=0;$i<count($file['upload']);$i++) {
				$file['upload'][$i]['target'] = t3lib_div::getFileAbsFileName('fileadmin/_temp_/');
				// Die File nur zurückgeben wenn auch wirklich eine datei hochgeladen wurde!
				$newFile[] = $fileProcessor->func_upload($file['upload'][$i], $i);
			}


			// Resultat zurückgeben
			return $newFile;
		}


		/**
		 * Erzeugt die Upload-Felder für das Formular,
		 * allerdings nur für die Datenbanktabellen für die
		 * ein Mapping vorliegt, ansonsten wird eine Hinweiss-
		 * Meldung ausgegeben das keine Maps vorhanden sind.
		 *
		 * @author			Pascal Hinz <hinz (at) elemente dot ms>
		 * @return	string	Upload-Formulare oder Hinweiss
		 */
		function BuildUploadForms() {

			// Init alle für die Methode benötigten globalen Variabeln
			global $GLOBALS,$LANG,$BE_USER;

			// DB Verbindung herstellen und alle relevanten Daten holen
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,dbtable,dbtitle,dbdescription','tx_importmanager_mapping','hidden=0 AND deleted=0');

			// Nachschauen ob der DB Select ein Resultat zurück gibt
			if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {

				// Init Block; Setzte Zähler und Content
				$i = 0;
				$content = '<dl>';

				// Erzeuge für jedes Resultat ein Upload Feld
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

					// Erzeuge das Upload-Feld
					$content.= ('
						<dt><label for="form-upload-'.$i.'" style="font-weight:bold;font-size:11px;">'.$row['dbtitle'].':</label>&nbsp;<span style="color:#ABABAB;font-size:9px;">(= '.$row['dbtable'].')</span></dt>
						<dd style="margin-bottom: 5px;">
							<p>'.$row['dbdescription'].'</p>
							<input type="file" size="30" name="upload_'.$i.'" id="form-upload-'.$i.'" />&nbsp;<a href="class.tx_importmanager_csvtemplate.php?&uid='.$row['uid'].'" target="self"><img src="../mod/res/tx_importmanager_icon_download_csv.gif" alt="'.$LANG->getLL('UploadStep1CSVTemplateLink').'" title="'.$LANG->getLL('UploadStep1CSVTemplateLink').'" width="16" height="16" /></a>
							<input type="hidden" name="tx_importmanager[upload]['.$i.'][data]" value="'.$i.'" />
							<input type="hidden" name="tx_importmanager[upload]['.$i.'][uid]" value="'.$row['uid'].'" />
						</dd>
					');

					// Setzte Zähler ein höher
					$i++;

				}
				$content.= '</dl>';
				$content.= $this->doc->divider(5);
				$content.= $this->doc->t3Button('this.form.submit();', $LANG->getLL('UploadStep1Upload'));

				// Gebe den Content zurück
				return $content;

			} else {

				// Gebe Hinweiss Meldung zurück
				$content.= '<div class="warningbox">'.$this->doc->sectionBegin();
				$content.= $this->doc->sectionHeader($this->doc->icons(2).$LANG->getLL('ExtImportantNotice'), FALSE);
				$content.= '<p>'.$LANG->getLL('UploadStep1NoMappingsAvaible').'</p>';
				if(!$BE_USER->user['admin']) $content.= '<p class="noadmin">'.$LANG->getLL('UploadStep1NoMappingsAvaibleAndNoAdmin').'</p>';
				$content.= $this->doc->sectionEnd().'</div>';
				return $content;

			}

		}


		/**
		 * Erzeugt ein hidden Feld Formular in dem die Action des
		 * abgesendeten Formulars für die Extension definiert wird.
		 *
		 * @author Pascal Hinz <hinz (at) elemente dot ms>
		 * @param string $action
		 * @return string hidden Formular Feld
		 */
		function SetFormAction( $action ) {
			return '<input type="hidden" name="tx_importmanager[action]" value="'.$action.'" />';
		}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/importmanager/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/importmanager/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_importmanager_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>