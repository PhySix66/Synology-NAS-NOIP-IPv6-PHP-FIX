#!/usr/bin/php -d open_basedir=/usr/syno/bin/ddns
<?php

$def_account = 'noip_username';
$def_pwd = 'noip_password';
$def_hostname = 'noip_domain';
$test_file_path = '/files path for testing/noipv6.php';
$response = '';
$debug = false;

function gethostbynamel6($host, $try_a = false) {
        // get AAAA records for $host,
        // if $try_a is true, if AAAA fails, it tries for A
        // results are returned in an array of ips found matching type
        // otherwise returns false

        $dns6 = dns_get_record($host, DNS_AAAA);
        if ($try_a == true) {
            $dns4 = dns_get_record($host, DNS_A);
            $dns = array_merge($dns4, $dns6);
        }
        else { $dns = $dns6; }
        $ip6 = array();
        $ip4 = array();
        foreach ($dns as $record) {
            if ($record["type"] == "A") {
                $ip4[] = $record["ip"];
            }
            if ($record["type"] == "AAAA") {
                $ip6[] = $record["ipv6"];
            }
        }
        if (count($ip6) < 1) {
            if ($try_a == true) {
                if (count($ip4) < 1) {
                    return false;
                }
                else {
                    return $ip4;
                }
            }
            else {
                return false;
            }
        }
        else {
            return $ip6;
        }
    }

function gethostbyname6($host, $try_a = false)
{
	// get AAAA record for $host
	// if $try_a is true, if AAAA fails, it tries for A
	// the first match found is returned
	// otherwise returns false

	$dns = gethostbynamel6($host, $try_a);
	if ($dns == false) { return false; }
	else { return $dns[0]; }
}

//if(filter_var($_GET['dbg'], FILTER_VALIDATE_BOOLEAN))
if(isset($_GET['dbg']))
{
	if($_GET['dbg'] === true)
	{
		$debug = true;
	}
	else if($_GET['dbg'] === 1)
	{
		$debug = true;
	}
	else
	{
		$debug = false;
	}
	
	if($debug === true)
	{
		echo '<br>DEBUG ENABLED via GET<br>';
	}
}
// debugger foo
else if($_SERVER['SCRIPT_FILENAME'] === $test_file_path)
{
	$debug = true;
	echo '<br>DEBUG ENABLED via SCRIPT_FILENAME<br>';
}
else
{
	$debug = false;
}

$noipv4_addr = gethostbyname($def_hostname);
$noipv6_addr = gethostbyname6($def_hostname, $try_a = false);
$wmyipv4_addr = get_data('v4.ipv6-test.com/api/myip.php');
$wmyipv6_addr = get_data('v6.ipv6-test.com/api/myip.php');

if($debug === true)
{
	echo '<br>DEBUG ENABLED<br>';
	echo "noipv4_addr: $noipv4_addr<br>";
	echo "noipv6_addr: $noipv6_addr<br>";
	echo "wmyipv4_addr: $wmyipv4_addr<br>";
	echo "wmyipv6_addr: $wmyipv6_addr<br>";
	echo '<br>DEBUG ENABLED<br>';
}

function get_data($url)
{
	$ch = curl_init();
	$timeout = 20;
	curl_setopt($ch,CURLOPT_URL,$url);
	//curl_setopt($ch,CURLOPT_IPRESOLVE, CURL_IPRESOLVE_WHATEVER);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
	$curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
	
    if ($curl_errno > 0)
	{
        echo "cURL Error ($curl_errno): $curl_error\n";
		$data = '';
    }
	else
	{
        $data = curl_exec($ch);
	}
	
	curl_close($ch);
	return $data;
}

$ipv4 = get_data('http://v4.ipv6-test.com/api/myip.php');
$ipv6 = get_data('http://v6.ipv6-test.com/api/myip.php');

if(($ipv6 === '') && ($ipv4 === ''))
{
	if($debug === false)
	{
		$ipv4 = (string)$argv[4];
	}
	else
	{
		exit('Exiting do to NO IPv4 NOR IPv6 Addr received. (badparam)');
	}	
}

if ($argc !== 5)
{
    // default login data
	$account = $def_account;
	$pwd = $def_pwd;
	$hostname = $def_hostname;
	
	if($debug)
	{
		echo '<br>';
		echo 'argv cont = ' . count($argv) . '<br>';
		echo 'argc cont = ' . count($argc) . '<br>';		
	}
	/*
	else
	{
		exit('Exiting do to argc count !== 5. (badparam)');
	}
	*/
}
else
{
    $account = (string)$argv[1];
	$pwd = (string)$argv[2];
	$hostname = (string)$argv[3];
}


// check the hostname contains '.'
if(strpos($hostname, '.') === false)
{
    exit('Exiting do to Hostname is FALSE. badparam');
}

$url = 'https://dynupdate.no-ip.com/nic/update?hostname='.$hostname;
if($ipv4 !== '')
{
	if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
	{
		$url .= '&myip=' . $ipv4;
	}
}

if($ipv6 !== '')
{
	if (filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
	{
		$url .= '&myipv6=' . $ipv6;
	}	
}

if((strpos($url, '&myip=')) || (strpos($url, '&myipv6=')))
{
	
}
else
{
	exit('Exiting do to bad URL. No IPv4 NOR IPv6 Addr set. badparam');
}

if($debug)
{
	echo $url . '<br>';
}

$req = curl_init();
$timeout = 20;
curl_setopt($req, CURLOPT_URL, $url);
curl_setopt($req, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($req, CURLOPT_USERPWD, "$account:$pwd");
curl_setopt($req, CURLOPT_RETURNTRANSFER,1);
curl_setopt($req, CURLOPT_CONNECTTIMEOUT,$timeout);
curl_setopt($req, CURLOPT_TIMEOUT,$timeout);
$curl_errno = curl_errno($req);
$curl_error = curl_error($req);

if ($curl_errno > 0)
{
	echo "cURL Error ($curl_errno): $curl_error\n";
	$res = '';
}
else
{
	$res = curl_exec($req);
}

curl_close($req);

if($debug)
{
	//echo 'res value:	' . $res . '<br>';
	if(true)
	{	
		if(strpos($res, 'good') !== false)
		{
			$response =  "good";
		} 
		else if(strpos($res, 'nochg') !== false)
		{
			$response =  "nochg";
		}
		else if(strpos($res, 'nohost') !== false)
		{
			$response =  "nohost";
		}
		else if(strpos($res, 'abuse') !== false)
		{
			$response =  "abuse";
		}
		else if(strpos($res, 'notfqdn')!== false)
		{
			$response =  "notfqdn";
		}
		else if(strpos($res, 'badauth') !== false)
		{
			$response =  "badauth";
		}
		else if(strpos($res, '911') !== false)
		{
			$response =  "911";
		}
		else if(strpos($res, 'badagent') !== false)
		{
			$response =  "badagent";
		}
		else if(strpos($res, 'badresolv') !== false)
		{
			$response =  "badresolv";
		}
		else if(strpos($res, 'badconn') !== false)
		{
			$response =  "badconn";
		}
		else
		{
			$response =  "badparam";
		}
	}

	if(($response === 'good') || ($response === 'nochg'))
	{
		if($ipv4 !== '')
		{
			if(strpos($res, $ipv4) !== false)
			{
				echo 'IPv4 Address:	' . $ipv4 . '	Status: ' . $response . '<br>';
			}
		}
		
		if($ipv6 !== '')
		{
			if(strpos($res, $ipv6) !== false)
			{
				echo 'IPv6 Address:	' . $ipv6 . '	Status: ' . $response . '<br>';
			}
		}
	}
	else
	{
		echo 'ERROR:	' . $response . '<br>';
	}
}
else
{
	echo '<br>' . $res . '<br>';
}


if($debug === true)
{
	echo 'END OF DEBUG<br>';
}

exit();	
