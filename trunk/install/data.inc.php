<?php

$dat = array();

$dat['tips'] = array(
	'To customise your SE experience try playing with the variables on the <em>options page</em>.',
	'You can change your colour scheme at any time from the options page.
There are plenty to choose from.',
	'Rule Number One: The Admin Is Always Right.<br />
Rule Number Two: If The Admin Is Wrong, See Rule Number One.<br />
{starfox25, Dec 06 2000 - 14:26}',
	'Just because a ship is more expensive does not necessarily mean it is better.',
	'The only source of knowledge is experience.<br />
{Albert Einstein}',
	'Do not repeat the tactics which have gained you one victory, but let your 
methods be regulated by the infinite variety of circumstances.<br />
{Sun Tzu, The Art of War - 6:28, 300BC}',
	'Nothing is foolproof to a sufficiently talented fool.<br />
{CrymsonKyng, Apr 21 2001 - 05:56}',
	'You can click on the Mini-map to get a complete picture of the universe.',
	'Clicking a player\'s name gives you information about that player.<br />
This can also be done with your own name, and will reveal several new options.',
	'If you find any bugs, report them to the admin, along with details as to 
what you where doing to get it.',
	'Autowarp allows you to automatically find your way between A and B.
It is not necessarily the shortest route though.',
	'Wormholes offer a great way to get across the universe.',
	'It\'s generally possible to get things on the cheap using the auction house.
As well as lots of things you can\'t get anywhere else in the game.<br />
You can get to it from any star-port.',
	'You should change all your password every few months.<br />
You should also never give your password to other players. Ever!',
	'Upgrades allow you to improve your star-ships, however they cannot be 
removed once installed.',
	'Joining a clan can get you new friends and allies but also new foes.',
	'Statistics about the game you are in can be found by clicking on the games 
name in the top left corner of the screen.',
	'You may only own one flagship class ship at a time.  If you loose it, 
the next one will cost double.',
	'Transversers with the <em>Wormhole Stabiliser</em> upgrade are ideal for 
getting colonists onto your planets quickly and cheaply.',
	'The hardest thing of all for a soldier is to retreat.<br />
{Duke of Wellington}',
	'Wise people learn when they can; fools learn when they must.<br />
{Duke of Wellington}',
	'Never interrupt your enemy when he is making a mistake.<br />
{Napoleon Bonaparte}',
	'You must not fight too often with one enemy or you will teach him all your art of war.<br />
{Napoleon Bonaparte}',
	'You should not use one password for all applications; have a different 
password for each account.'
);

$dat['options'] = array(
	array('news_back', 10, 700, 'Allows you to set how many hours of news will be shown per screen.', 2),
	array('forum_back', 1, 168, 'Allows you to choose how many hours the forum should list per screen.', 2),
	array('show_pics', 0, 1, 'Pictures are loaded in numerous places throughout the game. They can be turned off here. (This will not affect the Minimap. That can be turned off elsewhere on this page) &&& Hide Pictures. &&& Show Pictures.', 1),
	array('show_minimap', 0, 1, 'The Minimap is the map in the top right corner of the star System. When disabled, a link to the full map will be shown in it\'s place. &&& Minimap Disabled. &&& Minimap Enabled.', 1),
	array('show_sigs', 0, 1, 'Signatures are are appended to the end of personal or forum messages sent by another player.<br />Turning them off can make the forums load significantly faster. &&& Signatures Hidden. &&& Signatures Shown.', 1),
	array('show_clan_ships', 0, 1, 'This options controls whether all clan ships are shown on the clan_control page, or an overview of them. If turned off, the page will load much quicker later in the game.<br />
There is a link in clan control that will allow you to see all clan ships if have the long list disabled. &&& Limited clan ship list shown. &&& Full clan ship list shown.', 1),
	array('show_abbr_ship_class', 0, 1, 'Ship listings in a star system can be made to show only abbreviated ship types (such as MF for Merchant Freighter). All such abbreviations are shown in the help next to the relevent ship. &&& Show full ship type. &&& Show abbreviated ship type.', 1),
	array('show_rel_sym', 0, 1, 'Relations symbols allow a player to see what relation you (or your clan) have set up with another player.<br />
This is generally un-nessary for indeps, but a must for clans. &&& Hide relations symbol. &&& Show relations symbol.', 1),
	array('attack_report', 1, 2, 'This variable lets you decide what sort of report you get after attacking, or being attacked. &&& Receive only a brief overview of any battle that takes place &&& Receive a very comprehensive battle report if you are the attacker. If you are the defender you will be sent a very comprehensive message if the ship that got attacked was big/warship, otherwise a brief report will be sent.', 1),
	array('cursing_filter', 0, 2, 'Determines the cursing filter (default is low) &&& None &&& Low &&& High', 1),
	array('planet_report', 0, 2, 'Decides whether a production report is returned from a planet during the daily maintenance. &&& Nothing returned &&& A report will be returned, but only if the planet produces something &&& All planets will return a report, no matter what.', 1)
);

$dat['gameoptionlist'] = array(
	array('admin_var_show', 1, 0, 1, 'If 0, players cannot see the game vars, on the game_vars page.'),
	array('allow_search_map', 1, 0, 1, 'Determines if users are allowed to run searches to try and find a system on the map (best left for the server admin to set).'),
	array('allow_signatures', 0, 0, 1, 'If set to 1, posts will have the signatures on them. Otherwise, they will be turned off.'),
	array('bomb_cost', 100000, 0, 10000000, 'Cost of the normal bombs: alpha/gamma. Other bombs will cost a multiple of this number.'),
	array('bomb_level_auction', 0, 0, 2, 'If set to 0, bombs cannot be purchased. 1 Gamma/Alpha.  3 Gamma/Alpha/Delta.'),
	array('bomb_level_shop', 0, 0, 2, 'If set to 0, bombs cannot be purchased. 1 Gamma/Alpha.  3 Gamma/Alpha/Delta.'),
	array('bilkos_time', 24, 6, 72, 'The amount of hours a player must hold the highest bid on an item to win it.'),
	array('buy_elect', 230, 1, 1000, 'Price Electronics can be brought for. Sell price is 20% less.'),
	array('buy_fuel', 90, 1, 1000, 'Price Fuel can be brought for. Sell price is 20% less.'),
	array('buy_metal', 80, 1, 1000, 'Price Metal can be brought for. Sell price is 20% less.'),
	array('buy_organ', 60, 1, 1000, 'Price Organics can be brought for. Sell price is 20% less.'),
	array('clan_member_limit', 5, 0, 100, 'Max number of players able to join a single clan.'),
	array('cost_colonist', 1, 0, 10000, 'Cost per colonist, as taken from Earth'),
	array('cost_genesis_device', 20000, 0, 100000, 'Cost of genesis devices.'),
	array('enable_superweapons', 1, 0, 1, 'Setting this to 0 will mean the terra maelstrom, and the omega missile will be turned off. <br>Useful if you want very big planets in your game, or if using the uv_planets variable for a limited planet count within the game.'),
	array('fighter_cost_earth', 100, 1, 10000, 'The cost to buy a fighter at earth.'),
	array('flag_planet_attack', 1, 0, 1, 'Planet attack flag.  When set to 0 planets can not be attacked.'),
	array('flag_sol_attack', 1, 0, 1, 'If set to 0 then attacking at Sol is dissallowed. Bombs are not allowed either.'),
	array('flag_space_attack', 1, 0, 1, 'Space attack flag.  When set to 0 ships can not be attacked.'),
	array('increase_shields', 5, 0, 100, 'Shield percentage regenerated per tick.'),
	array('increase_turns', 5, 0, 1000, 'Turns gained per tick.'),
	array('max_clans', 10000, 0, 10000, 'Max number of clans that can be created.'),
	array('max_players', 100000, 0, 100000, 'Max number of players that can be signed up in the game.'),
	array('max_ships', 100, 0, 1000, 'Max number of ships that a player can have.'),
	array('max_turns', 250, 10, 1000000, 'Max number of turns a player can have.'),
	array('min_before_transfer', 3, 0, 10000, 'Min number of days before players can transfer cash/ships.'),
	array('new_logins', 1, 0, 1, 'New login flag. When set to 0, new players cannot sign-up.'),
	array('attack_turn_cost_ship', 2, 0, 1000, 'Number of turns it takes to attack another ship.'),
	array('attack_turn_cost_planet', 10, 0, 1000, 'Number of turns it takes to attack a planet.'),
	array('planet_elect', 10, 1, 10000, 'The number of electronics a user gets produced from 50 assigned colonists using 10 metal and 10 fuel.'),
	array('planet_fighters', 50, 1, 10000, 'The number of fighters a user gets produced from 100 assigned colonists using 10 metals, fuels and electronics.'),
	array('planet_organ', 550, 1, 1000000, 'The number of colonists required to produce 1 unit of organics.'),
	array('rr_fuel_chance', 50, 0, 100, 'Chance that a star system will recieve random amount of fuel.'),
	array('rr_fuel_chance_max', 5000, 0, 1000000, 'Maximum amount of fuel that a system will recieve.'),
	array('rr_fuel_chance_min', 100, 0, 1000000, 'Minimum amount of fuel that a system will recieve.'),
	array('rr_metal_chance', 75, 0, 100, 'Chance that a star system will recieve random amount of metal.'),
	array('rr_metal_chance_max', 5000, 0, 1000000, 'Maximum amount of metal that a system will recieve.'),
	array('rr_metal_chance_min', 100, 0, 1000000, 'Minimum amount of metal that a system will recieve.'),
	array('score_method', 0, 0, 2, 'Decides method of scoring used. 0: scores are off. 1: Score is based on fighter kills and such like. 2: score is based on point value of ships killed and lost.'),
	array('ship_warp_cost', 1, -1, 1000, 'This var determines how much it costs for players to warp between systems.<br><br>Set it between 0 and 1000 to determine the number of turns,<br>OR<br>set it to -1, whereby a different system will be used, where different ship types take different numbers of turns to get to places. The bigger the ship the more turns it takes.'),
	array('start_cash', 5000, 0, 1000000, 'Amount of cash a player starts out with.'),
	array('start_ship', 5, 3, 6, 'Ship player starts in. 3 = SS, 4 = MF, 5 = ST, 6= HM'),
	array('start_turns', 40, 0, 1000000, 'Amount of turns a player starts out with.'),
	array('sudden_death', 0, 0, 1, 'When this is set to 1, players can never regenerate, nor can new players join the game.'),
	array('turns_before_attack', 50, 0, 1000, 'Turns that have to be used before a new account can attack ships.'),
	array('turns_before_planet_attack', 50, 0, 1000, 'Turns that a player has to use before they can attack/use planets.'),
	array('turns_safe', 50, 0, 1000, 'Turns that have to pass before a new player can be attacked.'),
	array('uv_fuel_max', 113205, 1, 1000000, 'Max amount of fuel in a star system when universe is generated.'),
	array('uv_fuel_min', 695, 1, 1000000, 'Min amount of fuel in a star system when universe is generated.'),
	array('uv_fuel_percent', 30, 0, 100, 'Percent of star systems that will have fuel when universe is generated.'),
	array('uv_map_graphics', 1, 0, 1, 'Whether to use graphics in the maps.'),
	array('uv_map_layout', 0, 0, 3, 'Choose the layout of the map.<p>0 = Random. 1 = Galactic Core. 2 = Clusters. 3 = Stars within a circle.'),
	array('uv_max_link_dist', 100, 0, 10000, 'Maximum distance a link between two star systems may be.  Setting this too low will result in most/all stars not being linked.'),
	array('uv_metal_max', 99835, 1, 1000000, 'Max amount of metal in a star system when universe is generated.'),
	array('uv_metal_min', 134, 1, 1000000, 'Min amount of metal in a star system when universe is generated.'),
	array('uv_metal_percent', 60, 0, 100, 'Percent of star systems that will have metal when universe is generated.'),
	array('uv_min_star_dist', 20, 20, 60, 'Minimum distance between star systems - in pixels.'),
	array('uv_num_bmrkt', 10, 0, 50, 'Sets number of blackmarkets created during Universe generation.'),
	array('uv_num_ports', 25, 0, 300, 'Number of star ports when universe is generated.'),
	array('uv_num_stars', 150, 10, 1000, 'Number of stars in the universe.'),
	array('uv_planets', -1, -1, 1000, 'This sets the maximum number of planet slots that may appear (randomly) in a system when the universe is generated. <br>This can be used in conjunction with <b>uv_planets</b> to create a universe that has both planets, and planetary slots.'),
	array('uv_planet_slots', 5, 0, 50, 'This sets the maximum number of planet slots that may appear (randomly) in a system when the universe is generated.<br>Note: If <b class=b1>uv_planets</b> is set to anything other than -1, this variable will be ignored.'),
	array('uv_planet_slots_use', 0, 0, 1, 'Set this to 1 to use planetary slots!'),
	array('uv_port_variance', 50, 0, 100, 'Amount of variance in prices at star ports. <*>'),
	array('uv_show_warp_numbers', 1, 0, 1, 'Show warp numbers.  When set to 0 warp numbers will not be shown on starmaps after universe is generated.'),
	array('uv_universe_size', 500, 200, 5000, 'Size in pixels of the universe.'),
	array('wormholes', 1, 0, 1, 'Set to 0 disable Wormholes or 1 to have them in the game'),
	array('process_cleanup', 3600, 1, 604800, 'The frequency of this processed task.'),
	array('process_turns', 3600, 1, 604800, 'The frequency of this processed task.'),
	array('process_systems', 3600, 1, 604800, 'The frequency of this processed task.'),
	array('process_ships', 3600, 1, 604800, 'The frequency of this processed task.'),
	array('process_planets', 86400, 1, 604800, 'The frequency of this processed task.'),
	array('process_government', 43200, 1, 604800, 'The frequency of this processed task.')
);

?>
