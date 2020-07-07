<?php
define( 'WP_CACHE', false ); // Added by WP Rocket
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', "fildenuit" );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', "root" );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', "root" );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', "localhost" );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '(bWrV{O_n^h6KmIWw)/wpXOPH#sjg;N^Ax_1Gurvir 2;@VabL`oMi^i/;<EZpsu' );
define( 'SECURE_AUTH_KEY',  'HDyD%jcg#E=#fnwsU:N>,B~wsy8JfRLIEQVs,DZ)%>]sda#bAw|jmxODiTq#u2?W' );
define( 'LOGGED_IN_KEY',    '(zeApr?LP1r]MN6z%|Ze~{ZCxA1&u=dF*ogwTcvKak14<.mVXOsLGm:@Po]%^]FR' );
define( 'NONCE_KEY',        '(z1^J|=?vQg>$hDG4&* s/My9^(UEr!qc.06Q~K)Z.BL7-R<e<29m,y5sDn18yg=' );
define( 'AUTH_SALT',        '~xlqH-fG(r[nuJTW`@;~Oz*yTgCht %sI?m<qm$W6Tz|/(OQRSVr&3`n=[1~Z9_H' );
define( 'SECURE_AUTH_SALT', 'clBkg+9/U=Cn<1AQo?$s`J(SYx?n<(~aKVsL./2+;CfwIl{,{jeu^L?g_=Y}[N0h' );
define( 'LOGGED_IN_SALT',   '#bOms=6$iio4F=n,_/R_v z|AVg;C;:j^tNQHO3~N$gYMs`A?2{#9X=sAHA1j3>u' );
define( 'NONCE_SALT',       'x`)pg!^-5<G_x_t 1W*#+XaYaJ*XZh&dt^M~,!LaQ6?`MB!9Or9qj29tS122)+8>' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');