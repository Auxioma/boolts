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

namespace App\Form\Dashboard\AgenceImmobiliere;

use App\Entity\HoraireOuverture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpenHourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jour', HiddenType::class, [
                'required' => false,
            ])

            ->add('isOpen', CheckboxType::class, [
                'required' => false,
            ])

            ->add('ouvertureMatin', TimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ])

            ->add('fermetureMatin', TimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ])

            ->add('ouvertureApresMidi', TimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ])

            ->add('fermetureApresMidi', TimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HoraireOuverture::class,
        ]);
    }
}
