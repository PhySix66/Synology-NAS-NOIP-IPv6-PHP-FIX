# Synology NAS NOIP IPv6 PHP FIX
Workaround for Synology NAS DSM 6.X.X NOIP IPv6 Address Update with PHP

This is my workaround to the problem, that Synology NAS with DSM 6 is unable to Update IPv6 Addresses.
Only the first 4 lines have to be changed, as a fallback/test values.
```
$def_account = 'noip_username';
$def_pwd = 'noip_password';
$def_hostname = 'noip_domain';
$test_file_path = '/files path for testing/noipv6.php';
...
```
This file has to be uploaded to /usr/syno/bin/ddns/noipv6.php with SSH and the most importaint part:  
***The noipv6.php has to receive some sort of (don't know the actual term) argument/flag/command? that makes it executable or what...***

I've failed at this so my solution was:   
You might have to log in as sudo su (superuser).

Step 1 - copy(save as) an existing file in /usr/syno/bin/ddns/ in the same dir like so    
cp /usr/syno/bin/ddns/google.php /usr/syno/bin/ddns/noipv6.php

Step 2 - download the new file  
cp /usr/syno/bin/ddns/noipv6.php /volume(x)/your destination folder/sub folders/.../noipv6.php

Step 3 - overwrite the downloaded noipv6.php content with my code (copy+paste)

Step 4 - upload the new file  
cp /volume(x)/your destination folder/sub folders/.../noipv6.php /usr/syno/bin/ddns/noipv6.php

Step 5 - add(copy) this to ddns_provider.conf file  
files location: /etc.defaults/ddns_provider.conf
```
[NoIPv6_I.com]
		modulepath=/usr/syno/bin/ddns/noipv6.php
		website=http://noip.com
		register_module=/usr/syno/bin/ddns/noipv6.php
		queryurl=dynupdate.no-ip.com/nic/update/
 ```
Step 6 - Done/ Might need a NAS reboot... Or a service reboot (don't know the command for that).
