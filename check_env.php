<?php
echo file_exists('.env') ? '.env EXISTS' : '.env MISSING';
echo "\n";
if (file_exists('.env')) {
    echo "Content:\n";
    echo file_get_contents('.env');
}
?>