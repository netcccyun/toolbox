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
echo "<div class='shout'>".strtoupper($lang["Search"])."<br/>".$lang["Query"].": ".htmlspecialchars($sql)."<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/><a href='br.php?k=$k&tb=$tb'>".$lang["Browse"]."</a> |<a href='table.php?k=$k&tb=$tb'> ".$lang["Structure"]." </a> | ".$lang["Search"]." | <a href='in.php?k=$k&tb=$tb'>".$lang["Insert"]."</a> | <a href='trun.php?k=$k&tb=$tb'>".$lang["Clear"]."</a> | <a href='dropt.php?k=$k&tb=$tb'>".$lang["Drop"]."</a><br/>- - -<br/>";
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


$vmi=$_GET["vmi"];

if(!$vmi) {
echo "<div class='shout'>".strtoupper($lang["Search"])."<br/>".$lang["Query"].": ".htmlspecialchars($sql)."<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/><a href='br.php?k=$k&tb=$tb'>".$lang["Browse"]."</a> |<a href='table.php?k=$k&tb=$tb'> ".$lang["Structure"]." </a> | ".$lang["Search"]." | <a href='in.php?k=$k&tb=$tb'>".$lang["Insert"]."</a> | <a href='trun.php?k=$k&tb=$tb'>".$lang["Clear"]."</a> | <a href='dropt.php?k=$k&tb=$tb'>".$lang["Drop"]."</a><br/>- - -<br/>";

echo "<form action='?k=$k&tb=$tb&vmi=ionutvmi' align='left' method='post'>
";
$i = 0;
while ($i < mysqli_num_fields($result)) {
$meta = mysqli_fetch_field_direct($result, $i);

echo "<b>".htmlspecialchars($meta->name)."</b><input type='hidden' name='nm[]' value='".$meta->name."'>";
echo '<select name="func[]">';
	
if($meta->numeric == 1) {
echo '					<option value="1">&gt;</option>
                        <option value="2">&gt;=</option>
                        <option value="3">&lt;</option>
                        <option value="4">&lt;=</option>';
						}
echo '                  <option value="5" SELECTED>LIKE</option>
                        <option value="6">LIKE %...%</option>
                        <option value="7">NOT LIKE</option>
                        <option value="8">=</option>
                        <option value="9">!=</option>
                        <option value="10">REGEXP</option>
                        <option value="11">REGEXP ^...$</option>
                        <option value="12">NOT REGEXP</option>
                        <option value="13">= \'\'</option>
                        <option value="14">!= \'\'</option>
                        <option value="15">IN (...)</option>
                        <option value="16">NOT IN (...)</option>
                        <option value="17">BETWEEN</option>
                        <option value="18">NOT BETWEEN</option>
                        <option value="19">IS NULL</option>
                        <option value="20">IS NOT NULL</option>
                </select>';

echo " <input name='sq[]' type='text'><br/>";
$i++;
}
echo "<input type='submit' name='q' value='".$lang["Search"]."'></form>";
mysqli_free_result($result);

} else {
if($_POST['q']) {

$fun=$_POST['func'];
$nm=$_POST['nm'];
$sq=$_POST['sq'];
$vi=0;
$cond='';
foreach($fun as $fu) 
{
	if(trim($sq[$vi]) != '' || $fu=='13' || $fu=='14' || $fu=='19' || $fu=='20') 
	{
		if($fu == '1')
		{
		$cond.=" AND `".$nm[$vi]."` > ".$sq[$vi];
		}
		if($fu == '2')
		{
		$cond.=" AND `".$nm[$vi]."` >= ".$sq[$vi];
		}
		if($fu == '3')
		{
		$cond.=" AND `".$nm[$vi]."` < ".$sq[$vi];
		}
		if($fu == '4')
		{
		$cond.=" AND `".$nm[$vi]."` <= ".$sq[$vi];
		}
		if($fu == '5')
		{
		$cond.=" AND `".$nm[$vi]."` LIKE '".$sq[$vi]."'";
		}
		if($fu == '6')
		{
		$cond.=" AND `".$nm[$vi]."` LIKE '%".$sq[$vi]."%'";
		}
		if($fu == '7')
		{
		$cond.=" AND `".$nm[$vi]."` NOT LIKE '".$sq[$vi]."'";
		}
		if($fu == '8')
		{
		$cond.=" AND `".$nm[$vi]."` = '".$sq[$vi]."'";
		}
		if($fu == '9')
		{
		$cond.=" AND `".$nm[$vi]."` != '".$sq[$vi]."'";
		}
		if($fu == '10')
		{
		$cond.=" AND `".$nm[$vi]."` REGEXP '".$sq[$vi]."'";
		}
		if($fu == '11')
		{
		$cond.=" AND `".$nm[$vi]."` REGEXP '^".$sq[$vi]."$'";
		}
		if($fu == '12')
		{
		$cond.=" AND `".$nm[$vi]."` NOT REGEXP '".$sq[$vi]."'";
		}
		if($fu == '13')
		{
		$cond.=" AND `".$nm[$vi]."` =''";
		}
		if($fu == '14')
		{
		$cond.=" AND `".$nm[$vi]."` !=''";
		}
		if($fu == '15')
		{
		$cond.=" AND `".$nm[$vi]."` IN (".$sq[$vi].")";
		}
		if($fu == '16')
		{
		$cond.=" AND `".$nm[$vi]."` NOT IN (".$sq[$vi].")";
		}
		if($fu == '17')
		{
		$cond.=" AND `".$nm[$vi]."` BETWEEN ".str_replace(',',' AND ',$sq[$vi]);
		}
		if($fu == '18')
		{
		$cond.=" AND `".$nm[$vi]."` NOT BETWEEN ".str_replace(',',' AND ',$sq[$vi]);
		}
		if($fu == '19')
		{
		$cond.=" AND `".$nm[$vi]."` IS NULL ";
		}
		if($fu == '20')
		{
		$cond.=" AND `".$nm[$vi]."` IS NOT NULL ";
		}

	}
++$vi;
}
$cond= base64_encode(substr($cond, 4));
$link="?k=$k&tb=$tb&vmi=ionutvmi&cc=$cond";

echo "<div class='shout'>
<meta http-equiv=\"refresh\" content=\"0; url=$link\" />";
printf($lang["Search_has_been_submited_wait"],$link);
echo " <br/>
</div>";
include "foot.php";
exit;
}

if(!$_GET['cc']) die($lang["No_condition_to_search"]);

$ccod=trim($_GET["cc"]);
$cond= base64_decode($ccod);
$cond= "WHERE (".$cond.")";
// number of results
$nr = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM `$tb` $cond"));

if($nr > 0) {
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
$sql="SELECT * FROM `$tb` $cond ORDER BY `$nm` $ord LIMIT $from, $perp";
$val=mysqli_query($conn,$sql);
echo "<div class='shout'>".strtoupper($lang["Search"])."<br/>".$lang["Query"].": ".htmlspecialchars($sql)."<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/><a href='br.php?k=$k&tb=$tb'>".$lang["Browse"]."</a> |<a href='table.php?k=$k&tb=$tb'> ".$lang["Structure"]." </a> | ".$lang["Search"]." | <a href='in.php?k=$k&tb=$tb'>".$lang["Insert"]."</a> | <a href='trun.php?k=$k&tb=$tb'>".$lang["Clear"]."</a> | <a href='dropt.php?k=$k&tb=$tb'>".$lang["Drop"]."</a><br/>- - -<br/>";
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
<input type='hidden' name='cc' value='$ccod'>
<input type='hidden' name='vmi' value='ionutvmi'>
<input type='submit' value='".$lang["Show"]."'><br>- - -<br></form><p align='left'>";

// asc/desc

if ($ord=='asc') {
echo $lang["Asc"]." | <a href='?k=$k&nm=$nnm&tb=$tb&or=1&page=$page&perp=$perp&cc=$ccod&vmi=ionutvmi'>".$lang["Desc"]."</a><br/><br/>"; } else {
echo "<a href='?k=$k&nm=$nnm&tb=$tb&page=$page&perp=$perp&cc=$ccod&vmi=ionutvmi'>".$lang["Asc"]."</a> | ".$lang["Desc"]."<br/><br/>"; }

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
echo pag($nr,$page,"?or=$or&nm=$nnm&k=$k&perp=$perp&tb=$tb&cc=$ccod&vmi=ionutvmi&page=",true,$perp);
else
echo "1 page<br>- - -";

if ($total_pages>2) {
echo "<form action='?' align='left'><input type='hidden' name='k' value='$k'><input type='hidden' name='tb' value='$tb'><input type='hidden' name='nm' value='$nnm'><input type='hidden' name='perp' value='$perp'>
<input type='hidden' name='cc' value='$ccod'>
<input type='hidden' name='vmi' value='ionutvmi'>
<input name='page' value='$page' size='2'>";
if ($ord=='desc') echo "<input type='hidden' name='or' value='$or'>";
echo " <input type='submit' value='".$lang["Jump"]."'><br/>- - -</form>";
}

echo "<form action='?' align='left'><br/>".$lang["Show"]." <input type='hidden' name='k' value='$k'><input type='hidden' name='tb' value='$tb'>
<input type='hidden' name='or' value='$or'>
<input type='hidden' name='cc' value='$ccod'>
<input type='hidden' name='vmi' value='ionutvmi'>
<input type='hidden' name='nm' value='$nnm'>
<input name='perp' value='$perp' size='2'>";
echo " <input type='submit' value='".$lang["Per_Page"]."'></form>";

 } else {
printf($lang["No_results_search"],"?k=$k&tb=$tb");
 }

}
} else {
echo "<div class='shout'>".strtoupper($lang["Search"])."<br/>".$lang["Query"].": ".htmlspecialchars($sql)."<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a><br/>- - -<br/><a href='br.php?k=$k&tb=$tb'>".$lang["Browse"]."</a> |<a href='table.php?k=$k&tb=$tb'> ".$lang["Structure"]." </a> | ".$lang["Search"]." | <a href='in.php?k=$k&tb=$tb'>".$lang["Insert"]."</a> | <a href='trun.php?k=$k&tb=$tb'>".$lang["Clear"]."</a> | <a href='dropt.php?k=$k&tb=$tb'>".$lang["Drop"]."</a><br/>- - -<br/>";
echo $lang["No_records_search"];
}
}
echo "<p align='center'>$nr ".$lang["records"]."</div>";
include('foot.php');
?>