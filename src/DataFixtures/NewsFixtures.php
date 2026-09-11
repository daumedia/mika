<?php

namespace App\DataFixtures;

use App\Entity\News;
use App\Enum\NewsCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NewsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $articles = [
            [
                'titleLb' => 'Nei Spillplaz fir eis Kanner zu Kayl',
                'titleEn' => 'New Playground for Our Children in Kayl',
                'summaryLb' => 'Eng modern Spillplaz mat inklusiven Elementer gëtt um Terrain hannert der Schoul gebaut.',
                'summaryEn' => 'A modern playground with inclusive elements will be built on the grounds behind the school.',
                'contentLb' => 'Ech freeë mech Iech matzedeelen, datt eis Gemeng eng nei Spillplaz fir eis Kanner baue wäert. D\'Spillplaz wäert mat inklusiven Elementer ausgestatt sinn, sou datt all Kand ka matspillen. De Projet ass an Zesummenaarbecht mat den Elteren an den Enseignanten entwéckelt ginn. D\'Aarbechten fänken am Fréijoer un a sollen bis den Hierscht fäerdeg sinn.',
                'contentEn' => 'I am pleased to announce that our municipality will build a new playground for our children. The playground will be equipped with inclusive elements so that every child can participate. The project was developed in collaboration with parents and teachers. Construction will begin in spring and should be completed by autumn.',
                'category' => NewsCategory::Youth,
                'slug' => 'nei-spillplaz-kayl',
                'publishedAt' => '2026-03-15',
            ],
            [
                'titleLb' => 'Bezuelbare Wunnraum: Eist Engagement',
                'titleEn' => 'Affordable Housing: Our Commitment',
                'summaryLb' => 'D\'DP Kayl-Teiteng setzt sech fir méi bezuelbare Wunnraum an eiser Gemeng an.',
                'summaryEn' => 'DP Kayl-Teiteng is committed to more affordable housing in our municipality.',
                'contentLb' => 'D\'Wunnengsfrô ass eng vun de gréissten Erausfuerderungen an eiser Gemeng. Mir setzen eis dofir an, datt jonk Familljen an eeler Leit bezuelbare Wunnraum fannen. Eise Plang gesäit vir, datt op ëffentlechem Terrain nei Wunnengen entstinn, déi zu engem faire Präis verlount ginn. Mir wëllen och d\'Renovatioun vun eidele Gebaier fërderen.',
                'contentEn' => 'The housing question is one of the biggest challenges in our municipality. We are committed to ensuring that young families and elderly people find affordable housing. Our plan provides for the construction of new homes on public land that will be rented at a fair price. We also want to promote the renovation of empty buildings.',
                'category' => NewsCategory::Housing,
                'slug' => 'bezuelbare-wunnraum',
                'publishedAt' => '2026-03-10',
            ],
            [
                'titleLb' => 'Kulturzentrum Teiteng gëtt renovéiert',
                'titleEn' => 'Teiteng Cultural Centre to be Renovated',
                'summaryLb' => 'D\'Kulturzentrum zu Teiteng kritt eng komplett Renovatioun mat neie Raim fir Veräiner.',
                'summaryEn' => 'The cultural centre in Teiteng will receive a complete renovation with new spaces for associations.',
                'contentLb' => 'D\'Kulturzentrum zu Teiteng ass e wichtege Bestanddeel vun eisem Gemeinschaftsliewen. No Joerzéngten intensiver Notzung brauch et eng Renovatioun. De Projet gesäit nei Prouweraim fir Museksveräiner, e modernen Theatersall an eng Bibliothéik vir. Mir wëllen, datt d\'Kulturzentrum en Treffpunkt fir all Generatiounen gëtt.',
                'contentEn' => 'The cultural centre in Teiteng is an important part of our community life. After decades of intensive use, it needs renovation. The project includes new rehearsal rooms for music associations, a modern theatre hall and a library. We want the cultural centre to become a meeting place for all generations.',
                'category' => NewsCategory::Culture,
                'slug' => 'kulturzentrum-teiteng',
                'publishedAt' => '2026-03-05',
            ],
        ];

        foreach ($articles as $data) {
            $news = new News();
            $news->setTitleLb($data['titleLb']);
            $news->setTitleEn($data['titleEn']);
            $news->setSummaryLb($data['summaryLb']);
            $news->setSummaryEn($data['summaryEn']);
            $news->setContentLb($data['contentLb']);
            $news->setContentEn($data['contentEn']);
            $news->setCategory($data['category']);
            $news->setSlug($data['slug']);
            $news->setPublishedAt(new \DateTimeImmutable($data['publishedAt']));

            $manager->persist($news);
        }

        $manager->flush();
    }
}
