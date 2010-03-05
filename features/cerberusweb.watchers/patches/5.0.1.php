<?php

$db = DevblocksPlatform::getDatabaseService();
$tables = $db->metaTables();

// ===========================================================================
// New watcher_filter table

if(!isset($tables['watcher_filter'])) {
	$sql = "
		CREATE TABLE IF NOT EXISTS watcher_filter (
			id INT UNSIGNED DEFAULT 0 NOT NULL,
			pos SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
			name VARCHAR(128) DEFAULT '' NOT NULL,
			created INT UNSIGNED DEFAULT 0 NOT NULL,
			worker_id INT UNSIGNED DEFAULT 0 NOT NULL,
			criteria_ser MEDIUMTEXT,
			actions_ser MEDIUMTEXT,
			is_disabled TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=MyISAM;
	";
	$db->Execute($sql);	
}
list($columns, $indexes) = $db->metaTable('watcher_filter');

if(!isset($indexes['is_disabled'])) {
	$db->Execute('ALTER TABLE watcher_filter ADD INDEX is_disabled (is_disabled)');
}
// ===========================================================================
// Migrate the data from the watcher_filter table to the watcher_filter table
if(isset($tables['watcher_filter'])) {
	$sql = "SELECT id, pos, name, created, worker_id, criteria_ser, actions_ser, is_disabled FROM watcher_filter";
	$rs = $db->Execute($sql);
	
	while($row = mysql_fetch_assoc($rs)) {
		@$old_id = intval($row['id']);
		@$pos = intval($row['pos']);
		@$name = $row['name'];
		@$created = intval($row['created']);
		@$worker_id = intval($row['worker_id']);
		@$criteria_ser = $row['criteria_ser'];
		@$actions_ser = $row['actions_ser'];
		@$is_disabled = $row['is_disabled'];
		
		// Create new filter
		$id = $db->GenID('generic_seq');
		$sql = sprintf("INSERT INTO watcher_filter (id,pos,name,created,worker_id,criteria_ser,actions_ser,is_disabled) ".
			"VALUES (%d,%d,%s,%d,%d,%s,%s,%d)",
			$id,
			$pos,
			$db->qstr($name),
			$created,
			$worker_id,
			$db->qstr($criteria_ser),
			$db->qstr($actions_ser),
			$is_disabled
		);
		$db->Execute($sql);
		
		// Delete source row (for partial success)
		$db->Execute(sprintf("DELETE FROM worker_mail_filter WHERE worker_id=%d AND id=%d", $worker_id, $old_id));
		
	}
}


return TRUE;
