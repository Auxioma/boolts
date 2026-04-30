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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

final class AuthCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('code', HiddenType::class, [
            'constraints' => [
                new NotBlank(message: 'Veuillez saisir le code reçu par email.'),
                new Length(
                    min: 6,
                    max: 6,
                    exactMessage: 'Le code doit contenir exactement 6 chiffres.'
                ),
                new Regex(
                    pattern: '/^\d{6}$/',
                    message: 'Le code doit contenir uniquement 6 chiffres.'
                ),
            ],
            'attr' => [
                'maxlength' => 6,
                'inputmode' => 'numeric',
                'autocomplete' => 'one-time-code',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
