<?php
/*
* wap phpmyadmin
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';

 
$tb=trim($_GET['tb']);
$prim=$_GET['pri'];
$pri=base64_decode($prim);
$vmi=$_GET['vmi'];
echo "<div class='shout'>
DROP - $v <p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>";
if (!$vmi)
{
echo "><a href='table.php?k=$k&tb=$tb'>$tb</a>><a href='br2.php?k=$k&tb=$tb&pri=$prim'>$v</a>><a href='?k=$k&tb=$tb&prim=$prim'>".$lang["Drop"]."</a><br/><br/>- - -<br/>".$lang["DROP_DELETE_this_record"]." <br/><br/><a href='?k=$k&tb=$tb&pri=$prim&vmi=ionutvmi'>".$lang["Yes"]."</a> | <a href='br.php?k=$k&tb=$tb'>".$lang["No"]."</a>
"; } else {
$d=mysqli_query($conn,"DELETE FROM `$tb` WHERE $pri LIMIT 1");
if ($d) {
echo "<br/>- - -<br/><i>".htmlspecialchars("DELETE FROM `$tb` WHERE $pri LIMIT 1")."</i>
<br/><br/>".$lang["Record_droped"]; } else {
echo "<br/>- - -<br/>".$lang["Error"].": ";
echo mysqli_error($conn);
}
}
echo "</div>";
include('foot.php');
?>