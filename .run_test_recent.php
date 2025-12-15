<?php
session_start();
$_GET['allow_sample'] = '1';
ob_start();
include __DIR__ . '/api/recent_activities.php';
out:  $output = ob_get_clean();
echo $output;
