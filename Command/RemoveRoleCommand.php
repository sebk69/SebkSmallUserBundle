<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2018 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveRoleCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('sebk:small-user:remove-role')
            ->setDescription('Remove a role to user')
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
        $userProvider = $this->getContainer()->get("sebk_small_user_provider");

        try {
            $user = $userProvider->loadUserByUsername($input->getArgument("username"));
            $user->removeRole($input->getArgument("role"));
            $userProvider->updateUser($user);
        } catch(\Exception $e) {
            $output->writeln($e->getMessage());
            return;
        }

        $output->writeln("User ".$input->getArgument("username")." has been updated");
    }
}