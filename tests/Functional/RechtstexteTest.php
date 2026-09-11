<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Feature 01 · Rechtstexte — funktionale Nachweise gegen
 * features/01-rechtstexte/spec.md.
 */
final class RechtstexteTest extends WebTestCase
{
    /**
     * @return iterable<string, array{0: string}>
     */
    public static function legalUrls(): iterable
    {
        yield 'impressum lb' => ['/lb/impressum'];
        yield 'impressum en' => ['/en/impressum'];
        yield 'datenschutz lb' => ['/lb/datenschutz'];
        yield 'datenschutz en' => ['/en/datenschutz'];
    }

    /**
     * AK-02, AK-03, AK-10 · beide Seiten in beiden Sprachen ohne Login erreichbar (200,
     * kein Redirect auf /login).
     *
     */
    #[DataProvider('legalUrls')]
    public function testAK02_AK03_AK10_pages_public_and_200(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    /** AK-04 · Sprachzweig bestimmt das `lang`-Attribut. */
    public function testAK04_locale_matches_url_prefix(): void
    {
        $client = static::createClient();

        $client->request('GET', '/lb/impressum');
        $this->assertSelectorExists('html[lang="lb"]');

        $client->request('GET', '/en/impressum');
        $this->assertSelectorExists('html[lang="en"]');
    }

    /** AK-07 · Impressum zeigt die Pflichtangaben (Name, mailto, Partei). */
    public function testAK07_impressum_shows_required_details(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/lb/impressum');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('body', 'Ferreira');
        $this->assertSelectorTextContains('body', 'Kayl-Téiteng');
        // Kontakt als mailto-Verweis
        $this->assertSelectorExists('a[href^="mailto:"]');

        $text = $crawler->filter('body')->text();
        // Postanschrift vorhanden, kein Platzhalter mehr
        $this->assertStringContainsString('rue de la Fontaine', $text);
        $this->assertStringContainsString('L-3768', $text);
        $this->assertStringNotContainsString('nach ze liwweren', $text);
    }

    /** AK-08 · Datenschutz nennt die zentralen Verarbeitungen und die CNPD. */
    public function testAK08_datenschutz_names_processing_and_cnpd(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/lb/datenschutz');
        $this->assertResponseIsSuccessful();

        $text = $crawler->filter('body')->text();
        $this->assertStringContainsString('Sentry', $text);
        $this->assertStringContainsString('CNPD', $text);
        // Hosting benannt, kein Platzhalter mehr
        $this->assertStringContainsString('Hostinger', $text);
        $this->assertStringNotContainsString('nach ze bestätegen', $text);
    }

    /**
     * AK-05, EC-01 · Auf einer Rechtsseite schaltet die Sprachpille auf die Gegenfassung
     * derselben Seite — nicht zurück auf die Startseite.
     */
    public function testAK05_language_toggle_targets_counterpart_page(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/lb/impressum');
        $this->assertResponseIsSuccessful();

        $hrefs = implode(' ', $crawler->filter('.lang-toggle a')->extract(['href']));
        $this->assertStringContainsString('/en/impressum', $hrefs);
    }

    /** AK-01 · Der Footer jeder öffentlichen Seite verlinkt beide Rechtsseiten. */
    public function testAK01_footer_links_both_legal_pages(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/lb');
        $this->assertResponseIsSuccessful();

        $hrefs = implode(' ', $crawler->filter('footer a')->extract(['href']));
        $this->assertStringContainsString('/lb/impressum', $hrefs);
        $this->assertStringContainsString('/lb/datenschutz', $hrefs);
    }

    /** AK-11 · Eigener, sprachrichtiger Seitentitel. */
    public function testAK11_pages_have_own_title(): void
    {
        $client = static::createClient();

        $client->request('GET', '/lb/impressum');
        $this->assertSelectorTextContains('title', 'Impressum');

        $client->request('GET', '/en/datenschutz');
        $this->assertSelectorTextContains('title', 'Privacy');
    }

    /** AK-05 · Auch die Datenschutzseite schaltet auf ihre Gegenfassung. */
    public function testAK05_datenschutz_toggle_targets_counterpart(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/datenschutz');
        $this->assertResponseIsSuccessful();

        $hrefs = implode(' ', $crawler->filter('.lang-toggle a')->extract(['href']));
        $this->assertStringContainsString('/lb/datenschutz', $hrefs);
    }

    /** EC-03 · Unbekanntes Locale-Präfix wird nicht bedient (404, keine Sonderbehandlung). */
    public function testEC03_unknown_locale_prefix_is_404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/de/impressum');
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * BF-13 · Kein Dritt-CDN-Polyfill mehr (ga.jspm.io / es-module-shims); Encore lädt
     * die App weiterhin. Wächter gegen die Rückkehr des IP-Abflusses (AK-09).
     */
    public function testBF13_no_third_party_polyfill(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lb/datenschutz');
        $this->assertResponseIsSuccessful();

        $html = (string) $client->getResponse()->getContent();
        $this->assertStringNotContainsString('jspm.io', $html);
        $this->assertStringNotContainsString('es-module-shims', $html);
        // Encore bleibt der Lader der App:
        $this->assertStringContainsString('/build/', $html);
    }

    /** AK-06 · Die EN-Fassung trägt den Verbindlichkeitshinweis, die LU-Fassung nicht. */
    public function testAK06_en_shows_authoritative_note(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/datenschutz');
        $this->assertResponseIsSuccessful();
        $enText = $crawler->filter('body')->text();
        $this->assertMatchesRegularExpression('/Luxembourgish|authoritative|binding/i', $enText);

        $crawler = $client->request('GET', '/lb/datenschutz');
        $lbText = $crawler->filter('body')->text();
        $this->assertDoesNotMatchRegularExpression('/is legally authoritative/i', $lbText);
    }
}
