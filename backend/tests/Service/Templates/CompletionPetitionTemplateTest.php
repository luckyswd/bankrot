<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\Court;
use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Service\Templates\CustomFunction;
use App\Service\Templates\DocumentTemplateProcessor;
use App\Service\Templates\EntityDataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CompletionPetitionTemplateTest extends TestCase
{
    private const string TEMPLATE_FILE = '/src/document-templates/judicial_report/7. Ходатайство о завершении процедуры реализации имущества гражданина.docx';
    private const string COURT_NAME = 'Арбитражный суд города Санкт-Петербурга и Ленинградской области';
    private const string COURT_TAIL = 'города Санкт-Петербурга и Ленинградской области';
    private const string COURT_ADDRESS = '191124, г. Санкт-Петербург, ул. Смольного, д. 6';

    public function testHeaderIsSubstituted(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('Финансовый управляющий Чаркова Андрея Вадимовича', $text);
        $this->assertStringContainsString(self::COURT_NAME, $text);
        $this->assertStringContainsString(self::COURT_ADDRESS, $text);
        $this->assertStringContainsString('Дело № А56-47114/2023', $text);
        $this->assertStringContainsString('«21» марта 2026 г. в 11 час. 45 мин.', $text);
        $this->assertStringContainsString('Судья Ильенко Юрий Витальевич', $text);
    }

    public function testStartNotificationParagraph(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString(
            'Решением Арбитражного суда ' . self::COURT_TAIL . ' от 25.07.2020 г. (резолютивная часть объявлена 24.07.2020 г.) по делу № А56-47114/2023 Чарков Андрей Вадимович (дата рождения: 29.10.1985 г., место рождения: гор. Печора Республики Коми, СНИЛС 127-818-617 85, ИНН 110503514682, адрес регистрации: г. Санкт-Петербург, ул. Хошимина, д. 11, кв. 96) признан несостоятельным (банкротом).',
            $text,
        );
        $this->assertStringNotContainsString('сроком на шесть месяцев', $text);
    }

    public function testHearingSentenceAndCourtInPetition(): void
    {
        $text = $this->text(xml: $this->process(contract: $this->contract()));

        $this->assertStringContainsString('назначено на 21 марта 2026 года в 11 час. 45 мин.', $text);
        $this->assertStringContainsString('с депозитного счета Арбитражного суда ' . self::COURT_TAIL, $text);
        $this->assertStringNotContainsString('Арбитражного суда ' . self::COURT_NAME, $text);
    }

    public function testAllPlaceholdersAreReplacedAndGreenRemoved(): void
    {
        $documentXml = $this->process(contract: $this->contract());
        $text = $this->text(xml: $documentXml);

        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringNotContainsString('${', $text);
        $this->assertTrue((new \DOMDocument())->loadXML($documentXml));
        $this->assertSame(0, preg_match_all('/<w:color w:val="00B050"\/>/', $documentXml));
        $this->assertGreaterThan(0, preg_match_all('/<w:color w:val="FF0000"\/>/', $documentXml));
    }

    public function testHearingIsNotScheduled(): void
    {
        $contract = $this->contract()->setProcedureInitiationReportHearingDateTime(null);

        $text = $this->text(xml: $this->process(contract: $contract));

        $this->assertStringNotContainsString('{{', $text);
        $this->assertStringContainsString('Судебное заседание по рассмотрению отчета финансового управляющего назначено на', $text);
        $this->assertStringNotContainsString('11 час. 45 мин.', $text);
    }

    private function contract(): Contracts
    {
        $court = (new Court())
            ->setName(self::COURT_NAME)
            ->setAddress(self::COURT_ADDRESS);

        return (new Contracts())
            ->setLastName('Чарков')
            ->setFirstName('Андрей')
            ->setMiddleName('Вадимович')
            ->setLastNameGenitive('Чаркова')
            ->setFirstNameGenitive('Андрея')
            ->setMiddleNameGenitive('Вадимовича')
            ->setGender('male')
            ->setBirthDate(new \DateTime('1985-10-29'))
            ->setBirthPlace('гор. Печора Республики Коми')
            ->setSnils('127-818-617 85')
            ->setInn('110503514682')
            ->setRegistrationCity('Санкт-Петербург')
            ->setRegistrationStreet('Хошимина')
            ->setRegistrationHouse('11')
            ->setRegistrationApartment('96')
            ->setCaseNumber('А56-47114/2023')
            ->setCourt($court)
            ->setProcedureInitiationJudge('Ильенко Юрий Витальевич')
            ->setProcedureInitiationDecisionDate(new \DateTime('2020-07-25'))
            ->setProcedureInitiationResolutionDate(new \DateTime('2020-07-24'))
            ->setProcedureInitiationReportHearingDateTime(new \DateTime('2026-03-21 11:45'));
    }

    private function process(Contracts $contract): string
    {
        $processor = new DocumentTemplateProcessor(
            entityDataResolver: new EntityDataResolver(propertyAccessor: PropertyAccess::createPropertyAccessor()),
            customFunction: new CustomFunction(),
        );

        $template = (new DocumentTemplate())
            ->setName('7. Ходатайство о завершении процедуры реализации имущества гражданина')
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
