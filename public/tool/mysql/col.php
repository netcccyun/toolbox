<?php
/*
* wap phpmyadmin 
* ionutvmi
* pimp-wap.net
*/
include 'config.php';
include 'head.php';
$tb=trim($_GET['tb']);
$cl=trim($_GET['cl']);
echo "<div class='shout'>
<b>$cl</b> <p align='left'>
<a href='tables.php?k=$k'>".$lang["tables"]."</a>><a href='table.php?k=$k&tb=$tb'>$tb</a>><a href='?k=$k&tb=$tb&cl=$cl'>$cl</a><br/>- - -<br/>
&#187; <a href='edit_col.php?k=$k&tb=$tb&cl=$cl'>".$lang["Edit"]."</a><br/><br/>
&#187; <a href='br.php?k=$k&tb=$tb&nm=".base64_encode($cl)."'>".$lang["Browse"]."</a><br/><br/>
&#187; <a href='copyc.php?k=$k&tb=$tb&cl=".$cl."'>".$lang["Copy"]."</a><br/><br/>
&#187; <a href='dropc.php?k=$k&tb=$tb&cl=$cl'>".$lang["Drop"]."</a> (".$lang["delete"].")
</div>";
include('foot.php');
?>
