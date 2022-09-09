<?php

namespace sb;

class PageFarmers extends \sb\IncludePageOrganicnearby
{

protected $perPage = 50;

function init()
	{
	$this->env->dis['farmers_active'] = 'active';
	return true;
	}

function page_prompt()
	{
	$dis = &$this->dis['Prompt'];
	if($this->SEC->GetRoles('page', 'admin'))
		{
		$this->setSources($dis);
		$sc_sources_id = $this->req('int', 'sc_sources_id');
		}
	else
		$sc_sources_id = null;
	$dis['gps_js_ts'] = $this->whichTs('gps/gps.js');
	$dis['show'] = '';
	$this->resolveLatLon($lat, $lon);
	if (!$order = $this->req('text', 'order'))
		$order = 'name';
	$ofield = "orderChecked" . ucfirst($order);
	$dis[$ofield] = 'CHECKED';
	$dis['search'] = $this->req('text', 'search');
	$res = $this->farmerQuery($dis['search'], $lat, $lon, $order, $sc_sources_id);
	$offset = floor($this->req('int', 'offset'));
	$limit = $res->numrows();
	if ($limit > $offset + $this->perPage)
		$limit = $offset + $this->perPage;

	$fields = ['sc_sources_id', 'authlogins_id', 'name', 'address', 'phone', 'website',
		'general_info', 'km', 'image', 'company_email', 'lat', 'lon'];
	$count = $offset+1;
	$next = $offset;
	while ($next >=0 && $iRow = $res->Next($next))
		{
		$next++;
		if ($next >= $limit)
			$next = -1;
		$row = [];
		foreach($fields AS $field)
			{
			if ($val = retval($iRow, "farmers_$field"))
				$row[$field] = $val;
			else
				$row[$field] = retval($iRow, "sc_farmers_$field");
			}
		$row['.address'] = nl2br(htmlentities($row['address']));
//		$row['sanitized_email'] = $this->sanitizeEmail($row['company_email']);
		$row['table'] = $iRow['authlogins_id'] ? 'farmers' : 'sc_farmers';
		$row['table_id'] = $iRow[$row['table'].'_id'];
		$row['urlname'] = $this->urlify($row['name']);
		if ($row['km'])
			$row['distance'] = number_format(IncludeGet::km2miles($row['km']), 1) . ' miles away';
//		$row['miles']
		if ($row['lat'] && $row['lon'])
			$row['MapLink'] = [
				'lat' => $row['lat'],
				'lon' => $row['lon'],
				];
		$row['count'] = $count;
		$dis['Companies'][]=$row;
		$count++;
		}
	// set the next/back
	$vars = $this->request;
	if ($offset >= $this->perPage)
		{
		$dis['PrevLink'] = [
			'perPage' => $this->perPage,
			'vars' => http_build_query(array_merge($vars, ['offset' => $offset - $this->perPage])),
			];
		}
	if ($count < $res->numRows())
		{
		$dis['NextLink'] = [
			'perPage' => $this->perPage,
			'vars' => http_build_query(array_merge($vars, ['offset' => $offset + $this->perPage])),
			];
		}
	$dis['start'] = $offset+1;
	$dis['count'] = $count-1;
	$dis['numRows'] = number_format($res->numRows());
	return true;
	}

function setSources(&$dis)
	{
	$sql="SELECT
			sc_sources.id,
			sc_sources.source,
			count(*)
		FROM sc_sources
		JOIN sc_farmers ON
			(
			sc_sources.id = sc_farmers.sc_sources_id
			)
		GROUP BY
			sc_sources.id,
			sc_sources.source
		ORDER BY
			LOWER(source) ASC";
	$res = $this->DB->Query($sql);
	$sc_sources_id = $this->req('int', 'sc_sources_id');
	while ($row=$res->Next())
		{
		if ($sc_sources_id == $row['id'])
			$row['selected'] = 'SELECTED';
		$dis['Sources']['Source'][]=$row;
		}
	return true;
	}

function SanitizeEmail($email)
	{
	$e = explode('@', strtolower($email));
	$f = ['', ''];
	$f[0] = $this->obfString($e[0]);

	// last half of the subdomain, leave the root domain alone.
	$lpos = strrpos($e[1], '.');
	$domain = substr($e[1], 0, $lpos);
	$newDomain = $this->obfString($domain);
	$f[1] = "$newDomain".substr($e[1], $lpos);
	// restore the email address!
	return implode('@', $f);
	}

	function obfString($str)
	{
	$len = strlen($str);
	$half = floor($len/2);
	$ret = substr($str, 0, $half);
	$ret = str_pad($ret, $len, '*', STR_PAD_RIGHT);
	return $ret;
	}

}
