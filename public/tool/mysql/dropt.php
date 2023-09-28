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
DROP - $tb <p align='left'><a href='tables.php?k=$k'>Tables</a>";
if (!$vmi)
{
echo "><a href='table.php?k=$k&tb=$tb'>$tb</a>><a href='?k=$k&tb=$tb'>Drop</a><br/><br/>- - -<br/>".$lang["DROP_DELETE_TABLE"]." $tb ? <br/><br/><a href='?k=$k&tb=$tb&vmi=ionutvmi'>".$lang["Yes"]."</a> | <a href='table.php?k=$k&tb=$tb'>".$lang["No"]."</a>
"; } else {
$sql="DROP TABLE `$tb`";
$d=mysqli_query($conn,$sql);
if ($d) {
echo "<br/>- - -<br/><center>".$lang["Query"].": <i>".htmlspecialchars($sql)."</i></center><p align='left'>";
printf($lang["Table_droped"],$tb);
 } else {
echo "<br/>- - -<br/><center>".$lang["Query"].": <i>".htmlspecialchars($sql)."</i></center><p align='left'>".$lang["Error"].": ";
echo mysqli_error($conn);
}
}
echo "</div>";
include('foot.php');
?>
