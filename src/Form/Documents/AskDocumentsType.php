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

namespace App\Form\Documents;

use App\Entity\Document\RequiredDocument;
use App\Entity\Pays;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AskDocumentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['include_country']) {
            $builder->add('pays', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez votre pays',
            ]);
        }

        if ($options['required_document'] instanceof RequiredDocument) {
            $builder
                ->add('requiredDocument', HiddenType::class, [
                    'data' => (string) $options['required_document']->getId(),
                    'mapped' => false,
                    'label' => false,
                ])
                ->add('document', FileType::class, [
                    'mapped' => false,
                    'label' => false,
                    'attr' => [
                        'accept' => 'image/*,application/pdf',
                    ],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'include_country' => true,
            'required_document' => null,
        ]);
        $resolver->setAllowedTypes('include_country', 'bool');
        $resolver->setAllowedTypes('required_document', [RequiredDocument::class, 'null']);
    }
}
