<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Sicherheits-Header und die Report-Only-CSP (SecurityHeadersSubscriber + CspReportController).
 */
final class SecurityHeadersTest extends WebTestCase
{
    public function testSecurityHeadersPresent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lb');
        $this->assertResponseIsSuccessful();

        $headers = $client->getResponse()->headers;
        $this->assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $headers->get('X-Frame-Options'));
    }

    public function testCspIsReportOnlyAndStrict(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lb');

        $csp = $client->getResponse()->headers->get('Content-Security-Policy-Report-Only');
        $this->assertNotNull($csp, 'CSP läuft im Report-Only-Modus');
        // Keine erzwingende CSP (die würde die Seite bei Verstößen zerlegen)
        $this->assertNull($client->getResponse()->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString('report-uri /csp-report', $csp);
    }

    public function testCspReportEndpointAccepts204(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/csp-report',
            [],
            [],
            ['CONTENT_TYPE' => 'application/csp-report'],
            (string) json_encode(['csp-report' => [
                'document-uri' => 'https://example.test/lb',
                'violated-directive' => "style-src 'self'",
                'blocked-uri' => 'inline',
            ]]),
        );
        $this->assertResponseStatusCodeSame(204);
    }
}
