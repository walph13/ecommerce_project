<?php
// 1. Start the session so we can access it
session_start();

// 2. Unset all of the session variables (clear the data)
$_SESSION = array();

// 3. Destroy the session entirely
session_destroy();

// 4. Redirect the user back to the login page (or homepage)
header("Location: login.php");
exit;
?>
