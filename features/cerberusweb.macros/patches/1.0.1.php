<?php
$db = DevblocksPlatform::getDatabaseService();
$tables = $db->metaTables();

// `automator_filter` =============================
if(!isset($tables['macro_action'])) {
	$db->Execute("ALTER TABLE macro_action ADD COLUMN source varchar(255) DEFAULT '' NOT NULL");	
}


return TRUE;