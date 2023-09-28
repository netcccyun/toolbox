<?php

echo '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd"> <html xmlns="http://www.w3.org/1999/xhtml">';
echo "<head><title>wap phpmyadmin by ionutvmi</title><link rel='stylesheet' href='./default.css'/></head><body>";
echo "<div class='header'><a href='./index.php' style='color:white;'>wap phpmyadmin v2.0.3</a></div>";

function err($txt){
print "<div class='shout'>$txt </div>";
include 'foot.php';
exit;
}


?>