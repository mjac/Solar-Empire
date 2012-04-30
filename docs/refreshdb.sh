#!/bin/sh
/usr/local/mysql/bin/mysql -u solarempire --password=solarempire solarempire < sql/new_server.sql
/usr/local/mysql/bin/mysql -u solarempire --password=solarempire solarempire < sql/new_game.sql
