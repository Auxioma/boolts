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

use App\Entity\Caracteristique;
use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Property;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MesBiensType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeBien', EntityType::class, [
                'class' => CategoryBien::class,
                'choice_label' => 'name',
                'choice_attr' => static function (CategoryBien $categoryBien) {
                    return [
                        'icon' => $categoryBien->getIcone(),
                        'name' => $categoryBien->getName(),
                    ];
                },
            ])
            ->add('typeTransaction', EntityType::class, [
                'class' => CategoryBienTransaction::class,
                'choice_label' => 'name',
                'choice_attr' => static function (CategoryBienTransaction $categoryBienTransaction) {
                    return [
                        'icon' => $categoryBienTransaction->getIcone(),
                        'name' => $categoryBienTransaction->getName(),
                    ];
                },
            ])
            ->add('titreDuLogement')
            ->add('descriptionLogement')
            ->add('prix')
            ->add('anneeConstruction')
            ->add('adresse')
            ->add('codePostal')
            ->add('ville')
            ->add('pays', CountryType::class, [
                'placeholder' => 'Sélectionnez un pays',
            ])
            ->add('referenceInterne')

            ->add('chambres', HiddenType::class, [
                'required' => true,
            ])

            ->add('salleDeBains', HiddenType::class, [
                'required' => true,
            ])

            ->add('surfaceTotal')
            ->add('caracteristique', EntityType::class, [
                'class' => Caracteristique::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choice_attr' => static function (Caracteristique $caracteristique) {
                    return [
                        'icon' => $caracteristique->getIcone(),
                        'name' => $caracteristique->getNom(),
                    ];
                },
            ])

            ->add('dpe')
            ->add('ges')
            ->add('dpeMax')
            ->add('dpeMin')
            ->add('dateIndexationEnergie', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('dpeLettre', ChoiceType::class, [
                'choices' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                    'E' => 'E',
                    'F' => 'F',
                    'G' => 'G',
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])
            ->add('propertyImages', CollectionType::class, [
                'entry_type' => PropertyImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
        ]);
    }
}
