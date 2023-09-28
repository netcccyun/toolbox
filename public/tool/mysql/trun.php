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
CLEAR - $tb <p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a>><a href='?k=$k&tb=$tb'>".$lang["Clear"]."</a>";
if (!$vmi)
{
echo "<br/><br/>- - -<br/>- This will delete all records !<br/> ".$lang["TRUNCATE_TABLE"]." ".htmlspecialchars($tb)." ? <br/><br/><a href='?k=$k&tb=$tb&vmi=ionutvmi'>".$lang["Yes"]."</a> | <a href='table.php?k=$k&tb=$tb'>".$lang["No"]."</a>"; } else {
$sql="TRUNCATE TABLE `$tb`";
$d=mysqli_query($conn,$sql);
if ($d) {
echo "<br/>- - -<br/><center>".$lang["Query"].": <i>".htmlspecialchars($sql)."</i></center><p align='left'>
Table $tb cleared !"; } else {
echo "<br/>- - -<br/><center>".$lang["Query"].": <i>".htmlspecialchars($sql)."</i></center><p align='left'>".$lang["Error"].": ";
echo mysqli_error($conn);
}
}
echo "</div>";
include('foot.php');
?>