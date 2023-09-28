<?php
/*
* wap phpmyadmin
* ionutvmi
* pimp-wap.net
*/
error_reporting(0);
include "lang/index.php";
include 'head.php';
$vmi=trim($_GET['vmi']);
if(!$vmi){
print "
<div class='shout'>
<form action='?'>
".$lang["Database_Server"].":<br/> <input name='dbs' value='localhost'><br/>
".$lang["Database_Port"].":<br/> <input name='port' value='3306'><br/>
".$lang["Database_User"].":<br/> <input name='dbu'><br/>
".$lang["Database_Password"].":<br/> <input type='password' name='dbp'><br/>
".$lang["Database_Name"].":<br/> <input name='dbn'><br/>
<br/><input name='vmi' type='submit' value='".$lang["ENTER_DB"]."'>
</form></div>";
}
else{
$a=trim($_GET['dbs']);
$b=trim($_GET['dbu']);
$c=trim($_GET['dbp']);
$d=trim($_GET['dbn']);
$e=trim($_GET['port']);
if($a=="" || $b=="" || $c=="" || $d=="" || $e=="") err("Something is blank");
$e=base64_encode($a."^^^".$b."^^^".$c."^^^".$d."^^^".$e);
print"<div class='shout'> ".$lang["WELCOME"]." $dbu <br/>
<br/> &#187; <a href='tables.php?k=$e'>".$lang["ENTER"]."</a> &#171;
</div>";
}

include 'foot.php';
?>