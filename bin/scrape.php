#! /usr/bin/php
<?php
/**
 * FIMXE:
 *	1) sc_farmers.externalid not unique by source.
 *	2) sc_products.grower dual foreign key (externalid, src)
 * @param type $url
 * @return type
 */

function usage()
	{
	$usage = "Usage:\
scrape.php SITENAME VendorDef OPTIONS

EG:
scrape.php t.organicnearby.com lincfoods

OPTIONS given in key=value pairs. EG:
	cache=3600 Used cached data if less than 3600 seconds old.

";
	if (!$_SERVER['argv'][2])
		die($usage."Site and/or vendor not specified\n");
	$rpath = realpath(dirname(__FILE__)."/../../");
	if (!is_dir($rpath . '/'.$_SERVER['argv'][1]))
		die($usage."Path not found for site given");
	$vendor = $_SERVER['argv'][2];
	$file = __DIR__."/class.vendor.$vendor.php";
	if (!file_exists("Handler not found for $vendor"));
	include_once $file;
	return $vendor;
	}

### BEGIN SRS BSNS ###
$vendor = Usage();
$pwd = dirname(__FILE__);
include "$pwd/scrape.functions.php";
include "$pwd/simplehtmldom/simple_html_dom.php";
$vendor::Vendor();

