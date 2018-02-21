<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 13.2.18
 * Time: 16.31
 */

namespace App\Form;


use App\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilesLoadForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, array(
                'label' => false,
                'required'   => true,
                'attr' => array(
                    'class'         => 'btn btn-default',
                )
            ))

            ->add('flag_test_mode', CheckboxType::class, array(
                'label' => false,
                'required'   => false,
                'attr' => array(
                    'checked' => false,
                    'hidden' => false,
                )
            ))

            ->add('save', SubmitType::class, array(
                'label' => 'Load File',
                'attr' => array(
                    'class'         => 'btn btn-default',
                )
            ))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => File::class,
        ));
    }
}