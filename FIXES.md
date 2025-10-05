Fixes and notes for Plugin.php

Issue
-----
When this plugin was loaded inside LibreNMS it crashed with: Error Class "LibreNMS\\Interfaces\\Plugin" not found.

What I changed
--------------
- `Plugin.php` previously attempted to extend `LibreNMS\Interfaces\Plugin` which isn't present in this repository copy.
- Replaced that dependency by extending Laravel's `Illuminate\Support\ServiceProvider` and added missing facade imports (`Event`, `Config`).

Why
---
LibreNMS plugins can be provided in different shapes. Extending a Laravel ServiceProvider is a compatible approach for local plugin code so the class will load without failing due to a missing interface in environments where the LibreNMS-specific interface isn't autoloadable.

Notes and next steps
--------------------
- This change avoids the fatal PHP error when the file is autoloaded outside a full LibreNMS installation. However, full runtime integration with LibreNMS requires testing inside a LibreNMS instance and might require switching back to the official `LibreNMS\Interfaces\Plugin` if present.
- If you run this inside LibreNMS and the original interface exists, consider changing the class to implement that interface instead of extending ServiceProvider, or register the plugin via the mechanism LibreNMS expects.
