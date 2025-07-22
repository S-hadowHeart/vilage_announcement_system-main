<?php
require_once '../includes/auth.php';
admin_logout();
header('Location: login.php');
exit; 