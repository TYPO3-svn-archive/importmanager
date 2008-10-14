<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_importmanager_mapping"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:importmanager/locallang_db.xml:tx_importmanager_mapping',        
        'label'     => 'uid',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",    
        'delete' => 'deleted',    
        'enablecolumns' => array (        
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_importmanager_mapping.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "hidden, dbtable, dbtitle, dbdescription, dbmapping",
    )
);

if (TYPO3_MODE == 'BE')	{
	t3lib_extMgm::addModule('tximportmanager','','',t3lib_extMgm::extPath($_EXTKEY).'mod/');
	t3lib_extMgm::addModule('tximportmanager','tximportmanagerM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
	// t3lib_extMgm::addModule('tximportmanager','tximportmanagerM2','',t3lib_extMgm::extPath($_EXTKEY).'mod2/');
}
?>