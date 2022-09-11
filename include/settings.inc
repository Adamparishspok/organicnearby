<?php


$settings = [
//	'connstr' => null,
	'modules' => [
		'authgoogle',
		'authroles',
// fixme: re-integrate this code!
		'contactform',
		'geo',
		'kiraric',
		'phpmailer',
		],
#	'dfscontext' => 'organicnearby',
	'redo' => false,
	'company' => 'Organic Nearby',
	'product' => 'Organic Nearby',
	// fixme: domain should go away, prefer using $env->getDomains()[0] now.
	'domain' => 'organicnearby.effortlessis.com',
	'address' => 'Spokane, WA',
	'dev_email_override' => [		
		'yesthatguy+organicnearby@gmail.com',
		'juneswatzell@gmail.com',
		],
	// module settings
	'authroles' => [
		'use2fa' => false,
//		'email_logo' => 'https://airlot.app/images/airlot.logo.02.png',
		'email_logo' => 'https://organicnearby.com/images/OrganicNearbyLogo.png',
		// Allow public signup?
		'resetbutton' => true,
		'publicsignup' => true,
		'usetimezone' => false,
		],
	'phpmailer' => [
		'effortless' => [
			'Host' => 'host.effortlessis.com',
			'SMTPAuth' => true,
			'Username' => 'bsmith',
			'Password' => "fisk39ksiwCapital",
			'SMTPSecure' => 'starttls',
			'Port' => 587,
			],
		],
	/**
	 * This is used to encrypt/decrypt the SMS number in a browser cookie 
	 * so we know who it is that's joining us without having to ask for passwords.
	 */
//	'smskey' => 'KEY0fifjDB1FFC999DA62E72F65D5E41A_nvDlCtZofsOPZVmenPVzqb',
	];
