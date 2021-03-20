<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

class PhakeClientTest extends TestCase
{
    protected function setUp(): void
    {
        // unset the $client in Phake
        $refClass = new ReflectionClass('Phake');
        $clientProperty = $refClass->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue(null);
    }

    public function testAutoDetectsPHPUnitClient()
    {
        $client = Phake::getClient();
        $this->assertInstanceOf('Phake\Client\PHPUnit' . substr(Version::id(), 0, 1), $client);
    }
}
