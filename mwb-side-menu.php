<?php

ob_start();

/** 
 * @uses Hide side menu page template 
 * @version 1.0.0
 */
function mwb_hide_side_menu_page() {
  
  global $wp_roles;
  global $wp_session;
  
  $roles = ! empty( $wp_roles->roles ) ? $wp_roles->roles : array();
  
  if ( isset( $_POST['save'] ) ) {

    /**
     * @uses Check administrator access required
     */
    if( current_user_can( 'administrator' ) ) {

      /**
       * @uses Check wpnonce
       */
      if( check_admin_referer('menu-remove' ) ) {
        
        /**
         * @var { $menu_list } Admin all menu list.
         * @var { $sub_menu_list } Admin all sub menu list.
         */
        $menu_list = isset( $_POST['menu_list'] ) ? sanitize_text_field( wp_json_encode( $_POST['menu_list'] ) ) : array();
        $sub_menu_list = isset( $_POST['sub_menu_list'] ) ? sanitize_text_field( wp_json_encode( $_POST['sub_menu_list'] ) ) : array();

        /**
         * Decode stringify json
         * @var { $menu_list, $sub_menu_list }
         */
        $menu_list = ! empty( $menu_list ) ? json_decode( $menu_list, true ) : array();
        $sub_menu_list = ! empty( $sub_menu_list ) ? json_decode( $sub_menu_list, true ) : array();

        /**
         * @uses Define array for side menu array
         */
        $new_menu_list = array(); 

        /**
         * @uses Define array for side sub menu array
         */
        $new_sub_menu_list = array(); 

        foreach ( $menu_list as $list_data_key => $list_data_role ) {

          /**
           * @uses Data sanitization functions used for valid text (text validation)
           */
          if ( ! empty( $list_data_role ) ) {
            
            $list_data_role = array_map( 'sanitize_text_field', $list_data_role );

            foreach ( $list_data_role as $list_data ) {
              /**
               * @uses Validate the data
               */
              if ( is_numeric( $list_data ) ) { 
                /**
                * @uses We check input will be only number then after we process further.
                */ 
                $new_menu_list[$list_data_key][] = $wp_session['all_side_menus'][$list_data][2];
              }
            }

          }

        }

        foreach ( $sub_menu_list as $sub_list_data_key => $sub_list_data_role ) {
          
          if ( ! empty( $sub_list_data_role ) ) {

            $sub_list_data_role = array_map( 'sanitize_text_field', $sub_list_data_role );
            
            foreach ( $sub_list_data_role as $sub_list_data ) {

              /**
               * @uses We get parent and child key 
               */ 
              $key_data   = explode( '_', $sub_list_data );
              $parent_key = $key_data[0];
              $child_key  = $key_data[1];
              
              /**
               * @uses We find parent of child
               */
              $parent_value = $wp_session['all_side_menus'][$parent_key][2];

              /**
               * @uses We check input will be only number then after we process further.
               */ 
              $new_sub_menu_list[$sub_list_data_key][] = $parent_value.'__con__'.$wp_session['all_side_sub_menus'][$parent_value][$child_key][2];

            }  

          }                            
          
        }

        $remove_side_array          = $new_menu_list;
        $remove_sub_side_array      = $new_sub_menu_list;
        $json_remove_side_array     = wp_json_encode( $remove_side_array ); 
        $json_remove_sub_side_array = wp_json_encode( $remove_sub_side_array );
        
        /**
         * @var { $top_menu_list } Admin all top menu list.
         */
        $top_menu_list = isset( $_POST['top_menu_list'] ) ? sanitize_text_field( wp_json_encode( $_POST['top_menu_list'] ) ) : array();
        
        /**
         * Decode stringify json
         * @var { $top_menu_list }
         */
        $top_menu_list = ! empty( $top_menu_list ) ? json_decode( $top_menu_list, true ) : array();

        /**
         * @uses Array define for top menu 
         */
        $new_menu_list = array();        

        foreach ( $top_menu_list as $list_data_key => $list_data_role ) {
            
          /**
           * @uses Data sanitization functions used for valid text (text validation)
           */
          if ( ! empty( $list_data_role ) ) {

            $list_data_role = array_map( 'sanitize_text_field', $list_data_role );

            foreach ($list_data_role as $list_data) {
              /**
               * @uses We check input will be only number then after we process further.
               */
              $new_menu_list[$list_data_key][] = $wp_session['all_top_menus'][$list_data]->id;
            }

          }
          
        }

        $remove_top_array      = $new_menu_list;
        $json_remove_top_array = wp_json_encode( $remove_top_array ); 
        
        /**
         * @uses Update the values
         */
        update_option( 'hide_menu_bh_plugin' , $json_remove_side_array );
        update_option( 'hide_top_menu_bh_plugin' , $json_remove_top_array );
        update_option( 'hide_sub_menu_bh_plugin' , $json_remove_sub_side_array );

        wp_redirect( '?page=mwb&message=save' );  

      } 

    }

  } 

  if( isset( $_POST['default'] ) ) :

    update_option( 'hide_menu_bh_plugin' , '' );
    update_option( 'hide_sub_menu_bh_plugin' , '' );
    update_option( 'hide_top_menu_bh_plugin' , '' );

    wp_redirect( '?page=mwb&message=default' );    

  endif;

  echo '<div class="wrap">';

    echo '
      <h2 class="mwb-title">' . esc_html__( 'Hide Menus' , MWB_TEXTDOMAIN ) . '</h2>
      <div>
        <br class="clear" />
      </div>
    ';

    /**
     * @uses Set message after form submit
     */
    if( isset( $_GET['message'] ) ) {

      $msg = '';

      if( $_GET['message'] == 'save' ) {
        echo $msg =  '<div id="message" class="updated notice notice-success is-dismissible"><p>'. esc_html__( 'Your changes has been updated.', MWB_TEXTDOMAIN ) .'</p></div>';
      }

      if( $_GET['message'] == 'default' ) {
        echo $msg =  '<div id="message" class="updated notice notice-success is-dismissible"><p>'. esc_html__( 'Your default setting has been setup.', MWB_TEXTDOMAIN ) .'</p></div>';
      }

    }

    /**
     * @uses Now we have to fetch all hide_menu_array from the db for side bar
     */
    $get_data = get_option( 'hide_menu_bh_plugin' );

    if( ! empty( $get_data ) ):
      $fetch_hide_menu_array = json_decode( $get_data );
    else:
      $fetch_hide_menu_array = array();
    endif;  

    /**
     * @uses Now we have to fetch all hide_sub_menu_array from the db for side bar
     */
    $get_data = get_option( 'hide_sub_menu_bh_plugin' );

    if( ! empty( $get_data ) ):
      
      $get_data2 = json_decode( $get_data );

      foreach ( $get_data2 as $role_key => $get_data2_role ) {  

        /**
         * @uses Now we remove the parent key
         */
        foreach ( $get_data2_role as $gets_data ) {
          $new_get_data                           = explode( '__con__', $gets_data );
          $fetch_hide_sub_menu_array[$role_key][] = $new_get_data['1']; 
        }

      }  

    else:
      $fetch_hide_sub_menu_array = array();
    endif;

    /**
     * now we have to fetch all hide_menu_array from the db for top bar
     */
    $get_data = get_option( 'hide_top_menu_bh_plugin' );

    if( ! empty( $get_data ) ):
      $fetch_hide_top_menu_array = json_decode( $get_data );
    else:
      $fetch_hide_top_menu_array = array();
    endif;

    echo '<div class="hide-menu-bg">';
      echo '<form method="post" id="mwb-menu-form" >';

      wp_nonce_field( 'menu-remove' );

      ?>

      <!-- other-submenu-page-links -->
      <div class="mwb-btab">
          <a class="" href="<?php echo admin_url( 'options-general.php?page=mwb-plugins' ); ?> "><?php echo esc_html__( 'Hide Plugins' , MWB_TEXTDOMAIN ); ?></a>
          <a href="<?php echo admin_url( 'options-general.php?page=mwb-author' ) ?>"><?php echo esc_html__( 'Edit Author' , MWB_TEXTDOMAIN ); ?></a>   
          <a href="<?php echo admin_url( 'options-general.php?page=mwb-style' ) ?>"><?php echo esc_html__( 'Edit Style' , MWB_TEXTDOMAIN ); ?></a>   
      </div>
      <!-- other-submenu-page-links -->

      <div class="wp-table-responsive">
        <table class='wp-list-table widefat fixed striped posts table-second-td'>
          <tr>
            <th>
              <b>
                <?php echo esc_html__( 'Menu of Side Bar' , MWB_TEXTDOMAIN ) ;?>
              </b>
            </th>
            <?php foreach ( $roles as $role ) : ?>
            <th>
              <b>
                <?php echo esc_html( $role['name'] ) ?>
              </b>
            </th>
            <?php endforeach; ?> 
          </tr>

      <?php    

          $all_menu     = ! empty( $wp_session['all_side_menus'] ) ? $wp_session['all_side_menus'] : array();
          $all_sub_menu = ! empty( $wp_session['all_side_sub_menus'] ) ? $wp_session['all_side_sub_menus'] : array();

          foreach ( $all_menu as $key => $row ) { 

            if( isset( $row['6'] ) && $row['6'] != '' ) {

              $sub_menu_array = isset( $all_sub_menu[$row['2']] ) ? $all_sub_menu[$row['2']] : array();

              /**
               * @uses check it is array or not
               */
              $sub_menu_array = isset( $sub_menu_array ) ? (array) $sub_menu_array : array();

              ?>

              <tr class='my_text'>

                <td class="primary_menu_seprator">
                  <span class="dashicons-before  <?php echo esc_attr( $row['6'] ); ?>"></span> 
                  <span><?php echo esc_attr( wp_strip_all_tags( $row['0'] ) ); ?></span>
                </td>

                <?php foreach ( $roles as $role_key => $role ) { ?>
                  <td class="primary_menu_seprator">
                    <input
                    <?php 
                      if( in_array( $row['2'], isset( $fetch_hide_menu_array->$role_key ) ? $fetch_hide_menu_array->$role_key : array() ) ) :
                        echo 'checked';
                      endif;
                    ?>
                    type="checkbox" name="menu_list[<?php echo esc_html( $role_key ); ?>][]" value="<?php echo esc_attr( $key ); ?>" />
                  </td>  
                <?php } ?>

              </tr>

              <?php

              /**
               * @uses Now we add the sub menu to parent menu
               */
              foreach ( $sub_menu_array as $keys => $rows ) {

                $fetch_hide_sub_menu_array = isset( $fetch_hide_sub_menu_array ) ?  $fetch_hide_sub_menu_array : array();

                ?>
                
                <tr class='my_text sub-menu'>
                    <td>
                      <span class="dashicons dashicons-arrow-right-alt sub-icon"></span> 
                      <span><?php echo esc_attr( wp_strip_all_tags( $rows['0'] ) ); ?></span>
                    </td>
                    <?php foreach ( $roles as $role_key=>$role ) { ?> 
                      <td>
                        <input
                          <?php 
                          if(in_array($rows['2'], isset($fetch_hide_sub_menu_array[$role_key]) ? $fetch_hide_sub_menu_array[$role_key] : array() )) {
                            echo 'checked'; 
                          } ?>
                          type="checkbox" name="sub_menu_list[<?php echo esc_html( $role_key ) ?>][]" value="<?php echo esc_attr( $key ); ?>_<?php echo esc_attr( $keys ); ?>" />
                      </td>  
                    <?php } ?>
                </tr>

                <?php

              }

            }
          } 

        echo '</table>';
      echo '</div>';
      echo '<br/>';
      
      ?>
        <div class="wp-table-responsive">
          <table class='wp-list-table widefat fixed striped posts table-second-td'>
            <tr>
              <th>
                <b>
                  <?php echo esc_html__( 'Menu of Top Bar' , MWB_TEXTDOMAIN ); ?>
                </b>
              </th>
              <?php
                foreach ($roles as $role) {
                  ?>
                    <th >
                      <b>
                        <?php echo esc_html( $role['name'] ); ?>
                      </b>
                    </th>
                  <?php
                }
              ?> 
            </tr>
      <?php

            $all_menu        = ! empty( $wp_session['all_top_menus'] ) ? $wp_session['all_top_menus'] : array();
            $all_parent_menu = array();
            $all_child_menu  = array();

            foreach ( $all_menu as $key => $value ) {
              if( $value->title != '' && $key != 'menu-toggle' ) {
                if( $value->parent != '' && $value->parent != 'top-secondary' ) {
                  $all_child_menu[$key] = $value;
                } else {
                  if( $value->parent != 'wp-logo-external' ) {
                    $all_parent_menu[$key] = $value;
                  }
                }
              }
            }

            foreach ( $all_child_menu as $key => $value ) {
              if( isset( $all_parent_menu[$value->parent] ) ) {
                $all_parent_menu[$value->parent]->child_menu[] = $value;
              } else {
                if( $value->parent == 'wp-logo-external' ) {
                  $all_parent_menu['wp-logo']->child_menu[] = $value;
                } else if( $value->parent == 'user-actions' ) {
                  $value->parent = 'my-account';
                  $all_parent_menu['my-account']->child_menu[] = $value;   
                } else {
                  $all_parent_menu[$value->parent] = $value;   
                }
              }
            }

            foreach ( $all_parent_menu as $row ) { 

              if( $row->title != '' ) {

                ?>

                <tr class='my_text'>
                  <td>
                    <span id="wp-admin-bar-<?php echo esc_attr( $row->id ); ?>"></span> 
                    <span># <?php echo esc_attr( wp_strip_all_tags( $row->title ) ); ?></span>
                  </td>
                  <?php foreach ( $roles as $role_key => $role ) { ?>  
                    <td>
                      <input
                        <?php if( in_array( $row->id, isset( $fetch_hide_top_menu_array->$role_key ) ? $fetch_hide_top_menu_array->$role_key : array() )) {
                          echo 'checked';
                        } ?>
                        type="checkbox" name="top_menu_list[<?php echo esc_html( $role_key ) ?>][]" value="<?php echo esc_attr( $row->id ); ?>">
                    </td>  
                  <?php } ?>
                </tr>

                <?php

                if( isset( $row->child_menu ) ) {
                  foreach ( $row->child_menu as $child_menu ) {
                    if( $child_menu->title != '' ) {
                      ?>
                        <tr class='my_text'>
                          <td>
                            <span id="wp-admin-bar-<?php echo esc_attr( $child_menu->id ); ?>"></span> 
                            <span class="dashicons dashicons-arrow-right-alt sub-icon"></span>
                            <span> <?php echo esc_attr( wp_strip_all_tags( $child_menu->title ) ); ?></span>
                          </td>
                          <?php foreach ( $roles as $role_key=>$role ) { ?> 
                            <td>
                              <input
                                <?php if( in_array( $child_menu->id, isset( $fetch_hide_top_menu_array->$role_key ) ? $fetch_hide_top_menu_array->$role_key : array() )) { 
                                  echo 'checked'; 
                                } ?>
                                type="checkbox" name="top_menu_list[<?php echo esc_html( $role_key ) ?>][]" value="<?php echo esc_attr( $child_menu->id ); ?>" />
                            </td>
                          <?php } ?> 
                        </tr>
                      <?php   
                    }
                  }
                }
              }
            }

            ?>
          </table>
        </div>

        <br/>

        <div>
            <input name="page" type="hidden" value="social-option-2" />
            <input name="save" type="submit" class="btn top-btn" id="publish" value="Save" />
            <input name="default" class="hm-default-btn" type="submit" value="Set Default Setting" />
        </div>

        <div class="notes">
          <i><?php echo esc_html__( 'Hide menu according the role of user. Notes: This plugin will not able to show those menu which are not access by sub role of user according WordPress or other plugin. But it can hide those which was shown by default.', MWB_TEXTDOMAIN );?></i>
        </div>

        <div class="notes">
          <i>
            <?php
              echo sprintf( esc_html__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', MWB_TEXTDOMAIN ), sprintf( '<strong>%s</strong>', esc_html__( 'My Wp Brand', MWB_TEXTDOMAIN ) ), '<a href="https://wordpress.org/plugins/my-wp-brand/" target="_blank" class="is-rating-link" data-rated="' . esc_html__( 'Thanks :)', MWB_TEXTDOMAIN ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' ); 
            ?>
          </i>
        </div>

      </form>
    </div>
  </div>

  <div class="clear"></div>

  <?php

}

/**
 * @uses Remove menu from the admin at top bar
 * @version 1.0.0
 */
function mwb_custom_top_menu_page_removing() {

  global $wp_session;
  global $wp_admin_bar;

  $login_user                   = wp_get_current_user();
  $login_user_roles             = (array) $login_user->roles;
  $wp_session['all_top_menus']  = $wp_admin_bar->get_nodes();
  $all_menu                     = $wp_session['all_top_menus'];

  /**
   * @uses Now we have to fetch all hide_menu_array from the db
   */
  $get_data = get_option( 'hide_top_menu_bh_plugin' );

  if( ! empty( $get_data ) ) :
    $fetch_hide_menu_array = json_decode( $get_data );
  else:
    $fetch_hide_menu_array = array();
  endif;

  foreach ( $fetch_hide_menu_array as $role_key => $hide_menu_array_role ) {
    if( in_array( $role_key, $login_user_roles ) ) {
      foreach ( $hide_menu_array_role as $hide_menu_array ) {
        $wp_admin_bar->remove_node( $hide_menu_array );
      }
    }
  }

}
add_action( 'admin_bar_menu', 'mwb_custom_top_menu_page_removing', '9999' );

/**
 * @uses Remove menu from the admin at side bar
 * @version 1.0.0
 */
function mwb_custom_side_menu_page_removing() {

  global $menu;
  global $submenu;
  global $wp_session;

  $login_user                        = wp_get_current_user();
  $login_user_roles                  = (array) $login_user->roles;
  $wp_session['all_side_menus']      = $menu;
  $wp_session['all_side_sub_menus']  = $submenu;
  $all_menu                          = $wp_session['all_side_menus'];

  /**
   * @uses Now we have to fetch all hide_menu_array from the db
   */
  $get_data = get_option( 'hide_menu_bh_plugin' );

  if( ! empty( $get_data ) ) : 
    $fetch_hide_menu_array = json_decode( $get_data );
  else:
    $fetch_hide_menu_array = array();
  endif;

  /**
   * @uses Now fetch sub menu data
   */
  $get_data = get_option( 'hide_sub_menu_bh_plugin' ); 

  if( ! empty( $get_data ) ) :
    $fetch_hide_sub_menu_array = json_decode( $get_data );
  else:
    $fetch_hide_sub_menu_array = array();
  endif;

  foreach ( $fetch_hide_menu_array as $role_key => $hide_menu_array_role ) {
    if( in_array( $role_key, $login_user_roles ) ) {
      foreach ( $hide_menu_array_role as $hide_menu_array ) {
        remove_menu_page( $hide_menu_array );
      }
    }
  }

  foreach ( $fetch_hide_sub_menu_array as $role_key => $hide_menu_array ) {

    /**
     * @uses Now we ge the parent key and child key
     */
    if( in_array( $role_key, $login_user_roles ) ) {
      foreach ( $hide_menu_array as $hide_menu_role_array ) {
        $pare_child = explode( '__con__', $hide_menu_role_array );
        //this is the patch for the wordpress 4 or may be latetest version for the customize menu only
        if( $pare_child[0] == 'themes.php' ) {
          $parse_data = wp_parse_url( $pare_child[1] );
          if( $parse_data['path'] == 'customize.php' ) {
            unset( $submenu['themes.php'][6] );
          }
        }
        remove_submenu_page( $pare_child[0], $pare_child[1] );
      }
    }

  }

}
add_action( 'admin_menu', 'mwb_custom_side_menu_page_removing', '9999' );
