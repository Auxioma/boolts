<?php

namespace App\Form\SearchBar;

use App\Entity\CategoryBienTransaction;
use App\Entity\SearchBar\FilterCityCountry;
use App\Repository\CategoryBienTransactionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FilterCityCountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filter', SearchType::class, [
                'label' => false,
                'required' => false,
                'trim' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    'placeholder' => 'search.location.placeholder',
                    'class' => 'search-bar__input',
                ],
            ])

            /*
             * VRAI CHAMP LIÉ À LA BDD.
             *
             * Symfony va recevoir l'id dans le select caché,
             * puis il va te retourner directement l'objet CategoryBienTransaction.
             */
            ->add('transactionType', EntityType::class, [
                'class' => CategoryBienTransaction::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => false,
                'required' => true,
                'placeholder' => false,
                'query_builder' => function (CategoryBienTransactionRepository $repository) {
                    return $repository
                        ->createQueryBuilder('transaction')
                        ->orderBy('transaction.id', 'ASC');
                },
                'attr' => [
                    'class' => 'd-none',
                    'data-home-bt-transaction-input' => '',
                ],
            ])

            ->add('selectedValue', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedMapboxId', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedFeatureType', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedCountryName', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedCountryCode', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedRegionName', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedCityName', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedPostalCode', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedLatitude', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedLongitude', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedFullAddress', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedLocale', HiddenType::class, [
                'required' => false,
            ])

            ->add('selectedLocationJson', HiddenType::class, [
                'required' => false,
                'trim' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FilterCityCountry::class,
            'csrf_protection' => true,
        ]);
    }
}