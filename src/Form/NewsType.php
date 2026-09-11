<?php

namespace App\Form;

use App\Entity\News;
use App\Enum\NewsCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titleLb', TextType::class, [
                'label' => 'Titel (LB)',
                'constraints' => [new NotBlank()],
            ])
            ->add('titleEn', TextType::class, [
                'label' => 'Title (EN)',
                'constraints' => [new NotBlank()],
            ])
            ->add('summaryLb', TextareaType::class, [
                'label' => 'Resumé (LB)',
                'constraints' => [new NotBlank()],
                'attr' => ['rows' => 3],
            ])
            ->add('summaryEn', TextareaType::class, [
                'label' => 'Summary (EN)',
                'constraints' => [new NotBlank()],
                'attr' => ['rows' => 3],
            ])
            ->add('contentLb', TextareaType::class, [
                'label' => 'Inhalt (LB)',
                'constraints' => [new NotBlank()],
                'attr' => ['rows' => 8],
            ])
            ->add('contentEn', TextareaType::class, [
                'label' => 'Content (EN)',
                'constraints' => [new NotBlank()],
                'attr' => ['rows' => 8],
            ])
            ->add('category', EnumType::class, [
                'label' => 'Kategorie',
                'class' => NewsCategory::class,
                'choice_label' => fn (NewsCategory $c): string => $c->label(),
                'constraints' => [new NotBlank()],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug (URL — always English)',
                'constraints' => [new NotBlank()],
                'attr' => ['placeholder' => 'e.g. my-article-title'],
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Publizéiert um',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new NotBlank()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
