COMPLY-SN

Système de gestion de la conformité réglementaire



COMPLY-SN est une application web permettant de centraliser et de gérer les informations relatives à la conformité réglementaire d'une organisation.



L'application permet notamment de gérer les domaines de conformité, les réglementations, les obligations, les contrôles, les actions correctives, les preuves et les utilisateurs.



🎯 Objectifs du projet



COMPLY-SN a pour objectifs de :



centraliser les informations de conformité ;

faciliter le suivi des obligations réglementaires ;

permettre le suivi des contrôles ;

gérer les actions correctives ;

centraliser les preuves de conformité ;

suivre les utilisateurs responsables ;

fournir un tableau de bord de suivi ;

conserver un journal des actions importantes ;

faciliter l'exportation des données.

🛠️ Technologies utilisées



Le projet utilise principalement :



PHP

MySQL / MariaDB

HTML5

CSS3

JavaScript

XAMPP

Git / GitHub



Des bibliothèques PHP sont également présentes pour la génération des documents PDF :



FPDF

Dompdf

📁 Structure du projet

comply-sn/

│

├── auth/

│   └── Authentification et connexion

│

├── config/

│   └── Configuration de l'application

│

├── controls/

│   └── Gestion des contrôles

│

├── corrective\_actions/

│   └── Gestion des actions correctives

│

├── domains/

│   └── Gestion des domaines

│

├── dompdf/

│   └── Bibliothèque Dompdf

│

├── evidences/

│   └── Gestion des preuves

│

├── fpdf/

│   └── Bibliothèque FPDF

│

├── obligations/

│   └── Gestion des obligations

│

├── regulations/

│   └── Gestion des réglementations

│

├── setup/

│   └── Installation et configuration initiale

│

├── uploads/

│   └── Fichiers téléchargés

│

├── users/

│   └── Gestion des utilisateurs

│

├── dashboard.php

│   └── Tableau de bord

│

├── .gitignore

│   └── Fichiers exclus de Git

│

└── README.md

&#x20;   └── Documentation du projet

🔐 Authentification



L'application dispose d'un système d'authentification permettant aux utilisateurs autorisés d'accéder à l'application.



Les pages protégées vérifient notamment la présence d'une session utilisateur.



Les utilisateurs non authentifiés sont redirigés vers la page de connexion.



📊 Tableau de bord



Le tableau de bord permet d'avoir une vue globale de l'état de conformité.



Il présente notamment :



les différents modules ;

les statistiques ;

le nombre d'éléments enregistrés ;

l'état des obligations ;

des graphiques de suivi.

📚 Modules de l'application

Domaines



Permet de gérer les différents domaines de conformité de l'organisation.



Réglementations



Permet d'enregistrer et de gérer les réglementations applicables.



Obligations



Permet de gérer les obligations associées aux réglementations.



Une obligation peut notamment contenir :



une réglementation ;

un titre ;

une description ;

une fréquence ;

une date limite ;

une criticité ;

un statut ;

un responsable.

Contrôles



Permet de gérer les contrôles permettant de vérifier la conformité.



Actions correctives



Permet de suivre les actions mises en place lorsqu'une non-conformité est identifiée.



Preuves



Permet de gérer les documents et éléments permettant de démontrer la conformité.



Utilisateurs



Permet de gérer les utilisateurs de l'application.



📝 Journal d'audit



L'application utilise un journal d'audit permettant de conserver certaines actions réalisées dans le système.



Par exemple :



création d'une obligation ;

modification d'une obligation ;

utilisateur ayant effectué l'action ;

date de l'action ;

adresse IP ;

enregistrement concerné.



🗄️ Configuration de la base de données



La connexion à la base de données est configurée dans :



config/database.php



Ce fichier contient les paramètres locaux de connexion à MySQL.



Pour des raisons de sécurité, database.php n'est pas versionné sur GitHub.



Un fichier modèle est fourni :



config/database.example.php



Pour configurer une nouvelle installation :



Copier database.example.php.

Le renommer en database.php.

Adapter les paramètres MySQL.

Créer la base de données comply\_sn.

Importer la structure de la base de données.

💻 Installation avec XAMPP

Prérequis



Installer :



XAMPP ;

PHP ;

MySQL ou MariaDB ;

un navigateur web ;

Git.

Installation



Placer le projet dans :



C:\\xampp\\htdocs\\



Le dossier final doit être :



C:\\xampp\\htdocs\\comply-sn



Démarrer ensuite depuis le panneau XAMPP :



Apache

MySQL



Puis accéder à l'application depuis :



http://localhost/comply-sn/

🗄️ Base de données



Créer une base de données appelée :



comply\_sn



La structure de la base de données doit ensuite être importée dans MySQL/MariaDB.



📤 Exportation



L'application prévoit des fonctionnalités d'exportation des données.



Les exports peuvent notamment être utilisés pour produire des documents destinés au suivi ou à la présentation des informations de conformité.



Les bibliothèques présentes dans le projet comprennent :



FPDF

Dompdf

🔒 Sécurité



Plusieurs mesures de sécurité sont intégrées au projet, notamment :



contrôle des sessions ;

protection des pages nécessitant une authentification ;

utilisation de requêtes SQL préparées ;

validation des données envoyées par les formulaires ;

échappement des données affichées avec htmlspecialchars() ;

utilisation de PDO pour la connexion à la base de données ;

journalisation de certaines actions ;

exclusion des fichiers sensibles avec .gitignore.



Le fichier suivant ne doit pas être publié :



config/database.php

🌐 Git et GitHub



Le projet utilise Git pour assurer le suivi des modifications.



Le dépôt contient notamment les commits correspondant aux différentes étapes importantes du développement.



Exemple :



Initialisation du projet

Ajout du modèle de configuration MySQL



Les modifications futures doivent être enregistrées avec des messages de commit explicites.



Exemple :



git add .

git commit -m "Ajout du module de gestion des contrôles"

git push

👤 Utilisation



Après installation et configuration de la base de données :



Accéder à l'application.

Se connecter avec un compte utilisateur.

Accéder au tableau de bord.

Utiliser les différents modules.

Ajouter ou modifier les données de conformité.

Consulter les statistiques.

Effectuer les exports nécessaires.

📌 État du projet



Le projet comprend actuellement les principaux modules suivants :



Authentification

Tableau de bord

Domaines

Réglementations

Obligations

Contrôles

Actions correctives

Preuves

Utilisateurs

Journal d'audit

Exportation



La documentation et les fonctionnalités pourront être enrichies progressivement.



👩‍💻 Développement



Le projet est développé avec une architecture PHP/MySQL adaptée à un environnement local XAMPP.



Les développements sont suivis avec Git et GitHub afin de conserver l'historique des modifications et faciliter la collaboration.



📄 Licence



Projet à usage académique/professionnel.



La licence peut être définie ultérieurement selon les conditions de diffusion du projet.

