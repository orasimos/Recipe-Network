<?php
// logout.php

require 'config.php';

// Λήκη session
session_unset();
// Καταστροφή session
session_destroy();

// Ανακατεύθυνση στη σελίδα login.php
header('Location: login.php');
exit;
?>