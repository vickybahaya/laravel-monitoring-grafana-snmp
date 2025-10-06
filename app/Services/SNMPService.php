<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class SNMPService
{
    // Common SNMP OIDs
    const OID_SYSTEM_UPTIME = '1.3.6.1.2.1.1.3.0';
    const OID_SYSTEM_DESCRIPTION = '1.3.6.1.2.1.1.1.0';
    const OID_SYSTEM_NAME = '1.3.6.1.2.1.1.5.0';
    const OID_SYSTEM_LOCATION = '1.3.6.1.2.1.1.6.0';
    
    // Interface OIDs
    const OID_IF_NUMBER = '1.3.6.1.2.1.2.1.0';
    const OID_IF_TABLE = '1.3.6.1.2.1.2.2.1';
    const OID_IF_DESCR = '1.3.6.1.2.1.2.2.1.2';
    const OID_IF_OPER_STATUS = '1.3.6.1.2.1.2.2.1.8';
    const OID_IF_IN_OCTETS = '1.3.6.1.2.1.2.2.1.10';
    const OID_IF_OUT_OCTETS = '1.3.6.1.2.1.2.2.1.16';
    
    // MikroTik specific OIDs
    const OID_MIKROTIK_CPU_LOAD = '1.3.6.1.4.1.14988.1.1.3.14.0';
    const OID_MIKROTIK_MEMORY_TOTAL = '1.3.6.1.2.1.25.2.3.1.5.65536';
    const OID_MIKROTIK_MEMORY_USED = '1.3.6.1.2.1.25.2.3.1.6.65536';
    const OID_MIKROTIK_DISK_TOTAL = '1.3.6.1.2.1.25.2.3.1.5.131072';
    const OID_MIKROTIK_DISK_USED = '1.3.6.1.2.1.25.2.3.1.6.131072';
    
    private int $timeout = 5;
    private int $retries = 2;

    /**
     * Check if router is reachable via SNMP
     */
    public function checkConnection(
        string $ipAddress,
        string $community = 'public',
        string $version = '2c',
        int $port = 161
    ): bool {
        try {
            $result = $this->get($ipAddress, self::OID_SYSTEM_UPTIME, $community, $version, $port);
            return $result !== false;
        } catch (Exception $e) {
            Log::error("SNMP connection check failed for {$ipAddress}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get system information
     */
    public function getSystemInfo(
        string $ipAddress,
        string $community = 'public',
        string $version = '2c',
        int $port = 161
    ): array {
        try {
            return [
                'uptime' => $this->get($ipAddress, self::OID_SYSTEM_UPTIME, $community, $version, $port),
                'description' => $this->get($ipAddress, self::OID_SYSTEM_DESCRIPTION, $community, $version, $port),
                'name' => $this->get($ipAddress, self::OID_SYSTEM_NAME, $community, $version, $port),
                'location' => $this->get($ipAddress, self::OID_SYSTEM_LOCATION, $community, $version, $port),
            ];
        } catch (Exception $e) {
            Log::error("Failed to get system info for {$ipAddress}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get router resources (CPU, Memory, Disk)
     */
    public function getResources(
        string $ipAddress,
        string $community = 'public',
        string $version = '2c',
        int $port = 161
    ): array {
        try {
            $cpuLoad = $this->get($ipAddress, self::OID_MIKROTIK_CPU_LOAD, $community, $version, $port);
            $memoryTotal = $this->get($ipAddress, self::OID_MIKROTIK_MEMORY_TOTAL, $community, $version, $port);
            $memoryUsed = $this->get($ipAddress, self::OID_MIKROTIK_MEMORY_USED, $community, $version, $port);
            $diskTotal = $this->get($ipAddress, self::OID_MIKROTIK_DISK_TOTAL, $community, $version, $port);
            $diskUsed = $this->get($ipAddress, self::OID_MIKROTIK_DISK_USED, $community, $version, $port);

            return [
                'cpu_load' => $cpuLoad ? (int)$cpuLoad : 0,
                'memory_total' => $memoryTotal ? (int)$memoryTotal : 0,
                'memory_used' => $memoryUsed ? (int)$memoryUsed : 0,
                'memory_percent' => $memoryTotal > 0 ? round(($memoryUsed / $memoryTotal) * 100, 2) : 0,
                'disk_total' => $diskTotal ? (int)$diskTotal : 0,
                'disk_used' => $diskUsed ? (int)$diskUsed : 0,
                'disk_percent' => $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0,
            ];
        } catch (Exception $e) {
            Log::error("Failed to get resources for {$ipAddress}: " . $e->getMessage());
            return [
                'cpu_load' => 0,
                'memory_total' => 0,
                'memory_used' => 0,
                'memory_percent' => 0,
                'disk_total' => 0,
                'disk_used' => 0,
                'disk_percent' => 0,
            ];
        }
    }

    /**
     * Get interface statistics
     */
    public function getInterfaces(
        string $ipAddress,
        string $community = 'public',
        string $version = '2c',
        int $port = 161
    ): array {
        try {
            $ifNumber = $this->get($ipAddress, self::OID_IF_NUMBER, $community, $version, $port);
            $interfaces = [];

            if ($ifNumber) {
                for ($i = 1; $i <= (int)$ifNumber; $i++) {
                    $descr = $this->get($ipAddress, self::OID_IF_DESCR . ".{$i}", $community, $version, $port);
                    $status = $this->get($ipAddress, self::OID_IF_OPER_STATUS . ".{$i}", $community, $version, $port);
                    $inOctets = $this->get($ipAddress, self::OID_IF_IN_OCTETS . ".{$i}", $community, $version, $port);
                    $outOctets = $this->get($ipAddress, self::OID_IF_OUT_OCTETS . ".{$i}", $community, $version, $port);

                    $interfaces[] = [
                        'index' => $i,
                        'name' => $descr ?: "Interface {$i}",
                        'status' => $status == 1 ? 'up' : 'down',
                        'rx_bytes' => $inOctets ? (int)$inOctets : 0,
                        'tx_bytes' => $outOctets ? (int)$outOctets : 0,
                    ];
                }
            }

            return $interfaces;
        } catch (Exception $e) {
            Log::error("Failed to get interfaces for {$ipAddress}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single SNMP value
     */
    private function get(
        string $ipAddress,
        string $oid,
        string $community = 'public',
        string $version = '2c',
        int $port = 161
    ) {
        try {
            $target = "{$ipAddress}:{$port}";
            
            switch ($version) {
                case '1':
                    $result = @snmpget($target, $community, $oid, $this->timeout * 1000000, $this->retries);
                    break;
                case '2c':
                    $result = @snmp2_get($target, $community, $oid, $this->timeout * 1000000, $this->retries);
                    break;
                case '3':
                    // SNMPv3 requires additional parameters
                    // This is a simplified version, you may need to extend this
                    $result = @snmp3_get(
                        $target,
                        'noAuthNoPriv', // security level
                        '', // username
                        'MD5', // auth protocol
                        '', // auth passphrase
                        'DES', // priv protocol
                        '', // priv passphrase
                        $oid,
                        $this->timeout * 1000000,
                        $this->retries
                    );
                    break;
                default:
                    throw new Exception("Unsupported SNMP version: {$version}");
            }

            if ($result === false) {
                return false;
            }

            // Clean up the result (remove type prefix like "INTEGER: " or "STRING: ")
            return $this->cleanSnmpValue($result);
        } catch (Exception $e) {
            Log::error("SNMP GET failed for {$ipAddress} OID {$oid}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Walk SNMP tree
     */
    private function walk(
        string $ipAddress,
        string $oid,
        string $community = 'public',
        string $version = '2c',
        int $port = 161
    ): array {
        try {
            $target = "{$ipAddress}:{$port}";
            
            switch ($version) {
                case '1':
                    $result = @snmpwalk($target, $community, $oid, $this->timeout * 1000000, $this->retries);
                    break;
                case '2c':
                    $result = @snmp2_walk($target, $community, $oid, $this->timeout * 1000000, $this->retries);
                    break;
                default:
                    throw new Exception("SNMP walk not implemented for version: {$version}");
            }

            if ($result === false) {
                return [];
            }

            return array_map([$this, 'cleanSnmpValue'], $result);
        } catch (Exception $e) {
            Log::error("SNMP WALK failed for {$ipAddress} OID {$oid}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean SNMP value (remove type prefix)
     */
    private function cleanSnmpValue(string $value): string
    {
        // Remove type prefixes like "INTEGER: ", "STRING: ", "Timeticks: ", etc.
        $patterns = [
            '/^INTEGER:\s*/',
            '/^STRING:\s*/',
            '/^Timeticks:\s*$$\d+$$\s*/',
            '/^Counter32:\s*/',
            '/^Counter64:\s*/',
            '/^Gauge32:\s*/',
            '/^IpAddress:\s*/',
            '/^OID:\s*/',
        ];

        foreach ($patterns as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }

        return trim($value, '"');
    }

    /**
     * Set timeout for SNMP operations
     */
    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Set retries for SNMP operations
     */
    public function setRetries(int $retries): self
    {
        $this->retries = $retries;
        return $this;
    }
}
