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
                    'class' => 'form-control input',
                    'placeholder' => "Veuillez entrer le nom de l'entreprise",
                ],
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'form-control input',
                    'placeholder' => 'Entrez un numéro et un nom de rue',
                ],
            ])
            ->add('adresseComplement', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control input',
                    'placeholder' => 'Entrez un complément d’adresse',
                ],
            ])
            ->add('codePostal', TextType::class, [
                'attr' => [
                    'class' => 'form-control input',
                    'placeholder' => 'Entrez un code postal',
                ],
            ])
            ->add('ville', TextType::class, [
                'attr' => [
                    'class' => 'form-control input',
                    'placeholder' => 'Choisissez une ville',
                ],
            ])
            ->add('pays', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un pays',
                'attr' => [
                    'class' => 'form-control input',
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
