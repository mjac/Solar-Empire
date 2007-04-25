DROP TABLE IF EXISTS PREFIXauction;
DROP TABLE IF EXISTS PREFIXclan;
DROP TABLE IF EXISTS PREFIXclaninvite;
DROP TABLE IF EXISTS PREFIXoption;
DROP TABLE IF EXISTS PREFIXmsgclan;
DROP TABLE IF EXISTS PREFIXmsgforum;
DROP TABLE IF EXISTS PREFIXmsgplayer;
DROP TABLE IF EXISTS PREFIXnews;
DROP TABLE IF EXISTS PREFIXport;
DROP TABLE IF EXISTS PREFIXplanets;
DROP TABLE IF EXISTS PREFIXship;
DROP TABLE IF EXISTS PREFIXshiptype;
DROP TABLE IF EXISTS PREFIXstar;
DROP TABLE IF EXISTS PREFIXplayer;
DROP TABLE IF EXISTS PREFIXplayeropt;

CREATE TABLE PREFIXauction (
  lot_id int unsigned NOT NULL,
  lot_bidder int unsigned NULL default NULL,
  lot_name varchar(32) NOT NULL default '',
  lot_price int unsigned NOT NULL default 0,
  lot_code varchar(32) NOT NULL default '',
  lot_type int unsigned NOT NULL default 0,
  lot_date datetime NOT NULL default 0,
  lot_desc text NOT NULL default '',
  PRIMARY KEY (lot_id)
) TYPE=MyISAM;

CREATE TABLE PREFIXclan (
  clan_id smallint unsigned NOT NULL,
  clan_name varchar(32) NOT NULL default '',
  clan_leader int unsigned NOT NULL default 0,
  clan_symbol varchar(3) NOT NULL default '',
  clan_colour mediumint unsigned NOT NULL default 0,
  clan_score int NOT NULL default 0,
  PRIMARY KEY (clan_id)
) TYPE=MyISAM;

CREATE TABLE PREFIXclaninvite (
  clan_id smallint unsigned NOT NULL,
  acc_id int unsigned NOT NULL,
  cinv_sent datetime NOT NULL default 0
) TYPE=MyISAM;

CREATE TABLE PREFIXmsgclan (
  clan_id int NOT NULL,
  msg_id int unsigned NOT NULL,
  msg_sender int unsigned NULL default NULL,
  msg_sendername varchar(32) NULL default NULL,
  msg_sent datetime NOT NULL default 0,
  msg_content text NOT NULL,
  PRIMARY KEY (msg_id),
  KEY acc_id (acc_id),
  KEY msg_sent (msg_sent)
) TYPE=MyISAM;

CREATE TABLE PREFIXmsgforum (
  msg_id int unsigned NOT NULL,
  msg_sender int unsigned NULL default NULL,
  msg_sendername varchar(32) NULL default NULL,
  msg_sent datetime NOT NULL default 0,
  msg_content text NOT NULL,
  PRIMARY KEY (msg_id),
  KEY acc_id (acc_id),
  KEY msg_sent (msg_sent)
) TYPE=MyISAM;

CREATE TABLE PREFIXmsgplayer (
  msg_id int unsigned NOT NULL,
  acc_id int unsigned NOT NULL,
  msg_sender int unsigned NULL default NULL,
  msg_sendername varchar(32) NULL default NULL,
  msg_sent datetime NOT NULL default 0,
  msg_content text NOT NULL,
  PRIMARY KEY (msg_id),
  KEY acc_id (acc_id),
  KEY msg_sent (msg_sent)
) TYPE=MyISAM;

CREATE TABLE PREFIXnews (
  news_id int unsigned NOT NULL,
  acc_id int unsigned NULL default NULL,
  news_sent datetime NOT NULL default 0,
  news_title varchar(255) NOT NULL,
  news_content text NOT NULL,
  PRIMARY KEY  (news_id),
  KEY news_sent (news_sent)
) TYPE=MyISAM;

CREATE TABLE PREFIXoption (
  optn_name varchar(32) NOT NULL default '',
  optn_value int NOT NULL default 0,
  optn_min int NOT NULL default 1,
  optn_max int NOT NULL default 1,
  optn_desc text NOT NULL,
  PRIMARY KEY (optn_name)
) TYPE=MyISAM;

CREATE TABLE PREFIXplanet (
  star_id smallint unsigned NOT NULL,
  plnt_id int unsigned NOT NULL,
  acc_id int unsigned default NULL,
  plnt_name varchar(32) NOT NULL default '',
  plnt_type tinyint unsigned NOT NULL default 0,
  plnt_fighter int unsigned NOT NULL default 20,
  plnt_colonist int unsigned NOT NULL default 1000,
  plnt_credit int unsigned NOT NULL default 0,
  plnt_taxrate tinyint unsigned NOT NULL default 5,
  plnt_metal int unsigned NOT NULL default 0,
  plnt_fuel int unsigned NOT NULL default 0,
  plnt_elect int unsigned NOT NULL default 0,
  plnt_organ int unsigned NOT NULL default 0,
  plnt_alloc_fight int unsigned NOT NULL default 0,
  plnt_alloc_elect int unsigned NOT NULL default 0,
  plnt_alloc_organ int unsigned NOT NULL default 0,
  plnt_appearance tinyint unsigned default NULL,
  plnt_shield_gen tinyint unsigned NOT NULL default 0,
  plnt_shield_charge int unsigned NOT NULL default 0,
  plnt_launch_pad int unsigned NOT NULL default 0,
  plnt_missile mediumint unsigned NOT NULL default 0,
  plnt_report tinyint unsigned NOT NULL default 1,
  PRIMARY KEY (planet_id),
  KEY location (location),
  KEY login_id (login_id)
) TYPE=MyISAM;

CREATE TABLE PREFIXport (
  star_id smallint unsigned NOT NULL default 0,
  port_id smallint unsigned NOT NULL,
  PRIMARY KEY (port_id),
  KEY star_id (star_id)
) TYPE=MyISAM;

CREATE TABLE PREFIXplayer (
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
  PRIMARY KEY (acc_id),
  KEY plyr_name (plyr_name)
) TYPE=MyISAM;

CREATE TABLE PREFIXplayeropt (
  acc_id int unsigned NOT NULL,
  popt_news_back smallint unsigned NOT NULL default 150,
  popt_forum_back smallint unsigned NOT NULL default 30,
  popt_show_sigs tinyint unsigned NOT NULL default 1,
  popt_show_pics tinyint unsigned NOT NULL default 1,
  popt_show_minimap tinyint unsigned NOT NULL default 1,
  popt_show_clan_ships tinyint unsigned NOT NULL default 1,
  popt_show_abbr_ship_class tinyint unsigned NOT NULL default 1,
  popt_show_rel_sym tinyint unsigned NOT NULL default 1,
  popt_attack_report tinyint unsigned NOT NULL default 1,
  popt_cursing_filter tinyint unsigned NOT NULL default 1,
  popt_planet_report tinyint unsigned NOT NULL default 1,
  PRIMARY KEY (acc_id)
) TYPE=MyISAM;

CREATE TABLE PREFIXship (
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
  PRIMARY KEY  (ship_id),
  KEY star_id (star_id),
  KEY ship_towedby (ship_towedby),
  KEY acc_id (acc_id)
) TYPE=MyISAM;

CREATE TABLE PREFIXshiptype (
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
  PRIMARY KEY (type_id)
) TYPE=MyISAM;

CREATE TABLE PREFIXstar (
  star_id smallint unsigned NOT NULL,
  star_name varchar(32) NOT NULL default '',
  star_x smallint unsigned NOT NULL default 0,
  star_y smallint unsigned NOT NULL default 0,
  PRIMARY KEY (star_id)
) TYPE=MyISAM;

CREATE TABLE PREFIXstarlink (
  star_id smallint unsigned NOT NULL,
  slink_type ENUM('link', 'wormhole') NOT NULL default 'link',
  slink_to smallint unsigned NOT NULL
  PRIMARY KEY (star_id)
) TYPE=MyISAM;

INSERT INTO PREFIXoption (optn_name, optn_value, optn_min, optn_max, optn_desc) VALUES ('admin_var_show', 1, 0, 1, 'If 0, players cannot see the game vars, on the game_vars page.'),
('allow_search_map', 1, 0, 1, 'Determines if users are allowed to run searches to try and find a system on the map (best left for the server admin to set).'),
('allow_signatures', 0, 0, 1, 'If set to 1, posts will have the signatures on them. Otherwise, they will be turned off.'),
('bomb_cost', 100000, 0, 10000000, 'Cost of the normal bombs: alpha/gamma. Other bombs will cost a multiple of this number.'),
('bomb_level_auction', 0, 0, 2, 'If set to 0, bombs cannot be purchased. 1 Gamma/Alpha.  3 Gamma/Alpha/Delta.'),
('bomb_level_shop', 0, 0, 2, 'If set to 0, bombs cannot be purchased. 1 Gamma/Alpha.  3 Gamma/Alpha/Delta.'),
('bilkos_time', 24, 6, 72, 'The amount of hours a player must hold the highest bid on an item to win it.'),
('buy_elect', 230, 1, 1000, 'Price Electronics can be brought for. Sell price is 20% less.'),
('buy_fuel', 90, 1, 1000, 'Price Fuel can be brought for. Sell price is 20% less.'),
('buy_metal', 80, 1, 1000, 'Price Metal can be brought for. Sell price is 20% less.'),
('buy_organ', 60, 1, 1000, 'Price Organics can be brought for. Sell price is 20% less.'),
('clan_member_limit', 5, 0, 100, 'Max number of players able to join a single clan.'),
('cost_colonist', 1, 0, 10000, 'Cost per colonist, as taken from Earth'),
('cost_genesis_device', 20000, 0, 100000, 'Cost of genesis devices.'),
('enable_superweapons', 1, 0, 1, 'Setting this to 0 will mean the terra maelstrom, and the omega missile will be turned off. <br>Useful if you want very big planets in your game, or if using the uv_planets variable for a limited planet count within the game.'),
('fighter_cost_earth', 100, 1, 10000, 'The cost to buy a fighter at earth.'),
('flag_planet_attack', 1, 0, 1, 'Planet attack flag.  When set to 0 planets can not be attacked.'),
('flag_sol_attack', 1, 0, 1, 'If set to 0 then attacking at Sol is dissallowed. Bombs are not allowed either.'),
('flag_space_attack', 1, 0, 1, 'Space attack flag.  When set to 0 ships can not be attacked.'),
('increase_shields', 5, 0, 100, 'Shield percentage regenerated per tick.'),
('increase_turns', 5, 0, 1000, 'Turns gained per tick.'),
('max_clans', 10000, 0, 10000, 'Max number of clans that can be created.'),
('max_players', 100000, 0, 100000, 'Max number of players that can be signed up in the game.'),
('max_ships', 100, 0, 1000, 'Max number of ships that a player can have.'),
('max_turns', 250, 10, 1000000, 'Max number of turns a player can have.'),
('min_before_transfer', 3, 0, 10000, 'Min number of days before players can transfer cash/ships.'),
('new_logins', 1, 0, 1, 'New login flag. When set to 0, new players cannot sign-up.'),
('attack_turn_cost_ship', 2, 0, 1000, 'Number of turns it takes to attack another ship.'),
('attack_turn_cost_planet', 10, 0, 1000, 'Number of turns it takes to attack a planet.'),
('planet_elect', 10, 1, 10000, 'The number of electronics a user gets produced from 50 assigned colonists using 10 metal and 10 fuel.'),
('planet_fighters', 50, 1, 10000, 'The number of fighters a user gets produced from 100 assigned colonists using 10 metals, fuels and electronics.'),
('planet_organ', 550, 1, 1000000, 'The number of colonists required to produce 1 unit of organics.'),
('rr_fuel_chance', 50, 0, 100, 'Chance that a star system will recieve random amount of fuel.'),
('rr_fuel_chance_max', 5000, 0, 1000000, 'Maximum amount of fuel that a system will recieve.'),
('rr_fuel_chance_min', 100, 0, 1000000, 'Minimum amount of fuel that a system will recieve.'),
('rr_metal_chance', 75, 0, 100, 'Chance that a star system will recieve random amount of metal.'),
('rr_metal_chance_max', 5000, 0, 1000000, 'Maximum amount of metal that a system will recieve.'),
('rr_metal_chance_min', 100, 0, 1000000, 'Minimum amount of metal that a system will recieve.'),
('score_method', 0, 0, 2, 'Decides method of scoring used. 0: scores are off. 1: Score is based on fighter kills and such like. 2: score is based on point value of ships killed and lost.'),
('ship_warp_cost', 1, -1, 1000, 'This var determines how much it costs for players to warp between systems.<br><br>Set it between 0 and 1000 to determine the number of turns,<br>OR<br>set it to -1, whereby a different system will be used, where different ship types take different numbers of turns to get to places. The bigger the ship the more turns it takes.'),
('start_cash', 5000, 0, 1000000, 'Amount of cash a player starts out with.'),
('start_ship', 5, 3, 6, 'Ship player starts in. 3 = SS, 4 = MF, 5 = ST, 6= HM'),
('start_turns', 40, 0, 1000000, 'Amount of turns a player starts out with.'),
('sudden_death', 0, 0, 1, 'When this is set to 1, players can never regenerate, nor can new players join the game.'),
('turns_before_attack', 50, 0, 1000, 'Turns that have to be used before a new account can attack ships.'),
('turns_before_planet_attack', 50, 0, 1000, 'Turns that a player has to use before they can attack/use planets.'),
('turns_safe', 50, 0, 1000, 'Turns that have to pass before a new player can be attacked.'),
('uv_fuel_max', 113205, 1, 1000000, 'Max amount of fuel in a star system when universe is generated.'),
('uv_fuel_min', 695, 1, 1000000, 'Min amount of fuel in a star system when universe is generated.'),
('uv_fuel_percent', 30, 0, 100, 'Percent of star systems that will have fuel when universe is generated.'),
('uv_map_graphics', 1, 0, 1, 'Whether to use graphics in the maps.'),
('uv_map_layout', 0, 0, 3, 'Choose the layout of the map.<p>0 = Random. 1 = Galactic Core. 2 = Clusters. 3 = Stars within a circle.'),
('uv_max_link_dist', 100, 0, 10000, 'Maximum distance a link between two star systems may be.  Setting this too low will result in most/all stars not being linked.'),
('uv_metal_max', 99835, 1, 1000000, 'Max amount of metal in a star system when universe is generated.'),
('uv_metal_min', 134, 1, 1000000, 'Min amount of metal in a star system when universe is generated.'),
('uv_metal_percent', 60, 0, 100, 'Percent of star systems that will have metal when universe is generated.'),
('uv_min_star_dist', 20, 20, 60, 'Minimum distance between star systems - in pixels.'),
('uv_num_bmrkt', 10, 0, 50, 'Sets number of blackmarkets created during Universe generation.'),
('uv_num_ports', 25, 0, 300, 'Number of star ports when universe is generated.'),
('uv_num_stars', 150, 10, 1000, 'Number of stars in the universe.'),
('uv_planets', -1, -1, 1000, 'This sets the maximum number of planet slots that may appear (randomly) in a system when the universe is generated. <br>This can be used in conjunction with <b>uv_planets</b> to create a universe that has both planets, and planetary slots.'),
('uv_planet_slots', 5, 0, 50, 'This sets the maximum number of planet slots that may appear (randomly) in a system when the universe is generated.<br>Note: If <b class=b1>uv_planets</b> is set to anything other than -1, this variable will be ignored.'),
('uv_planet_slots_use', 0, 0, 1, 'Set this to 1 to use planetary slots!'),
('uv_port_variance', 50, 0, 100, 'Amount of variance in prices at star ports. <*>'),
('uv_show_warp_numbers', 1, 0, 1, 'Show warp numbers.  When set to 0 warp numbers will not be shown on starmaps after universe is generated.'),
('uv_universe_size', 500, 200, 5000, 'Size in pixels of the universe.'),
('wormholes', 1, 0, 1, 'Set to 0 disable Wormholes or 1 to have them in the game'),
('process_cleanup', 3600, 1, 604800, 'The frequency of this processed task.'),
('process_turns', 3600, 1, 604800, 'The frequency of this processed task.'),
('process_systems', 3600, 1, 604800, 'The frequency of this processed task.'),
('process_ships', 3600, 1, 604800, 'The frequency of this processed task.'),
('process_planets', 86400, 1, 604800, 'The frequency of this processed task.'),
('process_government', 43200, 1, 604800, 'The frequency of this processed task.');
