DROP TABLE IF EXISTS [server]account;

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


DROP TABLE IF EXISTS [server]forum;

CREATE TABLE [server]forum (
  game_id tinyint unsigned NULL default NULL,
  msg_id int unsigned NOT NULL default 0,
  msg_sent datetime NOT NULL default 0,
  msg_sender_id varchar(32) NOT NULL default '',
  msg_sender_name varchar(32) NOT NULL default '',
  msg_content text NOT NULL default '',
  PRIMARY KEY (msg_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [server]history;

CREATE TABLE [server]history (
  acc_id int unsigned NOT NULL,
  game_id tinyint unsigned NULL default NULL,
  hist_date datetime NOT NULL default 0,
  hist_action text NOT NULL default '',
  hist_ip varchar(16) NOT NULL default ''
) TYPE=MyISAM;


DROP TABLE IF EXISTS [server]poption;

CREATE TABLE [server]poption (
  opt_name varchar(32) NOT NULL default '',
  opt_min int NOT NULL default 0,
  opt_max int NOT NULL default 0,
  opt_desc text NOT NULL default '',
  opt_type tinyint unsigned NOT NULL default 1,
  PRIMARY KEY  (opt_name)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [server]tip;

CREATE TABLE [server]tip (
  tip_id smallint unsigned NOT NULL,
  tip_content text NOT NULL default '',
  PRIMARY KEY (tip_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]auction;

CREATE TABLE [game]auction (
  game_id tinyint unsigned NOT NULL default 1,
  lot_id int unsigned NOT NULL,
  lot_bidder int unsigned NULL default NULL,
  lot_name varchar(32) NOT NULL default '',
  lot_price int unsigned NOT NULL default 0,
  lot_code varchar(32) NOT NULL default '',
  lot_type int unsigned NOT NULL default 0,
  lot_date datetime NOT NULL default 0,
  lot_desc text NOT NULL default '',
  PRIMARY KEY (game_id, lot_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]clan;

CREATE TABLE [game]clan (
  game_id tinyint unsigned NOT NULL default 1,
  clan_id smallint unsigned NOT NULL,
  clan_name varchar(32) NOT NULL default '',
  clan_leader int unsigned NOT NULL default 0,
  clan_symbol varchar(3) NOT NULL default '',
  clan_colour mediumint unsigned NOT NULL default 0,
  clan_score int NOT NULL default 0,
  PRIMARY KEY (game_id, clan_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]claninvite;

CREATE TABLE [game]claninvite (
  game_id tinyint unsigned NOT NULL default 1,
  clan_id smallint unsigned NOT NULL,
  acc_id int unsigned NOT NULL,
  cinv_sent datetime NOT NULL default 0
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]list;

CREATE TABLE [game]list (
  game_id tinyint unsigned NOT NULL default 1,
  game_name varchar(32) NOT NULL default '',
  game_admin int unsigned NOT NULL default 1,
  game_status ENUM('hidden', 'paused', 'active', 'complete') NOT NULL default 'paused',
  game_summary tinytext NOT NULL,
  game_desc text NOT NULL,
  game_intro text NOT NULL,
  game_start datetime NOT NULL default 0,
  PRIMARY KEY (game_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]msgclan;

CREATE TABLE [game]msgclan (
  game_id tinyint unsigned NOT NULL default 1,
  clan_id int NOT NULL,
  msg_id int unsigned NOT NULL,
  msg_sender int unsigned NULL default NULL,
  msg_sendername varchar(32) NULL default NULL,
  msg_sent datetime NOT NULL default 0,
  msg_content text NOT NULL default '',
  PRIMARY KEY (game_id, msg_id),
  KEY clan_id (game_id, clan_id),
  KEY msg_sent (game_id, msg_sent)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]msgforum;

CREATE TABLE [game]msgforum (
  game_id tinyint unsigned NOT NULL default 1,
  msg_id int unsigned NOT NULL,
  msg_sender int unsigned NULL default NULL,
  msg_sendername varchar(32) NULL default NULL,
  msg_sent datetime NOT NULL default 0,
  msg_content text NOT NULL default '',
  PRIMARY KEY (game_id, msg_id),
  KEY msg_sent (game_id, msg_sent)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]msgplayer;

CREATE TABLE [game]msgplayer (
  game_id tinyint unsigned NOT NULL default 1,
  msg_id int unsigned NOT NULL,
  acc_id int unsigned NOT NULL,
  msg_sender int unsigned NULL default NULL,
  msg_sendername varchar(32) NULL default NULL,
  msg_sent datetime NOT NULL default 0,
  msg_content text NOT NULL default '',
  PRIMARY KEY (game_id, msg_id),
  KEY acc_id (game_id, acc_id),
  KEY msg_sent (game_id, msg_sent)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]news;

CREATE TABLE [game]news (
  game_id tinyint unsigned NOT NULL default 1,
  news_id int unsigned NOT NULL,
  acc_id int unsigned NULL default NULL,
  news_sent datetime NOT NULL default 0,
  news_title varchar(255) NOT NULL,
  news_content text NOT NULL default '',
  PRIMARY KEY  (game_id, news_id),
  KEY news_sent (game_id, news_sent)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]option;

CREATE TABLE [game]option (
  game_id tinyint unsigned NOT NULL default 1,
  optn_id tinyint unsigned NOT NULL default 1,
  optn_value int NOT NULL default 0,
  PRIMARY KEY (game_id, optn_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]optionlist;

CREATE TABLE [game]optionlist (
  optn_id tinyint unsigned NOT NULL default 1,
  optn_name varchar(32) NOT NULL default '',
  optn_default int NOT NULL default 0,
  optn_min int NOT NULL default 1,
  optn_max int NOT NULL default 1,
  optn_desc text NOT NULL default '',
  PRIMARY KEY (optn_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]planet;

CREATE TABLE [game]planet (
  game_id tinyint unsigned NOT NULL default 1,
  star_id smallint unsigned NOT NULL,
  plnt_id int unsigned NOT NULL,
  acc_id int unsigned default NULL,
  plnt_name varchar(32) NOT NULL default '',
  plnt_type tinyint unsigned NOT NULL default 0,
  plnt_colonist int unsigned NOT NULL default 1000,
  plnt_taxrate tinyint unsigned NOT NULL default 5,
  plnt_report tinyint unsigned NOT NULL default 1,
  PRIMARY KEY (game_id, plnt_id),
  KEY star_id (game_id, star_id),
  KEY acc_id (game_id, acc_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]planet;

CREATE TABLE [game]planet (
  game_id tinyint unsigned NOT NULL default 1,
  star_id smallint unsigned NOT NULL default 0,
  port_id smallint unsigned NOT NULL,
  PRIMARY KEY (game_id, port_id),
  KEY star_id (game_id, star_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]player;

CREATE TABLE [game]player (
  game_id tinyint unsigned NOT NULL default 1,
  acc_id int unsigned NOT NULL,
  clan_id smallint unsigned default NULL,
  plyr_name varchar(32) NOT NULL default '',
  plyr_joined int unsigned NOT NULL default 0,
  plyr_accesses int unsigned NOT NULL default 0,
  plyr_turns int unsigned NOT NULL default 40,
  plyr_turnsused int unsigned NOT NULL default 0,
  plyr_ship int unsigned default NULL,
  plyr_credit bigint unsigned NOT NULL default 10000,
  plyr_show_user_ships tinyint unsigned NOT NULL default 1,
  plyr_show_enemy_ships tinyint unsigned NOT NULL default 0,
  plyr_bounty int unsigned NOT NULL default 0,
  plyr_genesis tinyint unsigned NOT NULL default 0,
  plyr_alpha tinyint unsigned NOT NULL default 0,
  plyr_gamma tinyint unsigned NOT NULL default 0,
  plyr_delta tinyint unsigned NOT NULL default 0,
  plyr_signature varchar(128) NOT NULL default '',
  plyr_accessed int unsigned NOT NULL default 0,
  plyr_accessforum int unsigned NOT NULL default 0,
  plyr_accessclanforum int unsigned NOT NULL default 0,
  plyr_accessadminforum int unsigned NOT NULL default 0,
  plyr_score int NOT NULL default 0,
  popt_news_back smallint unsigned NOT NULL default 150,
  popt_forum_back smallint unsigned NOT NULL default 30,
  popt_show_sigs tinyint unsigned NOT NULL default 1,
  popt_show_clan_ships tinyint unsigned NOT NULL default 1,
  popt_show_abbr_ship_class tinyint unsigned NOT NULL default 1,
  popt_show_rel_sym tinyint unsigned NOT NULL default 1,
  popt_attack_report tinyint unsigned NOT NULL default 1,
  popt_cursing_filter tinyint unsigned NOT NULL default 1,
  popt_planet_report tinyint unsigned NOT NULL default 1,
  PRIMARY KEY (game_id, acc_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]ship;

CREATE TABLE [game]ship (
  game_id tinyint unsigned NOT NULL default 1,
  star_id smallint unsigned NOT NULL default 1,
  ship_id int unsigned NOT NULL,
  stype_id smallint unsigned NOT NULL default 0,
  acc_id int unsigned NOT NULL default 0,
  ship_name varchar(32) NOT NULL default '',
  ship_towedby int unsigned default NULL,
  ship_task enum('none','mine','defend','defend-fleet','defend-planet','patrol') NOT NULL default 'none',
  ship_fighters int unsigned NOT NULL default 0,
  ship_max_fighters int unsigned NOT NULL default 0,
  ship_space int unsigned NOT NULL default 0,
  ship_volume int unsigned NOT NULL default 0,
  ship_metal smallint unsigned NOT NULL default 0,
  ship_fuel smallint unsigned NOT NULL default 0,
  ship_electronic smallint unsigned NOT NULL default 0,
  ship_organic smallint unsigned NOT NULL default 0,
  ship_colonist smallint unsigned NOT NULL default 0,
  ship_point_value smallint unsigned NOT NULL default 0,
  ship_points_killed int unsigned NOT NULL default 0,
  ship_auxiliary smallint unsigned default NULL,
  PRIMARY KEY  (game_id, ship_id),
  KEY star_id (game_id, star_id),
  KEY ship_towedby (game_id, ship_towedby),
  KEY acc_id (game_id, acc_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]shiptype;

CREATE TABLE [game]shiptype (
  stype_id smallint unsigned NOT NULL,
  stype_name varchar(32) NOT NULL default '',
  stype_abbr varchar(8) NOT NULL default '',
  stype_desc text NOT NULL default '',
  stype_credit int unsigned NOT NULL default 0,
  stype_fighter int unsigned NOT NULL default 0,
  stype_fightermax int unsigned NOT NULL default 0,
  stype_space smallint unsigned NOT NULL default 0,
  stype_appearance varchar(32) NOT NULL default 'utility',
  upgrades tinyint unsigned NOT NULL default 0,
  auction tinyint unsigned NOT NULL default 0,
  move_turn_cost tinyint unsigned NOT NULL default 1,
  point_value smallint unsigned NOT NULL default 0,
  auxiliary_ship smallint unsigned default NULL,
  PRIMARY KEY (stype_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]star;

CREATE TABLE [game]star (
  game_id tinyint unsigned NOT NULL default 1,
  star_id smallint unsigned NOT NULL,
  star_name varchar(32) NOT NULL default '',
  star_x smallint unsigned NOT NULL default 0,
  star_y smallint unsigned NOT NULL default 0,
  PRIMARY KEY (game_id, star_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]starlink;

CREATE TABLE [game]starlink (
  game_id tinyint unsigned NOT NULL default 1,
  star_id smallint unsigned NOT NULL,
  slink_type ENUM('link', 'wormhole') NOT NULL default 'link',
  slink_to smallint unsigned NOT NULL,
  PRIMARY KEY (game_id, star_id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS [game]starname;

CREATE TABLE [game]starname (
  star_name varchar(32) NOT NULL
) TYPE=MyISAM;
