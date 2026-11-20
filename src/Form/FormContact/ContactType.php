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

namespace App\Form\FormContact;

use App\Entity\FormContact\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'form.last_name.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'form.last_name.not_blank_placeholder',
                    'class' => 'contact_lastname',
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'form.first_name.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'form.first_name.not_blank_placeholder',
                    'class' => 'contact_firstname',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.email.short_label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'form.email.short_placeholder',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'form.message.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'form.message.placeholder',
                    'class' => 'contact_message',
                    'rows' => 6,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
