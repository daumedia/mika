<?php

namespace App\Tests\Functional;

use App\Entity\News;
use App\Enum\NewsCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * QA B06 · Zweisprachigkeit — funktionale Nachweise gegen
 * features/B06-zweisprachigkeit/spec.md.
 */
final class B06ZweisprachigkeitTest extends WebTestCase
{
    public function testAK03_root_redirects_301_to_lb(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseRedirects('/lb', 301);
    }

    public function testAK01_AK04_lb_and_en_pages_have_correct_lang(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lb');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('html[lang="lb"]');

        $client->request('GET', '/en');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('html[lang="en"]');
    }

    public function testAK02_toggle_preserves_slug_across_locales(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->createQuery('DELETE FROM '.News::class)->execute();
        $n = new News();
        $n->setTitleLb('T')->setTitleEn('T EN')->setSummaryLb('s')->setSummaryEn('s')
            ->setContentLb('c')->setContentEn('c')->setCategory(NewsCategory::Youth)
            ->setSlug('mein-slug')->setPublishedAt(new \DateTimeImmutable('2026-01-01 10:00:00'));
        $em->persist($n);
        $em->flush();

        $crawler = $client->request('GET', '/lb/news/mein-slug');
        $this->assertResponseIsSuccessful();
        $hrefs = $crawler->filter('.lang-toggle a')->extract(['href']);
        $this->assertNotEmpty($hrefs);
        $joined = implode(' ', $hrefs);
        // EN-Umschalter zeigt auf dieselbe Seite mit erhaltenem Slug
        $this->assertStringContainsString('/en/news/mein-slug', $joined);
    }

    public function testAK06_admin_area_is_not_locale_prefixed(): void
    {
        // /admin ohne Locale-Praefix existiert (leitet auf /login, kein 404)
        $client = static::createClient();
        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(302);
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }
}
