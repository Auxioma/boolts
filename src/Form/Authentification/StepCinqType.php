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

use App\Entity\LangueParler;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class StepCinqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'asset_helper' => true,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Veuillez saisir une description de votre agence',
                    'style' => 'height: 86px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 14px; resize: vertical;',
                ],
            ])
            ->add('numeroContact', TelType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Veuillez entrer votre numéro de téléphone',
                    'autocomplete' => 'tel',
                    'inputmode' => 'tel',
                    'data-phone-target' => 'input',
                    'style' => 'height: 44px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 12px 14px;',
                ],
            ])
            ->add('emailContact', EmailType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Veuillez entrer votre adresse e-mail de contact',
                    'style' => 'height: 44px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 12px 14px;',
                ],
            ])
            ->add('langueParlers', EntityType::class, [
                'class' => LangueParler::class,
                'choice_label' => 'name',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'min-height: 44px; border: 2px solid #EBECEC; border-radius: 8px; font-size: 14px; padding: 8px 14px;',
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
