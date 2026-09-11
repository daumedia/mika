<?php

namespace App\Tests\Functional;

use App\Entity\News;
use App\Enum\NewsCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * QA B03 · Neuigkeiten lesen — funktionale Nachweise gegen
 * features/B03-news-lesen/spec.md. Prueft das Ist-Verhalten (kein Fix).
 * AK-07 (Vorab-Leck geplanter Beitraege) und AK-08 (XSS-Escaping) werden belegt.
 */
final class B03NewsLesenTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->createQuery('DELETE FROM '.News::class)->execute();
        $this->em->flush();
    }

    private function makeNews(string $slug, string $titleLb, string $titleEn, string $when, string $content = 'Zeile eins'."\n".'Zeile zwei'): void
    {
        $n = new News();
        $n->setTitleLb($titleLb)->setTitleEn($titleEn)
            ->setSummaryLb('Kuerzfassung')->setSummaryEn('Summary')
            ->setContentLb($content)->setContentEn($content)
            ->setCategory(NewsCategory::Youth)->setSlug($slug)
            ->setPublishedAt(new \DateTimeImmutable($when));
        $this->em->persist($n);
        $this->em->flush();
    }

    public function testAK01_list_shows_published_desc_lb(): void
    {
        $this->makeNews('aelter', 'Aelterer Beitrag', 'Older', '2026-01-01 10:00:00');
        $this->makeNews('neuer', 'Neuerer Beitrag', 'Newer', '2026-06-01 10:00:00');

        $this->client->request('GET', '/lb/news');
        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Neuerer Beitrag', $html);
        $this->assertStringContainsString('Aelterer Beitrag', $html);
        $this->assertStringContainsString('Kuerzfassung', $html);
        // absteigend: der neuere steht vor dem aelteren
        $this->assertLessThan(strpos($html, 'Aelterer Beitrag'), strpos($html, 'Neuerer Beitrag'));
    }

    public function testAK02_list_english_locale(): void
    {
        $this->makeNews('x', 'LB Titel', 'English Title', '2026-01-01 10:00:00');
        $this->client->request('GET', '/en/news');
        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('English Title', $html);
        $this->assertStringNotContainsString('LB Titel', $html);
    }

    public function testAK03_future_dated_absent_from_public_list(): void
    {
        $this->makeNews('geplant', 'Geplanter Beitrag', 'Planned', '2099-01-01 10:00:00');
        $this->client->request('GET', '/lb/news');
        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('Geplanter Beitrag', (string) $this->client->getResponse()->getContent());
    }

    public function testAK04_empty_state_when_no_published(): void
    {
        $this->client->request('GET', '/lb/news');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('.news-card');
    }

    public function testAK05_detail_shows_content(): void
    {
        $this->makeNews('detail', 'Detail Titel', 'Detail EN', '2026-01-01 10:00:00');
        $this->client->request('GET', '/lb/news/detail');
        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Detail Titel', $html);
        $this->assertStringContainsString('Zeile eins', $html);
        $this->assertStringContainsString('Zeile zwei', $html);
    }

    public function testAK06_unknown_slug_returns_404(): void
    {
        $this->client->request('GET', '/lb/news/gibt-es-nicht');
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * BF-08 behoben: geplanter (zukuenftig datierter) Beitrag ist ueber die Direkt-URL
     * NICHT mehr abrufbar — 404 bis zum Veroeffentlichungsdatum.
     */
    public function testAK07_future_dated_not_reachable_via_direct_url(): void
    {
        $this->makeNews('geheim-geplant', 'Geheim Geplant', 'Secret Planned', '2099-01-01 10:00:00');
        $this->client->request('GET', '/lb/news/geheim-geplant');
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * AK-08: Beitragsinhalt mit HTML/Script wird escaped ausgegeben (kein Stored-XSS).
     */
    public function testAK08_content_is_html_escaped(): void
    {
        $payload = '<script>alert(1)</script>';
        $this->makeNews('xss', 'XSS Titel', 'XSS EN', '2026-01-01 10:00:00', $payload);
        $this->client->request('GET', '/lb/news/xss');
        $this->assertResponseIsSuccessful();
        $html = (string) $this->client->getResponse()->getContent();
        // roher Payload nicht ausgegeben, sondern escaped
        $this->assertStringNotContainsString($payload, $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
