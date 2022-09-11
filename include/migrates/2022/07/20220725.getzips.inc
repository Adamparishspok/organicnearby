<?php

$sql[]="CREATE TABLE zip (
	id SERIAL PRIMARY KEY,
	zip INTEGER NOT NULL UNIQUE,
	location GEOGRAPHY(POINT) NOT NULL,
	title VARCHAR NOT NULL
	)";

$file = dirname(__FILE__) . "/20220725.getzips.csv";
if (!file_exists($file))
	die("Unable to find file");
$ifp = fopen($file, 'r');
while ($line = fgetcsv($ifp, 65535))
	{
	$lat = $line[1];
	$lon = $line[2];
	$title = pg_escape_string($line[3]);
	$sql[]="INSERT INTO zip (
		zip, location, title) 
		VALUES 
		($line[0], point($lon, $lat)::geometry, '$title')";
	}
//$sq[] = "delete from zip";