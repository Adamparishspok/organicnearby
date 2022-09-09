<?php

namespace sb;
class PageLocation extends \sb\IncludePageOrganicnearby
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
	if (!$locSource = retval($this->session, 'locSource'))
		$locSource = 'browser';
	// Default class is 'locBoxDisabled' except for the one that's active.
	$dis['addressLocClass'] = 'locBoxDisabled';
	$dis['latlonLocClass'] = 'locBoxDisabled';
	$dis['geoipLocClass'] = 'locBoxDisabled';
	$dis['browserLocClass'] = 'locBoxDisabled';
	$dis[$locSource . 'LocClass'] = '';
	// set the radio button.
	$dis[$locSource . 'Checked'] = 'CHECKED';

	$dis['locAddress'] = retval($this->session, 'locAddress');
	$dis['lat'] = retval($this->session, 'lat');
	$dis['lon'] = retval($this->session, 'lon');

	$geoip = $this->geoip();
	$dis['geoip_location'] = $this->getNearestCounty($geoip['lat'], $geoip['lon']);
	if ($dis['lat'] && $dis['lon'])
			{
			$dis['Map'] = [
				'lat' => $dis['lat'],
				'lon' => $dis['lon'],
				];
			}
	$dis['gps_js_ts'] = $this->whichts('gps/gps.js');
	return true;
	}


function page_setLocation()
	{
	$fnc = "setLocation".$this->Req('text', 'locBox');
	if (!is_callable([$this, $fnc]))
		return $this->error("Invalid location set request $fnc");
	if (!$return = $this->{$fnc}())
		return false;
	$dis = &$this->dis['SetLocation'];
	$dis['show'] = '';
	$this->nearestCounty();
	return $return;
	}

function setLocationAddress()
	{
	if (!$address = $this->req('text', 'address'))
		return $this->error("Address must be specified");
	$address = trim($address);
	if ($this->setByZip($address))
		return true;
	if (strlen($address) < 4)
		return $this->error("Valid addresses must be at least 4 characters long");
	$IG = new IncludeGeoapify($this->env);
	if (!$loc = $IG->addressTextToLatLon($address))
		return $this->error('No Lat/Lon results from resolution service');
	$this->session['lat'] = $loc['lat'];
	$this->session['lon'] = $loc['lon'];
	$this->session['locSource'] = 'address';
	$this->session['locAddress'] = $address;
	return true;
	}

function setLocationGeoIp()
	{
	$this->session['locSource'] = 'geoip';
	$data = $this->geoIP();
	$this->session['lat'] = $data['lat'];
	$this->session['lon'] = $data['lon'];
	return true;
	}

/**
 * If the user enters a US zip code, we'll use that preferentially.
 * @param type $address
 * @return boolean
 */
function setByZip($address)
	{
	// first, cancel out of all reasons it's not a zip code.
	if (strlen($address) != 5)
		return false;
	if (!is_numeric($address))
		return false;
	if ($address < 10000)
		return false;
	if ($address > 99999)
		return false;
	$find = ['zip' => $address ];
	if (!$ZIP = $this->DB->Find('zip', $find, 1))
		return false;
	if (!$loc = IncludeGet::Loc2array($ZIP->Get('location')))
		return false;
	$this->session['lat'] = $loc['lat'];
	$this->session['lon'] = $loc['lon'];
	$this->session['locSource'] = 'address';
	$this->session['locAddress'] = $address;
	return true;
	}

function setLocationLatLon()
	{
	if (!$this->LatLonReq2Sess())
		return false;
	$this->session['locSource'] = 'latlon';
	return true;
	}

function LatLonReq2Sess()
	{
	if (!$lat = $this->req('numeric', 'lat'))
		return $this->error("Latitude was not specified");
	if (!$lon = $this->Req('numeric', 'lon'))
		return $this->error("Longitude was not specified");
	$this->session['lat'] = $lat;
	$this->session['lon'] = $lon;
	return true;
	}

function setLocationBrowser()
	{
	if (!$this->LatLonReq2Sess())
		return false;
	unset($this->session['locSource']);
	return true;
	}

}
