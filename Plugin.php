<?php
namespace App\Plugins\PhpIpam;

use LibreNMS\Interfaces\Plugin;
use Illuminate\Support\Facades\Event;

class PhpIpamPlugin extends Plugin
{
    public function boot()
    {
        // Регистрация роутов
        $this->loadRoutesFrom(__DIR__ . '/Routes.php');

        // Регистрация миграций
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Hook: после добавления устройства в LibreNMS
        Event::listen('device.created', function ($device) {
            // возможно синхронизировать с phpIPAM
        });

        // Можно подписаться на poller hook
        $this->registerPollerHook('phpipam.sync', function ($device) {
            // тут запустить синхронизацию для данного устройства
        });
    }

    public function pluginDetails()
    {
        return [
            'name' => 'phpIPAM Integration',
            'description' => 'Sync devices and IP data between LibreNMS and phpIPAM',
            'author' => 'YourName',
            'version' => '0.1',
        ];
    }
    public function getConfigView()
{
    return view('PhpIpam::settings', [
        'config' => [
            'url' => Config::get('phpipam.url'),
            'token' => Config::get('phpipam.token'),
            'username' => Config::get('phpipam.username'),
            'password' => Config::get('phpipam.password'),
            'app_id' => Config::get('phpipam.app_id'),
            'verify_ssl' => Config::get('phpipam.verify_ssl'),
            'sync_interval' => Config::get('phpipam.sync_interval'),
        ],
    ]);
}

public function saveConfig($data)
{
    foreach ($data as $key => $value) {
        Config::set("phpipam.$key", $value);
    }
}

}
