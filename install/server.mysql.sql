DROP TABLE IF EXISTS daily_tips;
--
CREATE TABLE daily_tips (
  tip_id smallint unsigned NOT NULL,
  tip_content text NOT NULL,
  PRIMARY KEY (tip_id)
) TYPE=MyISAM;
--
INSERT INTO daily_tips (tip_id, tip_content) VALUES (1, 'To customise your SE experience, try playing with some of the options on the <b class=b1>Options</b> Page.'),
(2, 'You can change your colour scheme at any time from the options page.<br />There are plenty to choose from.'),
(3, 'Rule Number One: The Admin Is Always Right.\n<br />Rule Number Two: If The Admin Is Wrong, See Rule Number One.\n<br />{starfox25, Dec 06 2000 - 14:26 }'),
(5, 'Just because a ship is more expensive does not necassarily mean it is better.'),
(6, 'The only source of knowledge is experience.\n<br />{Albert Einstein}'),
(7, 'Do not repeat the tactics which have gained you one victory, but let your methods be regulated by the infinite variety of circumstances.\n<br />{Sun Tzu, The Art of War - 6:28, 300BC}'),
(8, 'Nothing is foolproof to a sufficiently talented fool.\n<br />{CrymsonKyng, Apr 21 2001 - 05:56}'),
(9, 'You can click on the Mini-map to get a complete picture of the universe.'),
(10, 'Clicking a player''s name gives you information about that player.<br />This can also be done with your own name, and will reveal several new options.'),
(13, 'If you find any bugs, report them to the admin, along with details as to what you where doing to get it.'),
(14, 'Autowarp allows you to automatically find your way between A and B. It is not necassarily the shortest route though.'),
(15, 'Wormholes offer a great way to get across the universe in only 1 turn (provided there are any around).'),
(16, 'Its generally possible to get things on the cheap using the auction house. As well as lots of things you can''t get anywhere else in the game.<br />You can get to it from any star-port, or Earth.'),
(17, 'You should change all your password every few months.<br />You should also never give your password to other players. Ever!'),
(18, 'Upgrades allow you to improve your star-ships, however they cannot be removed once installed.'),
(19, 'Joining a Clan can get you new friends and allies, but also new foes.'),
(20, 'Statistics about the game you are in can be found by clicking on the games name in the top left corner of the screen (below the date).'),
(21, 'You may only own one flagship class ship at a time. If you loose it, the next one will cost double.'),
(22, 'Transversers with the <b>Wormhole Stabiliser</b> upgrade are ideal for getting colonists onto your planets quickly and cheaply.'),
(23, 'The hardest thing of all for a soldier is to retreat.<br />{Duke of Wellington}'),
(24, 'Wise people learn when they can; fools learn when they must.<br />{Duke of Wellington}'),
(25, 'Never interrupt your enemy when he is making a mistake.<br />{Napoleon Bonaparte}'),
(26, 'You must not fight too often with one enemy, or you will teach him all your art of war.<br />{Napoleon Bonaparte}'),
(27, 'You should not use one password for all applications (i.e. e-mail, online games etc). Instead you should have a different password for each account.');
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
INSERT INTO option_list (option_name, option_min, option_max, option_desc, option_type) VALUES ('news_back', 10, 700, 'Allows you to set how many hours of news will be shown per screen.', 2),
('forum_back', 1, 168, 'Allows you to choose how many hours the forum should list per screen.', 2),
('show_pics', 0, 1, 'Pictures are loaded in numerous places throughout the game. They can be turned off here. (This will not affect the Minimap. That can be turned off elsewhere on this page) &&& Hide Pictures. &&& Show Pictures.', 1),
('show_minimap', 0, 1, 'The Minimap is the map in the top right corner of the star System. When disbabled, a link to the full map will be shown in it''s place. &&& Minimap Disabled. &&& Minimap Enabled.', 1),
('show_sigs', 0, 1, 'Signatures are are appended to the end of personal or forum messages sent by another player. <br />Turning them off can make the forums load significantly faster. &&& Signatures Hidden. &&& Signatures Shown.', 1),
('show_clan_ships', 0, 1, 'This options controls whether all clan ships are shown on the clan_control page, or an overview of them. If turned off, the page will load much quicker later in the game.\n<br />There is a link in clan control that will allow you to see all clan ships if have the long list disabled. &&& Limited clan ship list shown. &&& Full clan ship list shown.', 1),
('show_abbr_ship_class', 0, 1, 'Ship listings in a star system can be made to show only abbreviated ship types (such as MF for Merchant Freighter). All such abbreviations are shown in the help next to the relevent ship. &&& Show full ship type. &&& Show abbreviated ship type.', 1),
('show_rel_sym', 0, 1, 'Relations symbols allow a player to see what relation you (or your clan) have set up with another player.<br />This is generally un-nessary for indeps, but a must for clans. &&& Hide relations symbol. &&& Show relations symbol.', 1),
('attack_report', 1, 2, 'This variable lets you decide what sort of report you get after attacking, or being attacked. &&& Receive only a brief overview of any battle that takes place &&& Recieve a very comprehensive battle report if you are the attacker. If you are the defender you will be sent a very comprehensive message if the ship that got attacked was big/warship, otherwise a brief report will be sent.', 1),
('cursing_filter', 0, 2, 'Determines the cursing filter (default is low)\n &&& None\n &&& Low\n &&& High', 1),
('planet_report', 0, 2, 'Decides whether a production report is returned from a planet during the daily maintenance.\n &&& Nothing returned\n &&& A report will be returned, but only if the planet produces something\n &&& All planets will return a report, no matter what.', 1);
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
