<?php
// public/logout.php
require_once('../config.php');
require_once('../classes/User.php');

$user = new User();
$user->logout();
exit();
?>
