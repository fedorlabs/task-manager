<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnauthorizedAccessTest extends WebTestCase
{
    public function testTasksRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');

        $status = $client->getResponse()->getStatusCode();
        $this->assertContains($status, [401, 403]);
    }
}
