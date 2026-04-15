<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('schoolId', TextType::class, [
                'attr' => ['class' => 'form-input', 'placeholder' => 'e.g. 2022-00123'],
                'label' => 'School ID',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('firstName', TextType::class, [
                'attr' => ['class' => 'form-input', 'placeholder' => 'First Name'],
                'label' => 'First Name',
            ])
            ->add('lastName', TextType::class, [
                'attr' => ['class' => 'form-input', 'placeholder' => 'Last Name'],
                'label' => 'Last Name',
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-input', 'placeholder' => 'Email Address'],
                'label' => 'Email',
            ])
            ->add('dataPrivacyConsent', CheckboxType::class, [
                'mapped' => false,
                'label' => 'I have read and agree to the Data Privacy Act compliance statement.',
                'constraints' => [
                    new IsTrue(['message' => 'You must agree to the Data Privacy Act compliance statement.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
