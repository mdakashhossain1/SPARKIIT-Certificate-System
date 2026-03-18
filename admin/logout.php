<?php
require_once dirname(__DIR__) . '/includes/auth.php';
adminLogout();
header('Location: login.php');
exit;
