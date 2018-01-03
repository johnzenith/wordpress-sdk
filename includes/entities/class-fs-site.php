<?php
    /**
     * @package     Freemius
     * @copyright   Copyright (c) 2015, Freemius, Inc.
     * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
     * @since       1.0.3
     */

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    class FS_Site extends FS_Scope_Entity {
        /**
         * @var string
         */
        public $slug;
        /**
         * @var number
         */
        public $site_id;
        /**
         * @var number
         */
        public $plugin_id;
        /**
         * @var number
         */
        public $user_id;
        /**
         * @var string
         */
        public $title;
        /**
         * @var string
         */
        public $url;
        /**
         * @var string
         */
        public $version;
        /**
         * @var string E.g. en-GB
         */
        public $language;
        /**
         * @var string E.g. UTF-8
         */
        public $charset;
        /**
         * @var string Platform version (e.g WordPress version).
         */
        public $platform_version;
        /**
         * Freemius SDK version
         *
         * @author Leo Fajardo (@leorw)
         * @since  1.2.2
         *
         * @var string SDK version (e.g.: 1.2.2)
         */
        public $sdk_version;
        /**
         * @var string Programming language version (e.g PHP version).
         */
        public $programming_language_version;
        /**
         * @var FS_Plugin_Plan $plan
         */
        public $plan;
        /**
         * @var number|null
         */
        public $license_id;
        /**
         * @var number|null
         */
        public $trial_plan_id;
        /**
         * @var string|null
         */
        public $trial_ends;
        /**
         * @since 1.0.9
         *
         * @var bool
         */
        public $is_premium = false;
        /**
         * @author Leo Fajardo (@leorw)
         *
         * @since  1.2.1.5
         *
         * @var bool
         */
        public $is_disconnected = false;

        /**
         * @param stdClass|bool $site
         */
        function __construct( $site = false ) {
            $this->plan = new FS_Plugin_Plan();

            parent::__construct( $site );

            if ( is_object( $site ) ) {
                $this->plan->id = $site->plan_id;
            }

            if ( ! is_bool( $this->is_disconnected ) ) {
                $this->is_disconnected = false;
            }
        }

        static function get_type() {
            return 'install';
        }

        /**
         * @author Vova Feldman (@svovaf)
         * @since  1.2.4
         *
         * @param string $url
         *
         * @return bool
         */
        static function is_localhost_by_address( $url ) {
            if ( false !== strpos( $url, '127.0.0.1' ) ||
                 false !== strpos( $url, 'localhost' )
            ) {
                return true;
            }

            if ( ! fs_starts_with( $url, 'http' ) ) {
                $url = 'http://' . $url;
            }

            $url_parts = parse_url( $url );

            $subdomain = $url_parts['host'];

            return (
                // Starts with.
                fs_starts_with( $subdomain, 'local.' ) ||
                fs_starts_with( $subdomain, 'dev.' ) ||
                fs_starts_with( $subdomain, 'test.' ) ||
                fs_starts_with( $subdomain, 'staging.' ) ||

                // Ends with.
                fs_ends_with( $subdomain, '.dev' ) ||
                fs_ends_with( $subdomain, '.test' ) ||
                fs_ends_with( $subdomain, '.staging' ) ||
                fs_ends_with( $subdomain, '.local' ) ||
                fs_ends_with( $subdomain, '.example' ) ||
                fs_ends_with( $subdomain, '.invalid' ) ||
                // GoDaddy test/dev.
                fs_ends_with( $subdomain, '.myftpupload.com' ) ||
                // ngrok tunneling.
                fs_ends_with( $subdomain, '.ngrok.io' ) ||
                // SiteGround staging.
                fs_starts_with( $subdomain, 'staging' )
            );
        }

        function is_localhost() {
            // The server has no way to verify if localhost unless localhost appears in domain.
            return ( WP_FS__IS_LOCALHOST_FOR_SERVER || self::is_localhost_by_address( $this->url ) );
        }

        /**
         * Check if site in trial.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.0.9
         *
         * @return bool
         */
        function is_trial() {
            return is_numeric( $this->trial_plan_id ) && ( strtotime( $this->trial_ends ) > WP_FS__SCRIPT_START_TIME );
        }

        /**
         * Check if user already utilized the trial with the current install.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.0.9
         *
         * @return bool
         */
        function is_trial_utilized() {
            return is_numeric( $this->trial_plan_id );
        }
    }