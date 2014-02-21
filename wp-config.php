<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'uangelwp_en');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root.123');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'B}hzghV{eIPyme{Vf/+i)DD!jF|C[`@c;8&1I$`b$FM4V4z%PH-FT)|i#PrBe=F0');
define('SECURE_AUTH_KEY',  'I&Z_P=KW_jmy%+^~0X:1i-A]sNW@&G)|b,G3d?,X$SG<}=(w]9y^6h+>,FJMv5vn');
define('LOGGED_IN_KEY',    '5YYNKJvSe8JYRw-[U(!au3fn9?rbC(ye~cPgm;puN7-I/bOzJ&wQjwh3C%:o6jG(');
define('NONCE_KEY',        'y;DR{-j>sxN2-iBV{!_G|=RMQ3Cs*I:Z>~Aid=^j&#TYV&lTP}n_nf!8t 92u,#|');
define('AUTH_SALT',        '-(:=w!4s:6=<R[+rrR-wauhYPMNWQ8YDl+mf<~4&EfF`X=:3MB6M1=2ET:fD^H-_');
define('SECURE_AUTH_SALT', '4z+/r+- E-|R}s eCP+-+LA`+4=C.zaS]XAb^.596tCMozdVx3#R)w9P-5I`/]a;');
define('LOGGED_IN_SALT',   'pXN+C}yJ&fdDkpr|U9|UG#iJDq[(x2E!<=c&,YL!%}D /-6T:iIXjFj_lrfkQ>|@');
define('NONCE_SALT',       'y=#oucBGM`QR_Bc=<l5|0)wnaguNpH2VucIru@NF$M.U|||g-x:Wx1[/(!3QGDOX');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

#define('WP_HOME','http://192.168.1.9/');
#define('WP_SITEURL','http://192.168.1.9/');
