<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    private const STATUS_OK = 'ok';
    private const STATUS_ERROR = 'error';

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    #[Route('/api/v1/health', name: 'api_health', methods: ['GET'])]
    #[OA\Tag(name: 'Health')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Приложение работает, база данных доступна',
    )]
    #[OA\Response(
        response: Response::HTTP_SERVICE_UNAVAILABLE,
        description: 'База данных недоступна',
    )]
    public function health(): JsonResponse
    {
        try {
            $this->connection->executeQuery(sql: 'SELECT 1');
        } catch (DbalException) {
            return $this->json([
                'status' => self::STATUS_ERROR,
                'database' => self::STATUS_ERROR,
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $this->json([
            'status' => self::STATUS_OK,
            'database' => self::STATUS_OK,
        ]);
    }
}
