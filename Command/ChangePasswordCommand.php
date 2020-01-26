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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChangePasswordCommand extends Command
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;

        parent::__construct();
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function configure()
    {
        $this
            ->setName('sebk:small-user:change-password')
            ->setDescription('Change password of user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Email or nickname'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Password of user'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var UserProvider $userProvider */
        $userProvider = $this->getContainer()->get("sebk_small_user_provider");

        try {
            $user = $userProvider->loadUserByUsername($input->getArgument("username"));
            $userProvider->updateUser($user, $input->getArgument("password"));
        } catch(\Exception $e) {
            $output->writeln($e->getMessage());
            return;
        }

        $output->writeln("User ".$input->getArgument("username")." has been updated");
    }
}