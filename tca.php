<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA["tx_importmanager_mapping"] = array (
    "ctrl" => $TCA["tx_importmanager_mapping"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,dbtable,dbtitle,dbdescription,dbmapping"
    ),
    "feInterface" => $TCA["tx_importmanager_mapping"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        "dbtable" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:importmanager/locallang_db.xml:tx_importmanager_mapping.dbtable",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required,trim",
            )
        ),
        "dbtitle" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:importmanager/locallang_db.xml:tx_importmanager_mapping.dbtitle",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required,trim",
            )
        ),
        "dbdescription" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:importmanager/locallang_db.xml:tx_importmanager_mapping.dbdescription",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "dbmapping" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:importmanager/locallang_db.xml:tx_importmanager_mapping.dbmapping",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, dbtable, dbtitle, dbdescription, dbmapping")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);
?>