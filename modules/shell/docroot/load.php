<?php

namespace sb;
class PageLoad extends \sb\IncludePage
{

function init()
	{
	include_once $this->env->which('on_functions.inc', 'include');
	return true;
	}

function finit()
	{
	return true;
	}

function page_prompt()
	{
	$dis = &$this->dis['Prompt'];
	if (true)
		{
		if (!$this->uploadFarmers())
			return $this->error("Unable to upload farmers");
		}
	if (!$this->uploadProducts())
		return $this->error("Unable to upload products");
	die("Upload Finished Successfully");
	return true;
	}

function uploadFarmers()
	{
	$sql="SELECT
			sc_farmers.source,
			max(update_ts) AS update_ts
		FROM sc_farmers
		GROUP BY source
		";
	$res = $this->DB->Query($sql);
	$maxf = [];
	while ($row = $res->Next())
		$maxf[$row['source']] = $row['update_ts'];

	$sql="SELECT
			farmers.id AS farmers_id,
			farmers.source AS farmers_source,
			farmers.name AS farmers_name,
			farmers.address AS farmers_address,
			farmers.phone AS farmers_phone,
			farmers.website AS farmers_website,
			farmers.general_info AS farmers_general_info,
			farmers.company_email AS farmers_company_email,
			farmers.externalid AS farmers_externalid,
			farmers.insert_ts AS farmers_insert_ts,
			farmers.update_ts AS farmers_update_ts,
			farmers.bubbleid AS farmers_bubbleid,
			sc_farmers.id AS sc_farmers_id,
			sc_farmers.source AS sc_farmers_source,
			sc_farmers.name AS sc_farmers_name,
			sc_farmers.address AS sc_farmers_address,
			sc_farmers.phone AS sc_farmers_phone,
			sc_farmers.website AS sc_farmers_website,
			sc_farmers.general_info AS sc_farmers_general_info,
			sc_farmers.company_email AS sc_farmers_company_email,
			sc_farmers.externalid AS sc_farmers_externalid,
			sc_farmers.insert_ts AS sc_farmers_insert_ts,
			sc_farmers.update_ts AS sc_farmers_update_ts,
			sc_farmers.bubbleid AS sc_farmers_bubbleid
		FROM sc_farmers
		LEFT OUTER JOIN farmers ON
			(
			sc_farmers.externalid = farmers.externalid
			)
		ORDER BY sc_farmers.update_ts ASC";
	$res = $this->DB->Query($sql);
//	echo "<pre>";
	$update_ts = 0;
	$farmers = [];
	while ($row = $res->Next())
		{
		// most recent records first. Farmers without products won't get updated.
		// In theory. *cough*
		if (!$update_ts)
			$update_ts = $row['sc_farmers_update_ts'];

		// trashed if this record was last updated before the most recent scrape event.
		$trashed = ($row['sc_farmers_update_ts'] >= $maxf[$row['sc_farmers_source']])
			? 'false' : 'true';
		// OVERRIDE: we always show growers. I don't like this, personally.
		$trashed = 'false';
		$farmer = [
			"address" => is_null($row['farmers_address']) ?
				$row['sc_farmers_address'] : $row['farmers_address'],
			"company email" => is_null($row['farmers_company_email']) ?
				$row['sc_farmers_company_email'] : $row['farmers_company_email'],
			"company name" => is_null($row['farmers_name']) ?
				$row['sc_farmers_name'] : $row['farmers_name'],
			"general info" => is_null($row['farmers_general_info']) ?
				$row['sc_farmers_general_info'] : $row['farmers_general_info'],
			"phone" => is_null($row['farmers_phone']) ?
				$row['sc_farmers_phone'] : $row['farmers_phone'],
			"website" => is_null($row['farmers_website']) ?
				$row['sc_farmers_website'] : $row['farmers_website'],
			"externalId" => is_null($row['farmers_externalid']) ?
				$row['sc_farmers_externalid'] : $row['farmers_externalid'],
			"scraped" => "true",
			"trashed" => $trashed,
			];
		if (!$this->devmode())
			{
			if (!$return = SendRecordFarmer($farmer))
				return $this->error("Unable to address farmer $farmer[externalid]");
			$result = json_decode($return, true);
			if ($result['status'] != 'success')
				return $this->error("Invalid response for farmer $farmer[externalid]");
			}
		print_r($farmer);
		}
	return true;
	}

function getScProductsUpdateTs()
	{
	$sql="SELECT MAX(sc_products.update_ts) AS update_ts,
		sc_farmers.source AS source
		FROM sc_farmers JOIN sc_products ON
			(
			sc_farmers.id = sc_products.sc_farmers_id
			)
		GROUP BY sc_farmers.source";
	if (!$res = $this->DB->Query($sql))
		return $this->error("Unable to query for scrape product max upload times");
	$maxp = [];
	while ($row = $res->Next())
		$maxp[$row['source']]=$row['update_ts'];
	return $maxp;
	}

function uploadProducts()
	{
	$maxp = $this->getScProductsUpdateTs();
	if (!$server_name = retval($this->server, 'SERVER_NAME'))
		return $this->error("Invalid server name in ENV");
	$scraped = getScraped(getBubbleExisting('farm'));
	$fbyx = [];
	foreach($scraped AS $f)
		$fbyx[$f['externalId']]=$f;
	$sql="SELECT
		sc_farmers.externalid,
		sc_farmers.source AS sc_products_source,
		sc_products.id AS sc_products_id,
		sc_products.sc_farmers_id AS sc_products_sc_farmers_id,
		sc_products.name AS sc_products_name,
		sc_products.price::numeric AS sc_products_price,
		sc_products.pricetxt AS sc_products_pricetxt,
		sc_products.price_unit AS sc_products_price_unit,

		CASE WHEN sc_products.address IS NULL OR sc_products.address = ''
			THEN sc_farmers.address ELSE sc_products.address
			END AS sc_products_address,

		sc_products.description AS sc_products_description,
		sc_products.image AS sc_products_picture,
		sc_products.stock AS sc_products_stock,
		sc_products.externalid AS sc_products_externalid,
		sc_products.insert_ts AS sc_products_insert_ts,
		sc_products.update_ts AS sc_products_update_ts,
		sc_products.bubbleid AS sc_products_bubbleid,
		sc_products.linkcart AS sc_products_linkcart,

		products.id AS products_id,
		null					AS products_source,
		products.farmers_id AS products_farmers_id,
		products.name AS products_name,
		products.price::numeric AS products_price,
		products.pricetxt AS products_pricetxt,
		products.price_unit AS products_price_unit,
		products.address AS products_address,
		products.description AS products_description,
		products.image AS products_picture,
		products.stock AS products_stock,
		products.externalid AS products_externalid,
		products.insert_ts AS products_insert_ts,
		products.update_ts AS products_update_ts,
		products.bubbleid AS products_bubbleid,
		products.linkcart AS products_linkcart
	FROM sc_products
	JOIN sc_farmers ON
		(
		sc_products.sc_farmers_id = sc_farmers.id
		)
	LEFT OUTER JOIN products ON
		(
		sc_products.externalid = products.externalid
		)";
	$res = $this->DB->Query($sql);
	$count = 0;
	while ($row = $res->Next())
		{
		$p = $sp = [];
		foreach($row AS $f => $val)
			{
			if (substr($f, 0, 12) == 'sc_products_')
				{
				$sub = substr($f, 12);
				$sp[$sub] = $val;
				}
			elseif (substr($f, 0, 9) == 'products_')
				{
				$p[substr($f, 9)] = $val;
				}
			else
				{
				//en($f);
				}
			}
		$ref = null;
		$p['farmer'] = null;
		if (isset($fbyx[$row['externalid']]))
			{
			$ref = $fbyx[$row['externalid']];
			$sp['farmer'] = $ref['_id'];
			}
		else
			{
			$sp['farmer'] = null;
			}
		$fields = [
			'source',
			'update_ts',
			'address',
			'description',
			'externalid',
			'farmer',
			'name',
			'price',
			'pricetxt',
			'price_unit',
			'picture',
			'stock',
			'linkcart',
			];
		$product =[
//			'pricetxt' => 'Price Text',
//			'price unit' => 'Price Unit',
			'scraped' => 'true',
			'featured' => 'false',
			];
		foreach($fields AS $field)
			{
			if (!is_null($p[$field]))
				$product[$field] = $p[$field];
			else
				$product[$field] = $sp[$field];
			}
		// COLLAPSE NUMBER TO BOOL
		$product['stock'] = $product['stock'] ? 'true' : 'false';
		if (IncludeGet::toBool($product['stock']))
			{
//			if ($product['update_ts'] < $maxp[$product['source']])
//				$product['stock'] = 'false';
			}
			
		if ($product['picture'])
			$product['picture'] = "https://$server_name/img.php/$product[picture]";
		if (!$this->devmode())
			{
			if (!$return = sendRecordProduct($product))
				return $this->error("Unable to send product !");
			if (!$j = json_decode($return, true))
				return $this->error("Unable to decode response");
			if (retval($j, 'status') != 'success')
				return $this->error("Invalid status from response");
			}
		print_r($product);
		$count++;
		}
	return $count;
	}

}
