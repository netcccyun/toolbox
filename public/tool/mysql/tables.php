<?php
/*
* wap phpmyadmin 
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';
// per page 
$perp=(int)$_GET['perp'];
$perp=($perp==0) ? "10" : $perp;

$sql = "SHOW TABLES FROM `$dbn`";
echo "<div class='shout'><b>".$lang["tables"]."</b><br/>".$lang["Query"].": <i>".htmlentities($sql)."</i><p align='left'>";
$result = mysqli_query($conn,$sql);
$nr = mysqli_num_rows($result);
if ($nr =='0') {
echo $lang["no_tables"]." !";
} else {
echo "<form action='mtb.php?k=$k' method='post' align='left'>";
while ($row = mysqli_fetch_row($result)) {
$vmi[]=$row[0];
}
// calculate tbl size and rows
$result = mysqli_query($conn,"SHOW TABLE STATUS");
function convert($size)
{
$unit=array('B','KB','MB','GB','TB','PB');
return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}
while($row = mysqli_fetch_array($result)) {
   
    $total_size = convert($row[ "Data_length" ] + $row[ "Index_length" ]);	
 $size[$row['Name']] = $total_size."^".$row['Rows'];
}
include 'pagination.class.php';
$pagination = new pagination;
$vmi = $pagination->generate($vmi,$perp);

foreach($vmi as $tb) {
$a= explode("^",$size[$tb]);
echo "<input type='checkbox' name='tb[]' value='$tb'> <a href='table.php?k=$k&tb=$tb'>".htmlentities($tb)."</a> | ".$a[0]." | ".($a[1] == 1 ? '1 '.$lang["row"]: $a[1].' '.$lang["rows"])."<br/>";

}
echo "<br/>
<select name='act'>
<option value=''>".$lang["With_selected"]."...</option>
<option value='DROP'>".$lang["Drop"]."</option>
<option value='EXPORT'>".$lang["Export"]."</option>
<option value='EMPTY'>".$lang["Empty"]."</option>
<option value='RENAME'>".$lang["Rename"]."</option>
</select>
<input type='submit' value='".$lang["Go"]."'></form><p align='left'>";
echo $pagination->links(); 
mysqli_free_result($result);
}
echo "<br/>- - -<br/>
<form action='?' align='left'>".$lang["Show"]."<input type='text' name='perp' value='$perp' size='3'> <input type='hidden' value='$k' name='k'> <input type='submit' value='".$lang["Per_Page"]."'> </form><br/>
<a href='addtable.php?k=$k'>".$lang["Add_table"]."</a> | 
<a href='sql.php?k=$k'>".$lang["Sql_code"]."</a> | 
<a href='export.php?k=$k'>".$lang["Export"]."</a> | 
<a href='upl.php?k=$k'>".$lang["Upload_sql"]."</a> |
<a href='import.php?k=$k'>".$lang["Import_sql"]."</a> | 
<a href='data/index.php?k=$k'>".$lang["Files"]."</a><br/>
 <br/> $nr ".strtolower($lang["tables"])."
</div>";

include('foot.php');
?>