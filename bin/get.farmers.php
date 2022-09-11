<?php

// moved to on_functions.inc

function dn()
	{
	$as = func_get_args();
	print_r($as);
	die(1);
	}



$x = getFarmers();
$s = getScraped($x);
dn($s);

# https://meet.google.com/cgj-gjjn-zoi
