<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2018 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Command;

use Sebk\SmallUserBundle\Security\UserProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddRoleCommand extends Command
{
    protected static $defaultName = 'sebk:small-user:add-role';

    protected UserProvider $userProvider;

    /**
     * Constructor
     * @param UserProvider $userProvider
     */
    public function __construct(UserProvider $userProvider)
    {
        $this->userProvider = $userProvider;

        parent::__construct();
    }

    /**
     * Configure command
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Add a role to user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Email or nickname'
            )
            ->addArgument(
                'role',
                InputArgument::REQUIRED,
                'new role for user'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $user = $this->userProvider->loadUserByUsername($input->getArgument("username"));
            $user->addRole($input->getArgument("role"));
            $this->userProvider->updateUser($user);
        } catch(\Exception $e) {
            $output->writeln($e->getMessage());
            return self::FAILURE;
        }

        $output->writeln("User ".$input->getArgument("username")." has been updated");
        return self::SUCCESS;
    }
}