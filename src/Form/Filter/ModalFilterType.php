<?php

declare(strict_types=1);

namespace App\Form\Filter;

use App\Entity\CategoryBienTransaction;
use App\Entity\Filter\ModalFilter;
use App\Repository\CategoryBienTransactionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModalFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('natureDeLaPropriete', EntityType::class, [
                    'class' => CategoryBienTransaction::class,
                    'choice_label' => 'name',
                    'choice_attr' => static function (CategoryBienTransaction $categoryBienTransaction) {
                        return [
                            'icon' => $categoryBienTransaction->getIcone(),
                            'name' => $categoryBienTransaction->getName(),
                        ];
                    },
                ])

  /*          ->add('typeDePropriete', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'Maison' => 'maison',
                    'Appartement' => 'appartement',
                    'Villa' => 'villa',
                    'Local' => 'local',
                    'Terrain' => 'terrain',
                    'Bureau' => 'bureau',
                ],
            ])
*/
->add('paysSearch', SearchType::class, [
    'label' => false,
    'mapped' => false,
    'required' => false,
])

->add('pays', HiddenType::class, [
    'label' => false,
    'mapped' => false,
    'required' => false,
    'data' => '[]',
])

->add('villeSearch', SearchType::class, [
    'label' => false,
    'mapped' => false,
    'required' => false,
])

->add('ville', HiddenType::class, [
    'label' => false,
    'mapped' => false,
    'required' => false,
    'data' => '[]',
])


->add('quartierSearch', SearchType::class, [
    'label' => false,
    'mapped' => false,
    'required' => false,
])

->add('quartier', HiddenType::class, [
    'label' => false,
    'mapped' => false,
    'required' => false,
    'data' => '[]',
])
/*
            ->add('minChambres', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxChambres', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('minSallesDeBain', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxSallesDeBain', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('minSurface', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxSurface', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('minAnneeConstruction', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxAnneeConstruction', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('minPrix', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('maxPrix', IntegerType::class, [
                'label' => false,
                'required' => false,
            ])

            ->add('dpe', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                    'E' => 'E',
                    'F' => 'F',
                    'G' => 'G',
                ],
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ModalFilter::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'modal_filter';
    }
}