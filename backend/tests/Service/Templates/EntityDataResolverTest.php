<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Service\Templates\EntityDataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityDataResolverTest extends TestCase
{
    private EntityDataResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor());
    }

    public function testZeroIsResolvedAsDigit(): void
    {
        $contract = (new Contracts())->setClaimsRejectedCount(0);

        $this->assertSame('0', $this->resolver->resolveValue(contract: $contract, path: 'claimsRejectedCount'));
    }

    public function testEmptyFieldIsResolvedAsEmptyString(): void
    {
        $this->assertSame('', $this->resolver->resolveValue(contract: new Contracts(), path: 'caseNumber'));
    }

    public function testFilledFieldIsResolved(): void
    {
        $contract = (new Contracts())->setCaseNumber('А56-12578/2025');

        $this->assertSame('А56-12578/2025', $this->resolver->resolveValue(contract: $contract, path: 'caseNumber'));
    }
}
