<?php

namespace App\Tests\Functional;

use App\Entity\News;
use App\Enum\NewsCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * QA B04 · Startseite — funktionale Nachweise gegen features/B04-startseite/spec.md.
 */
final class B04StartseiteTest extends WebTestCase
{
    private function clearNews(): void
    {
        static::getContainer()->get(EntityManagerInterface::class)
            ->createQuery('DELETE FROM '.News::class)->execute();
    }

    public function testAK01_home_has_all_sections(): void
    {
        $client = static::createClient();
        $this->clearNews();
        $client->request('GET', '/lb');
        $this->assertResponseIsSuccessful();
        // Abschnittsanker der Startseite (übersetzungsunabhängig)
        $this->assertSelectorExists('#about');
        $this->assertSelectorExists('#themes');
        $this->assertSelectorExists('#contact');
    }

    public function testAK02_english_locale(): void
    {
        $client = static::createClient();
        $this->clearNews();
        $client->request('GET', '/en');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('html[lang="en"]');
    }

    public function testAK03_news_preview_hidden_when_empty(): void
    {
        $client = static::createClient();
        $this->clearNews();
        $client->request('GET', '/lb');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('.news-card');
    }

    public function testAK03_AK04_news_preview_shows_published(): void
    {
        $client = static::createClient();
        $this->clearNews();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $n = new News();
        $n->setTitleLb('Vorschau-Beitrag')->setTitleEn('Preview')->setSummaryLb('s')->setSummaryEn('s')
            ->setContentLb('c')->setContentEn('c')->setCategory(NewsCategory::Youth)
            ->setSlug('vorschau')->setPublishedAt(new \DateTimeImmutable('2026-01-01 10:00:00'));
        $em->persist($n);
        $em->flush();

        $crawler = $client->request('GET', '/lb');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.news-card');
        $this->assertSelectorTextContains('body', 'Vorschau-Beitrag');
        // Vorschau-Karte verlinkt in die Detailseite im aktuellen Locale
        $hrefs = $crawler->filter('.news-card')->extract(['href']);
        $this->assertStringContainsString('/lb/news/vorschau', implode(' ', $hrefs));
    }
}
