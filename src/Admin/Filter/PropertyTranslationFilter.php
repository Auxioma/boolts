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

namespace App\Admin\Filter;

use App\Entity\PropertyTranslation;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\TextFilterType;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Filtre texte sur un champ traduit du bien (ville, pays, région…).
 *
 * Les champs de localisation sont portés par {@see PropertyTranslation} via
 * KnpDoctrineBehaviors ; on interroge donc la traduction avec une sous-requête
 * EXISTS pour éviter de dupliquer les lignes du bien.
 */
final class PropertyTranslationFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(
        string $propertyName,
        TranslatableInterface|string|false|null $label = null,
    ): self {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(TextFilterType::class)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle');
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto,
    ): void {
        $alias = $filterDataDto->getEntityAlias();
        $property = $filterDataDto->getProperty();
        $comparison = $filterDataDto->getComparison();
        $parameterName = $filterDataDto->getParameterName();
        $translationAlias = 'trans_'.$parameterName;

        $subQuery = \sprintf(
            'SELECT 1 FROM %s %s WHERE %s.translatable = %s AND %s.%s %s :%s',
            PropertyTranslation::class,
            $translationAlias,
            $translationAlias,
            $alias,
            $translationAlias,
            $property,
            $comparison,
            $parameterName,
        );

        $queryBuilder
            ->andWhere(\sprintf('EXISTS (%s)', $subQuery))
            ->setParameter($parameterName, $filterDataDto->getValue());
    }
}
