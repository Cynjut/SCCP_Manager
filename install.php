<?php

global $db;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

if (!$db->getAll('SHOW COLUMNS FROM sccpdevmodel WHERE FIELD = "loadimage"')) {

    $sql = "CREATE TABLE IF NOT EXISTS `sccpdevmodel` (
    `model` varchar(20) NOT NULL DEFAULT '',
    `vendor` varchar(40) DEFAULT '',
    `dns` int(2) DEFAULT '1',
    `buttons` int(2) DEFAULT '0',
    `loadimage` varchar(40) DEFAULT '',
    PRIMARY KEY (`model`),
    KEY `model` (`model`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
    
    $check = $db->query($sql);
    if(db::IsError($check)) {
	die_freepbx("Can not create sccpdevmodel table\n");
    }
}

if (!$db->getAll('SHOW COLUMNS FROM sccpdevmodel WHERE FIELD = "loadinformationid"')) {
    $sql = "ALTER TABLE 'sccpdevmodel' ADD loadinformationid varchar(30);";
    $check = $db->query($sql);
    if(DB::IsError($check)) {
	die_freepbx("Can not add loadinformationid into sccpdevmodel table\n");
    }
}

$sql = "REPLACE INTO `sccpdevmodel` VALUES ('7925','CISCO',1,1,'',''),('7902','CISCO',1,1,'CP7902080002SCCP060817A','loadInformation30008'),('7905','CISCO',1,1,'CP7905080003SCCP070409A','loadInformation20000'),('7906','CISCO',1,1,'SCCP11.8-3-1S','loadInformation369'),('7910','CISCO',1,1,'P00405000700','loadInformation6'),('7911','CISCO',1,1,'SCCP11.8-3-1S','loadInformation307'),('7912','CISCO',1,1,'CP7912080003SCCP070409A','loadInformation30007'),('7914','CISCO',0,14,'S00105000300','loadInformation124'),('7920','CISCO',1,1,'cmterm_7920.4.0-03-02','loadInformation30002'),('7921','CISCO',1,1,'CP7921G-1.0.3','loadInformation365'),('7931','CISCO',1,1,'SCCP31.8-3-1S','loadInformation348'),('7936','CISCO',1,1,'cmterm_7936.3-3-13-0','loadInformation30019'),('7937','CISCO',1,1,'','loadInformation431'),('7940','CISCO',1,2,'P00308000500','loadInformation8'),('Digital Access+','CISCO',1,1,'D00303010033','loadInformation42'),('7941','CISCO',1,2,'P00308000500','loadInformation115'),('7941G-GE','CISCO',1,2,'P00308000500','loadInformation309'),('7942','CISCO',1,2,'P00308000500','loadInformation434'),('Digital Access','CISCO',1,1,'D001M022','loadInformation40'),('7945','CISCO',1,2,'P00308000500','loadInformation435'),('7960','CISCO',3,6,'P00308000500','loadInformation7'),('7961','CISCO',3,6,'P00308000500','loadInformation30018'),('7961G-GE','CISCO',3,6,'P00308000500','loadInformation308'),('7962','CISCO',3,6,'P00308000500','loadInformation404'),('7965','CISCO',3,6,'P00308000500','loadInformation436'),('7970','CISCO',3,8,'SCCP70.8-3-1S','loadInformation30006'),('7971','CISCO',3,8,'SCCP70.8-3-1S','loadInformation119'),('7975','CISCO',3,8,'SCCP70.8-3-1S','loadInformation437'),('7985','CISCO',3,8,'cmterm_7985.4-1-4-0','loadInformation302'),('ATA 186','CISCO',1,1,'ATA030203SCCP051201A','loadInformation12'),('IP Communicator','CISCO',1,1,'','loadInformation30016'),('12 SP','CISCO',1,1,'','loadInformation3'),('12 SP+','CISCO',1,1,'','loadInformation2'),('30 SP+','CISCO',1,1,'','loadInformation1'),('30 VIP','CISCO',1,1,'','loadInformation5'),('7914,7914','CISCO',0,28,'S00105000300','loadInformation124'),('7915','CISCO',0,14,'',''),('7916','CISCO',0,14,'',''),('7915,7915','CISCO',0,28,'',''),('7916,7916','CISCO',0,28,'',''),('CN622','MOTOROLA',1,1,'','loadInformation335'),('ICC','NOKIA',1,1,'',''),('E-Series','NOKIA',1,1,'',''),('3911','CISCO',1,1,'','loadInformation446'),('3951','CISCO',1,1,'','loadInformation412');";
$check = $db->query($sql);
if(DB::IsError($check)) {
    die_freepbx("Can not REPLACE defaults into sccpdevmodel table\n");
}

if (!$db->getAll('SHOW COLUMNS FROM sccpline WHERE FIELD = "id"')) {
    $sql = "CREATE TABLE IF NOT EXISTS `sccpline` (
	`id` varchar(45) default NULL,
	`pin` varchar(45) default NULL,
	`label` varchar(45) default NULL,
	`description` varchar(45) default NULL,
	`context` varchar(45) default NULL,
	`incominglimit` varchar(45) default NULL,
	`transfer` varchar(45) default NULL,
	`mailbox` varchar(45) default NULL,
	`vmnum` varchar(45) default NULL,
	`cid_name` varchar(45) default NULL,
	`cid_num` varchar(45) default NULL,
	`trnsfvm` varchar(45) default NULL,
	`secondary_dialtone_digits` varchar(45) default NULL,
	`secondary_dialtone_tone` varchar(45) default NULL,
	`musicclass` varchar(45) default NULL,
	`language` varchar(45) default NULL,
	`accountcode` varchar(45) default NULL,
	`echocancel` varchar(45) default NULL,
	`silencesuppression` varchar(45) default NULL,
	`callgroup` varchar(45) default NULL,
	`pickupgroup` varchar(45) default NULL,
	`amaflags` varchar(45) default NULL,
	`dnd` varchar(5) default 'on',
	`setvar` varchar(50) default NULL,
	`name` varchar(45) NOT NULL default '',
	PRIMARY KEY  (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    $check = $db->query($sql);
    if(DB::IsError($check)) {
	die_freepbx("Can not add sccpline table\n");
    }

    $sql = "ALTER TABLE sccpline
	 ALTER COLUMN incominglimit SET DEFAULT '2',
	 ALTER COLUMN transfer SET DEFAULT 'on',
	 ALTER COLUMN vmnum SET DEFAULT '*97',
	 ALTER COLUMN musicclass SET DEFAULT 'default',
	 ALTER COLUMN echocancel SET DEFAULT 'on',
	 ALTER COLUMN silencesuppression SET DEFAULT 'off',
	 ALTER COLUMN dnd SET DEFAULT 'off'
    " ;
    
    $check = $db->query($sql);
    if(DB::IsError($check)) {
	die_freepbx("Can not modify sccpline table\n");
    }
}

if (!$db->getAll('SHOW COLUMNS FROM sccpdevice WHERE FIELD = "type"')) {
    $sql = "CREATE TABLE IF NOT EXISTS `sccpdevice` (
	`type` varchar(45) default NULL,
	`addon` varchar(45) default NULL,
	`description` varchar(45) default NULL,
	`tzoffset` varchar(5) default NULL,
	`transfer` varchar(5) default 'on',
	`cfwdall` varchar(5) default 'on',
	`cfwdbusy` varchar(5) default 'on',
	`dtmfmode` varchar(10) default NULL,
	`imageversion` varchar(45) default NULL,
	`deny` varchar(45) default NULL,
	`permit` varchar(45) default NULL,
	`dndFeature` varchar(5) default 'on',
	`directrtp` varchar(3) default 'off',
	`earlyrtp` varchar(8) default 'off',
	`mwilamp` varchar(5) default 'on',
	`mwioncall` varchar(5) default 'off',
	`pickupexten` varchar(5) default 'on',
	`pickupcontext` varchar(100) default '',
	`pickupmodeanswer` varchar(5) default 'on',
	`private` varchar(5) default 'off',
	`privacy` varchar(100) default 'full',
	`nat` varchar(15) default 'off',
	`softkeyset` varchar(100) default '',
	`audio_tos` varchar(11) default NULL,
	`audio_cos` varchar(1) default NULL,
	`video_tos` varchar(11) default NULL,
	`video_cos` varchar(1) default NULL,
	`conf_allow` varchar(3) default 'on',
	`conf_play_general_announce` varchar(3) default 'on',
	`conf_play_part_announce` varchar(3) default 'on',
	`conf_mute_on_entry` varchar(3) default 'off',
	`conf_music_on_hold_class` varchar(80) default 'default',
	`setvar` varchar(100) default NULL,
	`disallow` varchar(255) DEFAULT NULL,
	`allow` varchar(255) DEFAULT NULL,
	`backgroundImage` varchar(255) DEFAULT NULL,
	`ringtone` varchar(255) DEFAULT NULL,
	`name` varchar(15) NOT NULL default '',
	PRIMARY KEY  (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    
    $check = $db->query($sql);
    if(DB::IsError($check)) {
	  die_freepbx("Can not add sccpdevice table\n");
    }
    
    $sql = "ALTER TABLE sccpdevice
    
	ALTER COLUMN transfer SET DEFAULT 'on',
	ALTER COLUMN cfwdall SET DEFAULT 'on',
	ALTER COLUMN cfwdbusy SET DEFAULT 'on',
	ALTER COLUMN dtmfmode SET DEFAULT 'outofband',
	ALTER COLUMN dndFeature SET DEFAULT 'on',
	ALTER COLUMN directrtp SET DEFAULT 'off',
	ALTER COLUMN earlyrtp SET DEFAULT 'progress',
	ALTER COLUMN mwilamp SET DEFAULT 'on',
	ALTER COLUMN mwioncall SET DEFAULT 'on',
	ALTER COLUMN pickupexten SET DEFAULT 'on',
	ALTER COLUMN pickupmodeanswer SET DEFAULT 'on',
	ALTER COLUMN private SET DEFAULT 'on',
	ALTER COLUMN privacy SET DEFAULT 'off',
	ALTER COLUMN nat SET DEFAULT 'off',
	ALTER COLUMN softkeyset SET DEFAULT 'softkeyset'
    " ;

    $check = $db->query($sql);
    if(DB::IsError($check)) {
	die_freepbx("Can not modify sccpdevice table\n");
    }
}

if (!$db->getAll('SHOW COLUMNS FROM buttonconfig WHERE FIELD = "device"')) {
    $sql = "CREATE TABLE IF NOT EXISTS `buttonconfig` (
	`device` varchar(15) NOT NULL default '',
	`instance` tinyint(4) NOT NULL default '0',
	`type` enum('line','speeddial','service','feature','empty') NOT NULL default 'empty',
	`name` varchar(36) default NULL,
	`options` varchar(100) default NULL,
	PRIMARY KEY  (`device`,`instance`),
	KEY `device` (`device`),

	FOREIGN KEY (device) REFERENCES sccpdevice(name) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

    $check = $db->query($sql);
    if(DB::IsError($check)) {
	die_freepbx("Can not add buttonconfig table\n");
    }
}

if (!$db->getAll('SHOW COLUMNS FROM sccpsettings WHERE FIELD = "keyword"')) {
    $sql = "CREATE TABLE IF NOT EXISTS `sccpsettings` (
	`keyword` varchar(50) NOT NULL default '',
	`data` varchar(255) NOT NULL default '',
	`seq` tinyint(1) NOT NULL default '0',
	`type` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`keyword`,`seq`,`type`),
	KEY `keyword` (`keyword`)
    
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    
    $check = $db->query($sql);
    if(DB::IsError($check)) {
	die_freepbx("Can not add sccpsettings table\n");
    }
}

$sql = "CREATE OR REPLACE
    ALGORITHM = MERGE
    VIEW sccpdeviceconfig AS
    SELECT GROUP_CONCAT( CONCAT_WS( ',', buttonconfig.type, 
	buttonconfig.name, buttonconfig.options )
    ORDER BY instance ASC
    SEPARATOR ';' ) AS button, sccpdevice.*
    FROM sccpdevice
    LEFT JOIN buttonconfig ON ( buttonconfig.device = sccpdevice.name )
    GROUP BY sccpdevice.name; ";

$check = $db->query($sql);
if(DB::IsError($check)) {
    die_freepbx("Can not create sccpdeviceconfig view\n");
}

