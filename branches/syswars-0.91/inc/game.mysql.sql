DROP TABLE IF EXISTS gamename_bilkos;
--
CREATE TABLE gamename_bilkos (
  item_id int unsigned NOT NULL,
  item_name varchar(32) NOT NULL default '',
  item_code varchar(32) NOT NULL default '',
  item_type int unsigned NOT NULL default 0,
  bidder_id int unsigned NOT NULL default 0,
  going_price int unsigned NOT NULL default 0,
  timestamp int unsigned NOT NULL default 0,
  active tinyint unsigned NOT NULL default 0,
  descr text NOT NULL,
  PRIMARY KEY (item_id)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_clans;
--
CREATE TABLE gamename_clans (
  clan_id smallint unsigned NOT NULL,
  clan_name varchar(32) NOT NULL default '',
  leader_id int unsigned NOT NULL default 0,
  symbol varchar(3) NOT NULL default '',
  sym_color mediumint unsigned NOT NULL default 0,
  clan_score int NOT NULL default 0,
  fighter_kills int unsigned NOT NULL default 0,
  PRIMARY KEY (clan_id)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_clan_invites;
--
CREATE TABLE gamename_clan_invites (
  clan_id smallint unsigned NOT NULL,
  login_id int unsigned NOT NULL,
  invited int unsigned NOT NULL default 0
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_db_vars;
--
CREATE TABLE gamename_db_vars (
  `name` varchar(30) NOT NULL default '',
  value int unsigned NOT NULL default 0,
  min int unsigned NOT NULL default 1,
  max int unsigned NOT NULL default 1,
  descript text NOT NULL,
  PRIMARY KEY (`name`)
) TYPE=MyISAM;
--
INSERT INTO gamename_db_vars (name, value, min, max, descript) VALUES ('admin_var_show', 1, 0, 1, 'If 0, players cannot see the game vars, on the game_vars page.'),
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
('uv_map_layout', 0, 0, 6, 'Choose the layout of the map.<p>0 = Random Star Distribution.<br>1 = Grid of stars.<br>2 = Galactic Core<br>3 = Clusters<br>4 = Stars within a circle'),
('uv_max_link_dist', -1, -1, 10000, 'Maximum distance a link between two star systems may be (in pixels).<br>Setting this too low will result in most/all stars not being linked. Note that Sol is always linked, no matter this var.<br>Set to -1 to allow nature to take it''s course.<p>Try experimenting.'),
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
('wormholes', 1, 0, 2, 'Set to 0 disable Wormholes, 1 to have them in the game but not on the Map, and 2 to have them in the game & on the Map.'),
('process_cleanup', 3600, 1, 604800, 'The frequency of this processed task.'),
('process_turns', 3600, 1, 604800, 'The frequency of this processed task.'),
('process_systems', 3600, 1, 604800, 'The frequency of this processed task.'),
('process_ships', 3600, 1, 604800, 'The frequency of this processed task.'),
('process_planets', 86400, 1, 604800, 'The frequency of this processed task.'),
('process_government', 43200, 1, 604800, 'The frequency of this processed task.');
--
DROP TABLE IF EXISTS gamename_diary;
--
CREATE TABLE gamename_diary (
  entry_id int unsigned NOT NULL,
  `timestamp` int unsigned NOT NULL default 0,
  login_id int unsigned NOT NULL default 0,
  entry text NOT NULL,
  PRIMARY KEY (entry_id),
  KEY `timestamp` (`timestamp`)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_messages;
--
CREATE TABLE gamename_messages (
  message_id int unsigned NOT NULL,
  login_id int NOT NULL,
  sender_id int unsigned NULL default NULL,
  sender_name varchar(32) NULL default NULL,
  `timestamp` int unsigned NOT NULL default 0,
  `text` text NOT NULL,
  clan_id int unsigned NOT NULL default 0,
  PRIMARY KEY (message_id),
  KEY login_id (login_id),
  KEY `timestamp` (`timestamp`)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_news;
--
CREATE TABLE gamename_news (
  news_id int unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int unsigned NOT NULL default 0,
  login_id int unsigned NOT NULL default 0,
  headline text NOT NULL,
  PRIMARY KEY  (news_id),
  KEY `timestamp` (`timestamp`)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_planets;
--
CREATE TABLE gamename_planets (
  planet_id int unsigned NOT NULL,
  planet_name varchar(32) NOT NULL default '',
  planet_type tinyint unsigned NOT NULL default 0,
  location int unsigned NOT NULL default 0,
  login_id int unsigned default NULL,
  fighters int unsigned NOT NULL default 20,
  colon int unsigned NOT NULL default 1000,
  fighter_set tinyint unsigned NOT NULL default 0,
  cash int unsigned NOT NULL default 0,
  tax_rate tinyint unsigned NOT NULL default 5,
  metal int unsigned NOT NULL default 0,
  fuel int unsigned NOT NULL default 0,
  elect int unsigned NOT NULL default 0,
  organ int unsigned NOT NULL default 0,
  alloc_fight int unsigned NOT NULL default 0,
  alloc_elect int unsigned NOT NULL default 0,
  alloc_organ int unsigned NOT NULL default 0,
  pass varchar(32) NOT NULL default '',
  planet_img tinyint unsigned default NULL,
  shield_gen tinyint unsigned NOT NULL default 0,
  shield_charge int unsigned NOT NULL default 0,
  launch_pad int unsigned NOT NULL default 0,
  missile mediumint unsigned NOT NULL default 0,
  daily_report tinyint unsigned NOT NULL default 1,
  PRIMARY KEY (planet_id),
  KEY location (location),
  KEY login_id (login_id)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_ports;
--
CREATE TABLE gamename_ports (
  port_id smallint unsigned NOT NULL,
  location smallint unsigned NOT NULL default 0,
  metal_bonus int unsigned NOT NULL default 0,
  fuel_bonus int unsigned NOT NULL default 0,
  organ_bonus int unsigned NOT NULL default 0,
  elect_bonus int unsigned NOT NULL default 0,
  PRIMARY KEY (port_id),
  KEY location (location)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_ship_types;
--
CREATE TABLE gamename_ship_types (
  type_id smallint unsigned NOT NULL,
  `name` varchar(32) NOT NULL default '',
  abbr varchar(8) NOT NULL default '',
  `type` varchar(16) NOT NULL default '',
  cost int unsigned NOT NULL default 0,
  hull int unsigned NOT NULL default 100,
  max_hull int unsigned NOT NULL default 100,
  max_shields int unsigned NOT NULL default 0,
  fighters int unsigned NOT NULL default 0,
  max_fighters int unsigned NOT NULL default 0,
  cargo_bays smallint unsigned NOT NULL default 0,
  mining_rate smallint unsigned NOT NULL default 0,
  appearance varchar(32) NOT NULL default 'utility',
  description text NOT NULL,
  config varchar(16) NOT NULL default '',
  upgrades tinyint unsigned NOT NULL default 0,
  auction tinyint unsigned NOT NULL default 0,
  move_turn_cost tinyint unsigned NOT NULL default 1,
  point_value smallint unsigned NOT NULL default 0,
  auxiliary_ship smallint unsigned default NULL,
  PRIMARY KEY (type_id)
) TYPE=MyISAM;
--
INSERT INTO gamename_ship_types (type_id, name, abbr, type, cost, hull, max_hull, max_shields, fighters, max_fighters, cargo_bays, mining_rate, appearance, description, config, upgrades, auction, move_turn_cost, point_value, auxiliary_ship) VALUES (1, 'Escape Pod', 'EP', 'Escape Pod', 1000, 5, 5, 100, 0, 0, 10, 1, 'pod', 'If you''re in one of these, you''re pretty darn dead. So hurry up and get a proper ship.', '', 0, 0, 1, 5, NULL),
(2, 'Scout Ship', 'SS', 'Scout Ship', 3000, 20, 20, 50, 10, 20, 10, 1, 'scout', 'This ship has a small mining capacity but is usually used for mobile scouting and spying.', 'hs:na', 0, 0, 0, 2, NULL),
(3, 'Merchant Freighter', 'MF', 'Freighter', 10000, 100, 100, 200, 20, 40, 200, 2, 'freighter', 'Everyones favourite ship, and an old classic. Good for mining, early attacking and scouting.', 'fr', 1, 0, 1, 5, NULL),
(4, 'Stealth Trader', 'ST', 'Freighter', 30000, 100, 100, 100, 50, 100, 300, 4, 'stealth-freighter', 'The concession on this ship is cargo capacity, however its mining rate and stealth  more than make up for this. It is Highly Stealthed.', 'hs:fr', 0, 0, 1, 15, 2),
(5, 'Harvester Mammoth', 'HM', 'Freighter', 50000, 250, 250, 300, 50, 100, 1000, 7, 'utility', 'The heaviest merchant on the market. Can hold a woping 1000 units of cargo, which makes it a great colonist transporter.', 'fr', 4, 0, 3, 10, 2),
(6, 'Frigate', 'AB', 'Battleship', 40000, 100, 100, 500, 200, 1000, 0, 0, 'frigate', 'A General purpose warship, the lightest in the group. Good early on in the game if you fancy taking someone out.', 'bs', 2, 0, 3, 30, 3),
(7, 'Warmonger', 'WM', 'Battleship', 95000, 400, 400, 1000, 500, 2000, 0, 0, 'destroyer', 'Heavier than the AB when it comes to a fight, this ship is capable of holding its own. High fighter capacity, as well as a scanner.', 'sc:bs', 4, 0, 4, 40, 3),
(8, 'Skirmisher', 'Skirm', 'Battleship', 200000, 750, 750, 1000, 1000, 5000, 0, 0, 'cruiser', 'If its all out war you want, then this is where you''ll get it. This one has everything any warship ever needed. Lots of added extras such as scanner and light stealthing. The neighbours will know when you bring one of these home.', 'sc:ls:bs', 2, 0, 5, 50, 7),
(10, 'Transverser', 'TV', 'Transporter', 250000, 50, 50, 800, 100, 100, 0, 0, 'advanced', 'Using the latest Sub-space jump technology, this ship can move fleets anywhere in the Cosmos.  Very good ship for large-scale movements, but also uses alot of turns making the jumps.', 'sj', 3, 0, 4, 30, 3),
(11, 'Brobdingnagian', 'Brob', 'Flagship', 1000000, 4000, 4000, 10000, 2000, 10000, 5000, 0, 'behemoth', 'The leviathan of space, and capable of making moons quake, this hulking mass of a ship is the best command ship out there. Comes with built in Scanner, Quark Disrupter, even a Transwarp Drive, and thats on top of the excellent offensive/defensive abilities it comes with too.', 'oo:sv:sc:tw', 0, 0, 7, 150, 8),
(12, 'Flexi-Hull', 'FH', 'Modular', 30000, 100, 100, 100, 100, 100, 100, 2, 'flexible', 'Designed with the intention that users can do as they wish with this ship, it''s completely flexible, allowing for many applications in the hostile and changing universe.', '', 15, 0, 5, 20, 2),
(13, 'Mega-Flex', 'M-Flex', 'Modular', 65000, 100, 100, 100, 100, 100, 0, 7, 'flexible', 'Bigger, and with more upgradability than ever before, this ship is at the top in the tech tree for Modular Technology.', '', 30, 0, 6, 25, 2),
(14, 'Civilian Transport', 'CT', 'Carrier', 60000, 200, 200, 1000, 100, 100, 4000, 0, 'freighter', 'A ship dedicated to the pursuit of getting people away from the crowded planets in the Sol system, and out there to do your bidding.', 'na:hs', 3, 0, 2, 10, 2),
(15, 'Super Skirmisher', 'SSkirm', 'Battleship', 600000, 1000, 1000, 4000, 2000, 8000, 0, 0, 'cruiser', 'A Great ship for getting rid of those pesky enemies, as it has a high fighter capacity, and lots of shields.', 'hs:sh:sc:bs', 5, 1, 6, 60, 7),
(16, 'Mega Miner/Cargo', 'MMC', 'Mega-Flex(tm)', 300000, 100, 100, 1000, 100, 200, 5000, 10, 'utility', 'Vast cargo bays that could house an army of colonists, as well as an exeptional mining rate. If only there were more of them.', 'hs:fr:na', 5, 1, 6, 25, 2),
(17, 'Adv. Transverser', 'ATV', 'Transverser', 400000, 500, 500, 2000, 100, 100, 1000, 0, 'advanced', 'The 8th Wonder of Transport Tech. Excellent for autoshifting, as the wormhole stabiliser comes built in, as does a transwarp drive. It cannot attack.', 'sj:tw:na:ws:hs', 1, 1, 3, 40, 3),
(18, 'Explorer Mark I', 'EM1', 'Alien Scout', 50000, 100, 100, 5000, 100, 100, 50, 2, 'scout', 'Fell off the back of an Alien Fleet.  Massive shields protect a vulnerable interior.', 'tw:sc:ls', 0, 1, 1, 10, NULL),
(19, 'Occultator', 'EC', 'Carrier', 10000000, 20000, 20000, 10000, 1000, 50000, 10000, 0, 'death-star', 'Welcome to the newest craze in the galaxy! A hollowed out asteroid with an Alien Battlestar''s engines nailed to its sides. The cost of this ship reflects the enourmous amount of effort required to remove the asteroids contents and fill it with fighter bays. Its gone from being a navigational hazard for ships, to a planet eliminator, and should you part with your cash, you are guaranteed hours of planet leveling fun.', 'po', 0, 0, 10, 120, 7),
(20, 'Alien Battlestar', 'BStar', 'Flagship', 2000000, 5000, 5000, 20000, 2000, 20000, 2000, 0, 'organic-behemoth', 'If you thought the Brobdingnagian was the Emperor of Space, think again. This converted alien vessal was found derelict and is a true Flagship.  It has Subspace Jump facilities (with wormhole stabiliser)  and a scanner for good measure.', 'oo:ws:sc:sj', 0, 0, 7, 200, 7);
--
DROP TABLE IF EXISTS gamename_ships;
--
CREATE TABLE gamename_ships (
  ship_id int unsigned NOT NULL,
  ship_name varchar(32) NOT NULL default '',
  login_id int unsigned NOT NULL default 0,
  location smallint unsigned NOT NULL default 1,
  towed_by int unsigned default NULL,
  type_id smallint unsigned NOT NULL default 0,
  hull int unsigned NOT NULL default 100,
  max_hull int unsigned NOT NULL default 100,
  shields int unsigned NOT NULL default 100,
  max_shields int unsigned NOT NULL default 100,
  fighters int unsigned NOT NULL default 0,
  max_fighters int unsigned NOT NULL default 0,
  cargo_bays smallint unsigned NOT NULL default 0,
  metal smallint unsigned NOT NULL default 0,
  fuel smallint unsigned NOT NULL default 0,
  elect smallint unsigned NOT NULL default 0,
  organ smallint unsigned NOT NULL default 0,
  colon smallint unsigned NOT NULL default 0,
  task enum('none','mine','defend','defend-fleet','defend-planet','patrol') NOT NULL default 'none',
  mining_mode enum('metal','fuel') NOT NULL default 'metal',
  mining_rate smallint unsigned NOT NULL default 0,
  config varchar(16) NOT NULL,
  upgrades smallint unsigned NOT NULL default 0,
  point_value smallint unsigned NOT NULL default 0,
  points_killed int unsigned NOT NULL default 0,
  auxiliary_ship smallint unsigned default NULL,
  PRIMARY KEY  (ship_id),
  KEY location (location),
  KEY towed_by (towed_by),
  KEY login_id (login_id)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_stars;
--
CREATE TABLE gamename_stars (
  star_id smallint unsigned NOT NULL,
  star_name varchar(32) NOT NULL default '',
  x smallint unsigned NOT NULL default 0,
  y smallint unsigned NOT NULL default 0,
  link_1 smallint unsigned NOT NULL default 0,
  link_2 smallint unsigned NOT NULL default 0,
  link_3 smallint unsigned NOT NULL default 0,
  link_4 smallint unsigned NOT NULL default 0,
  link_5 smallint unsigned NOT NULL default 0,
  link_6 smallint unsigned NOT NULL default 0,
  metal int unsigned NOT NULL default 0,
  fuel int unsigned NOT NULL default 0,
  wormhole smallint unsigned NOT NULL default 0,
  planetary_slots tinyint unsigned NOT NULL default 0,
  PRIMARY KEY (star_id)
) TYPE=MyISAM;
--
INSERT INTO gamename_stars (star_id, star_name, x, y, link_1, link_2, link_3, link_4, link_5, link_6, metal, fuel, wormhole, planetary_slots) VALUES (1, 'Sol', 250, 250, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
--
DROP TABLE IF EXISTS gamename_user_options;
--
CREATE TABLE gamename_user_options (
  login_id int unsigned NOT NULL,
  news_back smallint unsigned NOT NULL default 150,
  forum_back smallint unsigned NOT NULL default 30,
  show_sigs tinyint unsigned NOT NULL default 1,
  show_pics tinyint unsigned NOT NULL default 1,
  show_minimap tinyint unsigned NOT NULL default 1,
  show_clan_ships tinyint unsigned NOT NULL default 1,
  show_abbr_ship_class tinyint unsigned NOT NULL default 1,
  show_rel_sym tinyint unsigned NOT NULL default 1,
  attack_report tinyint unsigned NOT NULL default 1,
  cursing_filter tinyint unsigned NOT NULL default 1,
  planet_report tinyint unsigned NOT NULL default 1,
  PRIMARY KEY (login_id)
) TYPE=MyISAM;
--
DROP TABLE IF EXISTS gamename_users;
--
CREATE TABLE gamename_users (
  login_id int unsigned NOT NULL,
  login_name varchar(32) NOT NULL default '',
  joined_game int unsigned NOT NULL default 0,
  game_login_count int unsigned NOT NULL default 0,
  turns int unsigned NOT NULL default 40,
  turns_run int unsigned NOT NULL default 0,
  ship_id int unsigned default NULL,
  cash bigint unsigned NOT NULL default 10000,
  last_attack int unsigned NOT NULL default 1,
  last_attack_by varchar(32) NOT NULL default '',
  ships_killed int unsigned NOT NULL default 0,
  ships_lost int unsigned NOT NULL default 0,
  ships_killed_points int unsigned NOT NULL default 0,
  ships_lost_points int unsigned NOT NULL default 0,
  show_user_ships tinyint unsigned NOT NULL default 1,
  show_enemy_ships tinyint unsigned NOT NULL default 0,
  genesis tinyint unsigned NOT NULL default 0,
  clan_id smallint unsigned default NULL,
  fighters_killed int unsigned NOT NULL default 0,
  fighters_lost int unsigned NOT NULL default 0,
  bounty int unsigned NOT NULL default 0,
  score int NOT NULL default 0,
  alpha tinyint unsigned NOT NULL default 0,
  gamma tinyint unsigned NOT NULL default 0,
  delta tinyint unsigned NOT NULL default 0,
  sig varchar(128) NOT NULL default '',
  last_request int unsigned NOT NULL default 0,
  last_access_forum int unsigned NOT NULL default 0,
  last_access_clan_forum int unsigned NOT NULL default 0,
  last_access_admin_forum int unsigned NOT NULL default 0,
  banned_time int unsigned NOT NULL default 0,
  banned_reason tinytext default '' NOT NULL,
  one_brob tinyint NOT NULL default 0,
  PRIMARY KEY (login_id),
  KEY login_name (login_name)
) TYPE=MyISAM;
--
DELETE FROM se_games WHERE db_name = 'gamename';
--
INSERT INTO se_games (db_name, name, admin, status, description, intro_message, num_stars, difficulty, started, finishes, processed_cleanup, processed_turns, processed_systems, processed_ships, processed_planets, processed_government) VALUES ('gamename', 'gametitle', 1, 'paused', '', '', 150, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP() + 1728000, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP());