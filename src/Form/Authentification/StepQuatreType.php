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

use App\Entity\Pays;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepQuatreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entreprise', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "Veuillez entrer le nom de l'entreprise",
                    'style' => 'height: 44px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 12px 14px;',
                ],
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez un numéro et un nom de rue',
                    'style' => 'height: 44px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 12px 14px;',
                ],
            ])
            ->add('adresseComplement', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez un complément d’adresse',
                    'style' => 'height: 44px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 12px 14px;',
                ],
            ])
            ->add('codePostal', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez un code postal',
                    'style' => 'height: 44px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 12px 14px;',
                ],
            ])
            ->add('ville', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Choisissez une ville',
                    'style' => 'height: 44px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 12px 14px;',
                ],
            ])
            ->add('pays', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un pays',
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'height: 44px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 8px 14px;',
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
