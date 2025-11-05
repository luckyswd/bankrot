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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user-create',
    description: 'Создание пользователя',
)]
class UserCreateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $usernameQ = new Question('Введите username: ');
        $fioQ = new Question('Введите ФИО: ');
        $passwordQ = new Question('Введите пароль: ');
        $passwordQ->setHidden(true);
        $passwordQ->setHiddenFallback(false);

        $username = trim($helper->ask($input, $output, $usernameQ));
        $fio = trim($helper->ask($input, $output, $fioQ));
        $plainPassword = trim($helper->ask($input, $output, $passwordQ));

        if ($this->userRepository->findByUsername($username)) {
            $output->writeln('<error>Пользователь с таким username уже существует.</error>');

            return Command::FAILURE;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setFio($fio);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->hasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('<info>Пользователь успешно создан:</info>');
        $output->writeln("  ID: {$user->getId()}");
        $output->writeln("  Username: {$user->getUsername()}");
        $output->writeln("  FIO: {$user->getFio()}");

        return Command::SUCCESS;
    }
}
