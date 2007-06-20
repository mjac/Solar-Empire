Welcome to the Solar Empire Source Code
Last updated: April 25th 2004 by Moriarty


Release Version 1.3


Solar Empire is pretty hard to get installed and working. This is partly to
discourage people from setting up their server if they have no idea what they're
doing.  And parttly because i havn't bothered with a installer. :)

First up you need a system that has everything installed, and then you need to
configure a LOT of different variables. Follow the instructions below, and if
you get to any grief ask one of the people running a server for some assistance.
They're usually willing to try to help. Either that or swear vehemently.
Good Luck.



Software required:

PHP: www.php.net
Mysql: www.mysql.com

phpMyAdmin: www.phpmyadmin.net (not necessary, but very helpful)

plus a Webserver:
Apache: httpd.apache.org (recommended)
PWS: Windows 98 SE cd
IIS: Win 2000, NT (with one of the service packs), XP Pro.


Installation Steps
------------------

0. Download & extract SE package.

1-a. Get PHP(with GD support), Perl and MySQL installed, as well as web server.

1-b. Ensure that PHP is compiled/set with register_globals=On. In windows it's
in the php.ini. If you don't do this you'll have al sorts of problems.

2. Get them talking. PHP with MySQL (this is done automatically usually) and
Perl with MySQL (this is more troublesome. You will probably be required to
install the DBI and DBD-mysql modules for perl. Consult the mysql manual
section M2.1).

3. Edit the config.dat.php file and set the vars there to your server
configeration.

4. Edit the dir_names.php file to reflect any changes you've made in directory
names.

5. Create your database. use the name username set in the config.dat.php file
(u did set that, rigth?). A program called phpMyAdmin is good for doing the
database stuff. Available from sourceforge.net/projects/phpmyadmin amoung other
places.

6. Insert the generic tables into the database. These are found in the sql
directory. They are new_server.sql and "se_svr_star_names.sql". This is where
phpmyadmin comes in useful

7-a. Insert a row into the se_games table, for the game u are about to create.
You will need to enter 1 row for each new game you create. Each Admin MUST have
a unique password. Else the login system won't be happy. :)
As a server admin you only need to fill in: 'name' (with whatever you want the
game to be called), 'password', with the admin password and 'db_name' with the
table prefix for that game. All the other lines are fine as they are, and can
be altered by the admin at any time. The default admin password is 'passwd'.

7-b. Then insert the new_game.sql into the database too. Change the "gamename_"
in this file to whatever you put the 'db_name' as in the se_games table. Use a
text editor's (i.e. notepad on win) 'find and replace' function for this. There
are lots of entries to replace, so don't bother doing it manually.

8-a. Within the images directory (or whatever you renamed it too), you'll need
to create a folder for each game you plan on running. this will contain the
images for the universe. the name of the folder should be of the format:
gamename_maps - with gamename being replaced by the game's db_name
Permissions should make this directory writeable by the server

9. You should be able to log in as admin to the game now.  (passwords both = 
passwd unless you changed them).

10. Set a scheduling program to run the maints. Crontab for linux, and probably
task scheduler for Win. Under linux, run the short bash scripts via the
schedular. These being 'hour' and 'day'. Point crontab at these, and alter these
to point to your maints. This get's rid of some of the problems with user's and
stuff.