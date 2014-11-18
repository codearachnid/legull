<?php
class Legull_CustomPostType extends AdminPageFramework_PostType {
    
    public function start() {    

        $this->setPostTypeArgs(
            array(
                'labels' => array(
                    'name' => __('Legal Documents', 'legull'),
                    'all_items'     => __( 'All Documents', 'legull' ),
                    'singular_name' => 'Legal Document',
                    'add_new' => __( 'Add New', 'legull' ),
                    'add_new_item' => __( 'Add New APF Post', 'legull' ),
                    'edit' => __( 'Edit', 'legull' ),
                    'edit_item' => __( 'Edit Document', 'legull' ),
                    'new_item' => __( 'New Document', 'legull' ),
                    'view' => __( 'View', 'legull' ),
                    'view_item' => __( 'View Document', 'legull' ),
                    'search_items' => __( 'Search Documents', 'legull' ),
                    'not_found' => __( 'No documents generated. Please complete the <a href="admin.php?page=legull_dashboard">setup</a> and generate documents first.', 'legull' ),
                    'not_found_in_trash' => __( 'No document found in Trash', 'legull' ),
                    'parent' => __( 'Parent Document', 'legull' ),
                    'plugin_listing_table_title_cell_link' => __( 'Documents', 'legull' ),
                ),
                'public' =>    true,
                'rewrite' => array( 'slug' => 'legal' ),
                'menu_position'     => 110,
                'supports' => array( 'title', 'editor' ),
                'taxonomies' => array( '' ),
                'has_archive' =>    true,
                'show_admin_column' =>    true,
                'menu_icon' => LEGULL_URL . '/asset/icon-32.png',
                'screen_icon' => LEGULL_URL . '/asset/icon-32.png',
            )    
        );

        add_action( 'admin_head', array( $this, 'admin_head' ) );
        add_action( 'admin_menu', array( $this, 'admin_menue' ) );
        
        $this->addTaxonomy( 
            'legull_packages',
            array( 
                'labels' => array(
                    'name' => 'Packages',
                    'add_new_item' => 'Add New Package',
                    'new_item_name' => "New Package"
                ),
                'show_ui' =>    true,
                'show_tagcloud' => false,
                'hierarchical' =>    true,
                'show_admin_column' =>    true,
                'show_in_nav_menus' =>    false,
                'show_table_filter' =>    true,
                'show_in_sidebar_menus' =>    false,
            )
        );
                
    }
    function admin_head(){
        // show submenue and clear unneeded filters
        $screen = get_current_screen();
        if( in_array( $screen->id, array( 'edit-' . LEGULL_CPT, LEGULL_CPT ))){
            ?><script>
                jQuery(document).ready(function(){
                    var legullMenu = jQuery('li#toplevel_page_Legull');
                    legullMenu.addClass('wp-menu-open wp-has-current-submenu').removeClass('wp-not-current-submenu');
                    legullMenu.find('> a.menu-top-last').addClass('wp-menu-open wp-has-current-submenu').removeClass('wp-not-current-submenu');
                    legullMenu.find('.wp-submenu li').removeClass('current').eq(2).addClass('current');
                });
            </script><?php
        }
        // print_r($screen);
    }
    function admin_menue(){
        add_submenu_page( 'admin.php?page=legull_dashboard', 'Documents', 'Documents', 'manage_options', "edit.php?post_type={$this->oProp->sPostType}" );
        remove_menu_page( "edit.php?post_type={$this->oProp->sPostType}" );
    }
    
    /**
     * Automatically called with the 'wp_loaded' hook.
     */
    public function setUp() {

        if ( $this->oProp->bIsAdmin ) {
                
            $this->setAutoSave( false );
            $this->setAuthorTableFilter( true );     
            $this->setFooterInfoLeft( '<br />Custom Text on the left hand side.' );
            $this->setFooterInfoRight( '<br />Custom text on the right hand side' );     
            add_filter( 'request', array( $this, 'replyToSortCustomColumn' ) );
            
        }    
        
    }
    
    /*
     * Built-in callback methods
     */
    public function columns_apf_posts( $aHeaderColumns ) { // columns_{post type slug}
        
        return array_merge( 
            $aHeaderColumns,
            array(
                'cb' => '<input type="checkbox" />', // Checkbox for bulk actions. 
                'title' => __( 'Title', 'legull' ), // Post title. Includes "edit", "quick edit", "trash" and "view" links. If $mode (set from $_REQUEST['mode']) is 'excerpt', a post excerpt is included between the title and links.
                'date' => __( 'Date', 'legull' ),     // The date and publish status of the post. 
                'doc_status' => __( 'Status', 'legull' ),
            )     
        );
        
    }
    public function sortable_columns_apf_posts( $aSortableHeaderColumns ) { // sortable_columns_{post type slug}
        return $aSortableHeaderColumns + array(
            'doc_status' => 'doc_status',
        );
    }    
    public function cell_apf_posts_doc_status( $sCell, $iPostID ) { // cell_{post type}_{column key}
        
        return sprintf( __( 'Post ID: %1$s', 'legull' ), $iPostID ) . "<br />"
            . __( 'Text', 'legull' ) . ': ' . get_post_meta( $iPostID, 'metabox_text_field', true );
        
    }
    
    /**
     * Custom callback methods
     */
    
    /**
     * Modifies the way how the sample column is sorted. This makes it sorted by post ID.
     * 
     * @see http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
     */
    public function replyToSortCustomColumn( $aVars ){

        if ( isset( $aVars['orderby'] ) && 'doc_status' == $aVars['orderby'] ){
            $aVars = array_merge( 
                $aVars, 
                array(
                    'meta_key' => 'metabox_text_field',
                    'orderby' => 'meta_value',
                )
            );
        }
        return $aVars;
    }    
    
    /**
     * Modifies the output of the post content.
     * 
     * This method is called in the single page of this class post type.
     * 
     * Alternatively, you may use the 'content_{instantiated class name}' method,
     */
    // public function content( $sContent ) { 
                 
    //     if( WP_DEBUG ){
    //          $_iPostID   = $GLOBALS['post']->ID;
    //         $_aPostData = array();
    //         foreach( ( array ) get_post_custom_keys( $_iPostID ) as $sKey ) {    // This way, array will be unserialized; easier to view.
    //             $_aPostData[ $sKey ] = get_post_meta( $_iPostID, $sKey, true );
    //         }    
    //         $_aSavedOptions = get_option( 'APF_Demo' );
    //         $sContent .= "<h3>" . __( 'Saved Meta Field Values', 'legull' ) . "</h3>" 
    //         . $this->oDebug->getArray( $_aPostData )
    //         . "<h3>" . __( 'Saved Setting Options', 'legull' ) . "</h3>" 
    //         . $this->oDebug->getArray( $_aSavedOptions );
    //     }
            
    //     return $sContent;

    // }    
    
}