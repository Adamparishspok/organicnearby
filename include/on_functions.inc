<?php

/**
 * Get Bubble existing records. See getScraped() for a very useful filter.
 * @param string $type one of farm|product
 * @return type
 */
namespace sb;
define('BUBBLESUB', 'version-live');
function getBubbleExisting($type='farm')
	{
	$sub = BUBBLESUB;
	$map = [
		'farm' => "https://organicnearby.com/$sub/api/1.1/obj/farm",
		'product' => "https://organicnearby.com/$sub/api/1.1/obj/product",
		];
	if (!isset($map[$type]))
		return $this->error("Unable to get URL from map for $type");
	$url = $map[$type];
	$return = [];
	$cursor = 0;
	$done = false;
	while (!$done)
		{
		$endpoint = "$url?cursor=$cursor";
		$in = '';
		$fp = fopen($endpoint, 'r');
		while ($line = fgets($fp, 65535))
			$in .= $line;
		fclose($fp);
		$j = json_decode($in, true);
		foreach($j['response']['results'] AS $result)
			$return[] = $result;
		if ($j['response']['remaining'] > 0)
			$cursor += (sizeof($j['response']['results']) -0);
		else
			$done = true;
		}
	return $return;
	}

function getScraped($all)
	{
	$return = [];
	foreach($all AS $x)
		{
		if (!isset($x['scraped']))
			{}
		elseif (!isset($x['externalId']))
			{}
		elseif (!$x['scraped'])
			{}
		elseif (!$x['externalId'])
			{}
		else
			$return[]=$x;
		}
	return $return;
	}

function sendRecordProduct($product)
	{
	$sub = BUBBLESUB;
	$endpoint = "https://myorganicsnearby.bubbleapps.io/$sub/api/1.1/wf/update-price/initialize";
	$endpoint = "https://myorganicsnearby.bubbleapps.io/$sub/api/1.1/wf/update-price";

	// working example from Thio
	$body = '{
		"address" : "1830 Mulberry Street, Chico, CA",
		"description": "Durian Fruit from Laos",
		"externalid" : "zyxha-54321",
		"farmer" : "1606944280564x632401196012798000",
		"featured" : "true",
		"name" : "Durian Fruits",
		"picture" : "http://effortlessis.com/images/logo.png",
		"price" : "20",
		"price unit" : "1 Bag of Fruit",
		"pricetxt" : "This is a price text fields",
		"scraped" : "true",
		"stock" : "false"
		}';

	$body = json_encode($product);
	$ch = curl_init($endpoint);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-type: application/json',
		]);
	$return = curl_exec($ch);
	return $return;
	}


function sendRecordFarmer($farmer)
	{
	$sub = BUBBLESUB;
	//$endpoint = "https://myorganicsnearby.bubbleapps.io/$sub/api/1.1/wf/createfarm/initialize";
	$endpoint = "https://myorganicsnearby.bubbleapps.io/$sub/api/1.1/wf/createfarm";

	// original submissions tested to work with Thiago
	// got the "farm" from farm's "_id" field
	$body = '{
	"address" : "4005 Cowell Blvd, Davis, CA 95618",
	"company email" : "ima@bogusfarms.com",
	"company name" : "We Farm and stuff",
	"general info" : "General info 3",
	"phone" : "1.234.234.5678",
	"website" : "https://bogusfarms.com",
	"externalId" : "bogusfarmsid.123abc",
	"scraped" : "true",
	"trashed" : "false"
	}';
	$body = json_encode($farmer, JSON_PRETTY_PRINT);
	en($endpoint, $body);

	$ch = curl_init($endpoint);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-type: application/json',
		]);
	$return = curl_exec($ch);
	return $return;
	}

