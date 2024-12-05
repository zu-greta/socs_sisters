<?php
$db = new SQLite3('ssDB.sq3');
$sql = file_get_contents('tables.sql');
$db->exec($sql);
echo("Database tables created successfully");
unset($db);