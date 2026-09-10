<?php

namespace App\Tests\Functional;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * QA B01 · Admin-Login — funktionale Nachweise gegen features/B01-admin-login/spec.md.
 * Prueft das Ist-Verhalten (kein Fix). FB-01 (kein Throttling) wird belegt, nicht behoben.
 */
final class B01AdminLoginTest extends WebTestCase
{
    private const USER = 'qa_admin';
    private const PASS = 'qa_pass_123';

    /**
     * Client mit eindeutiger Absender-IP. Das Login-Throttling limitiert pro Nutzer+IP
     * und global pro IP; eine je Test frische IP isoliert die Limiter voneinander, ohne
     * vom Speicherort des Limiter-Zustands abzuhaengen.
     */
    private function newClient(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        return static::createClient([], [
            'REMOTE_ADDR' => sprintf('10.%d.%d.%d', random_int(0, 255), random_int(0, 255), random_int(1, 254)),
        ]);
    }

    private function seedAdmin(): void
    {
        $c = static::getContainer();
        $em = $c->get(EntityManagerInterface::class);
        $hasher = $c->get(UserPasswordHasherInterface::class);

        foreach ($em->getRepository(Admin::class)->findAll() as $existing) {
            $em->remove($existing);
        }
        $em->flush();

        $admin = new Admin();
        $admin->setUsername(self::USER);
        $admin->setPassword($hasher->hashPassword($admin, self::PASS));
        $em->persist($admin);
        $em->flush();
    }

    /** @return array{0:\Symfony\Component\Form\FormInterface|\Symfony\Component\DomCrawler\Form,1:\Symfony\Bundle\FrameworkBundle\KernelBrowser} */
    private function submitLogin(string $user, string $pass): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        $client = $this->newClient();
        $this->seedAdmin();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form([
            '_username' => $user,
            '_password' => $pass,
        ]);
        $client->submit($form);

        return $client;
    }

    public function testAK01_login_success_redirects_to_dashboard(): void
    {
        $client = $this->submitLogin(self::USER, self::PASS);
        $this->assertResponseRedirects('/admin');
    }

    public function testAK02_login_failure_shows_error_and_keeps_username(): void
    {
        $client = $this->submitLogin(self::USER, 'falsch');
        $this->assertResponseRedirects('/login');
        $crawler = $client->followRedirect();
        // Fehlerbanner vorhanden, Benutzername bleibt gefuellt
        $this->assertSelectorExists('.text-red-800');
        $this->assertSelectorExists('input#username[value="'.self::USER.'"]');
    }

    public function testAK03_already_authenticated_login_redirects_to_dashboard(): void
    {
        $client = $this->submitLogin(self::USER, self::PASS);
        $client->request('GET', '/login');
        $this->assertResponseRedirects('/admin');
    }

    public function testAK04_admin_requires_authentication(): void
    {
        $client = $this->newClient();
        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(302);
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testAK05_logout_ends_session_and_admin_locks_again(): void
    {
        $client = $this->submitLogin(self::USER, self::PASS);
        $crawler = $client->followRedirect(); // Dashboard /admin

        // Logout traegt jetzt das CSRF-Token (logout_path) — Link anklicken statt GET /logout
        $client->click($crawler->selectLink('Logout')->link());
        $this->assertResponseStatusCodeSame(302); // Ziel app_home (/lb)

        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(302);
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testAK06_login_without_valid_csrf_is_rejected(): void
    {
        $client = $this->newClient();
        $this->seedAdmin();
        // gueltige Zugangsdaten, aber manipuliertes CSRF-Token
        $client->request('POST', '/login', [
            '_username' => self::USER,
            '_password' => self::PASS,
            '_csrf_token' => 'ungueltig',
        ]);
        // darf NICHT auf /admin gelangen
        $location = (string) $client->getResponse()->headers->get('Location');
        $this->assertResponseStatusCodeSame(302);
        $this->assertStringContainsString('/login', $location);
        $this->assertStringNotContainsString('/admin', $location);
    }

    /**
     * Angriff §7: bösartige Eingaben im Login-Feld werden sauber abgewiesen
     * (keine 500, keine ausgefuehrte Eingabe) — Security-Layer parametrisiert.
     *
     */
    #[DataProvider('maliciousInputs')]
    public function testAttack_malicious_login_input_is_safely_rejected(string $payload): void
    {
        $client = $this->newClient();
        $this->seedAdmin();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form([
            '_username' => $payload,
            '_password' => $payload,
        ]);
        $client->submit($form);

        // Auth scheitert sauber (302 zurueck auf /login), niemals 500
        $this->assertResponseStatusCodeSame(302);
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    /** @return array<string,array{0:string}> */
    public static function maliciousInputs(): array
    {
        return [
            'sql-injection' => ["' OR '1'='1'; DROP TABLE admin; --"],
            'xss' => ['<script>alert(1)</script>'],
            'path-traversal' => ['../../etc/passwd'],
            'ueberlang' => [str_repeat('a', 10000)],
        ];
    }

    /**
     * BUG-01 behoben: Login-Throttling greift. Nach fuenf Fehlversuchen wird der
     * sechste Versuch geblockt — AUCH mit korrektem Passwort. Eigener Nutzer +
     * Pool-Reset, damit der Test unabhaengig von anderen Tests und Wiederholläufen ist.
     */
    public function testAK_login_throttling_blocks_after_five_failures(): void
    {
        // Rate-Limiter bereits von setUp() zurueckgesetzt.
        $client = $this->newClient();
        $c = static::getContainer();
        $em = $c->get(EntityManagerInterface::class);
        foreach ($em->getRepository(Admin::class)->findBy(['username' => 'throttle_target']) as $a) {
            $em->remove($a);
        }
        $em->flush();
        $hasher = $c->get(UserPasswordHasherInterface::class);
        $admin = new Admin();
        $admin->setUsername('throttle_target');
        $admin->setPassword($hasher->hashPassword($admin, self::PASS));
        $em->persist($admin);
        $em->flush();

        // fuenf Fehlversuche werden verarbeitet (Redirect auf /login)
        for ($i = 1; $i <= 5; ++$i) {
            $crawler = $client->request('GET', '/login');
            $form = $crawler->selectButton('Login')->form([
                '_username' => 'throttle_target',
                '_password' => 'falsch',
            ]);
            $client->submit($form);
            $this->assertResponseRedirects('/login');
        }

        // sechster Versuch mit KORREKTEM Passwort muss geblockt werden -> nicht /admin
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form([
            '_username' => 'throttle_target',
            '_password' => self::PASS,
        ]);
        $client->submit($form);
        $location = (string) $client->getResponse()->headers->get('Location');
        $this->assertStringNotContainsString('/admin', $location, 'Throttling muss den 6. Versuch blocken, auch mit korrektem Passwort.');
        $this->assertStringContainsString('/login', $location);
    }
}
