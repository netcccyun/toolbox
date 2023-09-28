<?php
/*
* wap phpmyadmin
* ionutvmi
* pimp-wap.tk
*/
include 'config.php';
include 'head.php';
$vmi=$_GET['vmi'];
$act=trim($_POST['act']);


echo "<div class='shout'>$act ".$lang["SELECTED"]."
<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a><br/>- - -<br/>";

if (!$vmi) {
	$rec=$_POST['tb'];
	if($rec && $act) 
	{
	if($act == 'RENAME') 
		{
		echo "<form action='?vmi=ionutvmi&k=$k' method='post' align='left'>";
			foreach($rec as $o)
			{
			echo "$o to <input name='new[]' type='text' value='$o'><input type='hidden' name='extb[]' value='$o'><br/>";
			}
		echo "<input type='hidden' name='act' value='$act'> <input type='submit' value='RENAME'>";
		} else 
		{
			$nrs=count($rec);
			printf($lang["You_selected_tables"],$nrs);
			echo ":<br/><br/>";
			foreach ($rec as $ag) {
			echo "$ag <br/>";
			}
			printf($lang["DO_YOU_WANT_TO_ACT_TABLE"],$act);
			echo "<br/><br/>";

			if($act !='EXPORT') {
			echo "<form action='?vmi=ionutvmi&k=$k' method='post' align='left'>"; } else {
			echo "<form action='export.php?k=$k&vmi=ionutvmi' method='post' align='left'>
			<select name='tp'><option value='vmi'>".$lang["structure_only"]."</option><option value='full'>".$lang["structure_data"]."</option></select> <br><input type='checkbox' name='zip' value='vmi'> ".$lang["Zip_sql_file"]."<br>
			";
			}
			foreach ($rec as $files) {
			echo "<input type='hidden' name='extb[]' value='$files'/>";
			}

			echo "<input type='hidden' name='act' value='$act'><input type='submit' value='YES $act'></form>";
		}
	} else {
	echo $lang["Nothing_selected"];
	}
echo "<br/> <a href='tables.php?k=$k'>".$lang["Back"]."</a>";
} else {
	$fl=$_POST['extb'];
	$new=$_POST['new'];
	$i=0;
	foreach ($fl as $clm) {
	if($act=="DROP") {
		$d=mysqli_query($conn,"DROP TABLE `$clm`");
		if ($d) {
		echo "<br/>".$lang["Table"]." ".htmlspecialchars($clm)." ".$lang["deleted"]." !"; } else {
		echo "<br/>".$lang["Error"].": ";
		echo mysqli_error($conn); }
	} elseif($act=="EMPTY") {
		$d=mysqli_query($conn,"TRUNCATE TABLE `$clm`");
		if ($d) {
		echo "<br/>".$lang["Table"]." ".htmlspecialchars($clm)." ".$lang["Was_emptyed"]." !"; } else {
		echo "<br/>".$lang["Error"].": ";
		echo mysqli_error($conn); }
	} elseif ($act=='RENAME') {
		$new_name=$new[$i]; ++$i;
		$d=mysqli_query($conn,"ALTER TABLE `$clm` RENAME `$new_name`");
		if ($d) 
		{
			echo "<br/> <b>$clm </b> ".$lang["was_renamed_to"]." <b>$new_name</b> ";
		} else
		{
		echo "<br/>".$lang["Error"].": ";
		echo mysqli_error($conn);
		}
	}

	}
}
echo "</div>";
include('foot.php');
?>