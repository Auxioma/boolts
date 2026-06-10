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

namespace App\Form\Dashboard\AgenceImmobiliere;

use App\Entity\FuseauHoraire;
use App\Entity\User;
use App\Repository\FuseauHoraireRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProfileAgenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom')
            ->add('nom')
            ->add('email')
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'data-phone-target' => 'input',
                ],
            ])
            ->add('adresse')
            ->add('adresseComplement')
            ->add('ville')
            ->add('codePostal')
            ->add('pays')
            ->add('entreprise')
            ->add('description')

            ->add('numeroContact', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'data-phone-target' => 'input',
                ],
            ])

            ->add('adresseContact')
            ->add('codePostalContact')
            ->add('villeContact')
            ->add('paysContact')
            ->add('emailContact')

            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'label' => 'Photo de profil',
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'asset_helper' => true,
            ])

        ->add('whatsApp', TelType::class, [
            'label' => 'Téléphone',
            'required' => false,
            'attr' => [
                'class' => 'form-control',
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
                        'class' => 'form-control password-input mt-8 mb-12',
                    ],
                ],

                'invalid_message' => 'form.password.error.mismatch',
                'mapped' => false,
            ])

            ->add('langues', EntityType::class, [
                'class' => \App\Entity\Langues::class,
                'choice_label' => 'name',
                'required' => false,
            ])

            ->add('devise')
            ->add('fuseauHoraire', EntityType::class, [
                'class' => FuseauHoraire::class,
                'choice_label' => 'nom',
                'query_builder' => static function (FuseauHoraireRepository $repository) {
                    return $repository->createOrderedByUtcQueryBuilder();
                },
            ])
            ->add('horaireOuvertures', CollectionType::class, [
                'entry_type' => OpenHourType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'required' => false,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
