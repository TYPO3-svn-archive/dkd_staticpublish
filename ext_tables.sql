#
# $Id: ext_tables.sql,v 1.4 2005/03/15 13:51:25 dkd-kahler Exp $
#
	
#
# Table structure for table 'tx_dkdstaticpublish_urls'
#
CREATE TABLE tx_dkdstaticpublish_urls (
    uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
    pid int(11) unsigned DEFAULT '0' NOT NULL,
    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

    pub_id tinytext NOT NULL,
    title tinytext NOT NULL,
    url tinytext NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);