<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Creates a new admin user',)]
class CreateAdmin extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // Запрос данных администратора
        $nickname = $helper->ask($input, $output, new Question('Nickname: '));
        $firstName = $helper->ask($input, $output, new Question('First Name: '));
        $lastName = $helper->ask($input, $output, new Question('Last Name: '));

        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $password = $helper->ask($input, $output, $passwordQuestion);

        // Проверка существования пользователя
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['nickname' => $nickname]);

        if ($existingUser) {
            $output->writeln('<error>User with this nickname already exists!</error>');
            return Command::FAILURE;
        }

        // Создание пользователя
        $user = new User();
        $user->setNickname($nickname);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles(['ROLE_ADMIN']);

        // Хеширование пароля
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Сохранение в БД
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>Admin user created successfully!</info>');
        $output->writeln('ID: ' . $user->getId());
        $output->writeln('Nickname: ' . $user->getNickname());
        $output->writeln('Roles: ' . implode(', ', $user->getRoles()));

        return Command::SUCCESS;
    }
}
