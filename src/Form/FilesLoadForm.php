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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'file',
                FileType::class,
                [
                    'label' => false,
                    'required' => true,
                    'attr' => [
                        'class' => 'btn btn-default',
                    ],
                ]
            )
            ->add(
                'flag_test_mode',
                CheckboxType::class,
                [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'checked' => false,
                        'hidden' => false,
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'Load File',
                    'attr' => [
                        'class' => 'btn btn-default',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => File::class,
            ]
        );
    }
}
