<?php
/*
* wap phpmyadmin 
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';

 
$tb=trim($_GET['tb']);
$sql="SELECT * FROM `$tb`";
$result = mysqli_query($conn,$sql);
if (!$result) {
echo "<div class='shout'>".strtoupper($lang["Browse"])."<br/>".$lang["Query"].": ".htmlspecialchars($sql)."<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/>".$lang["Browse"]."</a> |<a href='table.php?k=$k&tb=$tb'>".$lang["Structure"]."</a> | <a href='search.php?k=$k&tb=$tb'>".$lang["Search"]."</a> | <a href='in.php?k=$k&tb=$tb'>".$lang["Insert"]."</a> | <a href='trun.php?k=$k&tb=$tb'>".$lang["Clear"]."</a> | <a href='dropt.php?k=$k&tb=$tb'>".$lang["Drop"]."</a><br/>- - -<br/>
";
printf($lang["table_not_exists"],$tb);
} else {


$xx = mysqli_query($conn,"SHOW INDEXES FROM `$tb` WHERE Key_name = 'PRIMARY'");
$nrr=mysqli_num_rows($xx);

if($nrr>0){
while ($row = mysqli_fetch_array($xx)) {
$cvn= $row['Column_name'];
$cv[]=$cvn."``0";
}
} else {
$i=0;
while ($i < mysqli_num_fields($result)) {
 $meta = mysqli_fetch_field_direct($result, $i);
$cvn=$meta->name;
$cv[]=$cvn."``1";
$i++;
}
}

// get column

$nnm=$_GET['nm'];
if(!$nnm) {
$meta = mysqli_fetch_field_direct($result, 0);
$nm=$meta->name;
}else{
$nm= trim(base64_decode($nnm));
}
// get nr of columns
// $val=mysqli_query($conn,"SELECT * FROM `$tb`");
$nr=mysqli_num_rows($result);
if ($nr >0) {
// get psge

if(!isset($_GET['page'])){
$page = 1;
} else {
$page = (int)$_GET['page'];
}

if($page <= 0) $page=1;
$perp = (int)$_GET['perp'];
if(!$perp) $perp=10; 
if($perp <= 0) $perp=10; 
$from = (($page * $perp) - $perp);
$total_pages = ceil($nr / $perp);
if ($page>$total_pages) {
die("error"); }
$or=$_GET['or'];
if(!$or) $or=0;
if ($or==0) {
$ord='asc'; } else {
$ord='desc'; }
$sql="SELECT * FROM `$tb` ORDER BY `$nm` $ord LIMIT $from, $perp";
$val=mysqli_query($conn,$sql);
echo "<div class='shout'>".strtoupper($lang["Browse"])."<br/>".$lang["Query"].": ".htmlspecialchars($sql)."<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/>".$lang["Browse"]."</a> |<a href='table.php?k=$k&tb=$tb'>".$lang["Structure"]."</a> | <a href='search.php?k=$k&tb=$tb'>".$lang["Search"]."</a> | <a href='in.php?k=$k&tb=$tb'>".$lang["Insert"]."</a> | <a href='trun.php?k=$k&tb=$tb'>".$lang["Clear"]."</a> | <a href='dropt.php?k=$k&tb=$tb'>".$lang["Drop"]."</a><br/>- - -<br/>
";
// column list 
$col=mysqli_query($conn,"SHOW COLUMNS FROM `$tb`");
while ($r = mysqli_fetch_array($col)){
$nn[]=$r['Field'];
}
echo "<form action='?' align='left'><select name='nm'>";
foreach($nn as $n){
$nc=base64_encode($n);
if($nm == $n)
echo "<option value='$nc' SELECTED> ".htmlspecialchars($n)." </option>";
else
echo "<option value='$nc'> ".htmlspecialchars($n)." </option>";
}
echo "</select> <input type='hidden' name='k' value='$k'>
<input type='hidden' name='page' value='$page'>
<input type='hidden' name='tb' value='$tb'>
<input type='hidden' name='perp' value='$perp'>
<input type='hidden' name='or' value='$or'>
<input type='submit' value='".$lang["Show"]."'><br>- - -<br></form><p align='left'>";

// asc/desc

if ($ord=='asc') {
echo $lang["Asc"]." | <a href='?k=$k&nm=$nnm&tb=$tb&or=1&page=$page&perp=$perp'>".$lang["Desc"]."</a><br/><br/>"; } else {
echo "<a href='?k=$k&nm=$nnm&tb=$tb&page=$page&perp=$perp'>".$lang["Asc"]."</a> | ".$lang["Desc"]."<br/><br/>"; }

// column values
echo "<form action='mdelc.php?k=$k&tb=$tb' method='post' align='left'>";
while ($row = mysqli_fetch_array($val)) {
$v=$row["$nm"];
$pri="";
foreach ($cv as $c){
$cc=explode("``",$c);
$c=$cc[0];
$pv= $row["$c"];
if($cc[1] == 0) {
$cc="`$tb`.`$c`";
} else { 
$cc= "CONVERT(`$tb`.`$c` USING utf8)";
}
$pri.=" $cc = '$pv' AND";
}
$pri=substr($pri,0,-3);
$pri=base64_encode($pri);


echo "<input type='checkbox' name='i[]' value='$pri'> - <b><a href='br2.php?k=$k&tb=$tb&pri=$pri'>".htmlspecialchars($v)."</a></b>";
echo "<br/>
";
}


echo "<br/>- - -<br/><input type='submit' name='delbr' value='".$lang["DELETE_SELECTED"]."'></form><p align='left'>- - -<br/>";
mysqli_free_result($val);


// pagination function
function pag($total,$currentPage,$baseLink,$nextPrev=true,$limit=10) { 
global $lang;
if(!$total OR !$currentPage OR !$baseLink) { 
return false; } //Total Number of pages 
$totalPages = ceil($total/$limit); //Text to use after number of pages 
$txtPagesAfter = ($totalPages==1)? " page": " pages"; //Start off the list. 
$txtPageList = '<br />' .$totalPages.$txtPagesAfter .': <br />' ; //Show only 3 pages before current page(so that we don't have too many pages) 
$min = ($currentPage - 3 < $totalPages && $currentPage-3 > 0) ? $currentPage-3 : 1; //Show only 3 pages after current page(so that we don't have too many pages) 
$max = ($currentPage + 3 > $totalPages) ? $totalPages : $currentPage+3; //Variable for the actual page links 
$pageLinks = ""; //Loop to generate the page links 
for($i=$min;$i<=$max ;$i++) { 
if($currentPage==$i) { //Current Page 
$pageLinks .= ' <b class="selected">'.$i.'</b> ' ; } 
else { $pageLinks .= ' <a href="'.$baseLink.$i.'" class="page">'.$i.'</a> ' ; } } 
if($nextPrev ) { //Next and previous links 
$next = ($currentPage + 1 > $totalPages) ? false : '<a href="'.$baseLink.($currentPage + 1) .'">'.$lang["Next"].'</a>' ; 
$prev = ($currentPage - 1 <= 0 ) ? false : '<a href="'.$baseLink.($currentPage - 1).'">'.$lang["Prev"].'</a>' ; } 
$first= ($currentPage > 2) ? '<a href="'.$baseLink.'1">'.$lang["First"].'</a> ': false ;

 $last= ($currentPage < ($totalPages - 2)) ? " <a href='".$baseLink.$totalPages."'>".$lang["Last"]."</a> " : false ;

return $txtPageList.$first.$prev.$pageLinks.$next.$last; } 
 // end pagination

// show pages
if($total_pages != 1)
echo pag($nr,$page,"?or=$or&nm=$nnm&k=$k&perp=$perp&tb=$tb&page=",true,$perp);
else
echo "1 page<br>- - -";

if ($total_pages>2) {
echo "<form action='?' align='left'><input type='hidden' name='k' value='$k'><input type='hidden' name='tb' value='$tb'><input type='hidden' name='nm' value='$nnm'><input type='hidden' name='perp' value='$perp'><input name='page' value='$page' size='2'>";
if ($ord=='desc') echo "<input type='hidden' name='or' value='$or'>";
echo " <input type='submit' value='".$lang["Jump"]."'><br/>- - -</form>";
}

echo "<form action='?' align='left'><br/>".$lang["Show"]." <input type='hidden' name='k' value='$k'><input type='hidden' name='tb' value='$tb'>
<input type='hidden' name='or' value='$or'><input type='hidden' name='nm' value='$nnm'>
<input name='perp' value='$perp' size='2'>";
echo " <input type='submit' value='".$lang["Per_Page"]."'></form>";

} else {
echo "<div class='shout'>".strtoupper($lang["Browse"])."<br/>".$lang["Query"].": ".htmlspecialchars($sql)."<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/>".$lang["Browse"]."</a> |<a href='table.php?k=$k&tb=$tb'>".$lang["Structure"]."</a> | <a href='search.php?k=$k&tb=$tb'>".$lang["Search"]."</a> | <a href='in.php?k=$k&tb=$tb'>".$lang["Insert"]."</a> | <a href='trun.php?k=$k&tb=$tb'>".$lang["Clear"]."</a> | <a href='dropt.php?k=$k&tb=$tb'>".$lang["Drop"]."</a><br/>- - -<br/>
";
echo $lang["No_values_inserted"]."<br/>";
}
}
echo "<p align='center'>$nr ".$lang["records"]."</div>";
include('foot.php');
?>