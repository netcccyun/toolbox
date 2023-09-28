<?php
/*
* wap phpmyadmin 
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';
echo "<div class='shout'>".strtoupper($lang["Sql_code"])." <p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='?k=$k'>".$lang["Sql_code"]."</a><br/>- - -<br/>";
$k=$_GET['k'];
$vmi=$_GET['vmi'];
if (!$vmi) {
echo "<form action='?k=$k&vmi=ionutvmi' method='post' align='left'>
(!) ".$lang["DO_NOT_USE_THIS_IF_YOU_DO_NOT_KNOW_HOW"]." !<br/>(!) ".$lang["DO_NOT_FORGET_TO_INCLUDE_SEPARATOR_IN SQL_CODE"]."<br/>
Code:<br/>
<textarea name='sql' rows='10'>;</textarea>
<br/>".$lang["Separator"].": <input name='sep' value=';' size='2'><br><input type='submit' value='".$lang["Execute"]."'/>
</form>
";
} else {
$sql=stripslashes($_POST['sql']); $sep=trim($_POST['sep']);
$sql=str_replace("\r\n","\n",$sql); $sql=str_replace("\n","\r",$sql);
$sql=preg_replace("~(--|##)[^\r]*\r~","\r",$sql);
$sql=preg_replace("~\r\s*\r~","\r",$sql);
$sq=explode($sep,$sql);
foreach ($sq as $sql) {
if(!empty($sql)){
$do= mysqli_query($conn,$sql);
$wp=htmlspecialchars(substr($sql,0,40));
if ($do) {
echo "<i style='color:lime'>$wp...</i> ".$lang["Done"]." !<br/>"; } else {
echo "<i style='color:red'>$wp...</i> ".$lang["Error"].": ";
echo mysqli_error($conn)."<br/>";
} }
}
}
echo '</div>';
include('foot.php');
?>