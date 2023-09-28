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
COPY - $cl <p align='left'><a href='.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a>><a href='col.php?k=$k&tb=$tb&cl=$cl'>$cl</a><br/>- - -<br/>";
if(!$vmi) {
echo "<form action='?vmi=ionutvmi&k=$k&tb=$tb&cl=$cl' method='post' align='left'>
".$lang["Copy_to"].":<input name='nm' value='name2'><br/>
<input type='submit' value='".$lang["Copy"]."'></form>";
} else {
$nm=trim($_POST['nm']);
$d=mysqli_query($conn,"ALTER TABLE `$tb` ADD `$nm` TEXT NOT NULL;");
$d=mysqli_query($conn,"UPDATE `$tb` SET `$nm` = `$cl`;");
if ($d) {
echo "<b>
$cl </b> ".$lang["copyed_to"]." <b> $nm </b> !"; } else {
echo $lang["Error"].": ";
echo mysqli_error($conn);
}
}
echo "</div>";
include('foot.php');
?>
