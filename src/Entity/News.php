<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'Dëse Slug ass schonn a Gebrauch.')]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titleLb = null;

    #[ORM\Column(length: 255)]
    private ?string $titleEn = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $summaryLb = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $summaryEn = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contentLb = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contentEn = null;

    #[ORM\Column(length: 100)]
    private ?string $category = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $publishedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(string $locale = 'lb'): ?string
    {
        return $locale === 'en' ? $this->titleEn : $this->titleLb;
    }

    public function getSummary(string $locale = 'lb'): ?string
    {
        return $locale === 'en' ? $this->summaryEn : $this->summaryLb;
    }

    public function getContent(string $locale = 'lb'): ?string
    {
        return $locale === 'en' ? $this->contentEn : $this->contentLb;
    }

    public function getTitleLb(): ?string
    {
        return $this->titleLb;
    }

    public function setTitleLb(string $titleLb): static
    {
        $this->titleLb = $titleLb;
        return $this;
    }

    public function getTitleEn(): ?string
    {
        return $this->titleEn;
    }

    public function setTitleEn(string $titleEn): static
    {
        $this->titleEn = $titleEn;
        return $this;
    }

    public function getSummaryLb(): ?string
    {
        return $this->summaryLb;
    }

    public function setSummaryLb(string $summaryLb): static
    {
        $this->summaryLb = $summaryLb;
        return $this;
    }

    public function getSummaryEn(): ?string
    {
        return $this->summaryEn;
    }

    public function setSummaryEn(string $summaryEn): static
    {
        $this->summaryEn = $summaryEn;
        return $this;
    }

    public function getContentLb(): ?string
    {
        return $this->contentLb;
    }

    public function setContentLb(string $contentLb): static
    {
        $this->contentLb = $contentLb;
        return $this;
    }

    public function getContentEn(): ?string
    {
        return $this->contentEn;
    }

    public function setContentEn(string $contentEn): static
    {
        $this->contentEn = $contentEn;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }
}
