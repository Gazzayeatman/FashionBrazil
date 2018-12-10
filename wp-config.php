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
define('DB_NAME', 'fashionbrazil');

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
define('AUTH_KEY',         '49)Vc%,4178M N)3]^%YqsQn@lP7cGyf@8,Q+U PfCHEEE6OP!CN];_(R/dKk^H!');
define('SECURE_AUTH_KEY',  ';]CHBVB}hSxcS:ejbIQ0s7rxTg@k:zA7E%K@pwa[^Jo,8nXN)}b<ZmSSYMrV>co>');
define('LOGGED_IN_KEY',    '~lvlAnQ1]j2,C4}:7H)*w*_[T}c<#0Us1Mgglq{k?bMcs>8Y*lqzSvYp2]8^qGs$');
define('NONCE_KEY',        'W#v(+ GWK=xm6CN}|JYsC>v]&uH%r-0<48~!YTZ>dFgx,TP5y#5CPb<kTJc,x 0&');
define('AUTH_SALT',        'nT4v.G?r8nAecV2%l]&8~ucEj*AK@=aiE*IwXsCXX7mY|q6m3B`HO!2_SJ6cvmAF');
define('SECURE_AUTH_SALT', '?BWv1ZK6~9:jO/$%i]h<~l*4#7m)T:O_-3YYu!oUvM&{;apIo4fN)#&-eYO(avr=');
define('LOGGED_IN_SALT',   'hzk9-|m,~-L0p9+X~v!_wN/dwB%xlH-7P$J_)&_zP($9n#wKj$Wt<=9E$g#Ma:KF');
define('NONCE_SALT',       'pYQA;EjMQCYqO715w(h0`mrDgYi}%2l2Uw.e9AE;D3vE)R{@#59<pDrvAf<A:Hyn');

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

define('SYSTEM_URL', 'http://fashionbrazil.local');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
