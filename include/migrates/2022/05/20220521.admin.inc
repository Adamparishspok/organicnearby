<?php

$sql[]="INSERT INTO admin (authlogins_id)
	SELECT id FROM authlogins WHERE login IN ('yesthatguy@gmail.com', 'jmswatzell@msn.com')";
