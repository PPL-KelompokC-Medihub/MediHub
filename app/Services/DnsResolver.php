<?php

namespace App\Services;

class DnsResolver
{
    /** @var array<string, string> */
    private static array $resolvedIps = [];

    /**
     * @return array<int, string>
     */
    public static function getDnsResolveMapping(): array
    {
        $hosts = [
            'oauth2.googleapis.com',
            'firestore.googleapis.com',
            'identitytoolkit.googleapis.com'
        ];
        $mappings = [];

        foreach ($hosts as $host) {
            if (! isset(self::$resolvedIps[$host])) {
                $ip = self::resolveHost($host);
                if ($ip) {
                    self::$resolvedIps[$host] = $ip;
                }
            }

            if (isset(self::$resolvedIps[$host])) {
                $ip = self::$resolvedIps[$host];
                $mappings[] = "$host:443:$ip";
            }
        }

        return $mappings;
    }

    private static function resolveHost(string $host): ?string
    {
        try {
            $records = dns_get_record($host, DNS_A) ?: [];

            foreach ($records as $record) {
                $ip = $record['ip'] ?? null;

                if (is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return $ip;
                }
            }
        } catch (\Throwable) {
            // Let the HTTP client perform normal DNS resolution.
        }

        try {
            foreach (gethostbynamel($host) ?: [] as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return $ip;
                }
            }
        } catch (\Throwable) {
            // Let the HTTP client perform normal DNS resolution.
        }

        return null;
    }
}
