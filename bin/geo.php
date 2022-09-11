#! /usr/bin/php
<?php
if (!isset($_SERVER['argv'][1]))
	die("\nUsage: ./geo.php SITE\n");

$pwd = dirname(__FILE__);
$site = $_SERVER['argv'][1];
if (!realpath("$pwd/../../$site"))
	die("Site must exist: $site");

$cmd = "cd $pwd/../../../bin && ./runpage.php /geo.php modules[]=shell -site=$site -request=action[setFarmers]";
passthru($cmd);
