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
COPY - $tb <p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>";
if(!$vmi) {
echo "><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/><form action='?vmi=ionutvmi&k=$k&tb=$tb' method='post' align='left'>
".$lang["Copy_to"].":<input name='nm' value='table2'><br/>
<select name='tp'><option value='1'>".$lang["Copy_structure_and_data"]."</option><option value='2'>".$lang["Copy_structure"]."</option></select><br/><input type='submit' value='".$lang["Copy"]."'></form>";
} else {
$tp=trim($_POST['tp']); $nm=trim($_POST['nm']);
if ($tp=='1') {
$d=mysqli_query($conn,"CREATE TABLE `$nm` SELECT * FROM `$tb`");
if ($d) {
echo "<br/>- - -<br/><b>
$tb </b> ".$lang["copyed_to"]." <b> $nm </b> !"; } else {
echo "<br/>- - -<br/>".$lang["Error"].": ";
echo mysqli_error($conn);
}
} elseif ($tp=='2') {
$d=mysqli_query($conn,"CREATE TABLE `$nm` LIKE `$tb`");
if ($d) {
echo "<br/>- - -<br/><b>
$tb ".$lang["structure"]."</b> ".$lang["copyed_to"]." <b> $nm </b> !"; } else {
echo "<br/>- - -<br/>".$lang["Error"].": ";
echo mysqli_error($conn);
}
}

}
echo "</div>";
include('foot.php');
?>
