<?php

declare(strict_types=1);

namespace UciGraphQL\Tests\Utils;

use PHPUnit\Framework\TestCase;
use UciGraphQL\Utils\UciCommand;
use UciGraphQL\Utils\UciSection;

class UciCommandDump extends UciCommand
{
    /**
     * @var string
     */
    public static $uciOutputCommand;

    /**
     * @inheritdoc
     */
    public static function getConfigurationCommand(): array
    {
        return explode(PHP_EOL, self::$uciOutputCommand);
    }
}

final class UciCommandTest extends TestCase
{
    /**
     * @return iterable<array{input: string, expectations: array}>
     */
    public function uciConfigDataProvider(): iterable
    {
        yield 'allConfig' => [
            'input' => "
        dhcp.@dnsmasq[0]=dnsmasq
        dhcp.@dnsmasq[0].domainneeded='1'
        dhcp.@dnsmasq[0].boguspriv='1'
        dhcp.@dnsmasq[0].filterwin2k='0'
        dhcp.@dnsmasq[0].localise_queries='1'
        dhcp.@dnsmasq[0].rebind_protection='1'
        dhcp.@dnsmasq[0].rebind_localhost='1'
        dhcp.@dnsmasq[0].local='/lan/'
        dhcp.@dnsmasq[0].domain='lan'
        dhcp.@dnsmasq[0].expandhosts='1'
        dhcp.@dnsmasq[0].nonegcache='0'
        dhcp.@dnsmasq[0].authoritative='1'
        dhcp.@dnsmasq[0].readethers='1'
        dhcp.@dnsmasq[0].leasefile='/tmp/dhcp.leases'
        dhcp.@dnsmasq[0].resolvfile='/tmp/resolv.conf.d/resolv.conf.auto'
        dhcp.@dnsmasq[0].nonwildcard='1'
        dhcp.@dnsmasq[0].localservice='1'
        dhcp.@dnsmasq[0].ednspacket_max='1232'
        dhcp.lan=dhcp
        dhcp.lan.interface='lan'
        dhcp.lan.start='100'
        dhcp.lan.limit='150'
        dhcp.lan.leasetime='12h'
        dhcp.wan=dhcp
        dhcp.wan.interface='wan'
        dhcp.wan.ignore='1'
        dropbear.@dropbear[0]=dropbear
        dropbear.@dropbear[0].PasswordAuth='on'
        dropbear.@dropbear[0].RootPasswordAuth='on'
        dropbear.@dropbear[0].Port='22'
        firewall.@defaults[0]=defaults
        firewall.@defaults[0].syn_flood='1'
        firewall.@defaults[0].input='ACCEPT'
        firewall.@defaults[0].output='ACCEPT'
        firewall.@defaults[0].forward='REJECT'
        firewall.@zone[0]=zone
        firewall.@zone[0].name='lan'
        firewall.@zone[0].network='lan'
        firewall.@zone[0].input='ACCEPT'
        firewall.@zone[0].output='ACCEPT'
        firewall.@zone[0].forward='ACCEPT'
        firewall.@zone[1]=zone
        firewall.@zone[1].name='wan'
        firewall.@zone[1].network='wan' 'wan6'
        firewall.@zone[1].input='REJECT'
        firewall.@zone[1].output='ACCEPT'
        firewall.@zone[1].forward='REJECT'
        firewall.@zone[1].masq='1'
        firewall.@zone[1].mtu_fix='1'
        firewall.@forwarding[0]=forwarding
        firewall.@forwarding[0].src='lan'
        firewall.@forwarding[0].dest='wan'
        firewall.@rule[0]=rule
        firewall.@rule[0].name='Allow-DHCP-Renew'
        firewall.@rule[0].src='wan'
        firewall.@rule[0].proto='udp'
        firewall.@rule[0].dest_port='68'
        firewall.@rule[0].target='ACCEPT'
        firewall.@rule[0].family='ipv4'
        firewall.@rule[1]=rule
        firewall.@rule[1].name='Allow-Ping'
        firewall.@rule[1].src='wan'
        firewall.@rule[1].proto='icmp'
        firewall.@rule[1].icmp_type='echo-request'
        firewall.@rule[1].family='ipv4'
        firewall.@rule[1].target='ACCEPT'
        firewall.@rule[2]=rule
        firewall.@rule[2].name='Allow-IGMP'
        firewall.@rule[2].src='wan'
        firewall.@rule[2].proto='igmp'
        firewall.@rule[2].family='ipv4'
        firewall.@rule[2].target='ACCEPT'
        firewall.@rule[3]=rule
        firewall.@rule[3].name='Allow-DHCPv6'
        firewall.@rule[3].src='wan'
        firewall.@rule[3].proto='udp'
        firewall.@rule[3].dest_port='546'
        firewall.@rule[3].family='ipv6'
        firewall.@rule[3].target='ACCEPT'
        firewall.@rule[4]=rule
        firewall.@rule[4].name='Allow-MLD'
        firewall.@rule[4].src='wan'
        firewall.@rule[4].proto='icmp'
        firewall.@rule[4].src_ip='fe80::/10'
        firewall.@rule[4].icmp_type='130/0' '131/0' '132/0' '143/0'
        firewall.@rule[4].family='ipv6'
        firewall.@rule[4].target='ACCEPT'
        firewall.@rule[5]=rule
        firewall.@rule[5].name='Allow-ICMPv6-Input'
        firewall.@rule[5].src='wan'
        firewall.@rule[5].proto='icmp'
        firewall.@rule[5].icmp_type='echo-request' 'echo-reply' 'destination-unreachable' 'packet-too-big' 'time-exceeded' 'bad-header' 'unknown-header-type' 'router-solicitation' 'neighbour-solicitation' 'router-advertisement' 'neighbour-advertisement'
        firewall.@rule[5].limit='1000/sec'
        firewall.@rule[5].family='ipv6'
        firewall.@rule[5].target='ACCEPT'
        firewall.@rule[6]=rule
        firewall.@rule[6].name='Allow-ICMPv6-Forward'
        firewall.@rule[6].src='wan'
        firewall.@rule[6].dest='*'
        firewall.@rule[6].proto='icmp'
        firewall.@rule[6].icmp_type='echo-request' 'echo-reply' 'destination-unreachable' 'packet-too-big' 'time-exceeded' 'bad-header' 'unknown-header-type'
        firewall.@rule[6].limit='1000/sec'
        firewall.@rule[6].family='ipv6'
        firewall.@rule[6].target='ACCEPT'
        firewall.@rule[7]=rule
        firewall.@rule[7].name='Allow-IPSec-ESP'
        firewall.@rule[7].src='wan'
        firewall.@rule[7].dest='lan'
        firewall.@rule[7].proto='esp'
        firewall.@rule[7].target='ACCEPT'
        firewall.@rule[8]=rule
        firewall.@rule[8].name='Allow-ISAKMP'
        firewall.@rule[8].src='wan'
        firewall.@rule[8].dest='lan'
        firewall.@rule[8].dest_port='500'
        firewall.@rule[8].proto='udp'
        firewall.@rule[8].target='ACCEPT'
        network.loopback=interface
        network.loopback.ifname='lo'
        network.loopback.proto='static'
        network.loopback.ipaddr='127.0.0.1'
        network.loopback.netmask='255.0.0.0'
        network.wan=interface
        network.wan.ifname='eth0'
        network.wan.proto='dhcp'
        network.wan6=interface
        network.wan6.ifname='eth0'
        network.wan6.proto='dhcp6'
            ",
            'expectations' => [
                [
                    'assert' => 'assertArrayHasKey',
                    'key' => 'network',
                    'message' => 'Must contain network field',
                ],
                [
                    'assert' => 'assertIsArray',
                    'config' => 'firewall',
                    'section' => 'rule',
                    'message' => 'Must be an array firewall.rule',
                ],
                [
                    'assert' => 'assertIsObject',
                    'config' => 'network',
                    'section' => 'wan',
                    'message' => 'Must be an instance of ' . UciSection::class . ' network.wan',
                ],
            ],
        ];
    }

    /**
     * @dataProvider uciConfigDataProvider
     * @return void
     */
    public function testLoadAllConfigUciSystem(string $input, array $expectations)
    {
        UciCommandDump::$uciOutputCommand = $input;
        $result = UciCommandDump::getUciConfiguration();

        foreach ($expectations as $expected) {
            switch ($expected['assert']) {
                case 'assertArrayHasKey':
                    self::assertArrayHasKey($expected['key'], $result, $expected['message']);
                    continue 2;
                case 'assertIsArray':
                    self::assertIsArray($result[$expected['config']][$expected['section']], $expected['message']);
                    continue 2;
                case 'assertIsObject':
                    self::assertInstanceOf(UciSection::class, $result[$expected['config']][$expected['section']], $expected['message']);
                    continue 2;
            }
        }
    }
}
