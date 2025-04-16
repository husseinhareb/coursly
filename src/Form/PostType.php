<?php
// src/Form/PostType.php
namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         $builder
             ->add('title', TextType::class, [
                 'label' => 'post.title.label',
             ])
             ->add('content', TextareaType::class, [
                 'label' => 'post.content.label',
             ])
             ->add('type', ChoiceType::class, [
                   'label' => 'post.type.label',
                   'choices' => [
                        'post.type.message' => 'message',
                        'post.type.file'    => 'file',
                   ],
                   'choice_translation_domain' => 'messages',
             ])
             ->add('attachment', FileType::class, [
                 'label' => 'post.attachment.label',
                 'mapped' => false,
                 'required' => false,
                 'constraints' => [
                     new File([
                         'maxSize' => '10M',
                         // MIME type constraints removed intentionally
                         // Add more constraints if needed
                         // 'mimeTypesMessage' => 'post.attachment.mime_error',
                     ])
                 ],
             ]);
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
         $resolver->setDefaults([
              'data_class' => Post::class,
              'translation_domain' => 'messages',
         ]);
    }
}
