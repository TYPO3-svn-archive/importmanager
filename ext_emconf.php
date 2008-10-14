<?php

########################################################################
# Extension Manager/Repository config file for ext: "importmanager"
#
# Auto generated 01-09-2008 16:14
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
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'rs_userimp' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:31:{s:9:"ChangeLog";s:4:"5bc8";s:10:"README.txt";s:4:"ee2d";s:21:"ext_conf_template.txt";s:4:"3c8b";s:12:"ext_icon.gif";s:4:"e77c";s:14:"ext_tables.php";s:4:"1445";s:14:"ext_tables.sql";s:4:"d220";s:33:"icon_tx_importmanager_fsklassen.gif";s:4:"1e83";s:38:"icon_tx_importmanager_fuehrerscheine.gif";s:4:"b929";s:32:"icon_tx_importmanager_funktion.gif";s:4:"cb9c";s:44:"icon_tx_importmanager_unternehmenshistorie.gif";s:4:"7b17";s:16:"locallang_db.xml";s:4:"74a7";s:7:"tca.php";s:4:"2461";s:19:"doc/wizard_form.dat";s:4:"54bf";s:20:"doc/wizard_form.html";s:4:"4b19";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"7bcb";s:14:"mod1/index.php";s:4:"c280";s:18:"mod1/locallang.xml";s:4:"8339";s:22:"mod1/locallang_mod.xml";s:4:"209f";s:19:"mod1/moduleicon.gif";s:4:"1d24";s:14:"mod2/clear.gif";s:4:"cc11";s:13:"mod2/conf.php";s:4:"5658";s:14:"mod2/index.php";s:4:"e2e9";s:18:"mod2/locallang.xml";s:4:"3221";s:22:"mod2/locallang_mod.xml";s:4:"e01a";s:19:"mod2/moduleicon.gif";s:4:"6a0a";s:13:"mod/clear.gif";s:4:"cc11";s:12:"mod/conf.php";s:4:"d925";s:21:"mod/locallang_mod.xml";s:4:"e9a0";s:18:"mod/moduleicon.gif";s:4:"7663";s:30:"mod/res/tx_importmanager_mod.css";s:4:"e8ea";}',
	'suggests' => array(
	),
);

?>