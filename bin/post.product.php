<?php

// "version-test"
// "version-live"


function sendRecord()
{
$endpoint = "https://myorganicsnearby.bubbleapps.io/version-test/api/1.1/wf/update-price/initialize";
$endpoint = "https://myorganicsnearby.bubbleapps.io/version-test/api/1.1/wf/update-price";

$body = '{ 
"product-modal-name": "Oranges, Certified Organic - Tonnemaker Hill Farm",
"name": "Oranges",
"product_url": "https://lincfoods.localfoodmarketplace.com/Products",
"price": "52" }';

// got the "farm" from farm's "_id" field
$body = '{
"requestType" : "updateProduct",
"externalId" : "zyxha-54321",
"name" : "Durian Fruit",
"price":"44",
"description":"this is a yummy fruit",
"farmer" : "1606944280564x632401196012798000",
"scraped" : "yes",
}';

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
"stock" : "false",
"linkcart" : "https://mysite.com/products/1234"
}';


/*
"owner" : "I am the owner",
"owner_link" : "https://effortlessis.com",
### what is product cat? 
product cat ref 
"ships" : "Unused",
tags ref
"tagline" : "This is a tagline",
"slug" : "I am not a slug, laddie"
Creator ref
Modified Date date
Created Date date
*/

echo "Endpoint\n$endpoint\n";
echo "Sending\n$body\n";


//"farmer" : "1610813044878x242826769445421060"

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
	'Content-type: application/json',
	]);
$return = curl_exec($ch);
echo "Bubble platform response:\n";
print_r($return);
}
// Beef, Red

function getFarms()
	{
	$endpoint = "https://organicnearby.com/version-test/api/1.1/obj/farm";
	$body = '';
	$ch = curl_init($endpoint);
	curl_setopt($ch, CURLOPT_HTTPGET, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-type: application/json',
		]);
	$return = curl_exec($ch);
	print_r($return);
	}

//die(getFarms());
sendRecord();
