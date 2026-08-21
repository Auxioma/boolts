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

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('security')]
final class AdminSecurityTest extends WebTestCase
{
    public function testAnonymousUserCannotOpenAdminDashboardOverHttps(): void
    {
        $client = static::createClient();
        $client->request('GET', 'https://localhost/admin');

        $statusCode = $client->getResponse()->getStatusCode();

        self::assertContains(
            $statusCode,
            [302, 401, 403],
            \sprintf(
                'Le dashboard /admin doit être interdit aux utilisateurs anonymes. Code HTTP reçu : %d. '.
                'Si ce test retourne 200, réactivez la règle ROLE_ADMIN dans config/packages/security.yaml.',
                $statusCode
            )
        );
    }
}
