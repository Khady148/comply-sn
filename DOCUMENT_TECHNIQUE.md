\# DOCUMENT TECHNIQUE



\# COMPLY-SN



\## Plateforme de gestion de la conformité réglementaire



\---



\# Table des matières



1\. Introduction

2\. Présentation de COMPLY-SN

3\. Contexte et problématique

4\. Objectifs du projet

5\. Architecture générale

6\. Technologies et outils utilisés

7\. Architecture de la base de données

8\. Description des tables

9\. Relations entre les tables

10\. Fonctionnement des modules

11\. Sécurité de l'application

12\. Tests et validation

13\. Tests d'intégration

14\. Gestion des erreurs

15\. Git et GitHub

16\. Installation et configuration

17\. Déploiement

18\. Maintenance

19\. Limites du projet

20\. Perspectives d'amélioration

21\. Conclusion générale

22\. Annexes techniques



\---



\# 1. Introduction



La conformité réglementaire constitue un enjeu important pour les organisations qui doivent respecter différentes lois, réglementations, normes et exigences internes.



Le suivi de ces exigences peut devenir complexe lorsque les informations sont dispersées dans plusieurs fichiers ou outils. Il devient donc nécessaire de disposer d'une solution permettant de centraliser les réglementations applicables, les obligations associées, les contrôles effectués, les actions correctives et les preuves de conformité.



C'est dans ce contexte que s'inscrit le projet \*\*COMPLY-SN\*\*, une application web destinée à faciliter la gestion et le suivi de la conformité réglementaire.



L'application permet de centraliser les informations de conformité dans une base de données et fournit une interface permettant aux utilisateurs autorisés de consulter, ajouter, modifier et suivre les différents éléments du système.



\---



\# 2. Présentation de COMPLY-SN



\## 2.1 Définition



\*\*COMPLY-SN\*\* est une application web de gestion de la conformité réglementaire.



Elle permet à une organisation de structurer et de suivre les différents éléments nécessaires au pilotage de sa conformité.



L'application repose principalement sur PHP et MySQL et fonctionne actuellement dans un environnement local basé sur XAMPP.



\## 2.2 Fonctionnalités principales



COMPLY-SN comprend plusieurs modules permettant de gérer :



\* les utilisateurs ;

\* les domaines de conformité ;

\* les réglementations ;

\* les obligations ;

\* les contrôles ;

\* les actions correctives ;

\* les preuves ;

\* le journal d'audit.



L'application dispose également d'un tableau de bord permettant de visualiser les principales statistiques du système.



\## 2.3 Organisation fonctionnelle



Le fonctionnement général de l'application suit une logique de conformité :



\*\*Domaine → Réglementation → Obligation → Contrôle → Action corrective / Preuve\*\*



Cette organisation permet de suivre le cycle de traitement d'une exigence réglementaire.



\---



\# 3. Contexte et problématique



\## 3.1 Contexte



Les organisations sont soumises à différentes exigences réglementaires qui doivent être identifiées, suivies et contrôlées.



Le suivi manuel de ces exigences peut entraîner plusieurs difficultés :



\* dispersion des informations ;

\* difficulté à identifier les obligations applicables ;

\* manque de visibilité sur l'état des contrôles ;

\* difficulté à suivre les actions correctives ;

\* perte ou mauvaise organisation des preuves ;

\* difficulté à identifier les responsables ;

\* absence de traçabilité des opérations effectuées.



\## 3.2 Problématique



La problématique du projet peut être formulée ainsi :



\*\*Comment mettre en place une application permettant de centraliser, organiser et suivre les exigences de conformité réglementaire tout en assurant la traçabilité des opérations réalisées par les utilisateurs ?\*\*



COMPLY-SN apporte une réponse à cette problématique en proposant une plateforme permettant de centraliser les informations et de suivre les différentes étapes du processus de conformité.



\---



\# 4. Objectifs du projet



\## 4.1 Objectif général



L'objectif général de COMPLY-SN est de développer une application web permettant de \*\*centraliser et faciliter la gestion de la conformité réglementaire\*\* au sein d'une organisation.



\## 4.2 Objectifs spécifiques



Le projet vise notamment à :



\* gérer les domaines de conformité ;

\* gérer les réglementations ;

\* gérer les obligations réglementaires ;

\* effectuer et suivre les contrôles ;

\* gérer les actions correctives ;

\* conserver les preuves de conformité ;

\* gérer les utilisateurs ;

\* assurer la traçabilité des opérations ;

\* fournir un tableau de bord de suivi.



\## 4.3 Résultat attendu



COMPLY-SN doit permettre à une organisation de disposer d'un outil centralisé pour :



\* organiser ses exigences réglementaires ;

\* suivre ses obligations ;

\* réaliser ses contrôles ;

\* gérer les actions correctives ;

\* conserver les preuves ;

\* identifier les responsables ;

\* suivre les utilisateurs ;

\* consulter les statistiques ;

\* assurer une meilleure traçabilité des opérations.



\---



\# 5. Architecture générale



\## 5.1 Architecture de l'application



COMPLY-SN utilise une architecture client-serveur.



L'utilisateur accède à l'application à partir d'un navigateur web.



Les requêtes sont traitées par le serveur Apache de XAMPP. L'application PHP communique ensuite avec MySQL afin de récupérer ou enregistrer les données.



```text

┌──────────────────────────┐

│       UTILISATEUR        │

│      Navigateur Web      │

└────────────┬─────────────┘

&#x20;            │

&#x20;            │ HTTP

&#x20;            ▼

┌──────────────────────────┐

│      SERVEUR WEB         │

│       Apache/XAMPP       │

└────────────┬─────────────┘

&#x20;            │

&#x20;            │ PHP

&#x20;            ▼

┌──────────────────────────┐

│      APPLICATION         │

│       COMPLY-SN          │

│                          │

│ Authentification         │

│ Dashboard                │

│ Domaines                 │

│ Réglementations          │

│ Obligations              │

│ Contrôles                │

│ Actions correctives      │

│ Preuves                  │

│ Utilisateurs             │

│ Audit                    │

└────────────┬─────────────┘

&#x20;            │

&#x20;            │ PDO / SQL

&#x20;            ▼

┌──────────────────────────┐

│          MySQL           │

│        comply\_sn         │

└──────────────────────────┘

```



\## 5.2 Arborescence du projet



L'organisation du projet comprend notamment :



```text

comply-sn/

│

├── auth/

├── config/

├── controls/

├── corrective\_actions/

├── domains/

├── evidences/

├── obligations/

├── regulations/

├── users/

├── uploads/

│

├── dashboard.php

├── README.md

└── .gitignore

```



Cette organisation permet de séparer les différents modules fonctionnels et facilite la maintenance.



\---



\# 6. Technologies et outils utilisés



\## 6.1 PHP



PHP est utilisé pour développer la logique serveur de l'application.



Il permet notamment de :



\* traiter les formulaires ;

\* communiquer avec MySQL ;

\* gérer les sessions ;

\* effectuer les opérations CRUD ;

\* gérer l'authentification ;

\* contrôler les accès.



\## 6.2 HTML



HTML est utilisé pour structurer les différentes pages de l'application.



\## 6.3 CSS



CSS permet de mettre en forme les interfaces et d'améliorer l'expérience utilisateur.



\## 6.4 JavaScript



JavaScript est utilisé pour certaines validations côté client et pour améliorer l'interactivité des formulaires.



\## 6.5 SQL



SQL est utilisé pour communiquer avec la base de données MySQL.



\## 6.6 MySQL



MySQL constitue le système de gestion de base de données utilisé par COMPLY-SN.



La base utilisée est :



```text

comply\_sn

```



\## 6.7 XAMPP



XAMPP fournit l'environnement local permettant d'exécuter :



\* Apache ;

\* PHP ;

\* MySQL.



\## 6.8 phpMyAdmin



phpMyAdmin est utilisé pour administrer la base de données, notamment pour :



\* créer les tables ;

\* consulter les données ;

\* modifier les structures ;

\* effectuer des requêtes SQL ;

\* vérifier les relations entre les données.



\## 6.9 Git et GitHub



Git est utilisé pour le versionnement du code.



GitHub est utilisé comme dépôt distant afin de :



\* sauvegarder le projet ;

\* suivre son évolution ;

\* faciliter la collaboration ;

\* permettre l'accès au code à distance.



\---



\# 7. Architecture de la base de données



La base de données `comply\_sn` contient huit tables principales :



```text

users

domains

regulations

obligations

controls

corrective\_actions

evidences

audit\_logs

```



La structure suit le modèle fonctionnel suivant :



```text

DOMAINS

&#x20;  │

&#x20;  ▼

REGULATIONS

&#x20;  │

&#x20;  ▼

OBLIGATIONS

&#x20;  │

&#x20;  ▼

CONTROLS

&#x20;  │

&#x20;  ├──────────► EVIDENCES

&#x20;  │

&#x20;  └──────────► CORRECTIVE\_ACTIONS

```



La table `users` intervient dans plusieurs modules.



\---



\# 8. Description des tables



\## 8.1 Table `users`



Cette table contient les informations relatives aux utilisateurs.



| Colonne    | Type         | Description           |

| ---------- | ------------ | --------------------- |

| id         | int(11)      | Identifiant unique    |

| full\_name  | varchar(100) | Nom complet           |

| email      | varchar(150) | Adresse e-mail        |

| password   | varchar(255) | Mot de passe haché    |

| role       | enum         | Rôle de l'utilisateur |

| created\_at | timestamp    | Date de création      |



Les rôles disponibles sont :



\* `admin` ;

\* `advanced` ;

\* `standard`.



\---



\## 8.2 Table `domains`



Elle permet de gérer les domaines de conformité.



| Colonne     | Type         | Description      |

| ----------- | ------------ | ---------------- |

| id          | int(11)      | Identifiant      |

| name        | varchar(100) | Nom du domaine   |

| description | text         | Description      |

| created\_at  | timestamp    | Date de création |



\---



\## 8.3 Table `regulations`



Cette table contient les réglementations.



| Colonne        | Type         | Description              |

| -------------- | ------------ | ------------------------ |

| id             | int(11)      | Identifiant              |

| domain\_id      | int(11)      | Domaine associé          |

| title          | varchar(200) | Titre                    |

| reference      | varchar(150) | Référence                |

| description    | text         | Description              |

| effective\_date | date         | Date d'entrée en vigueur |

| created\_at     | timestamp    | Date de création         |



\---



\## 8.4 Table `obligations`



Cette table constitue un élément central de COMPLY-SN.



| Colonne             | Type         | Description             |

| ------------------- | ------------ | ----------------------- |

| id                  | int(11)      | Identifiant             |

| regulation\_id       | int(11)      | Réglementation associée |

| title               | varchar(200) | Titre                   |

| description         | text         | Description             |

| frequency           | varchar(50)  | Fréquence               |

| due\_date            | date         | Date limite             |

| criticality         | enum         | Criticité               |

| status              | enum         | Statut                  |

| responsible\_user\_id | int(11)      | Responsable             |

| created\_at          | timestamp    | Date de création        |



Criticités :



\* Faible ;

\* Moyenne ;

\* Élevée ;

\* Critique.



Statuts :



\* Conforme ;

\* Non conforme ;

\* En cours ;

\* À vérifier.



\---



\## 8.5 Table `controls`



Cette table permet de gérer les contrôles effectués sur les obligations.



| Colonne       | Type      | Description                            |

| ------------- | --------- | -------------------------------------- |

| id            | int(11)   | Identifiant                            |

| obligation\_id | int(11)   | Obligation contrôlée                   |

| controlled\_by | int(11)   | Utilisateur ayant effectué le contrôle |

| control\_date  | date      | Date                                   |

| result        | enum      | Résultat                               |

| comment       | text      | Commentaire                            |

| created\_at    | timestamp | Date de création                       |



\---



\## 8.6 Table `corrective\_actions`



Cette table permet de gérer les actions correctives.



| Colonne             | Type         | Description       |

| ------------------- | ------------ | ----------------- |

| id                  | int(11)      | Identifiant       |

| control\_id          | int(11)      | Contrôle concerné |

| title               | varchar(200) | Titre             |

| description         | text         | Description       |

| responsible\_user\_id | int(11)      | Responsable       |

| due\_date            | date         | Date limite       |

| status              | enum         | Statut            |

| created\_at          | timestamp    | Date de création  |



\---



\## 8.7 Table `evidences`



Cette table permet de gérer les preuves associées aux contrôles.



| Colonne     | Type         | Description      |

| ----------- | ------------ | ---------------- |

| id          | int(11)      | Identifiant      |

| control\_id  | int(11)      | Contrôle associé |

| file\_name   | varchar(255) | Nom du fichier   |

| file\_path   | varchar(500) | Chemin           |

| uploaded\_by | int(11)      | Utilisateur      |

| uploaded\_at | timestamp    | Date d'envoi     |



\---



\## 8.8 Table `audit\_logs`



Cette table assure la traçabilité des opérations.



| Colonne    | Type         | Description             |

| ---------- | ------------ | ----------------------- |

| id         | int(11)      | Identifiant             |

| user\_id    | int(11)      | Utilisateur             |

| action     | varchar(100) | Action réalisée         |

| table\_name | varchar(100) | Table concernée         |

| record\_id  | int(11)      | Enregistrement concerné |

| ip\_address | varchar(45)  | Adresse IP              |

| created\_at | timestamp    | Date et heure           |



\---



\# 9. Relations entre les tables



Les principales relations fonctionnelles sont :



```text

domains

&#x20;  │

&#x20;  │ 1:N

&#x20;  ▼

regulations

&#x20;  │

&#x20;  │ 1:N

&#x20;  ▼

obligations

&#x20;  │

&#x20;  │ 1:N

&#x20;  ▼

controls

&#x20;  │

&#x20;  ├──────► evidences

&#x20;  │

&#x20;  └──────► corrective\_actions

```



La table `users` est utilisée dans plusieurs relations :



```text

users

&#x20;│

&#x20;├──► obligations

&#x20;│

&#x20;├──► controls

&#x20;│

&#x20;├──► corrective\_actions

&#x20;│

&#x20;├──► evidences

&#x20;│

&#x20;└──► audit\_logs

```



Cette organisation permet de relier les différents éléments du processus de conformité.



\---



\# 10. Fonctionnement des modules



\## 10.1 Domaines



Le module permet de créer, consulter, modifier et supprimer les domaines de conformité.



\## 10.2 Réglementations



Il permet d'enregistrer les réglementations et de les associer aux domaines concernés.



\## 10.3 Obligations



Il permet de gérer les obligations réglementaires et leurs informations de suivi.



Une obligation peut être associée à :



\* une réglementation ;

\* un responsable ;

\* une fréquence ;

\* une date limite ;

\* une criticité ;

\* un statut.



\## 10.4 Contrôles



Le module permet d'effectuer des contrôles sur les obligations.



Chaque contrôle possède :



\* une obligation ;

\* un responsable ;

\* une date ;

\* un résultat ;

\* un commentaire.



\## 10.5 Actions correctives



Lorsqu'un contrôle révèle une situation nécessitant une correction, une action corrective peut être créée.



Elle peut être affectée à un responsable et suivie jusqu'à sa résolution.



\## 10.6 Preuves



Le module permet d'associer des documents justificatifs aux contrôles.



\## 10.7 Utilisateurs



Le module permet de gérer les comptes utilisateurs et leurs rôles.



\## 10.8 Audit



Le module d'audit conserve les informations relatives à certaines opérations réalisées dans l'application.



\---



\# 11. Sécurité de l'application



\## 11.1 Authentification



L'application dispose d'un système d'authentification permettant de contrôler l'accès aux fonctionnalités protégées.



Un utilisateur doit fournir ses identifiants avant d'accéder aux pages protégées.



\## 11.2 Sessions PHP



Les sessions PHP permettent de conserver l'identité de l'utilisateur connecté.



L'identifiant de l'utilisateur peut ensuite être utilisé pour associer les opérations à son compte.



\## 11.3 Hachage des mots de passe



Les mots de passe ne sont pas stockés en clair.



Les valeurs présentes dans la base utilisent un format de hachage bcrypt :



```text

$2y$10$...

```



Cette méthode permet de protéger les mots de passe en cas d'accès direct aux données de la table `users`.



\## 11.4 Contrôle des rôles



Les utilisateurs possèdent un rôle :



```text

admin

advanced

standard

```



Ces rôles permettent de différencier les niveaux d'accès.



\## 11.5 Requêtes préparées



PDO est utilisé pour communiquer avec MySQL.



Les données utilisateur sont traitées avec des requêtes préparées afin de réduire les risques d'injection SQL.



Exemple :



```php

$stmt = $pdo->prepare("

&#x20;   SELECT \*

&#x20;   FROM obligations

&#x20;   WHERE id = :id

");



$stmt->execute(\[

&#x20;   ":id" => $id

]);

```



\## 11.6 Validation des données



Les données reçues par les formulaires sont contrôlées côté serveur.



Des validations peuvent notamment vérifier :



\* les champs obligatoires ;

\* les longueurs ;

\* les identifiants ;

\* les dates ;

\* les valeurs autorisées.



\## 11.7 Échappement des données



`htmlspecialchars()` est utilisé lors de l'affichage de données provenant de sources externes ou de la base de données.



Exemple :



```php

<?= htmlspecialchars($obligation\["title"]) ?>

```



Cela contribue à limiter les risques liés à l'injection de contenu HTML ou JavaScript.



\## 11.8 Journal d'audit



Le journal `audit\_logs` permet de conserver une trace des opérations importantes.



\## 11.9 Protection des informations sensibles



Les informations de connexion à MySQL ne doivent pas être publiées sur GitHub.



Le fichier de configuration contenant les identifiants doit donc être protégé par `.gitignore` ou remplacé par une configuration sécurisée adaptée à l'environnement.



\## 11.10 Sécurité des fichiers



Les fichiers déposés comme preuves doivent faire l'objet de contrôles appropriés.



Pour un environnement de production, il est recommandé de renforcer :



\* les extensions autorisées ;

\* la taille des fichiers ;

\* le type MIME ;

\* les permissions du répertoire `uploads`.



\---



\# 12. Tests et validation



Les tests permettent de vérifier le bon fonctionnement de l'application.



\## 12.1 Tests d'authentification



Les scénarios testés comprennent :



| Test                        | Résultat attendu     |

| --------------------------- | -------------------- |

| Identifiants corrects       | Connexion            |

| Mot de passe incorrect      | Refus                |

| E-mail inexistant           | Refus                |

| Accès sans authentification | Redirection          |

| Déconnexion                 | Fermeture de session |



\## 12.2 Tests des domaines



Les opérations testées :



\* création ;

\* affichage ;

\* modification ;

\* suppression.



\## 12.3 Tests des réglementations



Les opérations testées :



\* création ;

\* association à un domaine ;

\* affichage ;

\* modification ;

\* suppression.



\## 12.4 Tests des obligations



Les éléments testés :



\* création ;

\* réglementation ;

\* responsable ;

\* criticité ;

\* statut ;

\* date limite ;

\* modification ;

\* suppression.



\## 12.5 Tests des contrôles



Les éléments vérifiés :



\* obligation ;

\* utilisateur ;

\* date ;

\* résultat ;

\* commentaire.



\## 12.6 Tests des actions correctives



Le cycle suivant a été vérifié :



```text

Contrôle

&#x20;  ↓

Identification d'un problème

&#x20;  ↓

Action corrective

&#x20;  ↓

Responsable

&#x20;  ↓

Date limite

&#x20;  ↓

Suivi du statut

```



\## 12.7 Tests des preuves



Les tests portent sur :



\* association au contrôle ;

\* sélection du fichier ;

\* enregistrement du nom ;

\* chemin ;

\* utilisateur ;

\* date.



\## 12.8 Tests des utilisateurs



Les fonctionnalités vérifiées comprennent :



\* création ;

\* consultation ;

\* modification ;

\* suppression ;

\* attribution des rôles.



\## 12.9 Tests du journal d'audit



Le système vérifie l'enregistrement des informations relatives aux actions importantes.



\---



\# 13. Tests d'intégration



Les tests d'intégration permettent de vérifier le fonctionnement de plusieurs modules ensemble.



Un scénario complet est :



```text

Création d'un domaine

&#x20;       ↓

Création d'une réglementation

&#x20;       ↓

Création d'une obligation

&#x20;       ↓

Désignation d'un responsable

&#x20;       ↓

Réalisation d'un contrôle

&#x20;       ↓

Enregistrement du résultat

&#x20;       ↓

Création éventuelle d'une action corrective

&#x20;       ↓

Ajout d'une preuve

&#x20;       ↓

Suivi dans le dashboard

```



Ce scénario permet de vérifier la cohérence entre les différents modules.



\---



\# 14. Gestion des erreurs



Les opérations liées à la base de données sont protégées par des mécanismes de gestion des exceptions.



Exemple :



```php

try {



&#x20;   // Opération sur la base de données



} catch (PDOException $e) {



&#x20;   $errors\[] =

&#x20;       "Une erreur est survenue lors du traitement.";



}

```



Cette approche permet de gérer les erreurs sans exposer inutilement les informations techniques internes à l'utilisateur.



\---



\# 15. Gestion du projet avec Git et GitHub



\## 15.1 Initialisation



Git a été utilisé pour versionner le projet.



Le projet local est situé dans :



```text

C:\\xampp\\htdocs\\comply-sn

```



Le dépôt a été initialisé avec :



```bash

git init

git add .

git commit -m "Initialisation du projet"

```



\## 15.2 Dépôt distant



Le dépôt local a été associé au dépôt GitHub avec le remote :



```text

origin

```



La vérification s'effectue avec :



```bash

git remote -v

```



\## 15.3 Envoi vers GitHub



Le projet a été envoyé avec :



```bash

git push -u origin main

```



\## 15.4 Vérification



L'état du dépôt peut être vérifié avec :



```bash

git status

```



Lorsque toutes les modifications sont enregistrées :



```text

nothing to commit, working tree clean

```



\## 15.5 Cycle de travail



Le cycle recommandé est :



```text

Modification

&#x20;    ↓

Test

&#x20;    ↓

git status

&#x20;    ↓

git add .

&#x20;    ↓

git commit

&#x20;    ↓

git push

```



\## 15.6 Exemples de commits



Des commits peuvent être organisés selon les étapes suivantes :



```text

Initialisation du projet

Ajout de la connexion MySQL

Ajout de l'authentification

Ajout du CRUD domaines

Ajout du CRUD réglementations

Ajout du CRUD obligations

Ajout du module contrôles

Ajout des actions correctives

Ajout du module preuves

Ajout du module utilisateurs

Ajout du dashboard

Ajout des exports

Amélioration de la sécurité

Correction des vulnérabilités

Mise à jour de la documentation

```



\---



\# 16. Installation et configuration



\## 16.1 Prérequis



L'environnement nécessite notamment :



\* Windows ;

\* XAMPP ;

\* Apache ;

\* PHP ;

\* MySQL ;

\* phpMyAdmin ;

\* navigateur Web ;

\* Git pour la récupération du projet depuis GitHub.



\## 16.2 Installation de XAMPP



Après installation de XAMPP, les services suivants doivent être démarrés :



```text

Apache

MySQL

```



\## 16.3 Installation du projet



Le projet doit être placé dans :



```text

C:\\xampp\\htdocs\\comply-sn

```



\## 16.4 Base de données



La base utilisée est :



```text

comply\_sn

```



Elle contient :



```text

users

domains

regulations

obligations

controls

corrective\_actions

evidences

audit\_logs

```



\## 16.5 Configuration MySQL



Le fichier :



```text

config/database.php

```



permet de configurer la connexion à MySQL.



Les informations sensibles de cette configuration ne doivent pas être publiées dans un dépôt GitHub public.



\---



\# 17. Déploiement



\## 17.1 Environnement local



La version actuelle est exécutée dans un environnement local avec XAMPP.



L'application peut être lancée à partir d'une adresse locale correspondant au projet.



Exemple :



```text

http://localhost/comply-sn/

```



\## 17.2 Déploiement futur



Pour un environnement de production, il faudra prévoir :



\* un serveur web ;

\* un serveur de base de données ;

\* HTTPS ;

\* un nom de domaine ;

\* une gestion sécurisée des secrets ;

\* des sauvegardes ;

\* une configuration des permissions ;

\* une surveillance des journaux ;

\* une politique de mise à jour.



\---



\# 18. Maintenance



La maintenance de COMPLY-SN comprend plusieurs aspects.



\## 18.1 Maintenance corrective



Elle consiste à corriger :



\* les bugs ;

\* les erreurs d'affichage ;

\* les problèmes de base de données ;

\* les vulnérabilités découvertes.



\## 18.2 Maintenance évolutive



Elle consiste à ajouter de nouvelles fonctionnalités.



Exemples :



\* nouveaux indicateurs ;

\* nouveaux modules ;

\* nouveaux rapports ;

\* nouvelles fonctionnalités de sécurité.



\## 18.3 Maintenance préventive



Elle consiste notamment à :



\* mettre à jour les composants ;

\* effectuer des sauvegardes ;

\* vérifier les journaux ;

\* contrôler les permissions ;

\* rechercher régulièrement les vulnérabilités.



\---



\# 19. Limites du projet



La version actuelle de COMPLY-SN possède certaines limites.



Parmi les améliorations possibles :



\* protection CSRF plus avancée ;

\* renforcement des permissions ;

\* sécurisation plus poussée des uploads ;

\* HTTPS en production ;

\* limitation des tentatives de connexion ;

\* gestion centralisée des secrets ;

\* tests automatisés ;

\* sauvegarde automatisée de la base.



Ces éléments pourront être intégrés dans les versions futures.



\---



\# 20. Perspectives d'amélioration



Plusieurs évolutions peuvent être envisagées.



\## 20.1 Notifications



Ajouter des notifications lorsqu'une obligation ou une action corrective approche de sa date limite.



\## 20.2 Notifications par e-mail



Le système pourrait envoyer automatiquement des rappels aux responsables.



\## 20.3 Rapports avancés



Des rapports PDF et Excel plus complets pourraient être générés.



\## 20.4 Amélioration du dashboard



Le tableau de bord pourrait intégrer :



\* des indicateurs supplémentaires ;

\* des graphiques interactifs ;

\* des filtres ;

\* des statistiques par période ;

\* des statistiques par domaine.



\## 20.5 Renforcement de la sécurité



Les futures versions pourraient intégrer :



\* authentification à deux facteurs ;

\* protection CSRF renforcée ;

\* limitation des tentatives de connexion ;

\* politiques de mots de passe ;

\* gestion avancée des permissions ;

\* analyse antivirus des fichiers.



\## 20.6 Déploiement cloud



Une future version pourrait être déployée dans une infrastructure cloud afin de permettre un accès distant sécurisé.



\---



\# 21. Conclusion générale



Le projet \*\*COMPLY-SN\*\* a permis de concevoir et de développer une application web destinée à faciliter la gestion de la conformité réglementaire.



L'application centralise plusieurs éléments essentiels du processus de conformité :



\* domaines ;

\* réglementations ;

\* obligations ;

\* contrôles ;

\* actions correctives ;

\* preuves ;

\* utilisateurs ;

\* journal d'audit.



L'utilisation de PHP, MySQL, HTML, CSS, JavaScript et XAMPP a permis de mettre en place une solution fonctionnelle dans un environnement local.



Plusieurs mécanismes de sécurité ont également été intégrés, notamment l'authentification, le hachage des mots de passe, les sessions, les requêtes préparées PDO, la validation des données, l'échappement des sorties et la traçabilité des opérations.



L'utilisation de Git et GitHub permet également de conserver l'historique du projet et de faciliter la collaboration à distance.



Bien que certaines améliorations restent nécessaires avant un déploiement en production, notamment concernant la sécurité avancée, les sauvegardes, HTTPS et les contrôles des fichiers, COMPLY-SN constitue une base fonctionnelle permettant de centraliser et de suivre les activités liées à la conformité réglementaire.



\---



\# 22. Annexes techniques



\## Annexe A — Arborescence du projet



```text

comply-sn/

│

├── auth/

│

├── config/

│

├── controls/

│

├── corrective\_actions/

│

├── domains/

│

├── evidences/

│

├── obligations/

│

├── regulations/

│

├── users/

│

├── uploads/

│

├── dashboard.php

│

├── README.md

│

└── .gitignore

```



\---



\## Annexe B — Structure de la base de données



```text

comply\_sn

│

├── users

│

├── domains

│

├── regulations

│

├── obligations

│

├── controls

│

├── corrective\_actions

│

├── evidences

│

└── audit\_logs

```



\---



\## Annexe C — Commandes Git principales



Initialisation :



```bash

git init

```



Vérification :



```bash

git status

```



Ajout des fichiers :



```bash

git add .

```



Création d'un commit :



```bash

git commit -m "Description de la modification"

```



Connexion au dépôt distant :



```bash

git remote -v

```



Envoi vers GitHub :



```bash

git push

```



Récupération des modifications :



```bash

git pull

```



Historique :



```bash

git log --oneline

```



\---



\## Annexe D — Cycle fonctionnel de COMPLY-SN



```text

&#x20;                COMPLY-SN

&#x20;                    │

&#x20;                    ▼

&#x20;                 Domaine

&#x20;                    │

&#x20;                    ▼

&#x20;             Réglementation

&#x20;                    │

&#x20;                    ▼

&#x20;                Obligation

&#x20;                    │

&#x20;                    ▼

&#x20;                 Contrôle

&#x20;                 /       \\

&#x20;                /         \\

&#x20;               ▼           ▼

&#x20;      Action corrective   Preuve

&#x20;               │

&#x20;               ▼

&#x20;            Suivi

&#x20;               │

&#x20;               ▼

&#x20;            Dashboard

```



\---



\## Annexe E — Cycle de développement



```text

Analyse

&#x20;  ↓

Conception

&#x20;  ↓

Développement

&#x20;  ↓

Tests

&#x20;  ↓

Correction

&#x20;  ↓

Sécurisation

&#x20;  ↓

Git / GitHub

&#x20;  ↓

Documentation

&#x20;  ↓

Déploiement

```



\---





