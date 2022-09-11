#! /usr/bin/php
<?php

### begin doing stuffs
if (!isset($_SERVER['argv'][1]))
	die("Usage: scrape.extract.php DOMAIN
EG: scrape.extracted.php t.organicnearby.effortlessis.com OPTIONS

OPTIONS k=v pairs passed to scrape.php
	cache=3600
");
$domain = $_SERVER['argv'][1];

$pwd = dirname(__FILE__);
include "$pwd/scrape.functions.php";
$o = getOptions();
$options = '';
foreach($o AS $k => $v)
	$options.="$k=$v ";

$fp = fopen("$pwd/extract.localfoodmarketplace/output.csv", 'r');
$h = [];
$pwd = dirname(__FILE__);
$skip = [
	'www.localfoodmarketplace.com',
	'home.localfoodmarketplace.com',
//	'lincfoods.localfoodmarketplace.com',
//	'thefarmersmarketplace.localfoodmarketplace.com',
	];

while($line = fgetcsv($fp, 65535))
	{
	if ($h)
		{
		$x = parse_url($line[1]);
		if (!in_array($x['host'], $skip))
			{
			$cmd = "cd $pwd && ./scrape.php $domain many $x[host] $options";
			echo "$cmd\n";
			PassThru($cmd);
			}
		}
	else
		$h = $line;
	}
