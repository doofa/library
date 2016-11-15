<?php
$host='localhost';
$uname='root';
//$pass='451230';
$link=mysqli_connect($host,$uname);
if(!$link)
die("could not be  connected".mysqli_error($link));
echo 'Connected';

$q="library";
$rep=mysqli_select_db($link,$q);
if(!$rep)
echo 'DB not selected'.'<br/>';
else
echo '<b />'.'DB selected';
?>