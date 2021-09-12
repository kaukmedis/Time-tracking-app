<?php

namespace App\Form;

use App\Entity\Tasks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TasksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,[
                'required' => true
            ])
            ->add('comment',TextareaType::class,[
                'required' => true
            ])
            ->add('date',DateType::class,[
                'widget' => 'single_text','attr'   => ['max' => '9999-12-01'],
                'format' => 'yyyy-MM-dd',
                'required'  => true
                    ]
            )
            ->add('timeSpent',IntegerType::class,[
                'attr'=>['min'=>1],
                'required'=>true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tasks::class,
        ]);
    }
}
