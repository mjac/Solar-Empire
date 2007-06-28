DROP TABLE IF EXISTS [server]account;
DROP TABLE IF EXISTS [server]forum;
DROP TABLE IF EXISTS [server]game;
DROP TABLE IF EXISTS [server]history;
DROP TABLE IF EXISTS [server]poption;
DROP TABLE IF EXISTS [server]starname;
DROP TABLE IF EXISTS [server]tip;

CREATE TABLE [server]account (
  acc_id int unsigned NOT NULL auto_increment,
  acc_handle varchar(32) NOT NULL default '',
  acc_password binary(32) NOT NULL default 0,
  acc_email varchar(64) NOT NULL default '',
  acc_created datetime NOT NULL default 0,
  acc_accessed datetime NOT NULL default 0,
  acc_accesses int unsigned NOT NULL default 0,
  acc_requests int unsigned NOT NULL default 0,
  acc_ip int unsigned NOT NULL default 0,
  acc_games smallint unsigned NOT NULL default 0,
  acc_name varchar(64) NOT NULL default '',
  PRIMARY KEY (acc_id),
  UNIQUE KEY acc_handle (acc_handle)
) TYPE=MyISAM;

CREATE TABLE [server]forum (
  game_id tinyint unsigned NULL default NULL,
  msg_id int unsigned NOT NULL default 0,
  msg_sent datetime NOT NULL default 0,
  msg_sender_id varchar(32) NOT NULL default '',
  msg_sender_name varchar(32) NOT NULL default '',
  msg_content text NOT NULL,
  PRIMARY KEY (msg_id)
) TYPE=MyISAM;

CREATE TABLE [server]game (
  game_id varchar(16) NOT NULL default '',
  game_name varchar(32) NOT NULL default '',
  game_admin int unsigned NOT NULL default 1,
  game_status ENUM('hidden', 'paused', 'active', 'complete') NOT NULL default 'paused',
  game_shortdesc tinytext NOT NULL,
  game_desc text NOT NULL,
  game_intro text NOT NULL,
  game_start datetime NOT NULL default 0,
  PRIMARY KEY (game_id)
) TYPE=MyISAM;

CREATE TABLE [server]history (
  acc_id int unsigned NOT NULL,
  game_id tinyint unsigned NULL default NULL,
  hist_date datetime NOT NULL default 0,
  hist_action text NOT NULL,
  hist_ip varchar(16) NOT NULL default ''
) TYPE=MyISAM;

CREATE TABLE [server]poption (
  opt_name varchar(32) NOT NULL default '',
  opt_min int NOT NULL default 0,
  opt_max int NOT NULL default 0,
  opt_desc text NOT NULL,
  opt_type tinyint unsigned NOT NULL default 1,
  PRIMARY KEY  (opt_name)
) TYPE=MyISAM;

CREATE TABLE [server]starname (
  star_name varchar(32) NOT NULL
) TYPE=MyISAM;

CREATE TABLE [server]tip (
  tip_id smallint unsigned NOT NULL,
  tip_content text NOT NULL,
  PRIMARY KEY (tip_id)
) TYPE=MyISAM;
