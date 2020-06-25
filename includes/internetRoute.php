<?php

define("ACCESS_CHECK_IP","1.1.1.1");
define("ACCESS_CHECK_DNS","one.one.one.one");

     $rInfo=array();
// get all default routes
     exec('ip route list |  sed -rn "s/default via (([0-9]{1,3}\.){3}[0-9]{1,3}).*dev (\w*).*src (([0-9]{1,3}\.){3}[0-9]{1,3}).*/\3 \4 \1/p"',$routes);
     if ( !empty($routes) ) {
        foreach ($routes as $i => $route) {
          $prop=explode(' ',$route);
          $rInfo[$i]["interface"]=$prop[0];
          $rInfo[$i]["ip-address"]=$prop[1];
          $rInfo[$i]["gateway"]=$prop[2];
          // resolve the name of the gateway (if possible)
          exec('host '.$prop[2].' | sed -rn "s/.*domain name pointer (.*)\./\1/p" | head -n 1',$host);
          if (empty($host)) $host[0]="*";
          $rInfo[$i]["gw-name"] = $host[0];
          if (isset($checkAccess) && $checkAccess) {
            // check internet connectivity w/ and w/o DNS resolution
            exec('ping -W1 -c 1 -I '.$prop[0].' '.ACCESS_CHECK_IP.' |  sed -rn "s/.*icmp_seq=1.*time=.*/\&check\;/p"',$okip);
            if (empty($okip)) $okip[0]="failed";
            $rInfo[$i]["access-ip"] = $okip[0];
            exec('ping -W1 -c 1 -I '.$prop[0].' '.ACCESS_CHECK_DNS.' |  sed -rn "s/.*icmp_seq=1.*time=.*/\&check\;/p"',$okdns);
            if (empty($okdns)) $okdns[0]="failed";
            $rInfo[$i]["access-dns"] = $okdns[0];
          }
        }
      } else {
        $rInfo = array("error"=>"No route to the internet found");
      }
      $rInfo_json = json_encode($rInfo);
?>
