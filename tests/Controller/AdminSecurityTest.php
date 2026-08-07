<?php

declare(strict_types=1);

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
            sprintf(
                'Le dashboard /admin doit être interdit aux utilisateurs anonymes. Code HTTP reçu : %d. '.
                'Si ce test retourne 200, réactivez la règle ROLE_ADMIN dans config/packages/security.yaml.',
                $statusCode
            )
        );
    }
}
