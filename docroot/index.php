<?php

namespace sb;

class PageIndex extends \sb\IncludePageOrganicnearby
{

function init()
	{
	$this->env->dis['home_active'] = 'active';
	return true;
	}

function finit()
	{
	return true;
	}

function page_prompt()
	{
	$putFood = '';
	if (array_key_exists('search', $this->request))
		{
		if (!$this->page_search())
			return false;
		$putFood = $this->env->render($this);
		$this->dis = [];
		}
	elseif ($this->resolveLatLon($lat, $lon))
		{
		if (!$this->page_search())
			return false;
		}
	elseif(!$this->page_search('distance', 10))
		return false;
	$dis = &$this->dis['Prompt'];
	$dis['show']='';
	if ($dis['.putFood'] = $putFood)
		{
		$dis['index_pageintro_hidden'] = 'hideme';
		}
//	dn($this->env->session);
	if (!$this->SEC->Get('login'))
		$dis['Signup']['show'] = '';
	$dis['search'] = $this->req('text', 'search');
	$dis['gps_js_ts'] = $this->whichts('gps/gps.js');
	$dis['locAddress'] = retval($this->env->session, 'locAddress');
	$this->renderTo('html');
	return true;
	}

function page_search($orderby='distance', $count=50)
	{
	$search = $this->req('text', 'search');
	$this->resolveLatLon($lat, $lon);
	$res = $this->ProductSearch($lat, $lon, $search, '', [], $orderby);
	$dis = &$this->dis['Search'];
	$this->renderTo('noshell');
	$fields = [ 'name', 'price', 'address', 'description', 'image', 'stock',
		'lat', 'lon', 'farmername'];
	while (($count-- > 0) && $iRow = $res->Next())
		{
		$row = [];
		foreach($fields AS $field)
			{
			// products table always comes before scraped products table.
			if ($value = retval($iRow, "products_$field"))
				{}
			elseif ($value = retval($iRow, "sc_products_$field"))
				{}
			elseif ($value = retval($iRow, "farmers_$field"))
				{}
			elseif ($value = retval($iRow, "sc_farmers_$field"))
				{}
			$row[$field]=$value;
			}
		if ($km = retval($iRow, 'products_km'))
			{}
		elseif ($km = retval($iRow, 'sc_products_km'))
			{}
		elseif ($km = retval($iRow, 'farmers_km'))
			{}
		else
			$km = retval($iRow['sc_farmers_km']);
		$distancetail = ($locAddress = retval($this->session, 'locAddress'))
			 ? 'from '. $locAddress
				: '';
		if ($km)
			$row['distance'] = number_format(IncludeGet::km2miles($km), 1)." miles $distancetail";
		if ($products_id = retval($iRow, 'sc_products_id'))
			{
			$row['source'] = 'sc_products';
			$row['id'] = $products_id;
			}
		else
			{
			$row['source'] = 'products';
			$row['id'] = retval($iRow, 'products_id');
			}
		$row['linkDescription'] = $this->urlify($row['name']);
		if ($row['price'] == '-$1.00')
			$row['price'] = '$--';
		$dis['Products'][]=$row;
		}
	if (!retval($dis,'Products'))
		$dis['NoProducts']['show'] = '';
	return true;
	}

}

/***
 * Notes:
 * 1) Edit local farmer record via admin interface:
 *	A) Unable to edit email address / authlogins after creation.
 *	B) See their products, unable to add products.
 *	C) See their products, shows red instaed of blue.
 */
