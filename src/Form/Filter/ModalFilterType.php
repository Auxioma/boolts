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

namespace App\Form\Filter;

use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Filter\ModalFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModalFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*
         * Préremplissage de la localisation (pays / ville / quartier).
         * Fourni par le contrôleur à partir de la recherche Mapbox
         * de la page d'accueil, au format JSON attendu par le Stimulus
         * "boolts-location" (parseJsonValue au connect()).
         */
        $locationPrefill = $options['location_prefill'] ?? [];

        $paysPrefill = $locationPrefill['pays'] ?? '[]';
        $villePrefill = $locationPrefill['ville'] ?? '[]';
        $quartierPrefill = $locationPrefill['quartier'] ?? '[]';

        $builder
            ->add('natureDeLaPropriete', EntityType::class, [
                'class' => CategoryBienTransaction::class,
                'choice_label' => 'name',
                'choice_attr' => static function (CategoryBienTransaction $categoryBienTransaction) {
                    return [
                        'icon' => $categoryBienTransaction->getIcone(),
                        'name' => $categoryBienTransaction->getName(),
                    ];
                },
            ])

            ->add('typeDePropriete', EntityType::class, [
                'class' => CategoryBien::class,
                'choice_label' => 'name',
                'required' => false,

                'expanded' => true,
                'multiple' => true,

                'choice_attr' => static function (CategoryBien $categoryBien): array {
                    return [
                        'icon' => $categoryBien->getIcone(),
                        'name' => $categoryBien->getName(),
                    ];
                },
            ])

            ->add('paysSearch', SearchType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
            ])

            ->add('pays', HiddenType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'data' => $paysPrefill,
            ])

            ->add('villeSearch', SearchType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
            ])

            ->add('ville', HiddenType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'data' => $villePrefill,
            ])

            ->add('quartierSearch', SearchType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
            ])

            ->add('quartier', HiddenType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'data' => $quartierPrefill,
            ])

            ->add('minChambres', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxChambres', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('minSallesDeBain', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxSallesDeBain', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('minSurface', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxSurface', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('minAnneeConstruction', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxAnneeConstruction', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('minPrix', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxPrix', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('dpe', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                    'E' => 'E',
                    'F' => 'F',
                    'G' => 'G',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ModalFilter::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,

            /*
             * Préremplissage optionnel de la localisation :
             * ['pays' => '[...json...]', 'ville' => '[...]', 'quartier' => '[...]']
             */
            'location_prefill' => null,
        ]);

        $resolver->setAllowedTypes('location_prefill', ['null', 'array']);
    }

    public function getBlockPrefix(): string
    {
        return 'modal_filter';
    }
}
