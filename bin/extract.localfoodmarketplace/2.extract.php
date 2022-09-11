#! /usr/bin/php
<?php

FUNCTION extractDomains($file, &$domains)
	{
	if (!$fp = fopen($file, 'r'))
		die("Unable to open $file for read");
	while ($line = fgetcsv($fp, 65535, ';'))
	 if (is_numeric($line[0]))
		{
		$row = [];
		foreach($h AS $k => $title)
			$row[$title]=$line[$k];
		$x = parse_url($line[1]);
		$row['domain'] = $x['host'];
		$domains[$x['host']]=$row;
		}
	 else
		{
		$line[]='Domain';
		$h = $line;
		$domains[0] = $h;
		}
	ksort($domains);
	return true;
	}

### BEGIN DOING STUFFS ###

if (!isset($_SERVER['argv'][1]))
	die("Usage:

extract.php filename.csv

(where there are many filename.csv.1, filename.csv.2, etc in PWD)

");

$fbase = $_SERVER['argv'][1];
$count = 1;

$done = false;
$domains = [];
while (!$done)
	{
	$file = "$fbase.$count";
	if (!file_exists($file))
		$done = true;
	else
		{
		echo "$file\n";
		extractDomains($file, $domains);
		$count++;
		}
	}

$ofp = fopen("output.csv", 'w');
foreach($domains AS $domain => $dline)
	{
	fputcsv($ofp, $dline);
	print_r($dline);
	}

