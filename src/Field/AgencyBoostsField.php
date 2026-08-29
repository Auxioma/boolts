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

namespace App\Field;

use App\Form\Admin\AgencyBoostsType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

final class AgencyBoostsField implements FieldInterface
{
    use FieldTrait;

    public static function new(
        string $propertyName,
        TranslatableInterface|string|bool|null $label = null,
    ): self {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setValue([])
            ->setVirtual(true)
            ->setFormType(AgencyBoostsType::class)
            ->setTemplatePath('admin/field/agency_boosts.html.twig')
            ->addFormTheme('admin/form/agency_boosts.html.twig')
            ->addCssClass('field-agency-boosts')
            ->setDefaultColumns('col-12');
    }
}
