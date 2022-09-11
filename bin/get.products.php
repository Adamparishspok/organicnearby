<?php


$endpoints = [
	"https://organicnearby.com/version-test/api/1.1/obj/product",
	"https://organicnearby.com/version-test/api/1.1/obj/product?cursor=90",
	];
	

$in = '';
foreach($endpoints AS $endpoint)
	{
	$fp = fopen($endpoint, 'r');
	while ($line = fgets($fp, 65535))
		$in .= $line;
	}

echo $in;

# https://meet.google.com/cgj-gjjn-zoi
