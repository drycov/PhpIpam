<?php

namespace App\Plugins\PhpIpam\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Plugins\PhpIpam\Models\PhpIpamSync;
use LibreNMS\Config;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiController extends Controller
{
    /**
     * Отображение списка синхронизированных записей phpIPAM
     */
    public function index(Request $request)
    {
        $records = PhpIpamSync::orderByDesc('updated_at')->paginate(50);
        $cfg = Config::get('phpipam');

        return view('PhpIpam::index', [
            'records' => $records,
            'last_sync' => $this->getLastSyncTime(),
            'phpipam_url' => $cfg['url'] ?? null,
        ]);
    }

    /**
     * Запускает синхронизацию phpIPAM вручную из веб-интерфейса
     */
    public function sync(Request $request)
    {
        try {
            $exitCode = Artisan::call('phpipam:sync', ['--verbose' => true]);
            $output = Artisan::output();

            Log::info('[PhpIpamPlugin] Manual sync executed', [
                'user' => $request->user()->username ?? 'system',
                'exitCode' => $exitCode,
            ]);

            return redirect()
                ->back()
                ->with('status', "✅ Sync completed (exit code: {$exitCode})")
                ->with('output', $output);

        } catch (\Throwable $e) {
            Log::error('[PhpIpamPlugin] Sync failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('status', '❌ Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint для AJAX / JSON-клиентов
     * GET /phpipam-plugin/api/records
     */
    public function apiRecords()
    {
        $records = PhpIpamSync::select('device_id', 'ip_address', 'subnet', 'status', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        return response()->json([
            'count' => $records->count(),
            'last_sync' => $this->getLastSyncTime(),
            'records' => $records,
        ], 200);
    }

    /**
     * Проверка соединения с phpIPAM API
     */
    public function test()
    {
        $cfg = Config::get('phpipam');

        if (empty($cfg['url']) || empty($cfg['app_id']) || empty($cfg['token'])) {
            return redirect()->back()->with('status', '⚠️ Missing phpIPAM configuration parameters.');
        }

        try {
            $client = new Client([
                'base_uri' => rtrim($cfg['url'], '/'),
                'verify' => $cfg['verify_ssl'] ?? true,
                'timeout' => 5,
            ]);

            $response = $client->get("/{$cfg['app_id']}/user/", [
                'headers' => [
                    'token' => $cfg['token'],
                    'Accept' => 'application/json',
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            $username = $body['data']['username'] ?? 'Authorized';

            Log::info('[PhpIpamPlugin] Connection test success', ['user' => $username]);

            return redirect()->back()->with('status', "✅ Connection OK (user: {$username})");

        } catch (RequestException $e) {
            $message = $e->getResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage();

            Log::warning('[PhpIpamPlugin] Connection failed', ['error' => $message]);
            return redirect()->back()->with('status', '❌ Connection failed: ' . $message);

        } catch (\Throwable $e) {
            Log::error('[PhpIpamPlugin] Unexpected error: ' . $e->getMessage());
            return redirect()->back()->with('status', '❌ Error: ' . $e->getMessage());
        }
    }

    /**
     * Получить время последней записи в таблице phpipam_sync
     */
    protected function getLastSyncTime(): ?string
    {
        $last = PhpIpamSync::orderByDesc('updated_at')->first();
        return $last ? $last->updated_at->toDateTimeString() : null;
    }
}
