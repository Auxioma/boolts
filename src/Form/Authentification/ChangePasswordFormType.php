<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Form\Authentification;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
                    ],

                    'constraints' => [
                        new NotBlank(
                            message: 'form.password.error.blank'
                        ),
                        new Length(
                            min: 8,
                            max: 4096,
                            minMessage: 'form.password.error.min',
                            maxMessage: 'form.password.error.max',
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
                    ],
                ],

                'invalid_message' => 'form.password.error.mismatch',
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
