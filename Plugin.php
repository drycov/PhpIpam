<?php
namespace App\Plugins\PhpIpam;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;

/**
 * If LibreNMS defines a Plugin interface/class, we want to prefer that
 * to ensure full compatibility. If not available, we'll use the
 * Laravel ServiceProvider as the base class.
 */
if (!class_exists('\LibreNMS\\Interfaces\\Plugin') && !interface_exists('\LibreNMS\\Interfaces\\Plugin')) {
    // Create a lightweight alias so `extends PluginBase` still works.
    class_alias(ServiceProvider::class, '\LibreNMS\\Interfaces\\Plugin');
}

// Provide a no-op trait for environments without LibreNMS hook methods
trait PhpIpamPluginCompatibilityTrait
{
    // LibreNMS provides registerPollerHook in their plugin base; provide a safe no-op
    public function registerPollerHook($name, callable $cb)
    {
        // no-op outside LibreNMS environment
        return false;
    }
}

/**
 * Compatibility plugin/provider for LibreNMS.
 *
 * This class extends Laravel's ServiceProvider so it can be autoloaded
 * outside of a full LibreNMS install. LibreNMS-specific hooks are
 * called only when the methods exist (guarded with method_exists).
 */
class PhpIpamPlugin extends \LibreNMS\Interfaces\Plugin
{
    use PhpIpamPluginCompatibilityTrait;
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

        // Можно подписаться на poller hook (LibreNMS-specific)
        if (method_exists($this, 'registerPollerHook')) {
            $this->registerPollerHook('phpipam.sync', function ($device) {
                // тут запустить синхронизацию для данного устройства
            });
        }
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
