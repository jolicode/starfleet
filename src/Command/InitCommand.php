<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use function Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\Dunglas\param_no_type;

class InitCommand extends Command
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private ManagerRegistry $registry;

    protected static $defaultName = 'starfleet:init';

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, ManagerRegistry $registry)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->registry = $registry;

        parent::__construct();
    }

    protected function configure()
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
        $admin->setPassword($this->passwordEncoder->encodePassword($admin, $input->getArgument('password')));
        $admin->addRole('ROLE_ADMIN');

        $this->registry->getManager()->persist($admin);
        $this->registry->getManager()->flush();

        $io->success(sprintf('Admin "%s" has been created!', $admin->getName()));

        return 0;
    }
}
