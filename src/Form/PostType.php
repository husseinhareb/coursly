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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder
             ->add('title', TextType::class)
             ->add('content', TextareaType::class)
             ->add('type', ChoiceType::class, [
                   'choices' => [
                        'Message' => 'message',
                        'File' => 'file',
                   ],
             ])
             ->add('attachment', FileType::class, [
                 'label' => 'Attachment (Optional)',
                 'mapped' => false,
                 'required' => false,
                 'constraints' => [
                     new File([
                         'maxSize' => '10M',
                         // Removed MIME type restrictions so any file is accepted.
                         // You can optionally add other file constraints if needed.
                     ])
                 ],
             ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
         $resolver->setDefaults([
              'data_class' => Post::class,
         ]);
    }
}
