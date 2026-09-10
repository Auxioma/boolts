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
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                ->add('codeIsoPays', HiddenType::class, [
                    'required' => false,
                ])
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
                ->add('surfaceTotal', TextType::class)
                ->add('anneeConstruction', TextType::class, [
                    'required' => false,
                ])
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

            // Colonnes numériques (NUMERIC / SMALLINT) : on tolère la saisie
            // libre ("150 m2", "1 200", "12,5", "vers 1983") mais on ne persiste
            // qu'un nombre propre, sinon la valeur est ignorée.
            $this->addNumericCleanup($builder, 'surfaceTotal');
            $this->addNumericCleanup($builder, 'anneeConstruction', true);
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

        /*
         * Étape 6 (photos) : plus aucun champ de formulaire. Les images sont
         * téléversées / réordonnées / supprimées en AJAX
         * (AgenceImmobiliereMesBiensImagesController) et persistées au fil de
         * l'eau. La contrainte Assert\Count(min: 1, groups: ['step_6']) sur
         * Property::$propertyImages continue de bloquer le bouton « Suivant ».
         */

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
                    ->add('montantLoyerHorsCharge', TextType::class)
                    ->add('montantDepotDeGarantie')
                    ->add('montantDesCharges')
                ;

                // montant_loyer_hors_charge est désormais une colonne NUMERIC.
                $this->addNumericCleanup($builder, 'montantLoyerHorsCharge');
            }

            if ('1' === $typeTransaction) {
                $builder
                    ->add('prix', TextType::class)
                ;

                // prix est désormais une colonne NUMERIC.
                $this->addNumericCleanup($builder, 'prix');
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

    /**
     * Nettoie une saisie « libre » (unités, espaces, virgule décimale) avant
     * qu'elle n'atteigne une colonne numérique de Property. Une valeur qui
     * n'est toujours pas un nombre après nettoyage est ramenée à null plutôt
     * que de faire échouer l'enregistrement.
     *
     * @param FormBuilderInterface<mixed> $builder
     * @param bool                        $integer true pour un entier (année) :
     *                                             on garde le premier groupe de
     *                                             4 chiffres ; false pour un
     *                                             montant décimal
     */
    private function addNumericCleanup(FormBuilderInterface $builder, string $field, bool $integer = false): void
    {
        $builder->get($field)->addModelTransformer(new CallbackTransformer(
            static function (int|string|null $value): string {
                if (null === $value || '' === $value) {
                    return '';
                }

                // Évite d'afficher "150.00" (NUMERIC) là où l'agence a saisi "150".
                if (is_numeric($value) && (float) $value === floor((float) $value)) {
                    return (string) (int) (float) $value;
                }

                return (string) $value;
            },
            static function (?string $value) use ($integer): int|string|null {
                if (null === $value) {
                    return null;
                }

                $value = str_replace([' ', "\u{00a0}", ','], ['', '', '.'], mb_trim($value));

                if ($integer) {
                    return 1 === preg_match('/\d{1,4}/', $value, $matches)
                        ? (int) $matches[0]
                        : null;
                }

                $value = preg_replace('/[^0-9.]/', '', $value) ?? '';

                return is_numeric($value) ? $value : null;
            }
        ));
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
