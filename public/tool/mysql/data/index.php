<?php
/*
* wap phpmyadmin
* ionutvmi
* pimp-wap.net
*/
include '../config.php';
include '../head.php';
echo "<div class='shout'>".strtoupper($lang["Files"]." ".$lang["saved"])." <p align='left'><a href='../tables.php?k=$k'>".$lang["tables"]." </a><br/>- - -<br/>";
$ac=$_GET['ac'];
if (!$ac) {
if ($handle = opendir('.')) {
$q=0;
while (false !=($file=readdir($handle))) {
if ($file !='.' && $file !='..' && $file !='index.php' && $file !='default.css') {
echo "&#187; <a href='$file'>$file </a> - <a href='?k=$k&ac=d&f=$file'>(".$lang["delete"].")</a><br/>";
$q++; } }
if ($q==0) {
echo $lang["No_files"]." !"; }
closedir($handle);
} else {
echo $lang["Error"].": Folder can not be open !"; }
} else {
$f=$_GET['f'];
unlink($f);
echo "$f ".$lang["deleted"]." ! <br><br><a href='?k=$k'>".$lang["Back"]."</a>";
}
echo "</div>";
include('../foot.php');
?>