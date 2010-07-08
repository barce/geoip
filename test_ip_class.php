<?php

// eaccelerator_rm_page($_SERVER['PHP_SELF'].'?GET='.serialize($_GET));
echo "time: ";
echo time();
echo "<br>\n";

require_once('ip_class.php');


$s_ip = $_GET['s_ip'];
if (strlen($s_ip) > 16) {
	echo "string too long<br>\n";
	die();
}
$ip = new IP($s_ip);

$s_int = $ip->to_int();
print "<br>\n";
echo $ip->as_ip . ' is located at<br>';

echo $ip->get_local();
print "<br>\n";
print "<pre>\n";
print_r($ip);
print "</pre>\n";

$ip = new IP('209.209.1.121');
print_r($ip);
print "<br>\n";

$s_int = $ip->to_int();
echo $s_int;
print "<br>\n";

$ip->get_local();
print "<br>\n";

$ip2 = new IP($s_int);

echo $ip2->to_ip();
print "<br>\n";

echo "doing 50331648<br>\n";
$ip3 = new IP(50331648);
echo $ip3->to_ip();
print "<br>\n";

print_r($ip3);
print "<br>\n";
$ip3->get_local();


?>
