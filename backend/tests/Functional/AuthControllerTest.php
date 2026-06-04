<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testSignupReturnsUser(): void
    {
        $client = static::createClient();
        $client->request('POST', '/signup', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Test User',
            'email' => 'test_functional@example.com',
            'password' => 'password123',
        ]));

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Test User', $data['name']);
        $this->assertSame('test_functional@example.com', $data['email']);
        $this->assertFalse($data['isAdmin']);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayNotHasKey('password', $data);
    }

    public function testSignupWithInvalidDataReturns400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/signup', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => '',
            'email' => 'invalid',
            'password' => '123',
        ]));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();

        // Create user first
        $client->request('POST', '/signup', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Login Test',
            'email' => 'login_test@example.com',
            'password' => 'password123',
        ]));

        // Login
        $client->request('POST', '/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'login_test@example.com',
            'password' => 'password123',
        ]));

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Login Test', $data['name']);
        $this->assertSame('login_test@example.com', $data['email']);
    }

    public function testLoginWithInvalidCredentialsReturns400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'wrong',
        ]));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }

    public function testWhoAmIRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/whoAmI');

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testLogoutReturnsNoContent(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/logout');

        $this->assertSame(204, $client->getResponse()->getStatusCode());
    }
}
