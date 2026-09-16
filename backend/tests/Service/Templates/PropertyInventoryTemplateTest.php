<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\ContractsProperty;
use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Entity\Enum\PropertySubtype;
use App\Service\Templates\CustomFunction;
use App\Service\Templates\DocumentTemplateProcessor;
use App\Service\Templates\EntityDataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PropertyInventoryTemplateTest extends TestCase
{
    private const string TEMPLATE_FILE = '/src/document-templates/judicial_report/3. Опись имущества гражданина.docx';
    private const string FULL_NAME_GENITIVE = 'Тереховой Светланы Александровны';

    public function testDebtorDataIsSubstituted(): void
    {
        $documentXml = $this->process(contract: $this->contract());
        $text = $this->text(xml: $documentXml);

        $this->assertStringContainsString('Финансовый управляющий ' . self::FULL_NAME_GENITIVE, $text);
        $this->assertSame(2, substr_count($text, self::FULL_NAME_GENITIVE));
        $this->assertStringContainsString('Терехова Светлана Александровна', $text);
        $this->assertStringContainsString('Смирнова', $text);
        $this->assertStringContainsString('23.10.1982', $text);
        $this->assertStringContainsString('гор. Ленинград', $text);
        $this->assertStringContainsString('782609400697', $text);
        $this->assertStringContainsString('141-362-038 17', $text);
        $this->assertStringContainsString('г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18', $text);
        $this->assertStringContainsString('серия: 4005 номер: 312038', $text);
    }

    public function testInventoryDateIsSubstituted(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('проведена «02» июля 2025 г.', $text);
    }

    public function testAllPlaceholdersAreReplacedAndColorsRemoved(): void
    {
        $documentXml = $this->process(contract: $this->contract());
        $text = $this->text(xml: $documentXml);

        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringNotContainsString('${', $text);
        $this->assertTrue((new \DOMDocument())->loadXML($documentXml));
        $this->assertSame(0, preg_match_all('/<w:color w:val="(?:00B050|FF0000)"\/>/', $documentXml));
    }

    public function testPropertyTablesAreFilledFromCase(): void
    {
        $contract = $this->contract()
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::APARTMENT)
                    ->setName('квартира в многоквартирном доме')
                    ->setOwnershipType('общая долевая собственность, доля в праве ½')
                    ->setLocation('г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 18')
                    ->setArea('78,8')
            )
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::CAR)
                    ->setName('LADA GRANTA, 2019 г. в.')
                    ->setIdentificationNumber('XTA219010K0512345')
            );

        $text = $this->text(xml: $this->process(contract: $contract));

        $this->assertStringContainsString(
            'Квартиры: квартира в многоквартирном домеобщая долевая собственность, доля в праве ½г. Санкт-Петербург, ул. Савушкина, д. 18, кв. 1878,8',
            $text,
        );
        $this->assertStringContainsString('Автомобили легковые: LADA GRANTA, 2019 г. в.XTA219010K0512345', $text);
        $this->assertStringNotContainsString('Земельные участки:', $text);
    }

    public function testRemainingCategoriesAreFilledFromCase(): void
    {
        $contract = $this->contract()
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::BANK_ACCOUNT)
                    ->setName('ПАО «Сбербанк России»')
                    ->setAccountType('текущий, рубли')
                    ->setOpenedAt(new \DateTime('2019-03-12'))
                    ->setAmount('50 000,00')
            )
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::CASH)
                    ->setName('наличные денежные средства')
                    ->setAmount('20 000,00')
                    ->setCurrency('рубли')
            )
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::SHARES)
                    ->setName('ООО «Ромашка»')
                    ->setLocation('г. Архангельск, ул. Ленина, д. 1')
                    ->setAmount('10 000,00')
                    ->setParticipationShare('25%')
                    ->setBasisText('решение учредителя от 12.05.2018 г.')
            )
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::SECURITIES)
                    ->setName('облигация')
                    ->setIssuer('ПАО «Газпром»')
                    ->setAmount('1 000,00')
                    ->setQuantity('100')
            )
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::ART)
                    ->setName('картина «Рассвет»')
                    ->setLocation('сейф в банке')
            )
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::RECEIVABLES)
                    ->setName('Иванов Иван Иванович')
                    ->setAmount('30 000,00')
                    ->setObligationContent('заём по расписке')
                    ->setBasisText('расписка от 01.02.2020 г.')
            )
            ->addProperty(
                (new ContractsProperty())
                    ->setSubtype(PropertySubtype::EXCLUSIVE_RIGHTS)
                    ->setName('товарный знак № 123456')
            );

        $text = $this->text(xml: $this->process(contract: $contract));

        $this->assertStringContainsString('ПАО «Сбербанк России»текущий, рубли12.03.201950000,00', $text);
        $this->assertStringContainsString('20000,00рубли', $text);
        $this->assertStringContainsString('ООО «Ромашка»г. Архангельск, ул. Ленина, д. 110000,0025%решение учредителя от 12.05.2018 г.', $text);
        $this->assertStringContainsString('облигацияПАО «Газпром»1000,00100', $text);
        $this->assertStringContainsString('Предметы искусства: картина «Рассвет»сейф в банке', $text);
        $this->assertStringContainsString('Иванов Иван Иванович30000,00заём по распискерасписка от 01.02.2020 г.', $text);
        $this->assertStringContainsString('товарный знак № 123456', $text);
    }

    public function testPropertyTablesStayFormTextWithoutProperty(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        foreach ([
            'Земельные участки:',
            'Квартиры:',
            'Автомобили легковые:',
            'Иные транспортные средства:',
            'Денежные средства на расчетных счетах',
            'Наличные денежные средства',
            'Акции и иное участие в коммерческих организациях',
            'Ценные бумаги',
            'Драгоценности, в том числе ювелирные украшения, и другие предметы роскоши:',
            'Дебиторская задолженность',
            'Исключительные права на результаты интеллектуальной деятельности',
        ] as $section) {
            $this->assertStringContainsString($section, $text);
        }
    }

    public function testLastNameWasNotChanged(): void
    {
        $contract = $this->contract()
            ->setIsLastNameChanged(false)
            ->setChangedLastName(null);

        $text = $this->text(xml: $this->process(contract: $contract));

        $this->assertStringNotContainsString('Смирнова', $text);
        $this->assertStringContainsString('—', $text);
    }

    public function testEmptyInventoryDateLeavesNoMarkers(): void
    {
        $contract = $this->contract()->setPropertyInventoryDate(null);

        $text = $this->text(xml: $this->process(contract: $contract));

        $this->assertStringContainsString('проведена', $text);
        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringNotContainsString('2025 г.', $text);
    }

    private function contract(): Contracts
    {
        return (new Contracts())
            ->setLastName('Терехова')
            ->setFirstName('Светлана')
            ->setMiddleName('Александровна')
            ->setLastNameGenitive('Тереховой')
            ->setFirstNameGenitive('Светланы')
            ->setMiddleNameGenitive('Александровны')
            ->setIsLastNameChanged(true)
            ->setChangedLastName('Смирнова')
            ->setBirthDate(new \DateTime('1982-10-23'))
            ->setBirthPlace('гор. Ленинград')
            ->setInn('782609400697')
            ->setSnils('141-362-038 17')
            ->setRegistrationCity('Санкт-Петербург')
            ->setRegistrationStreet('Савушкина')
            ->setRegistrationHouse('18')
            ->setRegistrationApartment('18')
            ->setPassportSeries('4005')
            ->setPassportNumber('312038')
            ->setPropertyInventoryDate(new \DateTime('2025-07-02'));
    }

    private function process(Contracts $contract): string
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('3. Опись имущества гражданина')
            ->setCategory(BankruptcyStage::JUDICIAL_REPORT)
            ->setPath(dirname(__DIR__, 3) . self::TEMPLATE_FILE);

        $outputPath = $processor->processTemplate(template: $template, contract: $contract);

        $archive = new \ZipArchive();
        $archive->open($outputPath);
        $documentXml = (string)$archive->getFromName('word/document.xml');
        $archive->close();
        unlink($outputPath);

        return $documentXml;
    }

    private function text(string $xml): string
    {
        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
