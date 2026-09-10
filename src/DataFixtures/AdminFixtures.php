<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new Admin();
        $admin->setUsername('mika');

        // BUG-02: kein hartkodiertes Passwort mehr. Aus ENV ADMIN_PASSWORD lesen,
        // sonst ein zufaelliges erzeugen und einmalig ausgeben (nur Dev-Fixture).
        $plain = $_ENV['ADMIN_PASSWORD'] ?? null;
        $generated = null === $plain;
        if ($generated) {
            $plain = bin2hex(random_bytes(8));
        }
        $admin->setPassword($this->hasher->hashPassword($admin, $plain));

        $manager->persist($admin);
        $manager->flush();

        if ($generated) {
            fwrite(\STDOUT, sprintf("\n[Fixtures] Admin 'mika' angelegt. Generiertes Passwort: %s\n", $plain));
        }
    }
}
