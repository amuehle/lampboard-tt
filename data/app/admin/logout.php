<?php
require_once "../config/database.php";

session_destroy();

header("Location: /admin/login.php");
exit;
