DROP TABLE IF EXISTS daily_tips;
--
CREATE TABLE daily_tips (
  tip_id smallint unsigned NOT NULL,
  tip_content text NOT NULL,
  PRIMARY KEY (tip_id)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS option_list;
--
CREATE TABLE option_list (
  option_name varchar(32) NOT NULL default '',
  option_min int NOT NULL default 0,
  option_max int NOT NULL default 0,
  option_desc text NOT NULL,
  option_type tinyint unsigned NOT NULL default 1,
  PRIMARY KEY  (option_name)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS se_central_forum;
--
CREATE TABLE se_central_forum (
  message_id int unsigned NOT NULL,
  `timestamp` int unsigned NOT NULL default 0,
  sender_name varchar(32) NOT NULL default '',
  sender_game varchar(32) NOT NULL default '',
  sender_game_db varchar(32) NOT NULL default '',
  `text` text NOT NULL,
  PRIMARY KEY (message_id)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS se_games;
--
CREATE TABLE se_games (
  db_name varchar(16) NOT NULL default '',
  `name` varchar(32) NOT NULL default '',
  admin int unsigned NOT NULL default 1,
  `status` ENUM('hidden', 'paused', 'running') NOT NULL default 'paused',
  description text NOT NULL,
  intro_message text NOT NULL,
  num_stars int unsigned NOT NULL default 150,
  difficulty int unsigned NOT NULL default 3,
  started int unsigned NOT NULL default 0,
  finishes int unsigned NOT NULL default 0,
  processed_cleanup int unsigned NOT NULL default 0,
  processed_turns int unsigned NOT NULL default 0,
  processed_systems int unsigned NOT NULL default 0,
  processed_ships int unsigned NOT NULL default 0,
  processed_planets int unsigned NOT NULL default 0,
  processed_government int unsigned NOT NULL default 0,
  PRIMARY KEY (db_name)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS user_accounts;
--
CREATE TABLE user_accounts (
  login_id int unsigned NOT NULL,
  login_name varchar(32) NOT NULL default '',
  passwd varchar(64) NOT NULL default '',
  session_exp int NOT NULL default 0,
  session_id varchar(32) NOT NULL default '',
  in_game varchar(32) default NULL,
  email_address varchar(64) NOT NULL default '',
  signed_up int unsigned NOT NULL default 0,
  last_login int unsigned NOT NULL default 0,
  login_count int unsigned NOT NULL default 0,
  last_ip varchar(16) NOT NULL default '',
  num_games_joined smallint unsigned NOT NULL default 0,
  page_views int unsigned NOT NULL default 0,
  real_name varchar(64) NOT NULL default '',
  total_score bigint NOT NULL default 0,
  style varchar(32) NULL default NULL,
  PRIMARY KEY (login_id),
  UNIQUE KEY login_name (login_name),
  UNIQUE KEY email_address (email_address)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS user_history;
--
CREATE TABLE user_history (
  login_id int unsigned NOT NULL,
  `timestamp` int NOT NULL default 0,
  game_db varchar(16) NOT NULL default '',
  `action` text NOT NULL,
  user_IP varchar(16) NOT NULL default '',
  other_info text NOT NULL
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS se_star_names;
--
CREATE TABLE se_star_names (
  `name` varchar(32) NOT NULL
) TYPE=MyISAM;
