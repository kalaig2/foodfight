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
 /** The name of the database for WordPress */
define('DB_NAME', 'foodfight_multi_new');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

    /** Database Charset to use in creating database tables. */
    define('DB_CHARSET', 'utf8mb4');

    /** The Database Collate type. Don't change this if in doubt. */
    define('DB_COLLATE', '');

    define( 'WP_AUTO_UPDATE_CORE', false );

    /* Multisite */
    define('WP_ALLOW_MULTISITE', true);

    define('FS_METHOD', 'direct');

    define('MULTISITE', true);
    define('SUBDOMAIN_INSTALL', false);
    define('DOMAIN_CURRENT_SITE', '202.129.197.46:3245');
    define('PATH_CURRENT_SITE', '/foodfight_latest/');
    define('SITE_ID_CURRENT_SITE', 1);
    define('BLOG_ID_CURRENT_SITE', 1);

    /*     * #@+
     * Authentication Unique Keys and Salts.
     *
     * Change these to different unique phrases!
     * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
     * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
     *
     * @since 2.6.0
     */
    define('AUTH_KEY', '<&2M:7P8943Cy;!!*Rz3.K EqlK[PL62~;REQA3x6jQbW-u[U<?T_1kGG!7N(9[r');
    define('SECURE_AUTH_KEY', 'st7tAv!~O*Z@27Z)gDsMdS|O]ce#N<6iA~23~O4uz]$c%wyY!]4EZRiQ,#.C}pfT');
    define('LOGGED_IN_KEY', '[Z^V0!Xz2sh^mmyhSic DBalA|d@/yb{&^VzQpofMXc0PRp}4T0{qZ&VT5-N%Bi+');
    define('NONCE_KEY', '}Mc/v6VpEK/EL1L}M[UQ@:e(C(1iXf$`$qUl|mgtm{LYQ3e(5EHrjP)d]sK2E12k');
    define('AUTH_SALT', 'L@vSHoXG$WwI4h:G--(>o^mzmHe9bG2&0ey6l,uf~=SuT:oo:R)I@(ZEg./8M0O>');
    define('SECURE_AUTH_SALT', '+XMS1}68jyAA15s-L4-?5 3pPi5l[Tl9D:K9788OzqeLu/xr/f,Fh)S8lIIUq=az');
    define('LOGGED_IN_SALT', '?{20xe7zJO<yBt1CuyF?k4#$d_3 r1eGw|c-?%ZIwuVL#X[alj$z6v/>P?g+Jl|L');
    define('NONCE_SALT', 'i4kK|lZ0UlzWmTx}:7)qEc:)Zr}&&&>X/qi+ssjz(|}1V{rkFBfsXU,LoK0m<923');

    /*     * #@- */

    /**
     * WordPress Database Table prefix.
     *
     * You can have multiple installations in one database if you give each
     * a unique prefix. Only numbers, letters, and underscores please!
     */
    $table_prefix = 'aff16_';

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
    if (!defined('ABSPATH'))
        define('ABSPATH', dirname(__FILE__) . '/');

    /** Sets up WordPress vars and included files. */
    require_once(ABSPATH . 'wp-settings.php');
