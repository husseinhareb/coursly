<?php

// src/Form/AdminUserType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         $builder
             ->add('firstName', TextType::class, [
                 'label' => 'admin_user.first_name', 
             ])
             ->add('lastName', TextType::class, [
                 'label' => 'admin_user.last_name',
             ])
             ->add('phoneNumber', TextType::class, [
                 'required' => false,
                 'label' => 'admin_user.phone_number',
             ])
             ->add('address', TextType::class, [
                 'required' => false,
                 'label' => 'admin_user.address',
             ])
             ->add('roles', ChoiceType::class, [
                 'label' => 'admin_user.roles',
                 'choices' => [
                     'User' => 'ROLE_USER',
                     'Admin' => 'ROLE_ADMIN',
                 ],
                 'multiple' => true,
                 'expanded' => true,
             ])
             ->add('profilePic', FileType::class, [
                 'label' => 'admin_user.profile_pic', 
                 'mapped' => false,
                 'required' => false,
                 'constraints' => [
                     new File([
                         'maxSize' => '2M',
                         'mimeTypes' => [
                             'image/jpeg',
                             'image/png',
                             'image/gif',
                         ],
                         'mimeTypesMessage' => 'admin_user.profile_pic.mime_error', 
                     ])
                 ],
             ])
         ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
         $resolver->setDefaults([
              'data_class' => User::class,
              'translation_domain' => 'messages',
         ]);
    }
}
