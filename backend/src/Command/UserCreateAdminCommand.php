<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user-create-admin',
    description: 'Создание администратора для доступа к API документации',
)]
class UserCreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Username администратора', 'admin')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Пароль администратора')
            ->addOption('fio', 'f', InputOption::VALUE_OPTIONAL, 'ФИО администратора', 'Администратор');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getOption('username');
        $plainPassword = $input->getOption('password');
        $fio = $input->getOption('fio');

        $existingUser = $this->userRepository->findByUsername($username);

        if ($existingUser) {
            $output->writeln("<info>Пользователь '{$username}' уже существует.</info>");

            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $updateQ = new Question('Обновить роль на ROLE_ADMIN? (y/n): ', 'y');
            $update = strtolower(trim($helper->ask($input, $output, $updateQ)));

            if ('y' === $update) {
                $existingUser->setRoles(['ROLE_ADMIN']);
                $this->em->flush();
                $output->writeln("<info>Роль ROLE_ADMIN назначена пользователю '{$username}'.</info>");
                $output->writeln('<info>Используйте существующий пароль для входа.</info>');

                return Command::SUCCESS;
            }

            $output->writeln('<info>Текущие роли пользователя: ' . implode(', ', $existingUser->getRoles()) . '</info>');

            return Command::SUCCESS;
        }

        if (!$plainPassword) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $passwordQ = new Question('Введите пароль администратора: ');
            $passwordQ->setHidden(true);
            $passwordQ->setHiddenFallback(false);
            $plainPassword = trim($helper->ask($input, $output, $passwordQ));
        }

        if (empty($plainPassword)) {
            $output->writeln('<error>Пароль не может быть пустым.</error>');

            return Command::FAILURE;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setFio($fio);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->hasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('<info>Администратор успешно создан:</info>');
        $output->writeln("  Username: <comment>{$user->getUsername()}</comment>");
        $output->writeln('  Password: <comment>***</comment>');
        $output->writeln("  FIO: {$user->getFio()}");
        $output->writeln('  Roles: ' . implode(', ', $user->getRoles()));

        return Command::SUCCESS;
    }
}
