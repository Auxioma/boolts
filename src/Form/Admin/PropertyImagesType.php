<?php

namespace App\Form\Admin;

use App\Entity\PropertyImage;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;

/**
 * @extends AbstractType<Collection<int, PropertyImage>>
 */
final class PropertyImagesType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'property_images';
    }
}
