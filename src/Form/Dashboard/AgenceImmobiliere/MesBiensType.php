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

use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Property;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('performanceEnergetique')
            ->add('adresse')
            ->add('codePostal')
            ->add('ville')
            ->add('pays', CountryType::class, [
                'placeholder' => 'Sélectionnez un pays',
            ])
            ->add('referenceInterne')

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
        ]);
    }
}
