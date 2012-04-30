DROP TABLE IF EXISTS `gamename_bilkos`;
CREATE TABLE `gamename_bilkos` (
  `item_id` int(4) NOT NULL auto_increment,
  `item_name` varchar(30) NOT NULL default '',
  `item_code` varchar(30) NOT NULL default '',
  `item_type` int(4) NOT NULL default '0',
  `bidder_id` int(4) NOT NULL default '0',
  `going_price` int(4) NOT NULL default '0',
  `timestamp` int(4) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '0',
  `descr` text NOT NULL,
  PRIMARY KEY  (`item_id`)
);

DROP TABLE IF EXISTS `gamename_bmrkt`;
CREATE TABLE `gamename_bmrkt` (
  `bmrkt_id` int(4) NOT NULL auto_increment,
  `location` int(4) NOT NULL default '0',
  `tech_variance` int(4) NOT NULL default '0',
  `bmrkt_type` tinyint(4) NOT NULL default '0',
  `bm_name` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`bmrkt_id`),
  KEY `location` (`location`)
);

DROP TABLE IF EXISTS `gamename_clans`;
CREATE TABLE `gamename_clans` (
  `clan_id` int(4) NOT NULL auto_increment,
  `clan_name` varchar(30) NOT NULL default '',
  `passwd` varchar(25) NOT NULL default '',
  `leader_id` int(4) NOT NULL default '0',
  `members` int(4) NOT NULL default '1',
  `symbol` varchar(3) NOT NULL default '',
  `sym_color` varchar(6) NOT NULL default '',
  `clan_score` int(4) NOT NULL default '0',
  `fighter_kills` int(4) NOT NULL default '0',
  PRIMARY KEY  (`clan_id`),
  KEY `login_id` (`clan_id`,`clan_name`)
);

DROP TABLE IF EXISTS `gamename_db_vars`;
CREATE TABLE `gamename_db_vars` (
  `name` varchar(30) NOT NULL default '',
  `value` int(4) NOT NULL default '0',
  `min` int(4) NOT NULL default '1',
  `max` int(4) NOT NULL default '1',
  `descript` text NOT NULL,
  PRIMARY KEY  (`name`)
);

INSERT INTO `gamename_db_vars` (`name`, `value`, `min`, `max`, `descript`) VALUES ('admin_var_show', 1, 0, 1, 'If 0, players cannot see the game vars, on the game_vars page.'),
('allow_search_map', 1, 0, 1, 'Determines if users are allowed to run searches to try and find a system on the map (best left for the server admin to set).'),
('allow_signatures', 0, 0, 1, 'If set to 1, posts will have the signatures on them. Otherwise, they will be turned off.'),
('alternate_play_1', 0, 0, 1, 'Determines if all ships can mine everything, or each ship mines either metal or fuel. <br>Players will not be able to buy metal or fuel, so will have to mine everything they plan to use.<br>Set to 0 to have original play style, and 1 to have alternate play style.'),
('bilkos_time', 24, 6, 72, 'The amount of hours a player must hold a bid on an item at bilkos for, before it can be won.'),
('buy_elect', 230, 1, 1000, 'Price Electronics can be brought for. Sell price is 20% less.'),
('buy_fuel', 90, 1, 1000, 'Price Fuel can be brought for. Sell price is 20% less.'),
('buy_metal', 80, 1, 1000, 'Price Metal can be brought for. Sell price is 20% less.'),
('buy_organ', 60, 1, 1000, 'Price Organics can be brought for. Sell price is 20% less.'),
('clan_member_limit', 5, 0, 100, 'Max number of players able to join a single clan.'),
('cost_bomb', 100000, 0, 10000000, 'Cost of the normal bombs. Other bombs will cost a multiple of this number.'),
('cost_colonist', 1, 0, 10000, 'Cost per colonist, as taken from Earth'),
('cost_genesis_device', 20000, 0, 100000, 'Cost of genesis devices.'),
('cost_to_post', 0, 0, 100, 'Cost (in turns) to post a message to the forum.'),
('count_days_left_in_game', 20, 0, 10000, 'Number of days left before reseting.  Counts down every night.'),
('enable_politics', 0, 0, 1, 'Determines if Politics are enabled or not. Set to 0 to disable.'),
('enable_superweapons', 1, 0, 1, 'Setting this to 0 will mean the terra maelstrom, and the omega missile will be turned off. <br>Useful if you want very big planets in your game, or if using the uv_planets variable for a limited planet count within the game.'),
('fighter_cost_earth', 100, 1, 10000, 'The cost to buy a fighter at earth.'),
('flag_bomb', 0, 0, 2, 'If set to 1, bombs cannot be purchased from equip shop. If set to 2, then only the delta bomb will appear in Bilkos.'),
('flag_planet_attack', 1, 0, 1, 'Planet attack flag.  When set to 0 planets can not be attacked.'),
('flag_research', 0, 0, 1, 'Research flag. When set to 0 research is disabled. Also disables the Blackmarket in the game. To enable, set to 1.'),
('flag_sol_attack', 1, 0, 1, 'If set to 0 then attacking at Sol is dissallowed. Bombs are not allowed either.'),
('flag_space_attack', 1, 0, 1, 'Space attack flag.  When set to 0 ships can not be attacked.'),
('hourly_shields', 15, 0, 100, 'Number of shield points regenerated per ship each hour.'),
('hourly_tech', 5, 0, 100, 'Number of Tech Units generated each hour by each planetary Research Facility. Increases by increments due to colony population growth to a maximum of 3 times the hourly rate per Facility.'),
('hourly_turns', 10, 0, 1000, 'Number of turns gained each hour.'),
('keep_sol_clear', 1, 0, 1, 'If 1 then will scatter all non-newbie ships from Sol if they are in that system for two consecutive hours.'),
('max_clans', 10000, 0, 10000, 'Max number of clans that can be created.'),
('max_players', 100000, 0, 100000, 'Max number of players that can be signed up in the game.'),
('max_ships', 100, 0, 1000, 'Max number of ships that a player can have.'),
('max_turns', 250, 10, 1000000, 'Max number of turns a player can have.'),
('message_colour', 3, 0, 4, 'Colour of forum & private messages of admin only: 1=yellow, 2=blue, 3=green, 4=red'),
('min_before_transfer', 3, 0, 10000, 'Min number of days before players can transfer cash/ships.'),
('new_logins', 1, 0, 1, 'New login flag. When set to 0, new players cannot sign-up.'),
('one_comp_one_user', 2, 0, 2, 'If 0, then only one user per comp; if 1 then many users, but admin gets told; if 2 then many users per comp, no warnings'),
('planet_attack_turn_cost', 10, 0, 1000, 'Number of turns it takes to attack a planet.'),
('planet_elect', 8, 1, 10000, 'The number of electronics a user gets produced from 500 assigned colonists using 10 metal and 10 fuel.'),
('planet_fighters', 5, 1, 10000, 'The number of fighters a user gets produced from 100 assigned colonists using 1 metal, 1 fuel, and 1.'),
('planet_organ', 550, 1, 1000000, 'The number of colonists required to produce 1 unit of organics.'),
('random_events', 0, 0, 3, 'The higher the number, the more events. If 0 then they are turned off.'),
('rr_fuel_chance', 50, 0, 100, 'Chance that a star system will recieve random amount of fuel daily.'),
('rr_fuel_chance_max', 19342, 0, 1000000, 'Maximum amount of fuel that a system will recieve.'),
('rr_fuel_chance_min', 143, 0, 1000000, 'Minimum amount of fuel that a system will recieve.'),
('rr_metal_chance', 75, 0, 100, 'Chance that a star system will recieve random amount of metal daily.'),
('rr_metal_chance_max', 15454, 0, 1000000, 'Maximum amount of metal that a system will recieve.'),
('rr_metal_chance_min', 214, 0, 1000000, 'Minimum amount of metal that a system will recieve.'),
('score_method', 0, 0, 4, 'Decides method of scoring used.<br><br>0: Scores are Off<br>1: Score is based on fighter kills and such like.<br>2: Score is based on point value of ships killed and lost.<br>3: Score based on fiscal value of player.<br>4: Score takes just about everything into account.'),
('ship_warp_cost', 1, -1, 1000, 'This var determines how much it costs for players to warp between systems.<br><br>Set it between 0 and 1000 to determine the number of turns,<br>OR<br>set it to -1, whereby a different system will be used, where different ship types take different numbers of turns to get to places. The bigger the ship the more turns it takes.'),
('space_attack_turn_cost', 2, 0, 1000, 'Number of turns it takes to attack another ship.'),
('start_cash', 5000, 0, 1000000, 'Amount of cash a player starts out with.'),
('start_ship', 5, 3, 6, 'Ship player starts in. 3 = SS, 4 = MF, 5 = ST, 6= HM'),
('start_tech', 0, 0, 1000000, 'Number of tech support units a player starts out with. Recommended as zero, unless running an Extreme Game.'),
('start_turns', 40, 0, 1000000, 'Amount of turns a player starts out with.'),
('sudden_death', 0, 0, 1, 'When this is set to 1, players can never regenerate, nor can new players join the game.'),
('turns_before_attack', 50, 0, 1000, 'Turns that have to be used before a new account can attack ships.'),
('turns_before_planet_attack', 50, 0, 1000, 'Turns that a player has to use before they can attack/use planets.'),
('turns_safe', 50, 0, 1000, 'Turns that have to pass before a new player can be attacked.'),
('uv_fuel_max', 113205, 1, 1000000, 'Max amount of fuel in a star system when universe is generated.'),
('uv_fuel_min', 695, 1, 1000000, 'Min amount of fuel in a star system when universe is generated.'),
('uv_fuel_percent', 30, 0, 100, 'Percent of star systems that will have fuel when universe is generated.'),
('uv_map_layout', 0, 0, 4, 'Choose the layout of the map.<p>0 = Random Star Distribution.<br>1 = Grid of stars.<br>2 = Galactic Core<br>3 = Clusters<br>4 = Stars within a circle'),
('uv_max_link_dist', -1, -1, 10000, 'Maximum distance a link between two star systems may be (in pixels).<br>Setting this too low will result in most/all stars not being linked. Note that Sol is always linked, no matter this var.<br>Set to -1 to allow nature to take it''s course.<p>Try experimenting.'),
('uv_metal_max', 99835, 1, 1000000, 'Max amount of metal in a star system when universe is generated.'),
('uv_metal_min', 134, 1, 1000000, 'Min amount of metal in a star system when universe is generated.'),
('uv_metal_percent', 60, 0, 100, 'Percent of star systems that will have metal when universe is generated.'),
('uv_min_star_dist', 15, 10, 20, 'Minimum distance between star systems - in pixels.'),
('uv_needs_gen', 0, 0, 1, 'Universe generation flag.  When this is set to 1 the universe will be (re)created when the next daily maint is run.'),
('uv_num_bmrkt', 10, 0, 50, 'Sets number of blackmarkets created during Universe generation.'),
('uv_num_ports', 25, 0, 300, 'Number of star ports when universe is generated.'),
('uv_num_stars', 150, 10, 300, 'Number of stars in the universe.'),
('uv_planets', -1, -1, 1000, 'This sets the maximum number of planet slots that may appear (randomly) in a system when the universe is generated. <br>This can be used in conjunction with <b>uv_planets</b> to create a universe that has both planets, and planetary slots.'),
('uv_planet_slots', 5, 0, 50, 'This sets the maximum number of planet slots that may appear (randomly) in a system when the universe is generated.<br>Note: If <b class=b1>uv_planets</b> is set to anything other than -1, this variable will be ignored.'),
('uv_planet_slots_use', 0, 0, 1, 'Set this to 1 to use planetary slots!'),
('uv_port_variance', 50, 0, 100, 'Amount of variance in prices at star ports. <*>'),
('uv_show_warp_numbers', 1, 0, 1, 'Show warp numbers.  When set to 0 warp numbers will not be shown on starmaps after universe is generated.'),
('uv_universe_size', 500, 200, 1000, 'Size in pixels of the universe.'),
('wormholes', 1, 0, 2, 'Set to 0 disable Wormholes, 1 to have them in the game but not on the Map, and 2 to have them in the game & on the Map.');

DROP TABLE IF EXISTS `gamename_diary`;
CREATE TABLE `gamename_diary` (
  `entry_id` int(4) NOT NULL auto_increment,
  `timestamp` int(4) NOT NULL default '0',
  `login_id` int(4) NOT NULL default '0',
  `entry` text NOT NULL,
  `topic` text NOT NULL,
  PRIMARY KEY  (`entry_id`),
  UNIQUE KEY `entry_id` (`entry_id`),
  KEY `entry_id_2` (`entry_id`,`timestamp`)
);

DROP TABLE IF EXISTS `gamename_messages`;
CREATE TABLE `gamename_messages` (
  `message_id` int(4) NOT NULL auto_increment,
  `sender_name` varchar(30) NOT NULL default '',
  `timestamp` int(4) NOT NULL default '0',
  `login_id` int(4) NOT NULL default '0',
  `text` text NOT NULL,
  `sender_id` int(4) NOT NULL default '1',
  `clan_id` int(4) NOT NULL default '0',
  PRIMARY KEY  (`message_id`),
  KEY `login_id` (`login_id`),
  KEY `timestamp` (`timestamp`)
);

DROP TABLE IF EXISTS `gamename_news`;
CREATE TABLE `gamename_news` (
  `news_id` int(4) NOT NULL auto_increment,
  `timestamp` int(4) NOT NULL default '0',
  `login_id` int(4) NOT NULL default '0',
  `headline` text NOT NULL,
  PRIMARY KEY  (`news_id`),
  KEY `timestamp` (`timestamp`)
);

DROP TABLE IF EXISTS `gamename_planets`;
CREATE TABLE `gamename_planets` (
  `planet_id` int(4) NOT NULL auto_increment,
  `planet_name` varchar(30) NOT NULL default '',
  `planet_type` int(4) NOT NULL default '0',
  `location` int(4) NOT NULL default '0',
  `login_id` int(4) NOT NULL default '0',
  `login_name` varchar(30) NOT NULL default 'Nobody',
  `fighters` int(4) NOT NULL default '20',
  `colon` int(4) NOT NULL default '1000',
  `fortress_level` int(4) NOT NULL default '0',
  `fighter_set` int(4) NOT NULL default '0',
  `cash` int(4) NOT NULL default '0',
  `tax_rate` int(4) NOT NULL default '5',
  `clan_id` int(4) NOT NULL default '-1',
  `metal` int(4) NOT NULL default '0',
  `fuel` int(4) NOT NULL default '0',
  `elect` int(4) NOT NULL default '0',
  `organ` int(4) NOT NULL default '0',
  `alloc_fight` int(4) NOT NULL default '0',
  `alloc_elect` int(4) NOT NULL default '0',
  `alloc_organ` int(4) NOT NULL default '0',
  `pass` varchar(30) NOT NULL default '0',
  `planet_img` tinyint(4) default NULL,
  `shield_gen` tinyint(4) NOT NULL default '0',
  `shield_charge` int(4) NOT NULL default '0',
  `launch_pad` tinyint(4) NOT NULL default '0',
  `missile` int(4) NOT NULL default '0',
  `tech` int(4) NOT NULL default '0',
  `research_fac` tinyint(4) NOT NULL default '0',
  `daily_report` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`planet_id`),
  KEY `planet_id` (`planet_id`),
  KEY `location` (`location`),
  KEY `login_id` (`login_id`)
);

DROP TABLE IF EXISTS `gamename_politics`;
CREATE TABLE `gamename_politics` (
  `position_id` int(4) NOT NULL default '0',
  `position_name` varchar(30) NOT NULL default '',
  `login_id` int(4) NOT NULL default '0',
  `login_name` varchar(30) NOT NULL default '',
  `timestamp` int(4) NOT NULL default '0',
  PRIMARY KEY  (`position_id`),
  UNIQUE KEY `position_id` (`position_id`)
);

INSERT INTO `gamename_politics` (`position_id`, `position_name`, `login_id`, `login_name`, `timestamp`) VALUES (1, 'Monarch', 0, '', 0),
(2, 'Industry Senator', 0, '', 0),
(3, 'Military Senator', 0, '', 0),
(4, 'Defense Senator', 0, '', 0),
(5, 'Trade Senator', 0, '', 0),
(6, 'War Senator', 0, '', 0),
(7, 'Espionage Senator', 0, '', 0),
(8, 'Research Senator', 0, '', 0);

DROP TABLE IF EXISTS `gamename_ports`;
CREATE TABLE `gamename_ports` (
  `port_id` int(4) NOT NULL auto_increment,
  `location` int(4) NOT NULL default '0',
  `metal_bonus` int(4) NOT NULL default '0',
  `fuel_bonus` int(4) NOT NULL default '0',
  `elect_bonus` int(4) NOT NULL default '0',
  PRIMARY KEY  (`port_id`),
  KEY `planet_id` (`port_id`),
  KEY `location` (`location`)
);

DROP TABLE IF EXISTS `gamename_ship_types`;
CREATE TABLE `gamename_ship_types` (
  `type_id` int(4) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `type` varchar(30) NOT NULL default '',
  `class_abbr` varchar(10) NOT NULL default '',
  `cost` int(4) NOT NULL default '0',
  `tcost` int(4) NOT NULL default '0',
  `fighters` int(4) NOT NULL default '0',
  `max_fighters` int(4) NOT NULL default '0',
  `max_shields` int(4) NOT NULL default '0',
  `cargo_bays` int(4) NOT NULL default '0',
  `mine_rate_metal` int(4) NOT NULL default '0',
  `mine_rate_fuel` int(4) NOT NULL default '0',
  `descr` text NOT NULL,
  `size` tinyint(4) NOT NULL default '0',
  `config` varchar(30) NOT NULL default '',
  `upgrades` int(4) NOT NULL default '0',
  `auction` tinyint(4) NOT NULL default '0',
  `move_turn_cost` int(4) NOT NULL default '1',
  `point_value` int(4) NOT NULL default '0',
  `num_pc` int(4) NOT NULL default '0',
  `num_ot` int(4) NOT NULL default '0',
  `num_dt` int(4) NOT NULL default '0',
  `num_sa` int(4) NOT NULL default '0',
  `num_ew` int(4) NOT NULL default '0',
  PRIMARY KEY  (`type_id`),
  KEY `type_id` (`type_id`)
);

INSERT INTO `gamename_ship_types` (`type_id`, `name`, `type`, `class_abbr`, `cost`, `tcost`, `fighters`, `max_fighters`, `max_shields`, `cargo_bays`, `mine_rate_metal`, `mine_rate_fuel`, `descr`, `size`, `config`, `upgrades`, `auction`, `move_turn_cost`, `point_value`, `num_pc`, `num_ot`, `num_dt`, `num_sa`, `num_ew`) VALUES (1, 'Ship Destroyed', '', 'SDestroyed', 10000, 0, 0, 0, 0, 0, 0, 0, '', 0, '', 0, 0, 1, 0, 0, 0, 0, 0, 0),
(2, 'Escape Pod', 'Escape Pod', 'EP', 10000, 0, 0, 10, 10, 10, 3, 0, 'If you''re in one of these, you''re pretty darn dead. So hurry up and get a proper ship.', 1, 'tw', 0, 0, 1, 5, 0, 0, 0, 0, 0),
(3, 'Scout Ship', 'Scout Ship', 'SS', 3000, 0, 15, 150, 30, 10, 1, 1, 'Though small, and cheap, this ship has a lot of potential. It''s invaluable for scouting in the random-event games. It also has a number of tactical uses.', 1, 'hs:na', 0, 0, 1, 5, 0, 0, 0, 0, 0),
(4, 'Merchant Freighter', 'Freighter', 'MF', 10000, 0, 70, 900, 150, 150, 1, 5, 'Everyones favourite ship, and an old classic. Good for mining, early attacking, scouting, and pretty much everything.', 3, 'fr', 1, 0, 2, 10, 0, 0, 0, 0, 0),
(5, 'Stealth Trader', 'Freighter', 'ST', 30000, 0, 130, 800, 150, 300, 8, 3, 'The concession on this ship is cargo capacity, however its mining rate and stealth  more than make up for this. It is Highly Stealthed.', 4, 'hs:fr', 2, 0, 3, 15, 0, 0, 0, 0, 0),
(6, 'Harvester Mammoth', 'Freighter', 'HM', 50000, 0, 100, 500, 300, 1000, 5, 12, 'The heaviest merchant on the market. Can hold a woping 1000 units of cargo, which makes it a great colonist transporter.<br>To ensure its prolonged survival in the hostile universe, it is also fitted with 1 array of Defensive Turrets.', 5, 'fr:dt', 4, 0, 4, 15, 0, 0, 1, 0, 0),
(7, 'Attack Battleship', 'Battleship', 'AB', 50000, 0, 250, 5000, 100, 0, 0, 0, 'A General purpose warship, the lightest in the group. Good early on in the game if you fancy taking someone out.<br>Also comes with 1 Pea Shooter Cluster, and 1 Defensive turret array.', 4, 'bs:dt:ot', 5, 0, 3, 30, 0, 1, 1, 0, 0),
(8, 'Warmonger', 'Battleship', 'WM', 95000, 0, 600, 9000, 195, 0, 0, 0, 'Heavier than the AB when it comes to a fight, this ship is capable of holding its own. High fighter capacity, as well as a scanner, 2 Pea Shooter Clusters and 1 Defensive Turret Array, make this a must buy for anyone anticipating a bad day at the office.', 5, 'sc:bs:dt:st', 4, 0, 4, 40, 0, 1, 2, 0, 0),
(9, 'Skirmisher', 'Battleship', 'Skirm', 200000, 0, 1000, 17000, 500, 0, 0, 0, 'If its all out war you want, then this is where you''ll get it. This one has everything any warship ever needed. Lots of firepower (including 3 Pea Shooter Clusters, and 1 Defensive Turret Array), as well as some added extras such as scanner and light stealthing. The neighbours will know when you bring one of these home.', 6, 'sc:ls:bs:dt:ot', 2, 0, 5, 50, 0, 1, 3, 0, 0),
(11, 'Transverser', 'Warp-point Generator', 'TV', 250000, 0, 400, 500, 0, 0, 0, 0, 'Using the latest Sub-space jump technology, this ship can move fleets anywhere in the Cosmos.  Very good ship for large-scale movements, but also uses alot of turns making the jumps.<br>Has 1 Defensive Turret Array to help protect your investment.', 5, 'sj:dt', 3, 0, 4, 30, 0, 0, 1, 0, 0),
(12, 'Brobdingnagian', 'Flagship', 'Brob', 1000000, 0, 2500, 32000, 1000, 3000, 0, 0, 'The leviathan of space, and capable of making moons quake, this hulking mass of a ship is the best command ship out there. Comes with built in Scanner, Quark Disrupter, even a Transwarp Drive, and thats on top of the excellent offensive/defensive abilities it comes with too (including 5 Pea Shooter Clusters, and 5 Deffensive Turret Arrays).<br>You''ll wonder what you ever did without it.', 8, 'oo:sv:sc:tw:ot:dt', 0, 0, 7, 150, 0, 5, 5, 0, 0),
(13, 'Flexi-Hull(tm)', 'Modular', 'FH', 30000, 0, 100, 100, 100, 100, 2, 6, 'Designed with the intention that users can do as they wish with this ship, it''s completely flexible, allowing for many applications in the hostile and changing universe.', 4, '', 30, 0, 5, 20, 0, 0, 0, 0, 0),
(14, 'Mega-Flex(tm)', 'Modular', 'M-Flex', 65000, 0, 100, 100, 100, 0, 7, 7, 'Bigger, and with more upgradability than ever before, this ship is at the top in the tech tree for Modular Technology.', 5, '', 70, 0, 6, 25, 0, 0, 0, 0, 0),
(15, 'Civilian Transport', 'Carrier', 'CT', 60000, 0, 100, 500, 200, 4000, 0, 0, 'A ship dedicated to the pursuit of getting people away from the crowded planets in the Sol system, and out there to do your bidding. Comes with high stealth and twin Defensive Turret Arrays, but alas it cannot attack.', 4, 'na:hs', 3, 0, 3, 10, 0, 0, 2, 0, 0),
(16, 'Super Skirmisher', 'Battleship', 'SSkirm', 600000, 0, 3000, 20000, 1000, 0, 0, 0, 'A Great ship for getting rid of those pesky enemies, as it incorporates 5 Plasma Cannon Clusters, and 2 Silicon Armour modules and 2 Electronic warfare Pods, as well as a high fighter capacity, and lots of shields.', 7, 'hs:sh:sc:bs:pc:sa:ew', 5, 1, 6, 60, 5, 0, 0, 2, 2),
(17, 'Mega Miner/Cargo', 'Mega-Flex(tm)', 'MMC', 300000, 0, 1000, 1500, 500, 5000, 10, 22, 'Vast cargo bays that could house an army of colonists, as well as an exeptional mining rate and two Arrays of Defensive Turrets. If only there were more of them.', 6, 'hs:fr:na:dt', 5, 1, 6, 25, 0, 0, 2, 0, 0),
(18, 'Adv. Transverser', 'Transverser', 'ATV', 400000, 0, 1500, 3000, 0, 0, 0, 0, 'The 8th Wonder of Transport Tech. Excellent for autoshifting, as the wormhole stabiliser comes built in, as does a transwarp drive. However it cannot attack, but has 2 Defensive Turret Arrays to ward off enemy ships.<p><center><a href=./images/ships/ship_18.jpg target=_blank><img border=0 height=120 width=160 src=./images/ships/ship_18_tn.jpg></a></center>', 6, 'sj:tw:na:ws:hs:dt', 1, 1, 3, 40, 0, 0, 2, 0, 0),
(207, 'Explorer Mark I', 'Alien Scout', 'EM1', 50000, 0, 100, 300, 400, 50, 2, 2, 'Fell off the back of an Alien Fleet. Includes 4* Normal Shield Charging Rate, and a Silicon Armour Module. <p><center><a href=./images/ships/ship_207.jpg target=_blank><img border=0 height=120 width=160 src=./images/ships/ship_207_tn.jpg></a></center>', 1, 'tw:sc:ls:sa', 0, 1, 1, 10, 0, 0, 0, 1, 0),
(301, 'Mammoth Ram-Scoop', 'Adv. Freighter', 'HMR', 72000, 150, 0, 0, 120, 1500, 3, 16, 'The new age of mining has dawned with the introduction of Advanced Ram-Scoop Technology which allows the ship to collect fuel each time it moves between systems. The elimination of fighters and most shields allows for more cargo bays, while the Ram-Scoop allows for a much improved fuel mining rate. The Silicon Armour module makes up for most of its defensive shortcomings. <br>Comes with two upgrade pods for slight customisation.', 6, 'fr:sa:br', 2, 0, 5, 20, 0, 0, 0, 1, 0),
(302, 'Mammoth Asteroid Processor', 'Adv. Freighter', 'HMA', 72000, 150, 0, 0, 120, 1500, 17, 2, 'Spawned at the same time as the Mammoth Ram scoop, this ships Asteriod Processing facilities allows for the ability to collect metal simply by moving between systems. The increase in cargo bays has led to the elimination of fighters and most shields, but there was still space to put a Silicon Armour module on. Two upgrade pods allow for slight customisation.', 6, 'fr:sa:br', 2, 0, 5, 20, 0, 0, 0, 1, 0),
(303, 'GunShip', 'Battleship', 'GB', 65000, 350, 300, 2000, 150, 0, 0, 0, 'A lightly armed warship that uses 3 plasma cannons as its teeth, 2 Silicon Armour Modules for additional defense, and 2 electronic warfare modules to back those up.<br>This ship is ideal for taking out light enemy vessels. It also has light stealth and a scanner to assist in the task.', 3, 'sc:ls:bs:pc:sa:ew', 1, 0, 2, 35, 3, 0, 0, 2, 2),
(304, 'Occultator', 'Carrier', 'EC', 500000, 500, 5, 125003, 1000, 0, 0, 0, 'Welcome to the newest craze in the galaxy!<br>A hollowed out asteroid with an Alien Battlestar''s engines nailed to its sides. <br>The cost of this ship reflects the enourmous amount of effort required to remove the asteroids contents and fill it with fighter bays.<br>Its gone from being a navigational hazard for ships, to a planet eliminator, and should you part with your cash, you are guaranteed hours of planet leveling fun.', 7, 'po', 5, 0, 7, 120, 0, 0, 0, 0, 0),
(399, 'Alien Battlestar', 'Flagship', 'BStar', 1050000, 2000, 3000, 40000, 1000, 500, 0, 0, 'If you thought the Brobdingnagian was the Emperor of Space, think again. <br>This converted alien vessal was found derelict and is a true Flagship, armed to the teeth with an incredible 10 Plasma Cannons, 5 Silicon Armour Modules, 4 Electronic Warfare modules, Subspace Jump facilities (with wormhole stabiliser) (Note: Alien Technology allows this and shields to reside on the same ship!), and a scanner for good measure.<br>This ship will lead your fleet to battle in true style.', 8, 'oo:ws:sc:sj:pc:sa:ew', 0, 0, 7, 200, 10, 0, 0, 5, 4);

DROP TABLE IF EXISTS `gamename_ships`;
CREATE TABLE `gamename_ships` (
  `ship_id` int(4) NOT NULL auto_increment,
  `ship_name` varchar(30) NOT NULL default '',
  `login_id` int(4) NOT NULL default '0',
  `login_name` varchar(30) NOT NULL default 'Nobody',
  `clan_id` int(4) NOT NULL default '-1',
  `location` int(4) NOT NULL default '1',
  `towed_by` int(4) NOT NULL default '0',
  `shipclass` int(4) NOT NULL default '0',
  `class_name` varchar(30) NOT NULL default '0',
  `class_name_abbr` varchar(10) NOT NULL default '0',
  `shields` int(4) NOT NULL default '0',
  `max_shields` int(4) NOT NULL default '0',
  `fighters` int(4) NOT NULL default '0',
  `max_fighters` int(4) NOT NULL default '0',
  `cargo_bays` int(4) NOT NULL default '0',
  `metal` int(4) NOT NULL default '0',
  `fuel` int(4) NOT NULL default '0',
  `elect` int(4) NOT NULL default '0',
  `organ` int(4) NOT NULL default '0',
  `colon` int(4) NOT NULL default '0',
  `defend_fleet` tinyint(4) NOT NULL default '0',
  `mine_mode` int(4) NOT NULL default '0',
  `mine_rate_metal` int(4) NOT NULL default '0',
  `mine_rate_fuel` int(4) NOT NULL default '0',
  `move_turn_cost` int(4) NOT NULL default '1',
  `config` tinytext NOT NULL,
  `size` tinyint(4) NOT NULL default '1',
  `upgrades` int(4) NOT NULL default '0',
  `point_value` int(4) NOT NULL default '0',
  `points_killed` int(4) NOT NULL default '0',
  `num_ot` int(4) NOT NULL default '0',
  `num_dt` int(4) NOT NULL default '0',
  `num_pc` int(4) NOT NULL default '0',
  `num_sa` int(4) NOT NULL default '0',
  `num_ew` int(4) NOT NULL default '0',
  PRIMARY KEY  (`ship_id`),
  KEY `ship_id` (`ship_id`),
  KEY `location` (`location`),
  KEY `fighters` (`fighters`),
  KEY `towed_by` (`towed_by`),
  KEY `login_id` (`login_id`)
);

DROP TABLE IF EXISTS `gamename_stars`;
CREATE TABLE `gamename_stars` (
  `star_id` int(4) NOT NULL auto_increment,
  `star_name` varchar(30) NOT NULL default '',
  `x_loc` int(4) NOT NULL default '0',
  `y_loc` int(4) NOT NULL default '0',
  `link_1` int(4) NOT NULL default '0',
  `link_2` int(4) NOT NULL default '0',
  `link_3` int(4) NOT NULL default '0',
  `link_4` int(4) NOT NULL default '0',
  `link_5` int(4) NOT NULL default '0',
  `link_6` int(4) NOT NULL default '0',
  `metal` int(4) NOT NULL default '0',
  `fuel` int(4) NOT NULL default '0',
  `event_random` tinyint(4) NOT NULL default '0',
  `wormhole` int(4) NOT NULL default '0',
  `planetary_slots` int(4) NOT NULL default '0',
  PRIMARY KEY  (`star_id`),
  KEY `login_id` (`star_id`,`star_name`)
);

INSERT INTO `gamename_stars` (`star_id`, `star_name`, `x_loc`, `y_loc`, `link_1`, `link_2`, `link_3`, `link_4`, `link_5`, `link_6`, `metal`, `fuel`, `event_random`, `wormhole`, `planetary_slots`) VALUES (1, 'Sol', 250, 250, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

DROP TABLE IF EXISTS `gamename_user_options`;
CREATE TABLE `gamename_user_options` (
  `login_id` int(4) NOT NULL default '0',
  `color_scheme` tinyint(4) NOT NULL default '1',
  `news_back` smallint(6) NOT NULL default '150',
  `forum_back` smallint(6) NOT NULL default '30',
  `show_sigs` tinyint(4) NOT NULL default '1',
  `show_pics` tinyint(4) NOT NULL default '1',
  `show_minimap` tinyint(4) NOT NULL default '1',
  `tow_method` tinyint(4) NOT NULL default '1',
  `show_config` tinyint(1) NOT NULL default '0',
  `show_aim` tinyint(4) NOT NULL default '0',
  `show_icq` tinyint(4) NOT NULL default '0',
  `show_clan_ships` tinyint(4) NOT NULL default '1',
  `show_abbr_ship_class` tinyint(4) NOT NULL default '1',
  `show_rel_sym` tinyint(4) NOT NULL default '1',
  `attack_report` tinyint(4) NOT NULL default '1',
  `system_disp_method` tinyint(4) NOT NULL default '2',
  `cursing_filter` tinyint(1) NOT NULL default '1',
  `planet_report` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`login_id`),
  UNIQUE KEY `login_id` (`login_id`)
);

INSERT INTO `gamename_user_options` (`login_id`, `color_scheme`, `news_back`, `forum_back`, `show_sigs`, `show_pics`, `show_minimap`, `tow_method`, `show_config`, `show_aim`, `show_icq`, `show_clan_ships`, `show_abbr_ship_class`, `show_rel_sym`, `attack_report`, `system_disp_method`, `cursing_filter`, `planet_report`) VALUES (1, 1, 100, 36, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 2, 1, 1);

DROP TABLE IF EXISTS `gamename_users`;
CREATE TABLE `gamename_users` (
  `login_id` int(4) NOT NULL auto_increment,
  `login_name` varchar(30) NOT NULL default '',
  `joined_game` int(4) NOT NULL default '0',
  `game_login_count` int(4) NOT NULL default '0',
  `turns` int(4) NOT NULL default '40',
  `turns_run` int(4) NOT NULL default '0',
  `location` int(4) unsigned NOT NULL default '1',
  `ship_id` int(4) default NULL,
  `cash` int(4) NOT NULL default '10000',
  `tech` int(4) NOT NULL default '0',
  `on_planet` int(4) NOT NULL default '0',
  `last_attack` int(4) NOT NULL default '1',
  `last_attack_by` varchar(30) NOT NULL default '',
  `ships_killed` int(4) NOT NULL default '0',
  `ships_lost` int(4) NOT NULL default '0',
  `ships_killed_points` int(4) NOT NULL default '0',
  `ships_lost_points` int(4) NOT NULL default '0',
  `show_enemy_ships` int(4) NOT NULL default '0',
  `show_user_ships` int(4) NOT NULL default '0',
  `genesis` int(4) NOT NULL default '0',
  `terra_imploder` int(4) NOT NULL default '0',
  `clan_id` int(4) NOT NULL default '0',
  `clan_sym` varchar(3) NOT NULL default '',
  `clan_sym_color` varchar(6) NOT NULL default '',
  `fighters_killed` int(4) NOT NULL default '0',
  `fighters_lost` int(4) NOT NULL default '0',
  `bounty` int(4) NOT NULL default '0',
  `score` int(4) NOT NULL default '0',
  `alpha` int(4) NOT NULL default '0',
  `gamma` int(4) NOT NULL default '0',
  `delta` tinyint(4) NOT NULL default '0',
  `sn_effect` tinyint(4) NOT NULL default '0',
  `politics` int(4) NOT NULL default '0',
  `sig` varchar(150) NOT NULL default '',
  `last_request` int(4) NOT NULL default '0',
  `last_access_forum` int(4) NOT NULL default '0',
  `last_access_clan_forum` int(4) NOT NULL default '0',
  `banned_time` int(4) NOT NULL default '0',
  `banned_reason` tinytext NOT NULL,
  `one_brob` tinyint(4) NOT NULL default '0',
  `second_scatter` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`login_id`),
  KEY `login_id` (`login_id`),
  KEY `login_name` (`login_name`),
  KEY `ships_killed` (`ships_killed`),
  KEY `turns_run` (`turns_run`)
);

INSERT INTO `gamename_users` (`login_id`, `login_name`, `joined_game`, `game_login_count`, `turns`, `turns_run`, `location`, `ship_id`, `cash`, `tech`, `on_planet`, `last_attack`, `last_attack_by`, `ships_killed`, `ships_lost`, `ships_killed_points`, `ships_lost_points`, `show_enemy_ships`, `show_user_ships`, `genesis`, `terra_imploder`, `clan_id`, `clan_sym`, `clan_sym_color`, `fighters_killed`, `fighters_lost`, `bounty`, `score`, `alpha`, `gamma`, `delta`, `sn_effect`, `politics`, `sig`, `last_request`, `last_access_forum`, `last_access_clan_forum`, `banned_time`, `banned_reason`, `one_brob`, `second_scatter`)
VALUES (1, 'admin', 1, 1, 250, 0, 1, NULL, 1000000000, 1000, 0, 0, '', 0, 0, 0, 0, 0, 0, 1, 1, 0, '', '', 0, 0, 0, 0, 1, 1, 1, 1, 0, '', 1, 1, 1, 0, '', 0, 0);

INSERT INTO `se_games` (`game_id`, `name`, `db_name`, `admin_name`, `admin_pw`, `admin_email`, `status`, `paused`, `description`, `intro_message`, `num_stars`, `todays_tip`, `difficulty`, `last_reset`, `session_id`, `session_exp`, `last_access_admin_forum`) VALUES (1, 'Test Game!', 'gamename', 'ChangeMe', 'd41d8cd98f00b204e9800998ecf8427e', 'you@uri.com', 1, 0, '', '', 150, 1, 3, 1124407424, '4CcRjlAeX97up2MQvITBcXQj1adXQeyK', 1124488318, 1124407143);


