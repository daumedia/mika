<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Nimmt die CSP-Verstoßmeldungen des Browsers entgegen (`report-uri` der
 * Content-Security-Policy-Report-Only) und schreibt sie ins Log. Öffentlich; verarbeitet
 * keine Eingabe außer Loggen und antwortet immer 204.
 *
 * Nur solange die CSP im Report-Only-Modus läuft. Wird sie scharf geschaltet oder die
 * Verstöße sind behoben, kann dieser Endpunkt wieder weg.
 */
class CspReportController extends AbstractController
{
    #[Route('/csp-report', name: 'app_csp_report', methods: ['POST'])]
    public function report(Request $request, LoggerInterface $logger): Response
    {
        $payload = json_decode($request->getContent(), true);
        $report = \is_array($payload) ? ($payload['csp-report'] ?? $payload) : null;

        if (\is_array($report)) {
            $logger->notice('CSP violation', [
                'directive' => $report['violated-directive'] ?? $report['effective-directive'] ?? null,
                'blocked-uri' => $report['blocked-uri'] ?? null,
                'document-uri' => $report['document-uri'] ?? null,
            ]);
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
