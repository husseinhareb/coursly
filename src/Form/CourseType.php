<?php
// src/Form/CourseType.php
namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         $builder
             ->add('title', TextType::class, [
                 'label' => 'course.new.title',
             ])
             ->add('description', TextareaType::class, [
                 'label' => 'course.new.description',
             ])
             ->add('code', TextType::class, [
                 'label' => 'course.new.code',
             ])
             ->add('image', FileType::class, [
                  'label' => 'course.image_label',
                  'required' => false,
                  'mapped' => false,
                  'constraints' => [
                      new File([
                           'maxSize' => '5M',
                           'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                           ],
                           'mimeTypesMessage' => 'course.image_mime_error',
                      ])
                  ],
             ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
         $resolver->setDefaults([
              'data_class' => Course::class,
              'translation_domain' => 'messages',
         ]);
    }
}
