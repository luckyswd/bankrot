<?php

declare(strict_types=1);

namespace App\Service\Templates;

use App\Entity\Contracts;
use App\Entity\DocumentTemplate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DocumentXlsxService
{
    public function handle(DocumentTemplate $documentTemplate, Contracts $contracts): Response|JsonResponse
    {
        $sourcePath = $documentTemplate->getPath();

        if (!file_exists($sourcePath)) {
            return new JsonResponse(data: ['error' => 'Файл не найден'], status: 404);
        }

        try {
            $spreadsheet = IOFactory::load($sourcePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // F4 - Полное имя
            $worksheet->setCellValue('F4', $contracts->getFullName() ?? '');

            // G4 - Серия и номер паспорта (БЕЗ пробелов, если серия начинается с 0, то _)
            $passportSeries = $contracts->getPassportSeries() ?? '';
            $passportNumber = $contracts->getPassportNumber() ?? '';

            if ($passportSeries !== '' && str_starts_with($passportSeries, '0')) {
                $passportSeries = '_' . $passportSeries;
            }

            $passportFull = $passportSeries . $passportNumber;
            $worksheet->setCellValue('G4', $passportFull);

            // H4 - Дата рождения
            $birthDate = $contracts->getBirthDate();
            $worksheet->setCellValue('H4', $birthDate !== null ? $birthDate->format('d.m.Y') : '');

            // I4 - Кем выдан паспорт
            $worksheet->setCellValue('I4', $contracts->getPassportIssuedBy() ?? '');

            // J4 - Дата выдачи паспорта
            $passportIssuedDate = $contracts->getPassportIssuedDate();
            $worksheet->setCellValue('J4', $passportIssuedDate !== null ? $passportIssuedDate->format('d.m.Y') : '');

            // K4 - Код подразделения
            $worksheet->setCellValue('K4', $contracts->getPassportDepartmentCode() ?? '');

            // L4 - Место рождения
            $worksheet->setCellValue('L4', $contracts->getBirthPlace() ?? '');

            // M4 - Полный адрес регистрации
            $worksheet->setCellValue('M4', $contracts->getFullRegistrationAddress() ?? '');

            // N4 - Номер дела о введении процедуры
            $worksheet->setCellValue('N4', $contracts->getCaseNumber() ?? '');

            // O4 - Дата решения о введении процедуры
            $procedureInitiationDecisionDate = $contracts->getProcedureInitiationDecisionDate();
            $worksheet->setCellValue('O4', $procedureInitiationDecisionDate !== null ? $procedureInitiationDecisionDate->format('d.m.Y') : '');

            $tempFileName = '/tmp/' . uniqid('xlsx_', true) . '.xlsx';
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($tempFileName);

            $fileContent = file_get_contents($tempFileName);

            if ($fileContent === false) {
                unlink($tempFileName);

                return new JsonResponse(data: ['error' => 'Ошибка при чтении файла'], status: 500);
            }

            $fileName = $documentTemplate->getName() . '.xlsx';
            $fileName = preg_replace('/[^a-zA-Z0-9а-яА-ЯёЁ\s\-_\.]/u', '', $fileName);

            $response = new Response($fileContent);
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Length', (string)strlen($fileContent));
            $response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($fileName) . '"');
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');

            unlink($tempFileName);

            return $response;
        } catch (\Exception $e) {
            return new JsonResponse(data: ['error' => 'Ошибка при обработке файла: ' . $e->getMessage()], status: 500);
        }
    }
}
