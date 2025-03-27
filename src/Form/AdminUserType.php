<?php

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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder
             ->add('firstName', TextType::class, [
                 'label' => 'First Name',
             ])
             ->add('lastName', TextType::class, [
                 'label' => 'Last Name',
             ])
             ->add('phoneNumber', TextType::class, [
                 'required' => false,
                 'label' => 'Phone Number',
             ])
             ->add('address', TextType::class, [
                 'required' => false,
                 'label' => 'Address',
             ])
             ->add('roles', ChoiceType::class, [
                 'label' => 'Roles',
                 'choices' => [
                     'User' => 'ROLE_USER',
                     'Admin' => 'ROLE_ADMIN',
                 ],
                 'multiple' => true,
                 'expanded' => true,
             ])
             ->add('profilePic', FileType::class, [
                 'label' => 'Profile Picture (Image file)',
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
                         'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, or GIF)',
                     ])
                 ],
             ])
         ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
         $resolver->setDefaults([
              'data_class' => User::class,
         ]);
    }
}
