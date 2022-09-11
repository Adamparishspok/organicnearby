<?php

namespace sb;
class PageGeo extends \sb\IncludePage
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
	return true;
	}

function Page_setFarmers()
	{
	$this->renderTo('json');
	$find = [];
	$o=['where' => 'location IS NULL OR address IS DISTINCT FROM location_addr' ];
	$this->DB->Transaction('begin', 'setFarmers');
	$this->DB->Verbose(true);
	$Farmers = $this->DB->Find('sc_farmers', $find, null, $o);
	$IG = new IncludeGeoapify($this->env);
	$count = 0;
	while ($Farmer = $Farmers->Fetch())
	 if ($this->hasAnAddress($Farmer))
		{
		$Source = $this->DB->Find('sc_sources', $Farmer->get('sc_sources_id'), true);
		$Action = [
			'source' => $Source->Get('source'),
			'name' => $Farmer->Get('name'),
			'Address' => $Farmer->Get('address'),
			'error' => '',
			'success' => 0,
			];
		if (!$text = $Farmer->Get('address'))
			$Action['error'] = 'Farmer address field is blank';
		elseif (!$x = $IG->addressTextToLatLon($text))
			$Action['error'] = 'No Lat/Lon results from resolution service';
		elseif (!$loc = implode(",", $x))
			$Action['error'] = 'Lat/Lon results are not in expected format';
		elseif (!$set = [ 'location' => $loc, 'location_addr' => $text] )
			$Action['error'] = 'How did this happen on line ' . __LINE__; 
		elseif (!$Farmer->set($set))
			$Action['error'] = $Farmer->error();
		elseif (!$Farmer->Save())
			$Action['error'] = $Farmer->error();
		else
			$Action['success'] = 1;
		$this->json[]=$Action;
		// rate limit: 4 per second max.
		usleep(250000);
		$count++;
		if (false && $this->devmode())
		 if ($count > 2)
			{
			return $this->DB->Transaction('commit', 'setFarmers');
			}
		}
	return $this->DB->Transaction('commit', 'setFarmers');
	}

/**
 * Used to filter out bogus addresses that consist of a single comma or other
 * gibberish / empty results.
 * @param type $Farmer
 * @return boolean
 */
function hasAnAddress($Farmer)
	{
	$address = $Farmer->get('address');
	$match="/[^a-zA-Z0-9]/";
	$x = trim(preg_replace($match, '', $address));
	if (!$x)
		return false;
	if (strlen($x) < 5)
		return false;
	return true;
	}

}
