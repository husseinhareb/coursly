<?php
// src/Form/AnnouncementType.php

namespace App\Form;

use App\Entity\Announcement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr'  => [
                    'placeholder' => 'Enter a title…',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'attr'  => [
                    'rows'        => 6,
                    'placeholder' => 'Your announcement…',
                ],
            ])
            ->add('file', FileType::class, [
                'label'    => 'Attachment (optional)',
                'mapped'   => false,
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create Announcement',
                'attr'  => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Announcement::class,
        ]);
    }
}
