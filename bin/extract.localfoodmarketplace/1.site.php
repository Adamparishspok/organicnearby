<?php

while (true)
	{
	$file = "sitelocalfoodmarketplace.com.csv";
	if (file_exists($file))
		{
		$count=1;
		$done = false;
		while (!$done)
			{
			$to = "$file.$count";
			if (!file_exists($to))
				$done = true;
			else
				$count++;
			}
		rename($file, $to);
		echo "$to\n";
		}
	sleep(1);
	}
