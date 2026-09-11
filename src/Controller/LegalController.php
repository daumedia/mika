<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Rechtstexte — Impressum und Datenschutzerklärung.
 *
 * Rein darstellend, kein Datenbankzugriff. Je Seite zwei Locale-Routen nach dem Muster
 * der übrigen öffentlichen Controller (Home/News). Öffentlich, da außerhalb von `^/admin`
 * (keine `#[IsGranted]`-Regel nötig).
 */
class LegalController extends AbstractController
{
    #[Route('/lb/impressum', name: 'app_impressum', defaults: ['_locale' => 'lb'])]
    #[Route('/en/impressum', name: 'app_impressum_en', defaults: ['_locale' => 'en'])]
    public function impressum(): Response
    {
        return $this->render('legal/impressum.html.twig');
    }

    #[Route('/lb/datenschutz', name: 'app_datenschutz', defaults: ['_locale' => 'lb'])]
    #[Route('/en/datenschutz', name: 'app_datenschutz_en', defaults: ['_locale' => 'en'])]
    public function datenschutz(): Response
    {
        return $this->render('legal/datenschutz.html.twig');
    }
}
