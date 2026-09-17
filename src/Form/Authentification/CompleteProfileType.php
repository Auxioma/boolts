<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Form\Authentification;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CompleteProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control mb-16',
                    'placeholder' => 'Veuillez entrer votre nom',
                ],
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'form-control mb-16',
                    'placeholder' => 'Veuillez entrer votre prénom',
                ],
            ])
            ->add('telephone', TelType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Veuillez entrer votre numéro de téléphone',
                    'autocomplete' => 'tel',
                    'inputmode' => 'tel',
                    'data-phone-target' => 'input',
                ],
            ])
->add('plainPassword', RepeatedType::class, [
    'type' => PasswordType::class,

    'options' => [
        'attr' => [
            'autocomplete' => 'new-password',
        ],
    ],

    'first_options' => [
        'toggle' => true,

        // ⚠️ DOIT être string (pas bool)
        'visible_label' => '',
        'hidden_label' => '',

        // icônes
        'visible_icon' => 'eye',
        'hidden_icon' => 'eye-slash',

        // ⚠️ DOIT être tableau
        'button_classes' => ['password-toggle-button'],
        'toggle_container_classes' => ['password-toggle'],

        'label' => 'form.password.new',
        'attr' => [
            'class' => 'form-control password-input',
            'placeholder' => 'Veuillez entrer votre mot de passe',
            'minlength' => 12,
            'data-password-strength-target' => 'input',
            'data-action' => 'input->password-strength#check',
        ],

        'constraints' => [
            new NotBlank(
                message: 'form.password.error.blank'
            ),
            new Length(
                min: 12,
                max: 4096,
                minMessage: 'form.password.error.min',
                maxMessage: 'form.password.error.max',
            ),
            new Regex(
                pattern: '/[A-Z]/',
                message: 'Le mot de passe doit contenir au moins une majuscule.',
            ),
            new Regex(
                pattern: '/[a-z]/',
                message: 'Le mot de passe doit contenir au moins une minuscule.',
            ),
            new Regex(
                pattern: '/\d/',
                message: 'Le mot de passe doit contenir au moins un chiffre.',
            ),
        ],
    ],

    'second_options' => [
        'toggle' => true,

        'visible_label' => '',
        'hidden_label' => '',

        'visible_icon' => 'eye',
        'hidden_icon' => 'eye-slash',

        'button_classes' => ['password-toggle-button'],
        'toggle_container_classes' => ['password-toggle'],

        'label' => 'form.password.repeat',
        'attr' => [
            'placeholder' => 'Veuillez confirmer votre mot de passe',
            'class' => 'form-control password-input mb-32',
            'data-password-strength-target' => 'confirmInput',
        ],
    ],

    'invalid_message' => 'form.password.error.mismatch',
    'mapped' => false,
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
