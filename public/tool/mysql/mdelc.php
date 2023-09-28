<?php
/*
* wap phpmyadmin 
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';


$tb=trim($_GET['tb']);

echo "<div class='shout'>".$lang["DELETE_SELECTED"];
$result = mysqli_query($conn,"SELECT * FROM `$tb`");
if (!$result) {
printf($lang["table_not_exists"],$tb); } else {
$vmi=$_GET['vmi'];
echo "<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/>";
if (!$vmi) {
$rec=$_POST['i'];
$nrs=count($rec);
printf($lang["You_selected_records"],$nrs);
echo " !<br/><br/>";
echo $lang["DO_YOU_WANT_TO_DELETE_RECORDS"]."<br/><br/><br/>
<form action='?vmi=ionutvmi&k=$k&tb=$tb' method='post' align='left'>";
foreach ($rec as $files) {
echo "<input type='hidden' name='i[]' id='i[]' value='$files'/>";
}
echo "<input type='submit' value='".strtoupper($lang["Yes"]." ".$lang["delete"])."'></form><a href='br.php?k=$k&tb=$tb'>".$lang["Back"]."</a>";
} else {
$fl=$_POST['i'];
foreach ($fl as $clm) {
$pri=base64_decode($clm);
$d=mysqli_query($conn,"DELETE FROM `$tb` WHERE $pri");
if ($d) {
echo "<br/>".$lang["Record_droped"]; } else {
echo "<br/>".$lang["Error"].": ";
echo mysqli_error($conn); }
}
}
}

echo "</div>";
include('foot.php');
?>