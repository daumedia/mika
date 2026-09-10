<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NewsController extends AbstractController
{
    #[Route('/lb/news', name: 'app_news', defaults: ['_locale' => 'lb'])]
    #[Route('/en/news', name: 'app_news_en', defaults: ['_locale' => 'en'])]
    public function index(NewsRepository $newsRepository): Response
    {
        return $this->render('news/index.html.twig', [
            'articles' => $newsRepository->findAllPublished(),
        ]);
    }

    #[Route('/lb/news/{slug}', name: 'app_news_show', defaults: ['_locale' => 'lb'])]
    #[Route('/en/news/{slug}', name: 'app_news_show_en', defaults: ['_locale' => 'en'])]
    public function show(string $slug, NewsRepository $newsRepository): Response
    {
        $article = $newsRepository->findOnePublishedBySlug($slug);

        if (!$article) {
            throw $this->createNotFoundException();
        }

        return $this->render('news/show.html.twig', [
            'article' => $article,
        ]);
    }
}
