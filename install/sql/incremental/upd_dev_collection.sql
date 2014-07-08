ALTER TABLE `mail_user`
	CHANGE `uid` `uid` int(11) NOT NULL DEFAULT '5000',
	CHANGE `gid` `gid` int(11) NOT NULL DEFAULT '5000';

ALTER TABLE `client_template` ADD `default_mailserver` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `client_template` ADD `default_webserver` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `client_template` ADD `default_dnsserver` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `client_template` ADD `default_slave_dnsserver` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `client_template` ADD `default_dbserver` INT(11) NOT NULL DEFAULT 1;