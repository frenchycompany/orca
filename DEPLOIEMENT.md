# Déploiement du site ORCA sur un serveur client

## Prérequis serveur

- PHP 8.0+ avec extensions `pdo_mysql`, `mbstring`, `fileinfo`, `gd`
- MySQL 8 ou MariaDB 10.4+
- Apache (mod_rewrite) ou Nginx
- Accès SSH + `git` installé (sinon upload FTP du zip)
- Un nom de domaine pointant sur le serveur, HTTPS activé (Let's Encrypt)

---

## 1. Récupérer les fichiers

```bash
cd /var/www
git clone https://github.com/frenchycompany/orca.git -b claude/fix-chatbot-issues-bDPTx orca
cd orca
```

> Sans git : télécharger le zip de la branche sur GitHub et le décompresser dans `/var/www/orca`.

## 2. Créer les dossiers d'upload (absents du repo)

```bash
mkdir -p uploads/maisons uploads/config uploads/plans uploads/actualites uploads/temoignages logs
chown -R www-data:www-data uploads logs
chmod -R 775 uploads logs
```

### Limites d'upload PHP (galerie photos des modèles)

Par défaut PHP limite à 8 Mo par requête. Pour uploader plusieurs photos d'un coup :
```bash
PHPINI=$(php -i | grep "Loaded Configuration File" | awk '{print $NF}' | sed 's#/cli/#/fpm/#')
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 10M/; s/^post_max_size.*/post_max_size = 64M/; s/^max_file_uploads.*/max_file_uploads = 30/' $PHPINI
systemctl restart php8.3-fpm
```
Et dans le server block Nginx : `client_max_body_size 64M;`

## 3. Créer la base de données

```bash
mysql -u root -p
```
```sql
CREATE DATABASE orca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'orca_user'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE_FORT';
GRANT ALL PRIVILEGES ON orca.* TO 'orca_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 4. Importer le schéma — DANS CET ORDRE

```bash
cd /var/www/orca/sql
mysql -u orca_user -p orca < database.sql                    # tables site + 6 modèles + admin
mysql -u orca_user -p orca < chatbot_complete_install.sql    # tables chatbot (pages admin en dépendent)
mysql -u orca_user -p orca < terrains.sql                    # terrains + 10 exemples
mysql -u orca_user -p orca < site_texts.sql                  # textes éditables
mysql -u orca_user -p orca < modeles_inclusions.sql          # colonnes inclusions par modèle
mysql -u orca_user -p orca < update_prix_modeles.sql         # prix_base des 6 modèles
mysql -u orca_user -p orca < frenchybot_config.sql           # clés config du chatbot (token à remplacer ensuite dans l'admin)
mysql -u orca_user -p orca < page_views.sql                  # statistiques de fréquentation (Admin > Statistiques)
```

Optionnel (intentions du chatbot local — utile uniquement si on réactive le chatbot interne) :
```bash
mysql -u orca_user -p orca < chatbot_intentions_enriched.sql
mysql -u orca_user -p orca < chatbot_intentions_20_new.sql
mysql -u orca_user -p orca < update_intentions_links.sql
```

> Ne PAS importer `chatbot-install.sql`, `chatbot.sql`, `chatbot_advanced_tables.sql` : versions anciennes remplacées par `chatbot_complete_install.sql`.

## 5. Configurer `includes/config.php`

```bash
nano /var/www/orca/includes/config.php
```

Modifier :
```php
// Lignes 7-12 : passer en mode production
error_reporting(0);
ini_set('display_errors', 0);
// (commenter les deux lignes error_reporting(E_ALL) / display_errors 1)

// Lignes 15-18 : identifiants BDD
define('DB_HOST', 'localhost');
define('DB_NAME', 'orca');
define('DB_USER', 'orca_user');
define('DB_PASS', 'MOT_DE_PASSE_FORT');
```

> `SITE_URL` est détecté automatiquement, rien à changer.

## 6. Token FrenchyBot

FrenchyBot vérifie le domaine d'origine : **créer un nouveau chatbot dans l'admin FrenchyBot avec le domaine du client** et récupérer son token.

Puis le renseigner dans **Admin > Configuration > 🤖 Chatbot FrenchyBot > Token**. Aucun fichier à modifier.

> Si le champ est vide, le chatbot est désactivé sur tout le site (bulle + iframes). Pratique pour mettre en ligne sans chatbot au début.

## 7. VirtualHost Apache

```apache
<VirtualHost *:80>
    ServerName www.domaine-client.fr
    ServerAlias domaine-client.fr
    DocumentRoot /var/www/orca

    <Directory /var/www/orca>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    # Protéger les dossiers sensibles
    <Directory /var/www/orca/sql>
        Require all denied
    </Directory>
    <Directory /var/www/orca/logs>
        Require all denied
    </Directory>

    ErrorDocument 404 /404.php
    ErrorLog ${APACHE_LOG_DIR}/orca-error.log
    CustomLog ${APACHE_LOG_DIR}/orca-access.log combined
</VirtualHost>
```

```bash
a2ensite orca.conf
a2enmod rewrite
systemctl reload apache2
certbot --apache -d domaine-client.fr -d www.domaine-client.fr
```

## 8. Premier login admin

- URL : `https://domaine-client.fr/admin/`
- Identifiant : `admin`
- Mot de passe par défaut : `password`

**Changer le mot de passe immédiatement** :
```bash
php -r 'echo password_hash("NOUVEAU_MDP", PASSWORD_BCRYPT), PHP_EOL;'
```
```sql
UPDATE users SET password_hash = 'HASH_GENERE', email = 'contact@domaine-client.fr' WHERE username = 'admin';
```

## 9. Configuration initiale dans l'admin

Dans l'ordre :

1. **Configuration** → nom du site, slogan, téléphone, email, adresse, couleurs, logo, favicon, **token FrenchyBot**
2. **Modèles** → vérifier les 6 modèles, uploader les photos, adapter les prix et les inclusions
3. **Terrains** → supprimer les 10 exemples, saisir les vrais terrains
4. **Pages CMS** → supprimer les doublons des pages statiques :
   ```sql
   DELETE FROM pages WHERE slug IN ('accueil','le-constructeur','nos-modeles','nos-engagements','contact');
   ```
5. **Visiter chaque page du site public une fois** (accueil, constructeur, modèles, engagements, contact, estimation, une fiche modèle) → les textes s'auto-enregistrent
6. **Textes du site** → relire et adapter tous les textes au client
7. **Témoignages / Actualités / FAQ** → remplacer les données de démo

## 10. Vérifications finales

- [ ] Toutes les pages s'affichent sans erreur PHP
- [ ] La bulle FrenchyBot apparaît en bas à droite
- [ ] L'iframe chatbot fonctionne sur l'accueil, `/estimation.php` et les fiches modèles
- [ ] Un lead test envoyé depuis le chatbot arrive dans l'admin FrenchyBot
- [ ] Le formulaire `/contact.php` crée un lead dans **Admin > Leads**
- [ ] L'upload d'image fonctionne (Modèles > Modifier > Image)
- [ ] `https://` actif, redirection http → https
- [ ] `/sql/` et `/logs/` inaccessibles depuis le navigateur

## Ce qui n'est PAS à installer

- `chatbot/cron-followups.php` : relances du chatbot interne, obsolète depuis l'externalisation vers FrenchyBot
- `js/chatbot.js`, `chatbot/api.php` : chatbot interne, non chargé (remplacé par l'embed FrenchyBot)

## Mises à jour futures

```bash
cd /var/www/orca && git pull origin claude/fix-chatbot-issues-bDPTx
```
Puis rejouer uniquement les nouveaux fichiers SQL éventuels listés dans le commit.
