<?php
   // class socssistersDB extends SQLite3 {
   //    function __construct() {
   //       $this->open('socssistersDB.db');
   //    }
   // }
   // $db = new socssistersDB();
   // if(!$db) {
   //    echo $db->lastErrorMsg();
   // } else {
   //    echo "Opened database successfully\n";
   // }
   try {
      // Create or open the SQLite database
      $conn = new SQLite3('socssistersDB.db');
  
      // If the connection is successful, print a success message
      echo "Connected successfully to SQLite database.\n";
   } catch (Exception $e) {
         // Handle connection errors
         die("Connection failed: " . $e->getMessage());
   }

   $sql =<<<EOF
      CREATE TABLE SCHEDULEEVENTS
      (ID INTEGER PRIMARY KEY AUTOINCREMENT,
      NAME           TEXT    NOT NULL,
      LOCATION      TEXT    NOT NULL,
      STARTDATE      TEXT    NOT NULL,
      ENDDATE        TEXT    NOT NULL,
      PARTICIPANTS   INT    NOT NULL,
      SLOT           INT    NOT NULL,
      CALENDAR       TEXT    NOT NULL,
      NOTES          TEXT    NOT NULL);
EOF;

   $ret = $db->exec($sql);
   if(!$ret){
      echo $db->lastErrorMsg();
   } else {
      echo "Table created successfully\n";
   }
   $db->close();
?>