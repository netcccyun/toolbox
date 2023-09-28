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
echo "<div class='shout'>".$lang["INFO"]."<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a>><a href='br.php?k=$k&tb=$tb'>".$lang["Browse"]."</a>><a href='?k=$k&tb=$tb&pri=$prim'>".$lang["INFO"]."</a><br/>- - -<br/><a href='edit_rec.php?k=$k&tb=$tb&pri=$prim'>".$lang["Edit"]."</a> | <a href='drop_rec.php?k=$k&tb=$tb&pri=$prim'>".$lang["Drop"]."</a> (".$lang["delete"].")<br/>- - -
";
$result = mysqli_query($conn,"SELECT * FROM `$tb`");
if (!$result) {
printf($lang["table_not_exists"],$tb); } else {

$de = mysqli_query($conn,"SHOW COLUMNS FROM `$tb`");
$nr=@mysqli_num_rows($de);
if ($nr >0) {
$i = 0;
echo "<table border='1' width='100%' cellpadding='0' cellspacing='0'><tr><td>".strtoupper($lang["Column"])."</td><td>".strtoupper($lang["Value"])."</td></tr>";
while ($row = mysqli_fetch_array($de)) {
$meta = mysqli_fetch_field_direct($result, $i);
$nm=$meta->name;
$ga = mysqli_query($conn,"SELECT `$nm` FROM `$tb` WHERE $pri");
$r = mysqli_fetch_array($ga);
$rr=$r["$nm"];
echo "<tr><td>".htmlentities($nm)."</td> <td>".htmlentities($rr)."</td></tr>";
echo "<!-- ionutvmi -->";
$i++;
}
echo "</table>";
} else {
echo $lang["No_values_inserted"];
}

}
echo "</div>";
include('foot.php');
?>