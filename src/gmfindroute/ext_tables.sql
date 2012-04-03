#
# Table structure for table 'tx_gmfindroute_locations'
#
CREATE TABLE tx_gmfindroute_locations (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	locationname tinytext,
	targetlat tinytext,
	targetlng tinytext,
	stationname tinytext,
	imgmarker text,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);