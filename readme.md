# SF4 Prototype

## Architecture
https://grafikart.fr/blog/structure-code-symfony

Tentative d'une architecture DDD, Domain Drive Development, dite hexagonale d'un projet sf4

## Points positifs
- Force aux développeurs de mieux compartimenter le projet.
- Centralisation des fonctions importantes pouvant être réutilisées dans des services par les domaines

## Points négatifs
- Le temps de mise en oeuvre par manque d'habitude.
- Les fichiers de config à modifier, notamment pour doctrine

````yaml
# config/packages/doctrine.yaml
doctrine:
    orm:
        mappings:
            {domain}:
                is_bundle: false
                type: annotation
                dir: '%kernel.project_dir%/src/{domain}/Model/Entity'
                prefix: 'App\{domain}\Model\Entity'
                alias: {domain}
````
Cet ajout est à faire pour autant de domaine que le projet possède.

## Techno

### PHP7.4
php7 apporte son lot d'évolutions.
- Typage fort des valeurs de retour des fonction
- Typage fort des paramètres de fonction
- Typage fort des membres de class (php7.4)

Ces évolutions rendent un peu moins souple le développement PHP (lorsque le typage fort est utilisé) 
donc force au développeur de concevoir un logiciel plus cohérent.

### Doctrine 
En ce passant des méthodes fournies (select(), leftjoin(), innerjoin()...)
la visualisation de la requête SQL est bien meilleure tout en gardant la sécurité proposée par Doctrine et son DQL

````SQL
SELECT t
FROM Auth:RefreshToken t -- Auth fait référence à l'alias de doctrine.yaml
WHERE t.token = :token
````

___astuce___: Par défaut le bundle Maker crée les entités et repository dans src/{Entity,Repository}. 
Les modifications d'architecture du projet nécessite une modification du fichier ``config/packages/dev/maker.yaml`` (ou le créer)

```yaml
# config/packages/dev/maker.yaml
maker:
    root_namespace: 'App\{domain}\Model\'
`````

Cette config permet de créer les dossiers Entity et Repository dans le dossier ``App\{domain}\Model\`` 
Pour voir toute la config du bundle :
``./bin/console debug:config MakerBundle``

### Json Web Token
Lien utile : https://smoqadam.me/posts/how-to-authenticate-user-in-symfony-5-by-jwt

Peut être facilement adaptable dans le cadre de Qobuz avec le système ``app_id`` et ``user_auth_token`` (bien que kong devrait prendre sa place)

Tentative d'utilisation de Bundle Symfony (LexikJWTAuthenticationBundle ).
Bien trop de configurations pour juste générer et décoder un Token...

La mise en place de ``refresh token`` est relativement simple pour passer par du code maison.

### Elasticsearch
L'installation locale sous Windows est simple:
- Télécharger un ZIP
- Exécuter un programme en admin avec PowerShell (au préalable avoir Java d'installer sur la machine)

Le fait de n'avoir que des index ES non strict permet dans un projet test de ne pas se soucier du mapping des index.

#### FosElasticaBundle
Points négatifs :
- Librairie peu souvent mise à jour
- Utilisation de Elastica (couche d'abstraction). 
- Mise en place complexe pour une utilisation uniquement en lecture

Point positif:
- Intégration de doctrine mais je n'ai pas pu le constater

#### Client PHP ES
Point positif:
- Pas de couche d'abstraction, utilisation de tableau PHP pour faire les requêtes

Point négatif:
- Nécessite de connaitre le format des requêtes ES

#### Extension navigateur
Les extensions dédiées à ES facilitent la visualisation des données dans ES

### Nelmio/OpenAPI
NelmioApiDocBundle est un bundle de documentation API utilisant swagger-php.

swagger-php est une librairie PHP permettant de générer des fichiers au format OpenAPI.

NelmioApiDocBundle propose entre autre l'affichage facilement

Points positifs: 
- Mise en place simple et rapide
- Pas de développement front
- Compatible OpenAPI 3.0
- Possibilité de ne pas afficher des routes en fonction d'un modèle (regexp)

Points négatifs:
- Les annotations prennent beaucoup de place dans les fichiers PHP (voir l'impact avec les annotations PHP8)
- L'utilisation des annotations n'est pas instinctive et est compliquée à prendre en main quand on ne connait pas la spécification OpenAPI

### DTO
DTO, ou Data Transform Object, est un design pattern qui permet de traiter/transformer un objet avant son utilisation (de ce que j'ai compris).

Symfony permet son implémentation de manière simple.
https://symfony.com/doc/current/controller/argument_value_resolver.html

### Message Asynchrone
Le package symfony-messenger permet l'envoi de messages asynchrones.
Prend en charge les messages via doctrine (fonctionne avec SQLite) et ampq (exemple RabbitMQ)

Points positifs:
- L'utilisation du Serializer aide grandement pour la sérialisation en JSON (ou autre) 
en vue d'une consommation d'un message par une autre technologie que Symfony
- Le découplage de consommateur/émetteur

## Constats
### Les entités mixtes
Une entité mixte est un objet dont les sources de données sont différentes (SQL, ES, Redis...).

Je ne suis pas convaincu par la structure mise en place dans ce mini projet
1. Les sérialisations et déserialisations à répétition sont compliquées et alourdissent le processus. 
2. Il faudrait avoir une librairie/méthode qui permette de convertir un array en object (la Reflection ?)
3. Beaucoup d'injections de services variés, on se perd vite entre la récupération des données et son exposition

### JWT + Symfony
- Couple intéressant.
- JWT Simple à mettre en place
- Utilisation personnalisée de Guard Symfony

### Architecture DDD
Concept très intéressant pour une application web / micro-service prenant en charge plusieurs domaines (minimum 3), 
car avec un seul domaine, le DDD perd tout son intérêt

Je pense notamment à l'API Qobuz qui pourrait être divisée en 3 microservices :
- Catalog
- User
- Magazine

On pourrait imaginer pour chaque microservice une url public de la sorte :
- https://api.qobuz.com/catalog/ ou https://qobuz.com/api/catalog/ ou https://qobuz.com/api/v3/catalog (versionning sujet sensible ?)
- https://api.qobuz.com/user/
- https://api.qobuz.com/magazine/

___Note___: Si on oublie la partie authentification qui sera déportée (Kong?) ou centralisée à travers un Bundle symfony 
et en oubliant également la partie de la gestion des droits des produits et pistes.

#### Catalog
Partie majeur de l'A.P.I. qui contiendrait les appels concernant le catalogue.

Les domaines de cette application seraient :
- Track
- Album 
- Label
- Playlist ? (doute car très "user centric")
- Search (Algolia)
- Autres

#### User 
Partie centrale de l'API Qobuz d'un point de vue métier.

Celle-ci contiendrait:
- Paiement/souscription (KPX/Apple)
- Favoris
- Purchase
- userLibrary (nouvelle génération ?)

#### Magazine
Partie qui apporte une plus-value non négligeable aux applications Qobuz. On y trouverait les domaines suivants :
- Article
- Focus
- Mise en avant (Featured [ Terme à bannir svp :) ])

## Idées
- Trouver le moyen de déporter les configurations de chaque domaine
- Trouver le moyen de ne pas dupliquer les mappings Doctrine
- Utiliser OpenAPI pour générer des DTO
- Utiliser OpenAPI pour contrôler les sorties API
