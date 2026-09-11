<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Setzt Sicherheits-Header auf jede Antwort.
 *
 * Bewusst OHNE erzwingende Content-Security-Policy: die Seite fährt eine doppelte
 * Asset-Pipeline (Encore + AssetMapper mit Inline-importmap-Script) und ein
 * Inline-<script> im News-Formular (BF-07). Eine strikte CSP bräuchte dort Nonces
 * und würde die Live-Seite sonst zerlegen. Die CSP kommt als eigener Schritt,
 * gekoppelt an die Bereinigung der Asset-Pipeline (siehe docs/betrieb.md).
 */
class SecurityHeadersSubscriber implements EventSubscriberInterface
{
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
