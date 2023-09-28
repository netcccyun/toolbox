<?php
/*
* wap phpmyadmin
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';
$vmi=$_GET['vmi'];
echo "<div class='shout'>".strtoupper($lang["Export"])."<p align='left'><a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='?k=$k'>".$lang["Export"]."</a><br/>- - -<br/>";
if (!$vmi) {
echo "<form action='?k=$k&vmi=ionutvmi' align='left' method='post'>".$lang["Export"].":<br/><select name='extb[]' multiple='multiple'>";
$tables = mysqli_query($conn,"SHOW TABLES FROM `$dbn`");
while($tabs = mysqli_fetch_row($tables)):
print "<option value='$tabs[0]' selected> $tabs[0] </option>";
endwhile;
print "</select><br/><select name='tp'><option value='vmi'>".$lang["structure_only"]."</option><option value='full'>".$lang["structure_data"]."</option></select> <br><input type='checkbox' name='zip' value='vmi'> ".$lang["Zip_sql_file"]."<br><input type='submit' value='".$lang["ok"]."'></form>

";
} elseif ($vmi=='ionutvmi') {
/* backup the db OR just a table */
function backup_tables($tables = '*',$dt,$zx)
{
	global $k,$lang,$conn;
		
	//get all of the tables
	if($tables == '*')
	{
		$tables = array();
		$result = mysqli_query($conn,'SHOW TABLES');
		while($row = mysqli_fetch_row($result))
		{
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}
	
	//cycle through
	foreach($tables as $table)
	{
		$result = mysqli_query($conn,'SELECT * FROM `'.$table.'`');
		$num_fields = mysqli_num_fields($result);
		$return.= 'DROP TABLE `'.$table.'`;';
		$row2 = mysqli_fetch_row(mysqli_query($conn,'SHOW CREATE TABLE `'.$table.'`'));
		$return.= "\n\n".$row2[1].";\n\n";
		if($dt=="full"){
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = mysqli_fetch_row($result))
			{
				$return.= 'INSERT INTO `'.$table.'` VALUES(';
				for($j=0; $j<$num_fields; $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = str_replace("\n","\\n",$row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		} }
		$return.="\n\n\n";
	}
	//save file
	$dd="backup-".date('d_M_Y')."_".time().".sql";
	
	$handle = fopen("data/$dd","w+");
	fwrite($handle,$return);
	fclose($handle);
if ($zx==1) {
fopen("data/$dd.zip","w");

$zip = new ZipArchive;
if ($zip->open("data/$dd.zip") === TRUE) {
$zip->addFile("data/$dd", "$dd") or die('error');
}
$zip->close();
echo "- ".$lang["File"]." $dd.zip ".$lang["created"].".<br/><br/>- <a href='data/$dd.zip'>".$lang["DOWNLOAD"]." $dd.zip </a><br/><br/>- <a href='?k=$k&vmi=del&f=$dd.zip'>".$lang["delete"]." ".$lang["File"]." $dd.zip </a><br/>";
} else {
echo "- ".$lang["File"]." $dd ".$lang["created"].".<br/><br/>- <a href='data/$dd'>".$lang["DOWNLOAD"]." $dd </a><br/><br/>- <a href='?k=$k&vmi=del&f=$dd'>".$lang["delete"]." ".$lang["File"]." $dd </a><br/>";
}
	
	echo $f;
	}
	$tbs=$_POST['extb'];
if(!$tbs) die($lang["No_Table_Selected"]);
$z=0;
if (isset($_POST['zip'])) {
$z=1;
}
// echo $_POST['tp'];
backup_tables($tbs,$_POST['tp'],$z);

}
if ($vmi=='del') {
$f=$_GET['f'];
$g= @unlink("data/$f");
if ($g) {
printf($lang["FILE_DELETED"],$f); } else {
echo $lang["Error"];
}
}
echo "</div>";
include('foot.php');
?>