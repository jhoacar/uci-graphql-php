<?php

namespace UciGraphQL\Utils;

/**
 * Class used for execute commands in shell.
 */
class Command
{
    /**
     * Return a string with the stdoutput and stderr for the command executed.
     * @param string $command
     * @return string
     */
    public static function execute(string $command): string
    {
        /* Using 2>&1 we redirect stderr to stdout */
        return shell_exec("$command 2>&1") ?: '';
    }
}
/*
uci set wireless.@wifi-iface[0].ssid='ArduinoWiFi'

uci add_list dhcp.@dnsmasq[0].address='/#/1.1.1.1'

uci add firewall redirect
uci set firewall.@redirect[-1].name=capture H T T P
uci set firewall.@redirect[-1].src=lan
uci set firewall.@redirect[-1].proto=tcp
uci set firewall.@redirect[-1].src_dip=!$(uci get network.lan.ipaddr)
uci set firewall.@redirect[-1].src_dport=80"
uci set firewall.@redirect[-1].dest_port=8080"
uci set firewall.@redirect[-1].dest_ip=$(uci get network.lan.ipaddr)
uci set firewall.@redirect[-1].target=DNAT

uci add firewall redirect
uci set firewall.@redirect[-1].name=captureDNS
uci set firewall.@redirect[-1].src=lan
uci set firewall.@redirect[-1].src_dip=!$(uci get network.lan.ipaddr)
uci set firewall.@redirect[-1].src_dport=53
uci set firewall.@redirect[-1].dest_port=53
uci set firewall.@redirect[-1].dest_ip=$(uci get network.lan.ipaddr)
uci set firewall.@redirect[-1].target=DNAT

echo >/etc/h t t p d_redirect.conf 'A:/:/cgi-bin/redirect.cgi'

cat >/w w w/cgi-bin/redirect.cgi <<EOM
#!/bin/sh
echo Status: 302 found
echo Location: h t t p://$(uci get network.lan.ipaddr)
echo Cache-Control: no-cache
echo
echo You are headed for h t t p ://$(uci get network.lan.ipaddr)
EOM

chmod +x /w w w/cgi-bin/redirect.cgi

uci set u h t t p d.redirect='u h t t p d'
uci set u h t t p d.redirect.listen_h t t p='0.0.0.0:8080'
uci set u h t t p d.redirect.cgi_prefix='/cgi-bin'
uci set u h t t p d.redirect.config='/etc/h t t p d_redirect.conf'
uci set u h t t p d.redirect.home='/w w w'

cat >/w w w/cgi-bin/control.cgi <<'EOM'
   #!/bin/sh
   echo $QUERY_STRING >/dev/ttyATH0
   echo Cache-Control: no-cache
   echo Content-type: text/plain
   echo
   echo Command sent
   EOM

chmod +x /w w w/cgi-bin/control.cgi

uci commit

reboot -d 1 &
 */
