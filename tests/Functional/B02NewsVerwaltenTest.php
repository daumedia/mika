<?php

namespace App\Tests\Functional;

use App\Entity\Admin;
use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * QA B02 · Neuigkeiten verwalten — funktionale Nachweise gegen
 * features/B02-news-verwalten/spec.md. Prueft das Ist-Verhalten (kein Fix).
 * BUG (Slug-Duplikat -> 500) wird belegt, nicht behoben.
 */
final class B02NewsVerwaltenTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->em->createQuery('DELETE FROM '.News::class)->execute();
        foreach ($this->em->getRepository(Admin::class)->findAll() as $a) {
            $this->em->remove($a);
        }
        $this->em->flush();

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $admin = new Admin();
        $admin->setUsername('qa_admin');
        $admin->setPassword($hasher->hashPassword($admin, 'qa_pass_123'));
        $this->em->persist($admin);
        $this->em->flush();

        $this->client->loginUser($admin);
    }

    private function makeNews(string $slug, string $titleLb, string $when): void
    {
        $n = new News();
        $n->setTitleLb($titleLb)->setTitleEn($titleLb.' EN')
            ->setSummaryLb('Kuerz')->setSummaryEn('Short')
            ->setContentLb('Inhalt')->setContentEn('Content')
            ->setCategory('youth')->setSlug($slug)
            ->setPublishedAt(new \DateTimeImmutable($when));
        $this->em->persist($n);
        $this->em->flush();
    }

    /**
     * BF-07 · Das News-Formular nutzt einen Stimulus-Controller statt Inline-JS
     * (Voraussetzung fuer eine strikte CSP: kein Inline-<script>, kein onclick).
     */
    public function testBF07_news_form_uses_stimulus_no_inline_js(): void
    {
        $this->client->request('GET', '/admin/news/create');
        $this->assertResponseIsSuccessful();

        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringNotContainsString('onclick=', $html);
        $this->assertStringNotContainsString('function switchTab', $html);
        $this->assertStringContainsString('data-controller="news-form"', $html);
        $this->assertStringContainsString('data-news-form-target="slug"', $html);
        $this->assertStringContainsString('news-form#switchTab', $html);
    }

    /** BF-07 · Loesch-Bestaetigung ueber Stimulus statt onsubmit="confirm(...)". */
    public function testBF07_dashboard_delete_uses_confirm_controller(): void
    {
        $this->makeNews('x-slug', 'X', '2026-01-01 10:00:00');
        $this->client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();

        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringNotContainsString('onsubmit=', $html);
        $this->assertStringContainsString('submit->confirm#check', $html);
    }

    public function testAK01_dashboard_lists_all_including_future_dated(): void
    {
        $this->makeNews('past-a', 'Vergangener Beitrag', '2020-01-01 10:00:00');
        $this->makeNews('future-b', 'Zukuenftiger Beitrag', '2099-01-01 10:00:00');

        $this->client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Vergangener Beitrag');
        $this->assertSelectorTextContains('body', 'Zukuenftiger Beitrag');
    }

    public function testAK02_create_saves_and_shows_flash(): void
    {
        $crawler = $this->client->request('GET', '/admin/news/create');
        $form = $crawler->selectButton('Publizéieren')->form([
            'news[titleLb]' => 'Neier Titel',
            'news[titleEn]' => 'New Title',
            'news[summaryLb]' => 'Kuerz LB',
            'news[summaryEn]' => 'Short EN',
            'news[contentLb]' => 'Inhalt LB',
            'news[contentEn]' => 'Content EN',
            'news[category]' => 'housing',
            'news[slug]' => 'neier-titel',
            'news[publishedAt]' => '2026-12-01T10:00',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin');

        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Artikel ugeluecht.');
        $this->assertSelectorTextContains('body', 'Neier Titel');
    }

    public function testAK03_missing_required_field_does_not_save(): void
    {
        $before = $this->em->getRepository(News::class)->count([]);
        $crawler = $this->client->request('GET', '/admin/news/create');
        $form = $crawler->selectButton('Publizéieren')->form([
            'news[titleLb]' => '', // Pflichtfeld leer
            'news[titleEn]' => 'New Title',
            'news[summaryLb]' => 'x', 'news[summaryEn]' => 'x',
            'news[contentLb]' => 'x', 'news[contentEn]' => 'x',
            'news[category]' => 'youth',
            'news[slug]' => 'kein-speichern',
            'news[publishedAt]' => '2026-12-01T10:00',
        ]);
        $this->client->submit($form);

        // Ungueltiges Formular: Symfony rendert es mit HTTP 422 neu, keine Weiterleitung,
        // nichts gespeichert. Zugleich Beleg fuer den Feldfehler.
        $this->assertResponseStatusCodeSame(422);
        $after = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(News::class)->count([]);
        $this->assertSame($before, $after);
    }

    public function testAK04_edit_persists_change(): void
    {
        $this->makeNews('edit-me', 'Alter Titel', '2026-01-01 10:00:00');
        $id = $this->em->getRepository(News::class)->findOneBy(['slug' => 'edit-me'])->getId();

        $crawler = $this->client->request('GET', '/admin/news/'.$id.'/edit');
        $form = $crawler->selectButton('Späicheren')->form();
        $form['news[titleLb]'] = 'Geänderter Titel';
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Artikel aktualiséiert.');
        $this->assertSelectorTextContains('body', 'Geänderter Titel');
    }

    public function testAK05_delete_with_valid_csrf_removes(): void
    {
        $this->makeNews('delete-me', 'Zu löschen', '2026-01-01 10:00:00');
        $crawler = $this->client->request('GET', '/admin');
        // Lösch-Formular per DOM absenden (enthaelt gueltiges CSRF-Token)
        $form = $crawler->filter('form[action$="/delete"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Artikel geläscht.');
        $count = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(News::class)->count([]);
        $this->assertSame(0, $count);
    }

    public function testAK06_unknown_id_returns_404(): void
    {
        $this->client->request('GET', '/admin/news/999999/edit');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testAK08_unauthenticated_admin_route_redirects_to_login(): void
    {
        // Sitzungscookie verwerfen → anonymer Zugriff (Logout traegt jetzt CSRF-Token)
        $this->client->getCookieJar()->clear();
        $this->client->request('GET', '/admin/news/create');
        $this->assertResponseStatusCodeSame(302);
        $this->assertStringContainsString('/login', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testAK09_delete_without_valid_csrf_does_not_remove(): void
    {
        $this->makeNews('keep-me', 'Bleibt', '2026-01-01 10:00:00');
        $id = $this->em->getRepository(News::class)->findOneBy(['slug' => 'keep-me'])->getId();

        $this->client->request('POST', '/admin/news/'.$id.'/delete', ['_token' => 'ungueltig']);
        $this->assertResponseRedirects('/admin');

        $count = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(News::class)->count([]);
        $this->assertSame(1, $count, 'Ohne gueltiges CSRF-Token darf nicht geloescht werden.');
    }

    public function testAK10_create_without_valid_form_csrf_is_rejected(): void
    {
        $before = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(News::class)->count([]);
        // vollstaendige Daten, aber manipuliertes Form-CSRF-Token
        $this->client->request('POST', '/admin/news/create', ['news' => [
            'titleLb' => 'X', 'titleEn' => 'X', 'summaryLb' => 'x', 'summaryEn' => 'x',
            'contentLb' => 'x', 'contentEn' => 'x', 'category' => 'youth',
            'slug' => 'csrf-test', 'publishedAt' => '2026-12-01T10:00',
            '_token' => 'ungueltig',
        ]]);
        $this->assertResponseStatusCodeSame(422); // Formular ungueltig (CSRF)
        $after = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(News::class)->count([]);
        $this->assertSame($before, $after, 'Ohne gueltiges Form-CSRF darf nichts gespeichert werden.');
    }

    /**
     * BF-04 behoben: doppelter Slug wird als Formular-/Feldfehler abgefangen
     * (UniqueEntity), nicht mehr als HTTP 500.
     */
    public function testAK_duplicate_slug_is_field_error_not_500(): void
    {
        $this->makeNews('dup', 'Erster', '2026-01-01 10:00:00');

        $crawler = $this->client->request('GET', '/admin/news/create');
        $form = $crawler->selectButton('Publizéieren')->form([
            'news[titleLb]' => 'Zweiter', 'news[titleEn]' => 'Second',
            'news[summaryLb]' => 'x', 'news[summaryEn]' => 'x',
            'news[contentLb]' => 'x', 'news[contentEn]' => 'x',
            'news[category]' => 'youth',
            'news[slug]' => 'dup', // schon vergeben
            'news[publishedAt]' => '2026-12-01T10:00',
        ]);
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422); // Formular ungueltig, kein 500
        $count = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(News::class)->count([]);
        $this->assertSame(1, $count, 'Kein zweiter Beitrag mit doppeltem Slug.');
    }
}
