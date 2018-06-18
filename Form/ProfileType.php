<?php
/**
 * This file is a part of SebkSmallUserBundle
 * Copyright 2015-2017 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Form;

use Sebk\SmallUserBundle\Model\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    protected function getAttributes($field) {
        return [];
    }

    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("email", TextType::class, ["required" => true, "attr" => $this->getAttributes("email")])
            ->add("nickname", TextType::class, ["required" => true, "attr" => $this->getAttributes("nickname")])
            ->add("password", PasswordType::class, ["required" => false, "attr" => $this->getAttributes("password")])
            ->add("passwordConfirm", PasswordType::class, ["required" => false, "attr" => $this->getAttributes("passwordConfirm")])
            ->add("save", SubmitType::class, ["attr" => $this->getAttributes("save")])
        ;
    }

    /**
     * Configure options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => User::class,
        ]);
    }

}