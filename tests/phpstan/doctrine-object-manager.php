<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$kernel = new Kernel('dev', true);
$kernel->boot();

/** @var EntityManagerInterface $entityManager */
$entityManager = $kernel->getContainer()
    ->get('doctrine')
    ->getManager();

return $entityManager;
