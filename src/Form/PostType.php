<?php
// src/Form/PostType.php

namespace App\Form;

use App\Entity\Post;
use App\Entity\MessageCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TextareaType,
    ChoiceType,
    FileType,
    CheckboxType
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Title of the post
            ->add('title', TextType::class, [
                'label'       => 'post.title.label',
                'constraints' => [
                    new NotBlank(['message' => 'post.title.not_blank']),
                ],
            ])
            // Main content/body
            ->add('content', TextareaType::class, [
                'label' => 'post.content.label',
                'attr'  => ['rows' => 5],
            ])
            // Type: message vs file
            ->add('type', ChoiceType::class, [
                'label'   => 'post.type.label',
                'choices' => [
                    'post.type.message' => 'message',
                    'post.type.file'    => 'file',
                ],
                'placeholder' => 'post.type.placeholder',
            ])
            // File upload (only used when type === 'file')
            ->add('attachment', FileType::class, [
                'label'    => 'post.attachment.label',
                'mapped'   => false,
                'required' => false,
            ])
            // New: category lookup
            ->add('category', EntityType::class, [
                'class'         => MessageCategory::class,
                'choice_label'  => 'name',
                'label'         => 'post.category.label',
                'placeholder'   => 'post.category.placeholder',
                'required'      => false,
            ])
            // New: pin/unpin checkbox
            ->add('isPinned', CheckboxType::class, [
                'label'    => 'post.pin.label',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Post::class,
            'translation_domain' => 'messages',
        ]);
    }
}
