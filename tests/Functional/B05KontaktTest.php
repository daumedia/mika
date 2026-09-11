<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * QA B05 · Kontaktseite — funktionale Nachweise gegen features/B05-kontakt/spec.md.
 * Bestaetigt: mailto/Links, KEIN Formular (der ungenutzte ContactType wurde entfernt, BF-11).
 */
final class B05KontaktTest extends WebTestCase
{
    public function testAK01_lb_shows_contact_links_and_no_form(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lb/contact');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href^="mailto:"]');
        // kein Eingabeformular auf der Seite (bewusst nur mailto)
        $this->assertSelectorNotExists('form');
        $this->assertSelectorNotExists('input');
        $this->assertSelectorNotExists('textarea');
    }

    public function testAK02_en_locale(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/contact');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('html[lang="en"]');
    }

    public function testAK03_AK04_party_and_social_links_are_safe_external(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/lb/contact');
        // Partei-Link auf dp.lu, extern und mit rel noopener
        $party = $crawler->filter('a[href="https://www.dp.lu"]');
        $this->assertGreaterThan(0, $party->count());
        $this->assertSame('_blank', $party->first()->attr('target'));
        $this->assertStringContainsString('noopener', (string) $party->first()->attr('rel'));
    }
}
