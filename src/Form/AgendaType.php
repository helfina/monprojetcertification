<?php

namespace App\Form;

use App\Entity\Agenda;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgendaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class,[
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'Title',
                ],
                'row_attr' => [
                    'class' => 'form-floating m-2',
                ],
            ])
            ->add('start', DateTimeType::class,[
                'date_widget' => 'single_text',
                'label' => 'Date début',
                'row_attr' => [
                    'class' => 'form-control m-2',
                ],
            ])
            ->add('end', DateTimeType::class,[
                'date_widget' => 'single_text',
                'label' => 'Date de fin',
                'row_attr' => [
                    'class' => 'form-control m-2',
                ],
            ])
            ->add('description', TextareaType::class,[
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description',
                ],

                'row_attr' => [
                    'class' => 'form-floating m-1',
                ],
            ])
            ->add('all_day',CheckboxType::class,[
                'label' => 'Toute la journée ',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-check form-switch form-control m-2',
                ],

            ])
            ->add('background_color', ColorType::class,[
                'label' => 'Couleur de fond',

                'row_attr' => [
                    'class' => 'form-control m-2',
                ],
            ])
            ->add('text_color', ColorType::class,[
                'label' => 'Couleur de fond',

                'row_attr' => [
                    'class' => 'form-control m-2',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agenda::class,
        ]);
    }
}
