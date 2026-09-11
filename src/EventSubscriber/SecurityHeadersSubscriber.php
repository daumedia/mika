<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Setzt Sicherheits-Header auf jede Antwort.
 *
 * Die Content-Security-Policy läuft zunächst im **Report-Only**-Modus: Der Browser
 * blockiert nichts, meldet Verstöße aber an /csp-report (siehe CspReportController) und in
 * die Entwickler-Konsole. So lässt sich gefahrlos sammeln, was eine erzwingende CSP bräuchte,
 * bevor sie scharf geschaltet wird. Bekannte offene Punkte für die scharfe Fassung:
 * das Inline-<script> und die Inline-Handler im Admin-News-Formular (BF-07) sowie eine
 * Inline-`style`-Animation auf der Startseite.
 */
class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    /**
     * Ziel-Policy (strikt, ausschließlich eigene Herkunft). Alles Externe würde gemeldet;
     * die Seite lädt JS/CSS aus /build (Encore), Schriften aus /fonts, Bilder aus /images
     * und das Favicon als data:-URI — alles `'self'` bzw. `data:`.
     */
    private const CSP_REPORT_ONLY =
        "default-src 'self'; "
        ."base-uri 'self'; "
        ."object-src 'none'; "
        ."frame-ancestors 'none'; "
        ."form-action 'self'; "
        ."script-src 'self'; "
        ."style-src 'self'; "
        ."img-src 'self' data:; "
        ."font-src 'self'; "
        ."connect-src 'self'; "
        .'report-uri /csp-report';

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=(), browsing-topics=()');

        // Vorerst nur beobachtend — bricht nichts, sammelt aber Verstöße.
        $headers->set('Content-Security-Policy-Report-Only', self::CSP_REPORT_ONLY);

        // HSTS nur über HTTPS senden (nach dem Trusted-Proxy-Setup erkennt Symfony
        // das korrekt). Über http würde der Browser den Header ohnehin ignorieren.
        if ($event->getRequest()->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Doppelt hält: expose_php=Off entfernt den Header bereits, hier zur Sicherheit.
        $headers->remove('X-Powered-By');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [['onKernelResponse', -10]],
        ];
    }
}
