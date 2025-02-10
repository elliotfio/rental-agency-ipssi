<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehicleFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('brand', SearchType::class, [
                'required' => false,
                'label' => 'Marque',
                'attr' => ['placeholder' => 'Rechercher par marque']
            ])
            ->add('maxPrice', NumberType::class, [
                'required' => false,
                'label' => 'Prix max (€ / jour)',
                'attr' => ['placeholder' => 'Prix maximum']
                
            ])
            ->add('availability', ChoiceType::class, [
                'required' => false,
                'label' => 'Disponibilité',
                'choices' => [
                    'Tous' => null,
                    'Disponible' => true,
                    'Indisponible' => false,
                ]
            ])
            ->add('filter', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
        ]);
    }
}