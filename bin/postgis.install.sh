#! /bin/sh

if [ `whoami` != "root" ] ; then
	echo "You must be r00t to run this!";
	fi;

echo "https://people.planetpostgresql.org/devrim/index.php?/archives/107-Installing-PostGIS-3.1-and-PostgreSQL-13-on-CentOS-8.html";
echo yum install postgis32_13.x86_64 postgis32_13-client.x86_64 postgis32_13-devel.x86_64 postgis32_13-docs.x86_64 postgis32_13-utils.x86_64
echo "echo 'Create extension postgis' | psql -U postgres organicnearby";

echo;
echo;
echo;

echo https://postgis.net/workshops/postgis-intro/geography.html
echo "create table airports(id serial, title varchar, lat numeric, lon numeric, geog GEOGRAPHY(point))";
echo "insert into airports(title, lat, lon) values
	('LAX', 33.9434, -118.4079),
	('CDG', 49.083, 2.5559),
	('KEF', 63.9850, -22.6056),
	('CIC', 39.79528, -121.85833)";
echo "update airports set geog = point(lat, lon)::geometry ;";

echo "select a.title as a, b.title AS b, st_distance(a.geog, b.geog)/1000 as distance from airports a join airports b on (a.id <> b.id and a.id <= b.id);";
