<?php

$sql[]="CREATE TABLE sc_sources (
	id SERIAL PRIMARY KEY,
	source VARCHAR NOT NULL,
	script VARCHAR NOT NULL,
	UNIQUE(source, script)
	)";
$sql[]="UPDATE sc_farmers SET source = 'lincfoods' WHERE source = 'linc'";
$sql[]="UPDATE sc_farmers SET source = 'thefarmersmarketplace' WHERE source = 'tfm'";

$sql[]="INSERT INTO sc_sources (source, script)
	SELECT DISTINCT source, 'scrape.php' FROM sc_farmers";
$sql[]="ALTER TABLE sc_farmers
	ADD sc_sources_id INTEGER REFERENCES sc_sources(id)";
$sql[]="UPDATE sc_farmers
	SET sc_sources_id = sc_sources.id
	FROM sc_sources
	WHERE sc_sources.source = sc_farmers.source";
$sql[]="ALTER TABLE sc_farmers DROP source";

$sql[]="ALTER TABLE farmers ADD sc_sources_id INTEGER REFERENCES sc_sources(id)";
$sql[]="ALTER TABLE farmers DROP source";