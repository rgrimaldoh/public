<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          't_($(CT`ovlsVq7NXm cl%p0QrE7sXY0cG2NT(K@TE3wFF#f+DjUV.(G|po04nD:' );
define( 'SECURE_AUTH_KEY',   'i88=pbrfCBAV*fa7]$70~3As4@t-^j~J_$-^Z37vkwl1wc#+Hl2;fB~2BQc{$V4]' );
define( 'LOGGED_IN_KEY',     'nw0O;5oej$j.bJSr.!8$ZE!+m/N%~)zss.O_6v$GnU?{g;XS]6T9 $K~Ft95.]qG' );
define( 'NONCE_KEY',         'UK?cWRHLx$?fRRLIG Q(RmX)N|7oSnx>/r$Z::.>m@bOWnRg1/a/PBCVp+X5uCS?' );
define( 'AUTH_SALT',         '%`)>K:L-M$49gC7,XE!kv3?=4ytL8u%c,.k1)W)gs8 :Aei7W[mE#X[,|Lu.2fwM' );
define( 'SECURE_AUTH_SALT',  'ajyeP,}paA@:l!5}%Y5F1wR+JQcT<9MzK)Qu6v?nDp0v^3LQ!7bOzEho;g(-,*iP' );
define( 'LOGGED_IN_SALT',    'Lc]Wx@7|eq`9qPJVd_D?vOrL(iq!W#,i}!oWlg2mkR [NVn<NSk PVkAHs^D5}Ni' );
define( 'NONCE_SALT',        'm19_Q1yK W<(?|]cZ=[+`Q~_xt_;$O=?v4Y-i72cYmpQfCT^*tRSv>h|o}zO;9+ ' );
define( 'WP_CACHE_KEY_SALT', 'Bol4;_hz5#])E};< !vp(/a_6%xv=vjl=K)$/}ScMkBnGqm`WSw#3KyiFt7@j&lL' );


define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
// if ( ! defined( 'WP_DEBUG' ) ) {
// 	define( 'WP_DEBUG', false );
// }

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
