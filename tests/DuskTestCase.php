<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use LogicException;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    #[BeforeClass]
    public static function prepare(): void
    {
        $port = getenv('VAULT_DUSK_DRIVER_PORT');
        if (getenv('VAULT_DUSK_ISOLATED') !== 'true' || $port === false || ! ctype_digit($port)) {
            throw new LogicException('Run browser tests with composer dusk.');
        }

        static::startChromeDriver(['--port='.$port]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $database = config('database.connections.sqlite.database');
        if (! config('dusk.isolated') || config('database.default') !== 'sqlite' ||
            ! preg_match('/\Avault-dusk-[a-f0-9]{16}\.sqlite\z/', basename($database))) {
            throw new LogicException('Dusk requires a dedicated SQLite database.');
        }
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--headless=new',
            '--window-size=1280,900',
            '--disable-gpu',
            '--disable-smooth-scrolling',
        ]);

        return RemoteWebDriver::create(
            'http://127.0.0.1:'.getenv('VAULT_DUSK_DRIVER_PORT'),
            DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options),
        );
    }
}
