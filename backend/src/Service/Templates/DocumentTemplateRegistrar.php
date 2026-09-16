<?php

declare(strict_types=1);

namespace App\Service\Templates;

use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Repository\DocumentTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class DocumentTemplateRegistrar
{
    private const array TEMPLATE_EXTENSIONS = ['docx', 'xlsx'];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private DocumentTemplateRepository $documentTemplateRepository,
        #[Autowire(param: 'app.document_templates_dir')]
        private string $templatesDir,
    ) {
    }

    /**
     * @return array{created: int, updated: int, removed: int}
     */
    public function register(): array
    {
        $created = 0;
        $updated = 0;

        foreach ($this->findTemplateFiles() as $file) {
            $template = $this->documentTemplateRepository->findByNameAndCategory(
                name: $file['name'],
                category: $file['category'],
            );

            if (!$template instanceof DocumentTemplate) {
                $this->entityManager->persist(
                    (new DocumentTemplate())
                        ->setName($file['name'])
                        ->setCategory($file['category'])
                        ->setPath($file['path'])
                );
                ++$created;

                continue;
            }

            if ($template->getPath() !== $file['path']) {
                $template->setPath($file['path']);
                ++$updated;
            }
        }

        $this->entityManager->flush();

        $removed = $this->removeTemplatesWithoutFile();

        $this->entityManager->flush();

        return ['created' => $created, 'updated' => $updated, 'removed' => $removed];
    }

    /**
     * @return array<int, array{category: BankruptcyStage, name: string, path: string}>
     */
    private function findTemplateFiles(): array
    {
        $files = [];

        foreach (BankruptcyStage::cases() as $category) {
            $categoryDir = $this->templatesDir . '/' . $category->value;

            if (!is_dir($categoryDir)) {
                continue;
            }

            foreach (new \FilesystemIterator($categoryDir) as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || str_starts_with($file->getFilename(), '.')) {
                    continue;
                }

                if (!in_array(strtolower($file->getExtension()), self::TEMPLATE_EXTENSIONS, true)) {
                    continue;
                }

                $files[] = [
                    'category' => $category,
                    'name' => $file->getBasename('.' . $file->getExtension()),
                    'path' => $file->getPathname(),
                ];
            }
        }

        return $files;
    }

    private function removeTemplatesWithoutFile(): int
    {
        $removed = 0;

        foreach ($this->documentTemplateRepository->findAll() as $template) {
            if (is_file($template->getPath())) {
                continue;
            }

            $this->entityManager->remove($template);
            ++$removed;
        }

        return $removed;
    }
}
