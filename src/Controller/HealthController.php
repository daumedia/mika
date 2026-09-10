<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    /**
     * Liveness-Endpunkt für Coolify und den Docker-HEALTHCHECK.
     *
     * Bewusst ohne Datenbank, Session oder Locale: Die Antwort sagt „der
     * PHP-Prozess nimmt Requests an", nicht „alle Abhängigkeiten sind gesund".
     * Coolify ruft diese Route beim Ausrollen ab; schlägt sie fehl, gilt der
     * frische Container als krank und Coolify rollt zurück.
     */
    #[Route('/health', name: 'app_health', methods: ['GET'])]
    public function check(): Response
    {
        return new Response('ok', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
