<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content')
            ->add('rating')
            ->add('createdAt', null, [
                'widget' => 'single_text'
            ])
            ->add('customer', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
            ->add('vehicle', EntityType::class, [
                'class' => Vehicle::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
