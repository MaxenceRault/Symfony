# Rault Maxence

Voici le Projet Ecommerce qu'il fallait rendre , j'ai utilisé symfony 5.10.5

Si vous avez un problème durant le lancement , n'hésitez pas a me contacter sur:
raultmaxence05@gmail.com
## Procédure d'installation

1. clone le repo
2. modifier le `.env` avec vos accès à la base de données
3. installer les dépendances manquantes : `composer update`
4. créer la base de données : `php bin/console doctrine:database:create`
5. exécuter les migrations : `php bin/console doctrine:migrations:migrate`
6. lancer le serveur symfony : `symfony server:start`

7. (optionnel) Commande pour vider le cache du projet : `php bin/console cache:clear`

