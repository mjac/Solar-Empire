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

?>
