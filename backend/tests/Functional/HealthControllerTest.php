<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthControllerTest extends WebTestCase
{
    public function testHealthEndpointReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        $status = $client->getResponse()->getStatusCode();
        $this->assertContains($status, [200, 503]);
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }
}
