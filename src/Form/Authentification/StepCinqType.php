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
use App\Repository\LangueParlerRepository;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
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
                    'class' => 'form-control input',
                    'placeholder' => 'Veuillez saisir une description de votre agence',
                    'style' => 'height: auto; padding: 14px; resize: vertical;',
                    'maxlength' => 500,
                    'rows' => 3,
                    'onkeydown' => "if (event.key === 'Enter') event.preventDefault();",
                ],
                'constraints' => [
                    new Length(max: 500),
                ],
            ])
            ->add('numeroContact', TelType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control input',
                    'placeholder' => 'Veuillez entrer votre numéro de téléphone',
                    'autocomplete' => 'tel',
                    'inputmode' => 'tel',
                    'data-phone-target' => 'input',
                ],
            ])
            ->add('emailContact', EmailType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control input',
                    'placeholder' => 'Veuillez entrer votre adresse e-mail de contact',
                ],
            ])
            ->add('langueParlers', EntityType::class, [
                'class' => LangueParler::class,
                'choice_label' => 'labelWithFlag',
                'query_builder' => static fn (LangueParlerRepository $repository) => $repository->createQueryBuilder('l')->orderBy('l.name', 'ASC'),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'attr' => [
                    'class' => 'agency-languages-select d-none',
                    'data-languages-target' => 'select',
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
