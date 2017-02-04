<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_fashion_brazil');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'aaaaaaaa');

/** MySQL hostname */
define('DB_HOST', '10.0.2.2');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         '.;=Snz6f@0&4!IItrS[5ig>nHjL~`bwgA>QA_xePmMS)<j~`.Yvpj}Q7F?{f(G#A');
define('SECURE_AUTH_KEY',  ')aZ-$H&H}&p-mCxQp:aPry|*:D`MFtYu<mj!CqPH8L=y[4!{~=Md-tJ/~Xeg.fiz');
define('LOGGED_IN_KEY',    '((p_:- ?ke4D>+yCCky4.c&)k!Jc?i-5]{PaK$5|!Xwi{[K_)}H);w3{Ie=N48=^');
define('NONCE_KEY',        '+P of29Nj7~{QU4^<UITNQjim0&`m=#9Hpy,}efGTfG:+f<|6gzG)iv b{[%0+T;');
define('AUTH_SALT',        'aY6&$9Rh`(h@z,&_//JyS n{UdX3HLe>)S,AQ,S<g%^tyu>tf?~?Ue}hlyu{TcWC');
define('SECURE_AUTH_SALT', 'EQ/]CeMOhq^fG{2sFU4y/&w].)8PM!.~7GnEAYGy.~1exRlx!z:. s;E;%Zi-H$e');
define('LOGGED_IN_SALT',   '4sAHw,Yi$G)S@1bnQi4Qg4US~hnQAZUISnB2FIgwuv4#tl9K9/^yCw#&QY=V486e');
define('NONCE_SALT',       'G!}JW*EL[O0q]yR>)s;&gpF$Jb]9oGSI@$M^,2A}FNZB89frUmGGNr7M=g9+I2xN');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
