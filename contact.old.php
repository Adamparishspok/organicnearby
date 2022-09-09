<?php

namespace sb;

class PageContact extends \sb\IncludePageOrganicnearby
{

var $secret = 'asdfvioe3fjhF893N';


var	$ciphering = "BF-CBC";

function init()
	{
	$this->getTableAndId();
	return true;
	}

function finit()
	{
	return true;
	}

function page_prompt()
	{
	$dis = &$this->dis['Prompt'];
	// who are we displaying?
	$dis['formid'] = rand(1, 2100000000);
	$this->displayRecipient($dis);
	$dis['code']=$this->makeCode();
	return true;
	}

function page_send()
	{
	$send = [
		'logo' => $this->env->get('settings', 'authroles', 'email_logo'),
		'company' => $this->env->get('settings', 'company'),
		'fromip' => $this->env->server['REMOTE_ADDR'],
		];
	if (!$send['name'] = $this->post('text', 'name'))
		return $this->error("Name is required");
	if (!$send['email'] = $this->post('email', 'email'))
		return $this->error("Email is required");
	if (!$send['subject'] = $this->post('text', 'subject'))
		return $this->error("Subject is required");
	if (!$send['message'] = $this->post('text', 'message'))
		return $this->error("Message is required");
	if (strlen($send['name']) < 2)
		return $this->error("Please enter your name");
	if (strlen($send['message']) < 3)
		return $this->error("Please leave a comment.");

	if (!$this->getRecipient($site_owners_email, $site_owners_name))
		return false;

	$from = 'noreply@'.$this->env->getDomains()[0];
	$from_name = $send['company'];

	$IM = New IncludeMessageContact($this);
	$IM->Prepare($send);
	$IM->setReplyTo($send['email'], $send['name']);

	// without the required code, we feign success cause it's a spammer
	// we might also check for one-time-use but that's not happening yet.
	if (!$this->checkCode($this->req('text', 'required')))
		return $this->reportSuccess($send, $site_owners_name, "IGNORED: Code check failed.");

	if (!$this->notAlreadySubmitted())
		return $this->reportSuccess($send, $site_owners_name, 'IGNORED: Form already Submitted');

	if ($this->tooMany())
		return $this->reportSuccess($send, $site_owners_name, "IGNORED: Too many messages sent in the last hour");

	if (!$this->req('int', 'formid'))
		return $this->reportSuccess($send, $site_owners_name, "IGNORED: Missing formid reference");

	if (!$this->saveMessage($send))
		return $this->error("Unable to save message");

	if (!$IM->send($site_owners_email, $from, $site_owners_name, $from_name))
		return $this->error("Unable to send message");
	
	return $this->reportSuccess($send,  $site_owners_name, "Success!");
	}

function notAlreadySubmitted()
	{
	$since = 60*60*24;
	$sql = "SELECT *
		FROM contact
		WHERE ts > [contact.ts]
		AND formid = [contact.formid]
		";
	$todb = ['contact' => ['ts' => $since, 'formid' => $this->req('text', 'formid') ]];
	if (!$res = $this->DB->SrQ($sql, $todb))
		return $this->error("Unable to query for submission status");
	if ($row = $res->Next())
		return $this->error("Previous submission found");
	return true;
	}

/**
 * Allows 5 messages per hour. If more than that, it cuts off the user.
 * Other algorithms may later be preferred
 */
function tooMany()
	{
	$since = time() - 60*60;
	$sql = "SELECT
			fromip,
			COUNT(*) AS count
		FROM contact
		WHERE fromip = [contact.fromip]
		GROUP BY fromip ";
	$todb['contact.fromip'] = $this->server['REMOTE_ADDR'];
	if (!$res = $this->DB->Srq($sql, $todb))
		return $this->Error("Unable to query for submission history");
	$row = $res->Next();
	return $row['count'] > 5;
	}

function reportSuccess($send,  $site_owners_name, $debug_message)
	{
	$dis = &$this->dis['Send'];
	$dis['to'] = $site_owners_name;
	$dis['fromName'] = $send['name'];
	$dis['fromAddr'] = $send['email'];
	$dis['subject'] = $send['subject'];
	$dis['message'] = $send['message'];
	if ($this->devMode())
		$dis['Actual']['debug_message'] = $debug_message;
	sleep(rand(1, 4));
	return true;
	}

function makeCode()
	{
	$hour = date('YmdH', time());
	$simple_string = "$hour.$this->secret";
	return sha1($simple_string);
	}

function checkCode($encryption)
	{
	// check current and previous hour
	$hours = [
		date('YmdH', time()),
		date('YmdH', time() - 60*60),
		];
	foreach($hours AS $hour)
		{
		$simple_string = "$hour.$this->secret";
		$check = sha1($simple_string);
		if ($encryption == $check)
			return true;
		}
	return false;
	}

function displayRecipient(&$dis)
	{
	$dis['name'] = $this->env->Get('settings', 'company');
	$dis['address'] = $this->env->Get('settings', 'address');
	$dis['Image']['img'] = $this->env->Get('authroles', 'email_logo');
	if (!$table = $this->req('text', 'table'))
		return true;
	if (!$table_id = $this->req('int', 'table_id'))
		return true;
	unset($dis['Image']);
	$ok = ['farmers', 'sc_farmers'] ;
	if (!in_array($table, $ok))
		return $this->error("Invalid access attempt");
	if (!$F = $this->DB->Find($table, $table_id, true))
		return true;
	$dis['name'] = $F->Get('name');
	$dis['address'] = $F->Get('address');
	if ($image = $F->Get('image'))
		$dis['Image']['img'] = "/img.php/$image";
	return true;
	}

function getrecipient(&$site_owners_email, &$site_owners_name)
	{
	$site_owners_email = 'bens@effortlessis.com'; // Replace this with your own hosting email address
	$site_owners_name = 'Benjamin Smith'; // replace with your name
	if (!$table = $this->req('text', 'table'))
		return true;
	if (!$table_id = $this->req('int', 'table_id'))
		return true;
	if (!$T = $this->DB->Find($table, $table_id, true))
		return true;
	if (!$T->Get('company_email'))
		return $this->error("Recipient address not found");
	$site_owners_email = $T->Get('company_email');
	$site_owners_name = $T->Get('name');
	return true;
	}


function saveMessage($send)
	{
	$C = $this->DB->create('contact', [], true);
	$save = [
		'fromip' => $send['fromip'],
		'ts' => time(),
		'tablename' => $this->Req('text', 'table'),
		'table_id' => $this->Req('int', 'table_id'),
		'name' => $send['name'],
		'email' => $send['email'],
		'subject' => $send['subject'],
		'message' => $send['message'],
		'formid' => $this->Req('int', 'formid'),
		];
	if (!retval($save, 'tablename'))
		$save['tablename'] = '';
	if (!retval($save, 'table_id'))
		$save['table_id'] = 0;
	if ($save['tablename'] && (!$save['table_id']))
		return $this->error("It shouldn't be possible to reference a table without a record id");
	if (!$C->Set($save))
		return $this->error($C);
	if (!$C->Save())
		return $this->error($C);
	return true;
	}

}
