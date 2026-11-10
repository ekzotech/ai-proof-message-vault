<?php

namespace Tests;

use App\Capsules\TransformationPreset;
use App\Models\Capsule;
use App\Models\CapsulePublication;
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

    protected function publishFixture(string $original, array $segments, string $title = 'Браузерная капсула'): CapsulePublication
    {
        $draft = Capsule::factory()->create(['title' => $title, 'body' => $original]);
        $publication = $draft->publication()->make([
            'title' => $title,
            'opens_at' => now()->subMinute(),
            'algorithm_version' => 1,
            'seed' => $draft->transformation_seed,
            'preset' => TransformationPreset::Experimental,
            'segments' => $segments,
            'accessible_text' => $original,
            'author_attested' => true,
        ]);
        $publication->issueReaderToken();
        $publication->save();

        return $publication;
    }
}
