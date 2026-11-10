# AI-Proof Message Vault

Капсула времени для текста. Порядок разработки и критерии приёмки описаны в [плане реализации](PLAN.md).

## Локальный запуск на Windows

Нужны PHP 8.4, Composer 2, Node.js 24 и npm. Для локальной базы используется SQLite.

Проверьте инструменты из корня проекта:

```powershell
.\scripts\check-platform.ps1
```

Если PHP и Composer распакованы отдельно, добавьте каталог с `php.exe` в `PATH` текущего сеанса и передайте путь к PHAR:

```powershell
$env:Path = "C:\Tools\php;$env:Path"
.\scripts\check-platform.ps1 -ComposerPhar C:\Tools\composer.phar
```

Для каждой команды `composer` ниже с переносимым PHAR используйте `php C:\Tools\composer.phar`.

```powershell
composer install
if (!(Test-Path .env)) {
    Copy-Item .env.example .env
    php artisan key:generate
}
php artisan migrate
npm.cmd ci
npm.cmd run build
php artisan serve
```

Откройте <http://127.0.0.1:8000>. Проверка доступности находится по адресу <http://127.0.0.1:8000/up>.

```powershell
php artisan test
```
