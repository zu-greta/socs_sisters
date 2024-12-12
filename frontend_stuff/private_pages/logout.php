<?php
//logout
session_start();
//TODO login checking - TEMPORARY RN

//clear session
session_unset();
session_destroy();

//redirect to landing
include './frontend_stuff/landing.html';
?>