<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Setzt Sicherheits-Header auf jede Antwort.
 *
 * Die Content-Security-Policy ist strikt (ausschließlich eigene Herkunft). Der Modus wird
 * über die Env `CSP_ENFORCE` umgeschaltet:
 *   - 0 (Standard): `Content-Security-Policy-Report-Only` — blockiert nichts, meldet nur an
 *     /csp-report (siehe CspReportController).
 *   - 1: `Content-Security-Policy` — erzwingend.
 *
 * So lässt sich in Coolify scharfschalten und bei einem Problem sofort ohne Code-Deploy
 * zurückschalten. Vorher gehören die Verstöße im Log auf null (die bekannten — Admin-Inline-JS
 * BF-07, Startseiten-Inline-Style — sind behoben).
 */
class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    /**
     * Ziel-Policy (strikt). Die Seite lädt JS/CSS aus /build (Encore), Schriften aus /fonts,
     * Bilder aus /images und das Favicon als data:-URI — alles `'self'` bzw. `data:`.
     */
    private const CSP_POLICY =
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

    public function __construct(
        #[Autowire('%env(bool:CSP_ENFORCE)%')]
        private readonly bool $enforceCsp = false,
    ) {
    }

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

        $headers->set(
            $this->enforceCsp ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only',
            self::CSP_POLICY,
        );

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
