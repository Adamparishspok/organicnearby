<?php

namespace sb;
trait OrmSc_farmersTraits
{

function on_presave()
	{
	if (!$this->get('insert_ts'))
		$this->set('insert_ts', time());
	$this->set('update_ts', time());
	if ($this->get('address') == $this->get('location_addr'))
		return true;
	if (!$this->get('address'))
		return true;
	$IG = new IncludeGeoapify($this->env);
	if (!$loc = $IG->addressTextToLatLon($this->get('address')))
		return true;
	if (!$this->set('location', implode(",", $loc)))
		return $this->error("Unable to set discovered location");
	if (!$this->set('location_addr', $this->get('address')))
		return $this->error("Unable to set location address");
	return true;
	}

}

class IncludeOrmFactoryresultSc_farmers extends IncludeOrmFactoryresult
{
use OrmSc_farmersTraits;
}

class IncludeOrmCrudSc_farmers extends IncludeOrmCrud
{
use OrmSc_farmersTraits;

/**
 *
 * @return boolean
 */
function isClaimed()
	{
	if (!$externalId = $this->get('externalid'))
		return $this->error("It should never happen that a scraped record lacks an externalid");
	$find = [ 'externalid' => $externalId ];
	if (!$Farmer = $this->DB->Find('farmers', $find, 1))
		return false;
	return $Farmer->Get('authlogins_id');
	}

}