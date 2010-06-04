<?php
$db = DevblocksPlatform::getDatabaseService();
$logger = DevblocksPlatform::getConsoleLog();
$tables = $db->metaTables();

// ===========================================================================
// Fix CRLF and tabs in ticket subjects

$db->Execute("update ticket set subject = replace(replace(replace(subject,\"\\n\",' '),\"\\r\",' '),\"\\t\",' ') where subject regexp \"(\\r|\\n|\\t)\"");

if(!isset($tables['contact_org']))
	return FALSE;
	
list($columns, $indexes) = $db->metaTable('contact_org');

if(!isset($columns['parent_id'])) {
	$db->Execute("ALTER TABLE contact_org ADD COLUMN parent_id INT UNSIGNED DEFAULT 0 NOT NULL");
}

return TRUE;
