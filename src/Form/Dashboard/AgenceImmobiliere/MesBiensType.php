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

use App\Entity\Caracteristique;
use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Property;
use App\Service\Intl\CountryNameResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MesBiensType extends AbstractType
{
    public function __construct(
        private readonly CountryNameResolver $countryNameResolver,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $step = $options['step'];
        $typeTransaction = $options['typeTransaction'] ?? null;

        if (1 === $step) {
            $builder
                ->add('typeBien', EntityType::class, [
                    'class' => CategoryBien::class,
                    'choice_label' => 'name',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(
                            message: 'Veuillez sélectionner un type de bien.',
                            groups: ['step_1']
                        ),
                    ],
                    'choice_attr' => static function (CategoryBien $categoryBien) {
                        return [
                            'icon' => $categoryBien->getIcone(),
                            'name' => $categoryBien->getName(),
                        ];
                    },
                ])
            ;
        }

        if (2 === $step) {
            $builder
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
            ;
        }

        if (3 === $step) {
            $builder
                ->add('adresse')
                ->add('codePostal')
                ->add('ville')
                ->add('pays', CountryType::class, [
                    'placeholder' => 'Saisissez le pays',
                ])
                ->add('neighborhood')
            ;

            /*
             * En base, "pays" est stocké sous forme de libellé ("France",
             * "Belgique"…) — c'est ce qu'écrit MapboxAddressTranslator et ce
             * que compare la recherche publique. Or CountryType attend un code
             * ISO 3166-1 alpha-2. Sans conversion, le pays enregistré n'est
             * jamais présélectionné quand on revient sur l'étape.
             *
             * transform()        : libellé (ou code déjà valide) -> code ISO
             * reverseTransform() : code ISO -> libellé, pour conserver le
             *                      format attendu par le reste de l'application.
             */
            $builder->get('pays')->addModelTransformer(new CallbackTransformer(
                fn (?string $stored): ?string => $this->countryNameResolver->toAlphaTwoCode($stored),
                fn (?string $code): ?string => $this->countryNameResolver->toName(
                    $code,
                    \Locale::getDefault()
                ) ?? $code,
            ));

            $builder
                ->add('locality')
                ->add('mapboxId', HiddenType::class)
                ->add('fullAddress', HiddenType::class)
                ->add('featureType', HiddenType::class)
                ->add('region', HiddenType::class)
                ->add('district', HiddenType::class)
                ->add('poi', HiddenType::class)
                ->add('longitude', HiddenType::class)
                ->add('latitude', HiddenType::class)
                ->add('sessionIdMapbox', HiddenType::class)
                ->add('showAdresse', CheckboxType::class)
            ;
        }

        if (4 === $step) {
            $builder
                ->add('chambres', HiddenType::class, [
                    'required' => false,
                ])
                ->add('salleDeBains', HiddenType::class, [
                    'required' => false,
                ])
                ->add('surfaceTotal')
                ->add('anneeConstruction')
                ->add('caracteristique', EntityType::class, [
                    'class' => Caracteristique::class,
                    'choice_label' => 'nom',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                    'choice_attr' => static function (Caracteristique $caracteristique) {
                        return [
                            'icon' => $caracteristique->getIcone(),
                            'name' => $caracteristique->getNom(),
                        ];
                    },
                ])
            ;
        }

        if (5 === $step) {
            $builder
                ->add('dpe')
                ->add('ges')
                ->add('dpeMax')
                ->add('dpeMin')
                ->add('dateIndexationEnergie', DateType::class, [
                    'widget' => 'single_text',
                    'html5' => true,
                    'attr' => [
                        'class' => 'form-control text-uppercase',
                    ],
                ])
                ->add('dpeLettre', ChoiceType::class, [
                    'choices' => [
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                        'E' => 'E',
                        'F' => 'F',
                        'G' => 'G',
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'required' => true,
                ])
                ->add('gesLettre', ChoiceType::class, [
                    'choices' => [
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                        'E' => 'E',
                        'F' => 'F',
                        'G' => 'G',
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'required' => true,
                ])
            ;
        }

        if (6 === $step) {
            $builder
                ->add('propertyImages', CollectionType::class, [
                    'entry_type' => PropertyImageType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                    'required' => false,
                ])
            ;
        }

        if (7 === $step) {
            $builder
                ->add('titreDuLogement')
                ->add('descriptionLogement', TextareaType::class, [
                    'attr' => [
                        'rows' => 5,
                    ],
                ])
                ->add('referenceInterne')
            ;
        }

        if (8 === $step) {
            if ('2' === $typeTransaction) {
                $builder
                    ->add('montantLoyerHorsCharge')
                    ->add('montantDepotDeGarantie')
                    ->add('montantDesCharges')
                ;
            }

            if ('1' === $typeTransaction) {
                $builder
                    ->add('prix')
                ;
            }
        }

        $builder
            ->add('saveAndExit', SubmitType::class, [
                'label' => 'Enregistrer et quitter',
                'validation_groups' => false,
                'attr' => [
                    'class' => 'btn-retour py-10 px-16',
                    'formnovalidate' => 'formnovalidate',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Suivant',
                'attr' => [
                    'class' => 'py-10 px-16 btn-suivant',
                    'data-mes-biens--submit-target' => 'submit',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
            'step' => 1,
            'typeTransaction' => null,
            'validation_groups' => static function (FormInterface $form): array {
                $step = $form->getConfig()->getOption('step');

                return ['step_'.$step];
            },
        ]);

        $resolver->setAllowedTypes('step', 'int');
        $resolver->setAllowedTypes('typeTransaction', ['null', 'string']);
    }
}
