from pathlib import Path
root=Path('.')
services={
'auth-service':'auth_service','mahasiswa-service':'mahasiswa_service','klinik-service':'klinik_service','bank-service':'bank_service','ppl-service':'ppl_service'}
base_composer=lambda name: f'''{{
  "name": "simpus/{name}",
  "type": "project",
  "description": "SIMPUS {name.replace('-', ' ').title()} backend service",
  "require": {{
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "laravel/sanctum": "^4.0"
  }},
  "require-dev": {{
    "fakerphp/faker": "^1.23",
    "laravel/pint": "^1.18",
    "phpunit/phpunit": "^11.0"
  }},
  "autoload": {{"psr-4": {{"App\\\\": "app/", "Database\\\\Factories\\\\": "database/factories/", "Database\\\\Seeders\\\\": "database/seeders/"}}}},
  "scripts": {{"post-autoload-dump": ["Illuminate\\\\Foundation\\\\ComposerScripts::postAutoloadDump", "@php artisan package:discover --ansi"]}}
}}\n'''
for s in services:
    p=root/s
    for d in ['app/Http/Controllers/Api','app/Http/Requests','app/Models','bootstrap','config','database/migrations','database/seeders','routes']:
        (p/d).mkdir(parents=True, exist_ok=True)
    (p/'composer.json').write_text(base_composer(s))
    (p/'artisan').write_text("""#!/usr/bin/env php
<?php
use Illuminate\\Foundation\\Application;
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$status = $app->handleCommand(new Symfony\\Component\\Console\\Input\\ArgvInput);
exit($status);
""")
    (p/'bootstrap/app.php').write_text("""<?php
use Illuminate\\Foundation\\Application;
use Illuminate\\Foundation\\Configuration\\Exceptions;
use Illuminate\\Foundation\\Configuration\\Middleware;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(api: __DIR__.'/../routes/api.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {})
    ->withExceptions(function (Exceptions $exceptions): void {})
    ->create();
""")
    (p/'routes/console.php').write_text("<?php\n\n")
    (p/'.env.example').write_text(f"APP_NAME={s}\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE={services[s]}\nDB_USERNAME=simpus\nDB_PASSWORD=secret\n")
print('scaffolded')
