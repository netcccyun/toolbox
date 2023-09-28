<?php
/*
* wap phpmyadmin
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';
$tb=trim($_GET['tb']);
$vmi=$_GET['vmi'];
echo "<div class='shout'>
".$lang["Rename"]." - $tb <p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>";
if(!$vmi) {
echo "><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/><form action='?vmi=ionutvmi&k=$k&tb=$tb' method='post' align='left'>
".$lang["Rename_to"].":<input name='nm' value='table2'><br/>
<input type='submit' value='".$lang["Go"]."'></form>";
} else {
$tp=trim($_POST['tp']); $nm=trim($_POST['nm']);

$d=mysqli_query($conn,"ALTER TABLE `$tb` RENAME `$nm`");
if ($d) {
echo "<br/>- - -<br/><b>
$tb </b> ".$lang["renamed_to"]." <b> $nm </b> !"; } else {
echo "<br/>- - -<br/>".$lang["Error"].": ";
echo mysqli_error($conn);
}
}
echo "</div>";
include('foot.php');
?>