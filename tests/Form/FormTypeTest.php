<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Tests\Support\ProjectClassDiscovery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

final class FormTypeTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::bootKernel();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        (new SchemaTool($entityManager))->updateSchema($metadata);

        self::ensureKernelShutdown();
    }

    #[DataProvider('formTypeProvider')]
    public function testEveryFormTypeIsResolvableAsSymfonyService(string $formTypeClass): void
    {
        self::bootKernel();

        $container = self::getContainer();

        self::assertTrue(
            $container->has($formTypeClass),
            sprintf('Le FormType %s doit être enregistré dans le conteneur Symfony.', $formTypeClass)
        );

        self::assertInstanceOf($formTypeClass, $container->get($formTypeClass));
    }

    #[DataProvider('formTypeProvider')]
    public function testEveryFormTypeCanBuildWithItsDefaultOptions(string $formTypeClass): void
    {
        self::bootKernel();

        $formFactory = self::getContainer()->get(FormFactoryInterface::class);

        try {
            $form = $formFactory->create($formTypeClass);
        } catch (MissingOptionsException $exception) {
            // Un type avec options obligatoires est valide : l'important est que Symfony
            // l'ait correctement résolu et indique explicitement ses options requises.
            self::assertNotSame('', trim($exception->getMessage()));

            return;
        }

        self::assertInstanceOf(FormInterface::class, $form);

        $innerType = $form->getConfig()->getType()->getInnerType();
        self::assertInstanceOf($formTypeClass, $innerType);

        $dataClass = $form->getConfig()->getDataClass();

        if ($dataClass !== null) {
            self::assertTrue(
                class_exists($dataClass) || interface_exists($dataClass),
                sprintf('%s déclare un data_class introuvable : %s', $formTypeClass, $dataClass)
            );
        }
    }

    #[DataProvider('formTypeProvider')]
    public function testEveryFormTypeHasAValidBlockPrefix(string $formTypeClass): void
    {
        self::bootKernel();

        /** @var AbstractType $type */
        $type = self::getContainer()->get($formTypeClass);
        $prefix = $type->getBlockPrefix();

        self::assertIsString($prefix);
        self::assertNotSame('', trim($prefix), sprintf('%s retourne un block prefix vide.', $formTypeClass));
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function formTypeProvider(): array
    {
        $types = [];

        foreach (ProjectClassDiscovery::concreteClassesIn('src/Form') as $class) {
            $reflection = new ReflectionClass($class);

            if ($reflection->isSubclassOf(AbstractType::class)) {
                $types[] = $class;
            }
        }

        return ProjectClassDiscovery::asDataProvider($types);
    }
}
