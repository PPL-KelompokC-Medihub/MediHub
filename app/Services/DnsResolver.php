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
                $ip = self::resolveHostViaNslookup($host);
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

    private static function resolveHostViaNslookup(string $host): ?string
    {
        try {
            $cmd = sprintf('nslookup %s 8.8.8.8', escapeshellarg($host));
            $output = shell_exec($cmd);
            if (! $output) {
                return null;
            }

            $parts = explode('Name:', $output);
            if (count($parts) < 2) {
                return null;
            }

            if (preg_match_all('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $parts[1], $matches)) {
                return $matches[0][0] ?? null;
            }
        } catch (\Throwable) {
            // fallback
        }

        return null;
    }
}
