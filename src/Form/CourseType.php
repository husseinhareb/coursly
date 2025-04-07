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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder
             ->add('title', TextType::class)
             ->add('description', TextareaType::class)
             ->add('code', TextType::class)
             ->add('image', FileType::class, [
                  'label' => 'Course Image (Optional)',
                  'required' => false,
                  'mapped' => false, 
                  'constraints' => [
                      new File([
                           'maxSize' => '5M',
                           'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                           ],
                           'mimeTypesMessage' => 'Please upload a valid image (JPEG or PNG).',
                      ])
                  ],
             ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
         $resolver->setDefaults([
              'data_class' => Course::class,
         ]);
    }
}
