DROP TABLE daily_tips;
DROP TABLE option_list;
DROP TABLE se_central_forum;
DROP TABLE se_games;
DROP TABLE se_star_names;
DROP TABLE user_accounts;
DROP TABLE user_history;

CREATE TABLE daily_tips (
  tip_id int4 NOT NULL,
  tip_content text NOT NULL,
  PRIMARY KEY (tip_id)
);

CREATE TABLE option_list (
  option_name varchar(32) NOT NULL default '',
  option_min int4 NOT NULL default 0,
  option_max int4 NOT NULL default 0,
  option_desc text NOT NULL,
  option_type int2 NOT NULL default 1,
  PRIMARY KEY  (option_name)
);

CREATE TABLE se_central_forum (
  message_id int4 NOT NULL,
  timestamp int8 NOT NULL default 0,
  sender_name varchar(32) NOT NULL default '',
  sender_game varchar(32) NOT NULL default '',
  sender_game_db varchar(32) NOT NULL default '',
  text text NOT NULL,
  PRIMARY KEY (message_id)
);

CREATE TABLE se_games (
  db_name varchar(16) NOT NULL default '',
  name varchar(32) NOT NULL default '',
  admin int8 NOT NULL default 1,
  status varchar(16) NOT NULL default 'paused',
  description text NOT NULL,
  intro_message text NOT NULL,
  num_stars int8 NOT NULL default 150,
  difficulty int8 NOT NULL default 3,
  started int8 NOT NULL default 0,
  finishes int8 NOT NULL default 0,
  processed_cleanup int8 NOT NULL default 0,
  processed_turns int8 NOT NULL default 0,
  processed_systems int8 NOT NULL default 0,
  processed_ships int8 NOT NULL default 0,
  processed_planets int8 NOT NULL default 0,
  processed_government int8 NOT NULL default 0,
  PRIMARY KEY (db_name)
);

CREATE TABLE user_accounts (
  login_id int8 NOT NULL,
  login_name varchar(32) NOT NULL default '',
  passwd varchar(64) NOT NULL default '',
  session_exp int4 NOT NULL default 0,
  session_id varchar(32) NOT NULL default '',
  in_game varchar(32) default NULL,
  email_address varchar(64) NOT NULL default '',
  signed_up int8 NOT NULL default 0,
  last_login int8 NOT NULL default 0,
  login_count int8 NOT NULL default 0,
  last_ip varchar(16) NOT NULL default '',
  num_games_joined int2 NOT NULL default 0,
  page_views int8 NOT NULL default 0,
  real_name varchar(64) NOT NULL default '',
  total_score int8 NOT NULL default 0,
  style varchar(32) NULL default NULL,
  PRIMARY KEY (login_id),
  UNIQUE (login_name),
  UNIQUE (email_address)
);

CREATE TABLE user_history (
  login_id int8 NOT NULL,
  timestamp int4 NOT NULL default 0,
  game_db varchar(16) NOT NULL default '',
  action text NOT NULL,
  user_IP varchar(16) NOT NULL default '',
  other_info text NOT NULL
);

CREATE TABLE se_star_names (
  name varchar(32) NOT NULL
);
