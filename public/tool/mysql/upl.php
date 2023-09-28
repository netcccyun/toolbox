<?php
/*
* wap phpmyadmin 
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';
$vmi=$_GET['vmi'];
$size_bytes = 12288000; // max file size in bytes
$kb = $size_bytes / 1024;
$mb = $size_bytes / 1024000;
echo "<div class='shout'>".$lang["UPLOAD_SQL"]." <p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='?k=$k'>".$lang["Upload"]."</a><br/>- - -<br/>";
if (!$vmi) {
echo "- ".$lang["Max"].": <b>$mb</b> MB<br/> - ".$lang["only_sql_files"];
echo '<form method="post" enctype="multipart/form-data" action="?k='.$k.'&vmi=add" align="left">';
echo '<input type="file" name="filetoupload"><br>';
echo '<select name="del"><option value="yes">'.$lang["Delete_after_execute"].' !</option><option value="no">'.$lang["Save_after_execute"].' !</option></select><br>'.$lang["Separator"].': <input name="sep" value=";" size="2"><br>
<select name="shw">
<option value="no">'.$lang["HIDE_MESSAGES"].'</option>
<option value="yes">'.$lang["SHOW_MESSAGES"].'</option></select><br/>
<input type="Submit" name="uploadform" value="'.$lang["Upload"].'">';
echo '</form></div>';
include('foot.php');
}
if($vmi=="add"){
$upload_dir='data/';
$filename = $_FILES['filetoupload']['name'];
$size = $_FILES['filetoupload']['size'];
$extlimit = "yes"; //yes/no
$ext = strrchr($_FILES['filetoupload']['name'],'.');
if (!is_uploaded_file($_FILES['filetoupload']['tmp_name']))
{
echo $lang["ERROR_NO_FILE_SELECTED"]."!<br />";
echo "</div>";
include("foot.php");
exit();
}
//IF EXT IS ALLOWED
if (($extlimit == "yes") && ($ext !=".sql")) {
echo($lang["EXT_NOT_ALLOWED"]."<br />");
echo "</div>";
include('foot.php');
exit(); }
// check file size
if ($size > $size_bytes) {
$kb = $size_bytes / 1024;
echo $lang["FILE_IS_TO_BIG_Maxim"]." <b>$kb</b> KB.<br />";
echo "</div>";
include("foot.php");
exit();
}
//IF FILE EXIST
if (file_exists("$upload_dir/$filename"))
{
echo($lang["File_name_already_exists"]);
echo "</div>";
include("foot.php");
exit();
}
if (move_uploaded_file($_FILES['filetoupload']['tmp_name'],$upload_dir.$filename))
{
// IF FILE IS UPLOAD
$handle = fopen("data/$filename", "r");
$contents = fread($handle, filesize("data/$filename"));
fclose($handle);
echo "- $filename uploaded !<br/>";
$sep=trim($_POST['sep']);
$shw=$_POST['shw'];
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
echo "><i style='color:lime'>$cop...</i> ".$lang["executed"]." !<br/>"; }
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
echo "<br/>- ".$lang["saved"]." !"; }
echo "</div>";
include("foot.php");
exit();
}
else
{
//IF IS ERROR
echo $lang["ERROR_Try_again"]."<br />";
echo "</div>";
include("foot.php");
exit();
}
}
?>