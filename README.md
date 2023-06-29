# errecade_alexandre_projet_7
APi pour l'utilisation de BILMO

## Table des matières
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contribuer](#contribuer)
- [Licence](#licence)

## Installation

1. Clonez le dépôt : `git clone https://github.com/ERRECADE/errecade_alexandre_projet_7`
2. Installez les dépendances : `composer install`
3. Lancer la configuration de la BDD :  `php bin/console make:migration`

## Utilisation

1. Lancez votre outil POSTMAN 
2. Connecter vous avec un client déjà dans la base de donnés avec la route  : `http://votre_chemain/api/login_check`
3. appelez les route que vous souhaitez utilisé en mettant votre token dans chacune 
4. Attention pour les routes PUT et POST (create), pensez a intégrer votre tableaux de donné jSON : {
  "name": "alex",
  "prenom": "test2",
  "email": "test11@test.fr"
}
