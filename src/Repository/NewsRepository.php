<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<News>
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    /**
     * @return News[]
     */
    public function findAllPublished(): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.publishedAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('n.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Einzelbeitrag per Slug — nur wenn bereits veröffentlicht (published_at <= now).
     * Verhindert, dass zukünftig datierte („geplante") Beiträge per Direkt-URL sichtbar
     * sind (BF-08).
     */
    public function findOnePublishedBySlug(string $slug): ?News
    {
        return $this->createQueryBuilder('n')
            ->where('n.slug = :slug')
            ->andWhere('n.publishedAt <= :now')
            ->setParameter('slug', $slug)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return News[]
     */
    public function findLatest(int $limit = 3): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.publishedAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('n.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
