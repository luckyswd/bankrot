<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class HealthControllerTest extends BaseTestCase
{
    public function testHealthIsPubliclyAvailable(): void
    {
        $this->client->request(method: 'GET', uri: '/api/v1/health');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertSame('ok', $response['status']);
        $this->assertSame('ok', $response['database']);
    }
}
