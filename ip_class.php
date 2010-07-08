<?php

/*
 *
 * db file below should look like this:

<?php

function dbConnectSelect($dbname) {
$dbh = mysql_connect("127.0.0.1", "dbuser", "dbpass") or die ("Cannot connect!");
@mysql_select_db($dbname) or die ("Cannot select $dbname");
return $dbh;
}

?>

*/
require_once('/var/www/localhost/htdocs/geoip/db.php');

/*
 * @class: IP
 * @author: barce@appdevandmarketing.com
 * @date: 7/7/2010
 *
 * This class takes a string or int as a parameter, and creates an
 * instance of an IP. The IP can be converted to an int or into a 
 * 4-byte address written in dot notation.
 *
 */
class IP {

	var $addr;
	var $as_int;
	var $as_ip;

	function __construct($addr) {
		$this->addr = $addr;

		if (preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $this->addr, $matches)) {
			$this->to_ip();
			$this->to_int();
		}

		if (is_numeric($this->addr)) {
			$this->to_ip();
			$this->as_int = $this->addr;
		}
	}

	function to_int() {

		if (is_int($this->addr)) {
			$this->as_int = $this->addr;
			return $this->addr;
		}

		if (preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $this->addr, $matches)) {

			$i_ip  = 0;
			$i_exp = 8;
			$a_list = preg_split("/\./", $this->addr);

			$i_ip = array_pop($a_list);
			$a_list = array_reverse($a_list);
			foreach ($a_list as $str) {
				$i_ip = $i_ip + (int) $str * pow(2, $i_exp);
				$i_exp = $i_exp + 8;
			}
		}

		$this->as_int = $i_ip;
		return $i_ip;

	}

	function to_ip() {
		if (preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $this->addr, $matches)) {
			$this->as_ip = $this->addr;
			return $this->addr;
		}

		if (!is_numeric($this->addr)) { return 0; }

		if (is_numeric($this->addr)) {
			$i_ip = $this->addr;
			$i_tmp = $i_ip;
			$a_ip  = array();
			for ($i = 3; $i >=0; $i--) {
				$i_tmp = $i_tmp / 256;
				$a_ip[] = floor(256 * ($i_tmp - (int) $i_tmp));
			}

			$a_ip = array_reverse($a_ip);
			$s_ip = join('.', $a_ip);
			$this->as_ip = $s_ip;
			return $s_ip;
		}
	}

	function get_local() {

		// use the converted number to get the zip
		if ($this->is_private() == 1) { return 'a private spot'; }
		$dbh = dbConnectSelect('432439_mcaapp');

		// get id first
		// get a row greater than the startip
		$sql = "select locID from geocity_blocks where startIpNum >= " .
			$this->as_int . " order by startIpNum asc limit 1";
		$result = mysql_query($sql, $dbh) or die("mysql_query failed: $sql");
		$row = mysql_fetch_assoc($result);
		$i_top = $row['locID'];

		// get a row less than the startip
		$sql = "select locID from geocity_blocks where endIpNum <= " .
			$this->as_int . " order by endIpNum desc limit 1";
		$result = mysql_query($sql, $dbh) or die("mysql_query failed: $sql");
		$row = mysql_fetch_assoc($result);
		$i_bottom = $row['locID'];

		$i_diff = $i_top - $i_bottom;
		$i_id   = $i_top - ($i_diff - 1);

		$sql = "select  b.*, l.* from geocity_blocks b, geocity_location l where b.locid = l.locId and b.locID = $i_id";
		$result = mysql_query($sql, $dbh) or die("mysql_query failed: $sql");
		$row = mysql_fetch_assoc($result);
		$i_rows = mysql_num_rows($result);
		
		if ($_SERVER['PHP_SELF'] == '/local/classes/test_ip_class.php') {
			print "<pre>";
			print "sql: $sql\n";
			print "i_top: $i_top\n";
			print "i_bottom: $i_bottom\n";
			print "i_diff: $i_diff\n";
			print "i_id: $i_id\n";
			print $row['postalcode'] . "\n";
			print "</pre>";
		}

		if (strlen($row['postalcode']) > 2) {
			return preg_replace('/\"/i', '', $row['postalcode']);
		}

		if (strlen($row['city']) > 0) {
			if (strcmp($row['country'], '"US"') == 0) {
				$locale = $row['city'] . ', ' . $row['region'];
			} else {
				$locale = $row['city'] . ', ' . $row['region'] . ' ' . 
				$row['country'];
			}
			$pattern = '/\"/i';
			$replacement = '';
			$locale = preg_replace($pattern, $replacement, $locale);
			return $locale;
		}
		
		return;
	}

	function is_private() {
        $ip = $this->as_ip;
        $a_ip = split ("\.", $ip);
        $i_secret = 0;

        if ($a_ip[0] == 10)
        {
                $i_secret = 1;
        }

        if ( ($a_ip[0] == 172) && ($a_ip[1] >= 16) && ($a_ip[1] <= 31) )
        {
                $i_secret = 1;
        }

        if ( ($a_ip[0] == 192) && ($a_ip[1] == 168) )
        {
                $i_secret = 1;
        }

        return $i_secret;


	}

}


?>
