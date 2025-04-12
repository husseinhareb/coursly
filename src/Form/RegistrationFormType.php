<?php
// src/Form/RegistrationFormType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'registration.first_name.not_blank']),
                ],
                'label' => 'registration.first_name.label',
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'registration.last_name.not_blank']),
                ],
                'label' => 'registration.last_name.label',
            ])
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'registration.username.not_blank']),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'registration.username.min_length',
                        'max' => 255,
                    ]),
                ],
                'label' => 'registration.username.label',
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'registration.email.not_blank']),
                    new Email(['message' => 'registration.email.invalid']),
                ],
                'label' => 'registration.email.label',
            ])
            ->add('plainPassword', TextType::class, [
                'mapped' => false,
                'attr'   => ['readonly' => true],
                'constraints' => [
                    new NotBlank(['message' => 'registration.plain_password.not_blank']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'registration.plain_password.min_length',
                        'max' => 4096,
                    ]),
                ],
                'label' => 'registration.plain_password.label',
            ])
            ->add('role', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'registration.role.administrator'      => 'ROLE_ADMIN',
                    'registration.role.student'            => 'ROLE_STUDENT',
                    'registration.role.professor'          => 'ROLE_PROFESSOR',
                    'registration.role.admin_professor'    => 'ROLE_ADMIN_PROFESSOR',
                ],
                'placeholder' => 'registration.role.placeholder',
                'label'       => 'registration.role.label',
                'choice_translation_domain' => 'messages',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'messages',
        ]);
    }
}
