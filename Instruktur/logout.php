<?php
session_start();
require_once '../config.php';

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
redirect('../login.php');
?>