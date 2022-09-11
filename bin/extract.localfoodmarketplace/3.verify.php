<?php

$fp = fopen("output.csv", 'r');
while ($line = fgetcsv($fp, 65535))
		print_r($line);
