<?php

#DESCRIPTION Administration of farmers records.
namespace sb;
class PageAdminFarmers extends \sb\IncludePageOrganicnearby
{

function init()
	{
	return true;
	}

function finit()
	{
	return true;
	}
function page_prompt()
	{
	$dis = &$this->dis['Prompt'];
	$dis['search'] = $this->Req('text', 'search');
	$this->resolveLatLon($lat, $lon);
	$res = $this->farmerQuery($dis['search'], $lat, $lon, 'name');
	$shown= ['sc_sources_id', 'name', 'phone', 'website', 'company_email', 'address', ];
	$hi = true;
	$count = 1;
	if (!$offset = $this->req('int', 'offset'))
		$offset=0;
	$start = $offset;
	$finish = $start + 100;
	$total = $res->numrows();
	if ($finish > $res->numRows())
		$finish = $res->numRows();
	$dis['lat'] = $lat;
	$dis['lon'] = $lon;
	if ($res->numRows() > $finish)
		$dis['LinkNext'] =[
			'offset' => $finish,
			'search' => $this->req('text', 'search'),
			'lat' => $dis['lat'],
			'lon' => $dis['lon'],
			];
	$offset = ($start - 100 < 0) ? 0 : $start - 100;
	if ($start)
		$dis['LinkPrev'] = [
			'offset' => $offset,
			'search' => $this->req('text', 'search'),
			'lat' => $dis['lat'],
			'lon' => $dis['lon'],
			];
	$dis['count_msg'] = "Displaying $start to $finish of $total.";

	$fields = ['sc_sources_id', 'name', 'address', 'phone', 'website', 'general_info',
		'company_email', 'km', 'update_ts', 'externalid'];
	for ($rcount = $start; $rcount < $finish; $rcount++)
		{
		$irow = $res->Next($rcount);
		$row = array(
			'mylat' => $irow['mylat'],
			'mylon' => $irow['mylon'],
			'authlogins_id' => $irow['authlogins_id'],
			);
		foreach($fields AS $field)
			{
			if (strlen($tmp = retval($irow, "farmers_$field")))
				{
				$row[$field] = $tmp;
				if ($field == 'table')
					{}
				elseif (retval($irow, 'sc_farmers_id') == null)
					{
					$row[$field."class"] = 'createdLocal';
					}
				else
					{
					$row[$field."class"] = 'editedLocal';
					}
				}
			else
				{
				$row[$field] = retval($irow, "sc_farmers_$field");
				}
			}
		if ($row['mylat'] && $row['mylon'])
			$row['distance'] = number_format(IncludeGet::Km2Miles($row['km']), 1) . " miles away";
		$row['update_str'] = date('m/d/y H:i', $row['update_ts']);
		$row['hilo'] = ($hi = !$hi) ? 'hi' : 'lo';
		if ($irow['sc_farmers_id'])
			{
			$row['table'] = 'sc_farmers';
			$row['table_id'] = $irow['sc_farmers_id'];
			}
		else
			{
			$row['table'] = 'farmers';
			$row['table_id'] = $irow['farmers_id'];
			}
//			if ($row['table_id'] == 8)
//				dn($row);
		$dis['Farmers'][]=$row;
		$count++;
		}
	$dis['show']='';
	return true;
	}
}
