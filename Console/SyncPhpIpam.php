<?php

namespace App\Plugins\PhpIpam\Console;

use Illuminate\Console\Command;
use LibreNMS\Config;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Plugins\PhpIpam\Models\PhpIpamSync;

class SyncPhpIpam extends Command
{
    protected $signature = 'phpipam:sync {--verbose}';
    protected $description = 'Synchronize IP data between phpIPAM and LibreNMS';

    public function handle()
    {
        $cfg = Config::get('phpipam');

        if (empty($cfg['url']) || empty($cfg['app_id']) || empty($cfg['token'])) {
            $this->error('phpIPAM configuration is missing! Please check plugin settings.');
            return Command::FAILURE;
        }

        $this->info("Connecting to phpIPAM API at {$cfg['url']} ...");

        $client = new Client([
            'base_uri' => rtrim($cfg['url'], '/'),
            'verify' => $cfg['verify_ssl'] ?? true,
            'timeout' => 15,
        ]);

        try {
            // --- Step 1: Request address list from phpIPAM ---
            $endpoint = "/{$cfg['app_id']}/addresses/";
            $response = $client->get($endpoint, [
                'headers' => [
                    'token' => $cfg['token'],
                    'Accept' => 'application/json',
                ],
            ]);

            $body = (string)$response->getBody();
            $data = json_decode($body, true);

            if (!isset($data['data']) || !is_array($data['data'])) {
                $this->warn("Unexpected phpIPAM API response structure.");
                return Command::FAILURE;
            }

            $records = $data['data'];
            $count = 0;

            // --- Step 2: Store or update entries ---
            foreach ($records as $item) {
                PhpIpamSync::updateOrCreate(
                    [
                        'ip_address' => $item['ip'] ?? null,
                        'subnet' => $item['subnetId'] ?? null,
                    ],
                    [
                        'device_id' => $item['deviceId'] ?? null,
                        'status' => $item['state'] ?? 'unknown',
                    ]
                );
                $count++;
            }

            $this->info("phpIPAM sync complete. Imported/updated records: {$count}");

        } catch (RequestException $e) {
            $this->error("HTTP request failed: " . $e->getMessage());
            if ($this->option('verbose')) {
                $this->line($e->getTraceAsString());
            }
            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error("Unexpected error: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
