<?php

namespace App\Field;

use App\Form\Admin\PropertyImagesType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

final class PropertyImagesField implements FieldInterface
{
    use FieldTrait;

    public static function new(
        string $propertyName,
        TranslatableInterface|string|bool|null $label = null,
    ): self {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(PropertyImagesType::class)
            ->addCssClass('field-property-images')
            ->setDefaultColumns('col-12')
            ->setDisabled();
    }
}
