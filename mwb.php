<?php
/**
 * My Wp Brand
 *
 * @package     My Wp Brand
 * @author      imw3
 * @copyright   2021 imw3
 * @license     GPLv3
 *
 * @wordpress-plugin
 * Plugin Name: My Wp Brand
 * Plugin URI:  https://imw3.com/product/my-wp-brand
 * Description: My Brand plugin is used to customize admin panel.
 * Version:     1.1.3
 * Author:      imw3
 * Author URI:  https://imw3.com/
 * Text Domain: my-wp-brand
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'MWB_TEXTDOMAIN' , 'my-wp-brand' );

if ( ! class_exists( 'MWB_Brand' ) ) :    
    
    class MWB_Brand {

        /**
         * Initialized my-wp-brand plugin
         * @version 1.0.0
         */
        public function __construct() {

            /**
             * Action hooks
             * 
             * @version 1.0.0
             * @version 1.1.3 Removed unnecessary wp_ajax_nopriv hooks
             */
            add_action( 'admin_menu' , array( $this , 'mwb_manage_admin_menu' ) );
            add_action( 'wp_ajax_mwb_style_ajax' , array( $this , 'mwb_style_ajax' ) );
            add_action( 'wp_ajax_mwb_author_form' , array( $this , 'mwb_author_form' ) );
            add_action( 'login_headerurl', array( $this , 'mwb_change_login_logo_url' ) );
            add_action( 'wp_ajax_mwb_plugins_form' , array( $this , 'mwb_plugins_form' ) );
            add_action( 'login_enqueue_scripts' , array( $this , 'mwb_manage_login_form' ) );
            add_action( 'admin_bar_menu' , array( $this , 'mwb_manage_admin_top_bar_menu' ) , 1 );
            add_action( 'admin_enqueue_scripts' , array( $this , 'mwb_enqueue_scripts_and_styles' ) );
            add_action( 'wp_before_admin_bar_render' , array( $this , 'mwb_remove_admin_top_bar_default_logo' ) );

            /**
             * Filter hooks
             * @version 1.0.0
             */
            add_filter( 'all_plugins', array( $this , 'mwb_hide_plugins' ) );
            add_filter( 'gettext', array( $this , 'mwb_change_howdy_text' ) ,  10 ,  3 );
            add_filter( 'admin_footer_text', array( $this , 'mwb_change_admin_footer_text' ) );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ) , array( $this , 'mwb_setting_link_plugin' ) );

        }

        /**
         * Show settings button in my wp brand plugin 
         * @version 1.0.0
         */
        public function mwb_setting_link_plugin( $links ) {

            $settings_link[] = '<a href="' .
            admin_url( 'options-general.php?page=mwb' ) .
            '">' . esc_html__( 'Settings' , MWB_TEXTDOMAIN ) . '</a>';
            return array_merge( $settings_link , $links );

        }

        /**
         * Add script and style in wp-admin
         * @version 1.1.1
         */
        public function mwb_enqueue_scripts_and_styles() {

            $page_name = ! empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

            if ( is_admin() 
            && ( 
                $page_name === 'mwb' 
                || $page_name === 'mwb-author' 
                || $page_name === 'mwb-plugins' 
                ) 
            ) :

                wp_enqueue_style( 'my-wp-brand', plugin_dir_url(__FILE__) . 'assets/css/style-my-wp-brand.css', false, '1.0.0' );

                wp_enqueue_script( 'my-wp-brand', plugin_dir_url(__FILE__) . 'assets/js/style-my-wp-brand.js' , array(), '1.0.0', true );

            elseif ( is_admin() && $page_name ===  'mwb-style' ) :

                wp_enqueue_media();

                wp_enqueue_style( 'my-wp-brand', plugin_dir_url(__FILE__) . 'assets/css/style-my-wp-brand.css', false, '1.0.0' );

                wp_enqueue_script( 'my-wp-brand', plugin_dir_url(__FILE__) . 'assets/js/style-my-wp-brand.js' , array(), '1.0.0', true );

            endif;

            wp_enqueue_script('jquery'); 

            wp_localize_script( 'jquery', 'mwb_ajax', array( 
                'url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'ajax-nonce' )
            ));

        }

        /**
         * Register any page in wp-admin
         *  -> Hide wordpress version from admin
         * @version 1.0.0
         */
        public function mwb_manage_admin_menu() {

            global $_registered_pages;
            
            if( current_user_can( 'manage_options' ) ) :

                /**
                 * @PageName Hide Plugins
                 * @PageSlug mwb-plugins
                 * @version  1.1.0
                 */
                $menu_slug = plugin_basename( 'mwb-plugins' );
                $hookname = get_plugin_page_hookname( $menu_slug , '' );
                if ( ! empty ( $hookname ) ) {
                    add_action( $hookname , array( $this , 'mwb_plugins_page' ) );
                }
                $_registered_pages[$hookname] = true;

                /**
                 * @PageName Edit Style
                 * @PageSlug mwb-style
                 * @version  1.0.0
                 */
                $menu_slug = plugin_basename( 'mwb-style' );
                $hookname = get_plugin_page_hookname( $menu_slug , '' );
                if ( ! empty ( $hookname ) ) {
                    add_action( $hookname , array( $this , 'mwb_style_page' ) );
                }
                $_registered_pages[$hookname] = true;

                /**
                 * @PageName Edit Author
                 * @PageSlug mwb-author
                 * @version  1.0.0
                 */
                $menu_slug = plugin_basename( 'mwb-author' );
                $hookname = get_plugin_page_hookname( $menu_slug , '' );
                if ( ! empty ( $hookname ) ) {
                    add_action( $hookname , array( $this , 'mwb_author_page' ) );
                }
                $_registered_pages[$hookname] = true;

            endif;

            /**
             * @Menu     Settings
             * @SubMenu  MY WP Brand
             * @PageName Hide Menus
             * @PageSlug mwb
             * @version  1.0.0
             */
            add_submenu_page( "options-general.php", esc_html__( 'MY WP Brand' ,  MWB_TEXTDOMAIN ) , esc_html__( 'MY WP Brand' , MWB_TEXTDOMAIN ) , "manage_options", "mwb", 'mwb_hide_side_menu_page' );

            /**
             * Hide the WordPress version from the admin panel
             * 
             * @version 1.0.0
             */
            if ( get_option( 'wp-version-hide' ) === 'on' || empty ( get_option( 'wp-version-hide' ) ) ) :
                remove_filter( 'update_footer', 'core_update_footer' ); 
            elseif ( get_option( 'wp-version-hide' ) === 'off' ) :
                add_filter( 'update_footer', 'core_update_footer' ); 
            endif;

        }

        /**
         * Hide plugins from the wp-plugin list in the admin panel 
         * 
         * @version 1.1.0
         */
        public function mwb_hide_plugins( $plugins ) {

            if ( get_option( 'hide-plugins' ) !== 'no' ) {

                $hide_plugins = get_option( 'hide-plugins' );
                
                if( ! empty ( $hide_plugins ) ) :
                    foreach ( $hide_plugins as $ph ) {
                        if( in_array( $ph, array_keys( $plugins ) ) ) {
                            unset( $plugins[$ph] );
                        }
                    }
                endif;
            
            }
            
            return $plugins;

        }

        /**
         * mwb-plugins page callback function to render page.
         * 
         * @version 1.1.0
         */
        public function mwb_plugins_page() {

            /**
             * @global $menu
             * 
             * @version 1.1.0
             */
            global $menu,$submenu;

            /**
             * Fetch activated plugins
             * 
             * @version 1.1.0
             */
            $get_plugins_activated = get_option( 'active_plugins' );
            $plugins               = get_plugins();
            $activated_plugins     = array();

            foreach ( $get_plugins_activated as $p ) {           
                if( isset( $plugins[$p] ) ){
                    $activated_plugins[] = [
                        'plugin_data' => $plugins[$p],
                        'plugin_dir_path' => $p
                    ];
                }           
            }

            if ( get_option( 'hide-plugins' ) !== 'no' ) {
                $hide_plugins = get_option( 'hide-plugins' );
            }

            if( isset ( $_GET['update'] ) ) :
                if ( $_GET['update'] === 'true' || $_GET['update'] === true ) :
                    $update_tag = esc_html__( 'Your changes has been updated.' , MWB_TEXTDOMAIN );
                    echo "<div class='updated notice notice-success is-dismissible'><p>{$update_tag}</p></div>";
                endif;
            endif;

            ?>
                <!-- page-title -->
                    <h2 class="mwb-title"><?php echo esc_html__( 'Hide Plugins' , MWB_TEXTDOMAIN ); ?></h2>
                <!-- page-title -->
                <!-- other-submenu-page-links -->
                <div class="mwb-btab">
                    <a href="<?php echo admin_url( 'options-general.php?page=mwb' ); ?> "><?php echo esc_html__( 'Hide Menus' , MWB_TEXTDOMAIN ); ?></a>
                    <a href="<?php echo admin_url( 'options-general.php?page=mwb-author' ); ?> "><?php echo esc_html__( 'Edit Author' , MWB_TEXTDOMAIN ); ?></a>
                    <a href="<?php echo admin_url( 'options-general.php?page=mwb-style' ); ?> "><?php echo esc_html__( 'Edit Style' , MWB_TEXTDOMAIN ); ?></a>
                </div>
                <!-- other-submenu-page-links -->
                
                <div class="mwb-hide-plugin">
                    <form method="post" id="mwb-plugins-form" > 
                        <?php foreach( $activated_plugins as $key => $ap ):?>
                        <label class="list-group-item mt-3 mb-3 rounded border border-1">
                                <input class="me-1" name="plugins[]" type="checkbox" value="<?php echo esc_html( $ap['plugin_dir_path'] ); ?>" <?php 
                                if( ! empty( $hide_plugins ) ):
                                    foreach ( $hide_plugins as $psh ){
                                        echo $psh === $ap['plugin_dir_path'] ? esc_html__( 'checked', MWB_TEXTDOMAIN ) : '';
                                    }
                                endif;
                            ?>/>
                            <?php echo esc_html( $ap['plugin_data']['Name'] );?>
                        </label>
                        <?php endforeach;?>
                        <button type="submit" class="btn top-btn"><?php echo esc_html__( 'Save' , MWB_TEXTDOMAIN ); ?></button>
                    </form>
                </div>

                <div class="notes">
                    <i>
                        <?php
                        echo sprintf( esc_html__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', MWB_TEXTDOMAIN ), sprintf( '<strong>%s</strong>', esc_html__( 'My Wp Brand', MWB_TEXTDOMAIN ) ), '<a href="https://wordpress.org/plugins/my-wp-brand/" target="_blank" class="is-rating-link" data-rated="' . esc_html__( 'Thanks :)', MWB_TEXTDOMAIN ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' ); 
                        ?>
                    </i>
                </div>

            <?php

        }

        /**
         * mwb-style page callback function to render page
         * @version 1.0.0
         */
        public function mwb_style_page() {

            if( isset ( $_GET['update'] ) ) :
                if ( $_GET['update'] === 'true' || $_GET['update'] === true ) :
                    echo "<div class='updated notice notice-success is-dismissible'><p>". esc_html__( 'Your changes has been updated.' , MWB_TEXTDOMAIN ) ."</p></div>";
                endif;
            endif;

            ?>
                <!-- page-title -->
                    <h2 class="mwb-title"><?php echo esc_html__( 'Edit Style' , MWB_TEXTDOMAIN ); ?> </h2>
                <!-- page-title -->
                <!-- other-submenu-page-links -->
                <div class="mwb-btab">
                    <a class="" href="<?php echo admin_url( 'options-general.php?page=mwb' ); ?> "><?php echo esc_html__( 'Hide Menus' , MWB_TEXTDOMAIN ); ?></a>
                    <a class="" href="<?php echo admin_url( 'options-general.php?page=mwb-plugins' ); ?> "><?php echo esc_html__( 'Hide Plugins' , MWB_TEXTDOMAIN ); ?></a>
                    <a class="" href="<?php echo admin_url( 'options-general.php?page=mwb-author' ); ?> "><?php echo esc_html__( 'Edit Author' , MWB_TEXTDOMAIN ); ?></a>
                </div>
                <!-- other-submenu-page-links -->
                <div class="mwb-style">
                    <form method="post" id="mwb-style-form">
                        <!-- admin-bar-default-logo -->
                        <div class="mwb-admin-bar-logo">
                            <h2><?php echo esc_html__('Add admin bar logo', MWB_TEXTDOMAIN );?></h2>
                            <div class="ufile">
                                <img src="<?php echo ! empty( get_option( 'admin-bar-logo' ) ) ? esc_url( get_option( 'admin-bar-logo' ) ) : ''; ?>" id="show-admin-bar-logo" />
                                <input type="hidden" name="hidden-admin-bar-logo" id="hidden-admin-bar-logo" />
                                <button type="button" id="admin-bar-logo"><?php echo empty( get_option( 'admin-bar-logo' ) ) ? esc_html__( 'Upload image' , MWB_TEXTDOMAIN ) : ''; ?></button>
                            </div>
                        </div>
                        <!-- admin-bar-default-logo -->

                        <!-- login-page-logo -->
                        <div class="mwb-login-page-logo">
                            <h2><?php echo esc_html__('Add login logo', MWB_TEXTDOMAIN ) ; ?></h2>
                            <div class="ufile">
                                <img src="<?php echo ! empty( get_option( 'login-logo' ) ) ? esc_url( get_option( 'login-logo' ) ) : ''; ?>" id="show-login-logo" />
                                <input type="hidden" name="hidden-login-logo" id="hidden-login-logo" />
                                <button type="button" id="login-logo"><?php echo empty( get_option( 'login-logo' ) ) ? esc_html__( 'Upload image' , MWB_TEXTDOMAIN ) : ''; ?></button>
                            </div>
                        </div>
                        <!-- login-page-logo -->

                        <button type="submit" name="submit-style-settings" class="submit-style-settings top-btn"><?php echo esc_html__( 'Save' , MWB_TEXTDOMAIN ); ?></button>

                    </form>
                </div>

                <div class="notes">
                    <i>
                        <?php
                        echo sprintf( esc_html__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', MWB_TEXTDOMAIN ), sprintf( '<strong>%s</strong>', esc_html__( 'My Wp Brand', MWB_TEXTDOMAIN ) ), '<a href="https://wordpress.org/plugins/my-wp-brand/" target="_blank" class="is-rating-link" data-rated="' . esc_html__( 'Thanks :)', MWB_TEXTDOMAIN ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' ); 
                        ?>
                    </i>
                </div>

            <?php
            
        }

        /**
         * Remove Admin top bar default logo
         * @version 1.0.0
         */
        public function mwb_remove_admin_top_bar_default_logo() {

            if ( ! empty ( get_option( 'admin-bar-logo' ) ) ) :
                global $wp_admin_bar;
                $wp_admin_bar->remove_menu('wp-logo');
            endif;

        }

        /**
         * Manage Admin top bar menu
         * @version 1.0.0
         */
        public function mwb_manage_admin_top_bar_menu( $wp_admin_bar ) {

            $logo_url = get_option( 'admin-bar-logo' );

            if ( ! empty( $logo_url ) ):

                $site_url = site_url( '' );
                $args = array(
                    'id' => 'logo',
                    'title' => "<a class='mwb-custom-admin-logo' href='$site_url'><img class='mwb-admin-top-bar-logo' src='". esc_url( $logo_url ) ."' /></img></a>",
                );
                $wp_admin_bar->add_node( $args );

                if ( is_admin() ) :
                    echo '<style>';
                        echo '.mwb-custom-admin-logo{ padding: 0px !important; } .mwb-admin-top-bar-logo{position: relative!important; top: 2px; padding: 4px 0px!important; width: 20px!important; height: 20px!important; border-radius:50%!important;}';
                    echo '</style>';
                else :
                    echo '<style>';
                        echo '.mwb-custom-admin-logo{ display: flex !important; justify-content: center; align-items: center; padding: 0px!important; } .mwb-admin-top-bar-logo{ width: 20px!important; height: 20px!important; border-radius:50%!important; }';
                    echo '</style>';
                endif;

            endif;

        }

        /**
         * Manage wp login form 
         *  -> Generate css dynamic for login layout
         * @version 1.0.0
         */
        public function mwb_manage_login_form() {

            if ( ! empty ( get_option( 'login-logo' ) ) ) :
                $login_logo_url = get_option( 'login-logo' );
                echo "
                <style type='text/css'>
                #login h1 a, .login h1 a {
                    background-image: url(". esc_url( $login_logo_url ) .");
                    background-size: 84px;
                    background-position: center center;
                    background-repeat: no-repeat;
                    color: #3c434a;
                    height: 84px;
                    font-size: 20px;
                    font-weight: 400;
                    line-height: 1.3;
                    margin: 0 auto 25px;
                    padding: 0;
                    text-decoration: none;
                    width: 84px;
                    text-indent: -9999px;
                    outline: 0;
                    overflow: hidden;
                    display: block;
                    border-radius:50%;
                }
                </style>
                ";
            endif;

        }

        /**
         * mwb-author page callback function to render page
         * @version 1.0.0
         */
        public function mwb_author_page() {

            if( isset ( $_GET['update'] ) ) :
                if ( $_GET['update'] === 'true' || $_GET['update'] === true ) :
                    echo "<div class='updated notice notice-success is-dismissible'><p>". esc_html__( 'Your changes has been updated.' , MWB_TEXTDOMAIN ) ."</p></div>";
                endif;
            endif;

            ?>
                <!-- page-title -->
                    <h2 class="mwb-title"><?php echo esc_html__( 'Edit Author' , MWB_TEXTDOMAIN ); ?> </h2>
                <!-- page-title -->

                <!-- other-submenu-page-links -->
                <div class="mwb-btab">
                    <a href="<?php echo admin_url( 'options-general.php?page=mwb' ); ?> "><?php echo esc_html__( 'Hide Menus' , MWB_TEXTDOMAIN ); ?></a>
                    <a class="" href="<?php echo admin_url( 'options-general.php?page=mwb-plugins' ); ?> "><?php echo esc_html__( 'Hide Plugins' , MWB_TEXTDOMAIN ); ?></a>
                    <a href="<?php echo admin_url( 'options-general.php?page=mwb-style' ); ?> "><?php echo esc_html__( 'Edit Style' , MWB_TEXTDOMAIN ); ?></a>
                </div>
                <!-- other-submenu-page-links -->
                <div class="mwb-author">
                    <form method="post" id="mwb-author-form" >
                        <!-- hide-admin-wp-version -->
                        <div class="mwb-hide-admin-wp-version">
                            <label class="switch">
                                <input type="checkbox" id="wp-version-hide" <?php echo empty( get_option( 'wp-version-hide' ) ) ? 'checked' : ''; ?> <?php echo get_option( 'wp-version-hide' ) === 'on' ? 'checked' : '' ;?> />
                                <span class="slider round"></span>
                            </label>
                            <label><?php echo esc_html__( 'Hide admin wordpress version' , MWB_TEXTDOMAIN );?></label>
                        </div>
                        <!-- hide-admin-wp-version -->

                        <!-- change-wp-admin-footer-text -->
                        <div class="mwb-change-wp-admin-footer-text mt-15">
                            <label><?php echo esc_html__( 'Change admin footer text “Thank You for Creating with WordPress”' , MWB_TEXTDOMAIN );?></label>
                            <input type="text" id="wp-admin-footer-text" value="<?php echo get_option( 'wp-admin-footer-text' ) !== 'no' ? esc_html( get_option( 'wp-admin-footer-text' ) ) : ''; ?>" placeholder="Enter your text here..." />
                        </div>
                        <!-- change-wp-admin-footer-text -->

                        <!-- change-admin-bar-howdy-text -->
                        <div class="mwb-change-admin-bar-howdy-text mt-15">
                            <label><?php echo esc_html__( 'Change admin bar Howdy text' , MWB_TEXTDOMAIN );?></label>
                            <input type="text" id="wp-admin-bar-howdy-text" value="<?php echo get_option( 'wp-admin-bar-howdy-text' ) !== 'no' ? esc_html( get_option( 'wp-admin-bar-howdy-text' ) ) : ''; ?>" placeholder="Enter your text here..." />
                        </div>
                        <!-- change-admin-bar-howdy-text -->

                        <button type="submit" class="mt-15 top-btn"><?php echo esc_html__( 'Save' , MWB_TEXTDOMAIN );?></button>
                    </form>
                </div>

                <div class="notes">
                    <i>
                        <?php
                        echo sprintf( esc_html__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', MWB_TEXTDOMAIN ), sprintf( '<strong>%s</strong>', esc_html__( 'My Wp Brand', MWB_TEXTDOMAIN ) ), '<a href="https://wordpress.org/plugins/my-wp-brand/" target="_blank" class="is-rating-link" data-rated="' . esc_html__( 'Thanks :)', MWB_TEXTDOMAIN ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' ); 
                        ?>
                    </i>
                </div>
            <?php

        }

        /**
         * Change wordpress admin footer text
         * @version 1.0.0
         */
        public function mwb_change_admin_footer_text() {

            if ( get_option( 'wp-admin-footer-text' ) !== 'no' && ! empty( get_option( 'wp-admin-footer-text' ) ) ):
                echo esc_html( get_option( 'wp-admin-footer-text' ) );
            else : 
                echo esc_html__( 'Thank You for Creating with WordPress' , MWB_TEXTDOMAIN );
            endif;

        }

        /**
         * Change admin top bar howdy text
         * @version 1.0.0
         */
        public function mwb_change_howdy_text( $translated , $text , $domain ) {

            if ( ! is_admin() || 'default' != $domain) :
                return $translated;
            endif;
    
            if (false !== strpos( $translated, 'Howdy') && get_option( 'wp-admin-bar-howdy-text' ) !== 'no' && ! empty( get_option( 'wp-admin-bar-howdy-text' ) ) ) :
                return str_replace( 'Howdy', esc_html__( get_option( 'wp-admin-bar-howdy-text' ) , MWB_TEXTDOMAIN ), $translated) ;
            endif;
    
            return $translated;  
        }

        /**
         * Change login logo url
         * @return url website url
         */
        public function mwb_change_login_logo_url() {
            return home_url();
        }

        /**
         * @ajax 
         *  -> mwb plugins form
         * 
         * @since 1.0.0
         * @since 1.1.3 Added user capability verification and nonce verification.
         */
        public function mwb_plugins_form(){

            if ( ! current_user_can( 'manage_options' ) ) {
                exit;
            }

            if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'] , 'ajax-nonce' ) ) {
                die('The token has expired!');
            }

            if ( empty( get_option( 'hide-plugins' ) ) ) { 
                add_option( 'hide-plugins' , array_map( 'sanitize_text_field', $_POST['plugins'] ) );
            } else {
                if ( ! empty( $_POST['plugins'] ) ) : 
                    update_option( 'hide-plugins' , array_map( 'sanitize_text_field', $_POST['plugins'] ) );
                else :
                    update_option( 'hide-plugins' , 'no' );
                endif;
            }

        }

        /**
         * @ajax 
         *  -> mwb style form
         * 
         * @since 1.0.0
         * @since 1.1.3 Added user capability verification and nonce verification.
         */
        public function mwb_style_ajax() {

            if ( ! current_user_can( 'manage_options' ) ) {
                exit;
            }

            if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'] , 'ajax-nonce' ) ) {
                die('The token has expired!');
            }

            /**
             * Admin bar logo
             */
            if ( isset ( $_POST['hidden_admin_bar_logo'] ) && ! empty( esc_url( $_POST['hidden_admin_bar_logo'] ) ) ) : 
                if ( empty( get_option( 'admin-bar-logo' ) ) ):
                    add_option( 'admin-bar-logo' , esc_url_raw( $_POST['hidden_admin_bar_logo'] ) );
                else :
                    update_option( 'admin-bar-logo' , esc_url_raw( $_POST['hidden_admin_bar_logo'] ) );
                endif;
            endif;

            /**
             * Login page logo
             */
            if ( isset ( $_POST['hidden_login_logo'] ) && ! empty( esc_url( $_POST['hidden_login_logo'] ) ) ) : 
                if ( empty( get_option( 'login-logo' ) ) ):
                    add_option( 'login-logo' , esc_url_raw( $_POST['hidden_login_logo'] ) );
                else:
                    update_option( 'login-logo' , esc_url_raw( $_POST['hidden_login_logo'] ) );
                endif;
            endif;

        }

        /**
         * @ajax 
         *  -> mwb author form
         * 
         * @since 1.0.0
         * @since 1.1.3 Added user capability verification and nonce verification.
         */
        public function mwb_author_form() {

            if ( ! current_user_can( 'manage_options' ) ) {
                exit;
            }

            if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'] , 'ajax-nonce' ) ) {
                die('The token has expired!');
            }

            /**
             * Hide the WordPress version from the admin panel
             */
            if ( ! empty( $_POST['wp_version_hide'] ) ) :
                if ( empty ( get_option( 'wp-version-hide' ) ) ) :
                    add_option( 'wp-version-hide' , sanitize_text_field( $_POST['wp_version_hide'] ) );
                else :
                    update_option( 'wp-version-hide' , sanitize_text_field( $_POST['wp_version_hide'] ) );
                endif;
            elseif ( empty( $_POST['wp_version_hide'] ) ) :
                if ( empty ( get_option( 'wp-version-hide' ) ) ) :
                    add_option( 'wp-version-hide' , 'off' );
                else :
                    update_option( 'wp-version-hide' , 'off' );
                endif;
            endif;

            /**
             * Change WordPress admin footer text
             */
            if ( ! empty( $_POST['wp_admin_footer_text'] ) ) :
                if ( empty( get_option( 'wp-admin-footer-text' ) ) ) :
                    add_option( 'wp-admin-footer-text' , sanitize_text_field( $_POST['wp_admin_footer_text'] ) );
                else : 
                    update_option( 'wp-admin-footer-text' , sanitize_text_field( $_POST['wp_admin_footer_text'] ) );
                endif;
            elseif ( empty( $_POST['wp_admin_footer_text'] ) ) :
                if ( empty( get_option( 'wp-admin-footer-text' ) ) ) :
                    add_option( 'wp-admin-footer-text' , 'no' );
                else : 
                    update_option( 'wp-admin-footer-text' , 'no' );
                endif;
            endif;

            /**
             * Change WordPress admin bar howdy text
             */
            if ( ! empty( $_POST['wp_admin_bar_howdy_text'] ) ) :
                if ( empty( get_option( 'wp-admin-bar-howdy-text' ) ) ) :
                    add_option( 'wp-admin-bar-howdy-text' , sanitize_text_field( $_POST['wp_admin_bar_howdy_text'] ) );
                else : 
                    update_option( 'wp-admin-bar-howdy-text' , sanitize_text_field( $_POST['wp_admin_bar_howdy_text'] ) );
                endif;
            elseif ( empty( $_POST['wp_admin_bar_howdy_text'] ) ) :
                if ( empty( get_option( 'wp-admin-bar-howdy-text' ) ) ) :
                    add_option( 'wp-admin-bar-howdy-text' , 'no' );
                else : 
                    update_option( 'wp-admin-bar-howdy-text' , 'no' );
                endif;
            endif;

        }

    }

    new MWB_Brand();

    /**
     * Render hide side menu page template 
     * @version 1.0.0
     */
    require_once( plugin_dir_path(__FILE__) . 'mwb-side-menu.php' );

    /**
     * On uninstall plugin 
     * @version 1.0.0
     */
    function mwb_on_uninstall() {

        delete_option( 'hide_menu_bh_plugin' );

        delete_option( 'hide_sub_menu_bh_plugin' );

        delete_option( 'hide_top_menu_bh_plugin' );

        delete_option( 'menu_order_bh_plugin' );

        delete_option( 'hide-plugins' );

        delete_option( 'admin-bar-logo' );

        delete_option( 'login-logo' );

        delete_option( 'wp-version-hide' );

        delete_option( 'wp-admin-footer-text' );

        delete_option( 'wp-admin-bar-howdy-text' );

    }
    register_uninstall_hook( __FILE__ , 'mwb_on_uninstall' );

    /**
     * On activation plugin 
     * @version 1.0.0
     */
    function mwb_on_activation() {

        add_option( 'hide_menu_bh_plugin' , '' );

        add_option( 'hide_sub_menu_bh_plugin' , '' );

        add_option( 'hide_top_menu_bh_plugin' , '' );

        add_option( 'menu_order_bh_plugin' , '' );
        
    }
    register_activation_hook( __FILE__ , 'mwb_on_activation' );
endif;
?>