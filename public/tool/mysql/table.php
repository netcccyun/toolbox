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
echo "<div class='shout'>$tb <br/>".$lang["Query"].": <i>".htmlspecialchars($sql)."</i><p align='left'>
<a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='?k=$k&tb=$tb'>$tb</a><br/>- - -<br/>";
$result = mysqli_query($conn,$sql);
if ($result) {
echo "<a href='br.php?k=$k&tb=$tb'>".$lang["Browse"]."</a> | ".$lang["Structure"]." | <a href='search.php?k=$k&tb=$tb'>".$lang["Search"]."</a> | <a href='in.php?k=$k&tb=$tb'>".$lang["Insert"]."</a> | <a href='trun.php?k=$k&tb=$tb'>".$lang["Clear"]."</a> | <a href='dropt.php?k=$k&tb=$tb'>".$lang["Drop"]."</a><br/>- - -<br/>
";

function mysqli_result($res, $row, $field=0) { 
    $res->data_seek($row); 
    $datarow = $res->fetch_array(); 
    return $datarow[$field]; 
}
function mysql_fetch_fields($tb) {
global $conn;
// LIMIT 1 means to only read rows before row 1 (0-indexed)
$result = mysqli_query($conn,"SELECT * FROM `$tb` LIMIT 0");
$describe = mysqli_query($conn,"SHOW COLUMNS FROM `$tb`");
$num = mysqli_num_fields($result);
$output = array();
for ($i = 0; $i < $num; ++$i) {
$field = mysqli_fetch_field_direct($result, $i);
// Analyze 'extra' field
$field->auto_increment = (strpos(mysqli_result($describe, $i, 'Extra'), 'auto_increment') === FALSE ? 0 : 1);
// Create the column_definition
$field->definition = mysqli_result($describe, $i, 'Type');
if ($field->not_null && !$field->primary_key) $field->definition .= ' NOT NULL';
$avmi=mysqli_result($describe, $i, 'Default');
if ( $avmi != "" && $avmi != "NULL") 
$field->def=$avmi;
else
$field->def=false;
if ($field->def) $field->definition .= " DEFAULT '" . mysqli_real_escape_string($conn,$field->def) . "'";
if ($field->auto_increment) $field->definition .= ' AUTO_INCREMENT';
if ($key = mysqli_result($describe, $i, 'Key')) {
if ($field->primary_key) $field->definition .= ' PRIMARY KEY';
else $field->definition .= ' UNIQUE KEY';
}
// Create the field length
//$field->len = mysqli_fetch_field_direct($result, $i);
// Store the field into the output
$output[$field->name] = $field;
}
return $output;
}
// Show
echo "<form action='mdelcl.php?k=$k&tb=$tb' method='post' align='left'>";
$fields = mysql_fetch_fields("$tb");
foreach ($fields as $key => $field) {
echo "&#187;<input type='checkbox' name='i[]' value='$field->name'> <a href='col.php?k=$k&tb=$tb&cl=$field->name'>".htmlspecialchars($field->name)."</a> ";
echo htmlspecialchars($field->definition)." <br/>";
}
echo "<input type='submit' value='".$lang["DELETE_SELECTED"]."'></form>- - - <br/>
<a href='addcol.php?k=$k&tb=$tb'>".$lang["add_column"]."</a> |
<a href='copyt.php?k=$k&tb=$tb'>".$lang["copy_table"]."</a> |
<a href='rename.php?k=$k&tb=$tb'>".$lang["rename_table"]."</a>";
} else {
printf($lang["table_not_exists"],$tb); }
echo "</div>";
include('foot.php');
?>
