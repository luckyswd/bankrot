<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Service\Templates\EntityDataResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CreditorsRegisterPlaceholdersTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function placeholders(): array
    {
        return [
            'ФИО в родительном падеже' => ['fullNameGenitive', 'Тереховой Светланы Александровны'],
            'дата рождения' => ['birthDate', '23.10.1982'],
            'место рождения' => ['birthPlace', 'гор. Ленинград'],
            'СНИЛС' => ['snils', '141-362-038 17'],
            'ИНН' => ['inn', '782609400697'],
            'адрес регистрации с индексом' => ['registrationAddressWithPostalCode', '196233, г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18'],
            'номер дела' => ['caseNumber', 'А56-117152/2023'],
            'дата открытия' => ['registryOpeningDateShort', '17.05.2025 г.'],
            'дата закрытия' => ['registryClosingDateShort', '17.07.2025 г.'],
            'кредиторов в части 2' => ['registryMainCreditorsCount', '2'],
            'кредиторов в части 4' => ['registrySanctionCreditorsCount', '2'],
            'требований в части 2' => ['registryMainClaimsCount', '2'],
            'требований в части 4' => ['registrySanctionClaimsCount', '2'],
            'размер части 2' => ['registryMainAmountText', '3616862,59'],
            'размер части 4' => ['registrySanctionAmountText', '2206637,98'],
            'погашено в части 2' => ['registryMainRepaidAmountText', '0,00'],
            'погашено в части 4' => ['registrySanctionRepaidAmountText', '0,00'],
            'процент погашения части 2' => ['registryMainRepaidPercentText', '0,00'],
            'процент погашения части 4' => ['registrySanctionRepaidPercentText', '0,00'],
        ];
    }

    #[DataProvider('placeholders')]
    public function testPlaceholderResolvesToDocumentText(string $path, string $expected): void
    {
        $resolver = new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor());

        $this->assertSame($expected, $resolver->resolveValue(contract: RegisterContractFactory::create(), path: $path));
    }

    public function testRowVariablesResolveFromCollectionItem(): void
    {
        $resolver = new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor());
        $row = RegisterContractFactory::create()->getRegistryMainClaims()->first();

        $this->assertNotFalse($row);
        $this->assertSame('1', $resolver->resolveValueFromObject(object: $row, path: 'creditorNumber'));
        $this->assertSame('Кредит', $resolver->resolveValueFromObject(object: $row, path: 'kind'));
        $this->assertSame('2116862,59', $resolver->resolveValueFromObject(object: $row, path: 'amountText'));
    }
}
