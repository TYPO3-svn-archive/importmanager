<?php

########################################################################
# Extension Manager/Repository config file for ext: "importmanager"
#
# Auto generated 16-01-2009 11:47
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Import Manager',
	'description' => 'A tool to manage the CSV import/export files',
	'category' => 'module',
	'author' => 'Pascal Hinz',
	'author_email' => 'hinz@elemente.ms',
	'shy' => '',
	'dependencies' => 'rs_userimp',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod,mod1,mod2',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'http://www.elemente.ms',
	'version' => '0.1.6',
	'constraints' => array(
		'depends' => array(
			'rs_userimp' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:34:{s:9:"ChangeLog";s:4:"96c8";s:21:"ext_conf_template.txt";s:4:"615f";s:12:"ext_icon.gif";s:4:"e77c";s:14:"ext_tables.php";s:4:"4fc7";s:14:"ext_tables.sql";s:4:"ea2d";s:16:"locallang_db.xml";s:4:"8e53";s:7:"tca.php";s:4:"358f";s:14:"doc/manual.sxw";s:4:"dd97";s:13:"mod/clear.gif";s:4:"cc11";s:12:"mod/conf.php";s:4:"8ea4";s:21:"mod/locallang_mod.xml";s:4:"cad7";s:18:"mod/moduleicon.gif";s:4:"7663";s:41:"mod/res/tx_importmanager_database_add.gif";s:4:"7b17";s:41:"mod/res/tx_importmanager_database_csv.gif";s:4:"f289";s:41:"mod/res/tx_importmanager_database_key.gif";s:4:"6a25";s:45:"mod/res/tx_importmanager_database_refresh.gif";s:4:"7663";s:43:"mod/res/tx_importmanager_database_table.gif";s:4:"193a";s:46:"mod/res/tx_importmanager_icon_download_csv.gif";s:4:"fa87";s:37:"mod/res/tx_importmanager_icon_key.gif";s:4:"6252";s:40:"mod/res/tx_importmanager_icon_unique.gif";s:4:"195b";s:32:"mod/res/tx_importmanager_mod.css";s:4:"cd26";s:43:"mod1/class.tx_importmanager_csvtemplate.php";s:4:"4724";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"32f8";s:14:"mod1/index.php";s:4:"adf0";s:18:"mod1/locallang.xml";s:4:"eba7";s:22:"mod1/locallang_mod.xml";s:4:"fe19";s:19:"mod1/moduleicon.gif";s:4:"1d24";s:14:"mod2/clear.gif";s:4:"cc11";s:13:"mod2/conf.php";s:4:"a0f5";s:14:"mod2/index.php";s:4:"a76c";s:18:"mod2/locallang.xml";s:4:"0dfb";s:22:"mod2/locallang_mod.xml";s:4:"2444";s:19:"mod2/moduleicon.gif";s:4:"6a0a";}',
	'suggests' => array(
	),
);

?>