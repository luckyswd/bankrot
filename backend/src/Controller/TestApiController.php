<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestApiController extends AbstractController
{
    #[Route('/api/v1/test', name: 'api_test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'message' => 'API работает! 🚀',
            'timestamp' => time(),
            'TEST' => 'TEST ',
            'data' => [
                'backend' => 'Symfony 6.4',
                'php_version' => PHP_VERSION,
            ],
        ]);
    }

    #[Route('/api/v1/health', name: 'api_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
            'service' => 'bankruptcy-api',
        ]);
    }
}
