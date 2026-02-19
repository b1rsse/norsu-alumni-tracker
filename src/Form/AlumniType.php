<?php

namespace App\Form;

use App\Entity\Alumni;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlumniType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('studentId')
            ->add('firstName')
            ->add('lastName')
            ->add('course')
            ->add('batchYear')
            ->add('currentEmploymentStatus')
            ->add('email')
            ->add('phone')
            ->add('current_position')
            ->add('company_name')
            ->add('salary_range')
            ->add('location')
            ->add('skills')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Alumni::class,
        ]);
    }
}
