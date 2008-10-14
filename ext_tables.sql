#
# Table structure for table 'tx_importmanager_mapping'
#
CREATE TABLE tx_importmanager_mapping (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    dbtable varchar(255) DEFAULT '' NOT NULL,
    dbtitle varchar(255) DEFAULT '' NOT NULL,
    dbdescription text NOT NULL,
    dbmapping text NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);