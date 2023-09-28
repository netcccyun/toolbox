<?php
/*
* wap phpmyadmin
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';
$tb=trim($_GET['tb']);
$cl=trim($_GET['cl']);
$vmi=$_GET['vmi'];
echo "<div class='shout'>
DROP - $cl <p align='left'><a href='vmi.php?k=$k'>Tables</a>";
if (!$vmi)
{
echo "><a href='table.php?k=$k&tb=$tb'>$tb</a>><a href='col.php?k=$k&tb=$tb&cl=$cl'>$cl</a>><a href='?k=$k&tb=$tb&cl=$cl'>Drop</a><br/><br/>- - -<br/>".$lang["DROP_DELETE_COLUMN"]." $cl ? <br/><a href='?k=$k&tb=$tb&cl=$cl&vmi=ionutvmi'>".$lang["Yes"]."</a> | <a href='col.php?k=$k&tb=$tb&cl=$cl'>".$lang["No"]."</a>
"; } else {
$d=mysqli_query($conn,"ALTER TABLE `$tb` DROP `$cl`");
if ($d) {
echo "><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/>
Column $cl droped !"; } else {
echo "<br/>- - -<br/>".$lang["Error"].": ";
echo mysqli_error($conn);
}
}
echo "</div>";
include('foot.php');
?>
