<?php

namespace sb;

class PageNewsletter extends \sb\IncludePage
{

function init()
	{
	$this->env->dis['newsletter_active'] = 'active';
	return true;
	}

function finit()
	{
	return true;
	}

function page_prompt()
	{
	$dis = &$this->dis['Prompt'];
	$dis['show']='';
	return true;
	}

function page_nlsave()
	{
	if (!$email = $this->req('email', 'email'))
		return $this->error("Sorry, this address doesn't pass validation tests");
	$email = strtolower(trim($email));
	$find = [ 'email' => $email ];
	$NL = $this->DB->FindOrCreate('newsletter',$find, true);
	if (!$NL->Get('id'))
		{
		$NL->Set('inserted_ts', time());
		}
	$NL->Set('stopped_ts' , null);
	$NL->Save();
	$dis = &$this->dis['NlSave'];
	$dis['show'] = '';
	return true;
	}

}
