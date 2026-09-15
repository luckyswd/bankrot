<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\DateHelperService;
use PHPUnit\Framework\TestCase;

class DateHelperServiceTest extends TestCase
{
    public function testFormatGenitive(): void
    {
        $this->assertSame('«02» июля 2025 г.', DateHelperService::formatGenitive(date: new \DateTimeImmutable('2025-07-02')));
    }

    public function testFormatGenitiveOfEmptyDate(): void
    {
        $this->assertSame('', DateHelperService::formatGenitive(date: null));
    }
}
