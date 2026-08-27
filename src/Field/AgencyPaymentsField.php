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

use App\Form\Admin\AgencyPaymentsType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

final class AgencyPaymentsField implements FieldInterface
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
            ->setFormType(AgencyPaymentsType::class)
            ->setTemplatePath('admin/field/agency_payments.html.twig')
            ->addFormTheme('admin/form/agency_payments.html.twig')
            ->addCssClass('field-agency-payments')
            ->setDefaultColumns('col-12');
    }
}
