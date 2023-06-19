<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Client;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use DateTime;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $client = new Client;
            $client->setName('Nom ' . $i);
            $client->setEmail('client.' . $i .'@test.fr');
            $client->setPassword($this->userPasswordHasher->hashPassword($client, "test1234"));
            $client->setRoles(["ROLE_ADMIN"]);;
            $client->setActif('1');
            $client->setIsDeleted('0');
            $client->setCreatedAt(new DateTime);
            $manager->persist($client);
        }

        for ($i = 0; $i < 5; $i++) {
            $utilisateur = new Utilisateur;
            $utilisateur->setName('Nom ' . $i);
            $utilisateur->setPrenom('Nom ' . $i);
            $utilisateur->setEmail('utilisateur.' . $i .'@test.fr');
            $utilisateur->setClient($client);
            $utilisateur->setActif('1');
            $utilisateur->setIsDeleted('0');
            $utilisateur->setCreatedAt(new DateTime);
            $manager->persist($utilisateur);
        }

        for ($i = 0; $i < 20; $i++) {
            $produit = new Produit;
            $produit->setName('Name ' . $i);
            $produit->setDescription('voila une descriptions');
            $produit->setPrix($i);
            $produit->setReference('ref' . $i);
            $manager->persist($produit);
        }

        $manager->flush();
    }
}
