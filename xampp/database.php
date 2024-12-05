<?php
   class socssistersDB extends SQLite3 {
      function __construct() {
         $this->open('socssistersDB.db');
      }
   }
   $db = new socssistersDB();
   if(!$db) {
      echo $db->lastErrorMsg();
   } else {
      echo "Opened database successfully\n";
   }

   $sql =<<<EOF
      CREATE TABLE SCHEDULEEVENTS
      (ID INT PRIMARY KEY     NOT NULL,
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