<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InitCommand extends Command
{
    protected static $defaultName = 'starfleet:init';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private ManagerRegistry $registry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Init command that create first admin user.')
            ->addArgument('name', InputArgument::REQUIRED, 'Admin name')
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $admin = new User();
        $admin->setName($input->getArgument('name'));
        $admin->setEmail($input->getArgument('email'));
        $admin->setPassword($this->passwordHasher->hashPassword($admin, $input->getArgument('password')));
        $admin->addRole('ROLE_ADMIN');

        $this->registry->getManager()->persist($admin);
        $this->registry->getManager()->flush();

        $io->success(sprintf('Admin "%s" has been created!', $admin->getName()));

        return 0;
    }
}
