<?php
/*
* wap phpmyadmin
* ionutvmi
* pimp-wap.tk
*/
include 'config.php';
include 'head.php';
$vmi=$_POST['vmi'];
print "<div class='shout'>".$lang["IMPORT_SQL"]."<br/><p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='?k=$k'>".strtolower($lang["IMPORT_SQL"])."</a><br/>- - -<br/>";
if(!$vmi) {
print "- ".$lang["only_sql_files"].".<br/><br/><form action='?k=$k' method='post' align='left'>".$lang["Url"].":<br/><input name='vmi' value='http://'><br/>".$lang["Separator"].": <input name='sep' size='2' value=';'><br/><select name='del'><option value='yes'>".$lang["Delete_after_execute"]."</option><option value='vmi'>".$lang["Save_after_execute"]."</option></select><br/><select name='shw'>
<option value='no'>".$lang["HIDE_MESSAGES"]."</option>
<option value='yes'>".$lang["SHOW_MESSAGES"]."</option></select><br/><input type='submit' value='".$lang["Execute"]."'>";

} else {
if($vmi==''||$vmi=='http://') die($lang["enter_url"]);
$ext= end(explode('.', $vmi));
if($ext !="sql") die($lang["file_must_be_sql"]);
$sep=trim($_POST['sep']);
$shw=$_POST['shw'];
if(!$sep) die($lang["enter_separator"]);
$filename="import".rand(1,100).".sql";
$dir="data/".$filename;
if(!copy($vmi,$dir)) {
die($lang["ERROR_File_NOT_copyed"]); } else {
// IF FILE IS UPLOAD
$handle = fopen("data/$filename", "r");
$contents = fread($handle, filesize("data/$filename"));
fclose($handle);
echo "- $filename uploaded !<br/>";
$sql=str_replace("\r\n","\n",$contents); $sql=str_replace("\n","\r",$sql);
$sql=preg_replace("~(--|##)[^\r]*\r~","\r",$sql);
$sql=preg_replace("~\r\s*\r~","\r",$sql);

$contents=explode($sep,$sql);
$q=0;
foreach($contents as $coo) {
if(!empty($coo)) {
$do=mysqli_query($conn,$coo);
$cop=htmlspecialchars(substr($coo, 0, 40));
if ($do) {
$q++;
if($shw=="yes") {
echo "><i style='color:lime'>$cop...</i> ".$lang["executed"]."!<br/>"; }
} else {
if($shw=="yes") {
echo "><i style='color: red'>$cop...</i> ".$lang["not_executed"]." ! ".$lang["Error"].": ";
echo mysqli_error($conn)."<br/>"; }
} }
}
echo "<br/>$q ".strtolower($lang["Query"]." ".$lang["executed"])." !";
if ($_POST['del'] == "yes") {
unlink("data/$filename");
echo "<br/>- ".$lang["deleted"]." !"; } else {
echo "<br/>-  ".$lang["saved"]."!"; }


}}
echo "</div>";
include('foot.php');
?>
