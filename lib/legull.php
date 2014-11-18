<?php

class Legull extends AdminPageFramework {
	function custom_admin_notices(){
		$_sID = md5( trim( 'The options have been updated.' ) );
		$_iUserID  = get_current_user_id();
        $_aNotices = $this->oUtil->getTransient( "apf_notices_{$_iUserID}" );
		if( isset( $_aNotices[ $_sID ] ) ){
			$_aNotices[ $_sID ]['sMessage'] .= ' <h2><a href="#" onclick="legull_ajax_generate_docs(event);" class="legull_ajax_generate_docs add-new-h2">' . __('Generate Documents', 'legull') . '</a></h2>';
			set_transient( "apf_notices_{$_iUserID}", $_aNotices );
		}
	}
	function setUp() {

		// add_action( 'admin_notices', array( $this, 'custom_admin_notices' ), 99 );
		if ( is_network_admin() ) {
            add_action( 'network_admin_notices', array( $this, 'custom_admin_notices' ), 5 );
        } else {
            add_action( 'admin_notices', array( $this, 'custom_admin_notices' ), 5 );
        }
		

	    $this->setRootMenuPage( __( 'Legull', 'legull' ), 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDUxMiA1MTIiIGhlaWdodD0iNTEycHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiB3aWR0aD0iNTEycHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxwYXRoIGQ9Ik00MDcuNjU4LDI1Ny4xOTFDNDQyLjA0NSwyMTEuODM4LDQzOC4yOTcsMTUxLjk3LDQ4MSw1My4yMzJDMjU0LjQwNyw1MC4yNDQsMTU2LjE2MywyMDYuNTQsMTE0LjM2MiwzNjMuMzcgIEwzMSw0NTguODExbDU5LjM4OS0xNi41NDZsNDcuODMzLTYzLjc4NmMxMzAuMzYzLTE2LjYzMSwyMzguMTc3LTg4Ljg3MywyMzguMTc3LTg4Ljg3M2wtNzAuMTU2LTUxLjQ5NyAgQzMwNi4yNDIsMjM4LjEwOCwzNjAuOTgsMjM0LjE5LDQwNy42NTgsMjU3LjE5MXogTTE3MC43NjQsMzI5Ljk0bC0xLjE5OC0xLjMyNGMxOC41ODktMjkuMjAxLDExOC43NS0xNzguOTI5LDI1NC4wNDYtMjM3LjY5OSAgQzM1OS4yMDUsMTI0Ljk2NywyMjUuNjg2LDI1OC44MjYsMTcwLjc2NCwzMjkuOTR6IiBmaWxsPSIjNEQ0RDREIi8+PC9zdmc+' );
	    $this->addSubMenuItems(
	    	array(
	    		'title' => __( 'Getting Started', 'legull' ),
	            'page_slug' => 'legull_dashboard',
	            'order' => 10
	    		),
	    	array(
	    		'title' => __( 'Documents', 'legull' ),
	            'href' => get_admin_url() . 'edit.php?post_type=' . LEGULL_CPT,
	            'order' => 15
	    		),
	    	array(
	            'title' => __( 'Settings', 'legull' ),
	            'page_slug' => 'legull_settings',
	            'order' => 20
	        ),
	        array(
	            'title' => __( 'Add-ons', 'legull' ),
	            'page_slug' => 'legull_addons',
	            'order' => 30
	        )
	    );	

	    $this->addSettingSections(	
			'legull_settings',
			array(
				'section_id'	=>	'ownership',
				'section_tab_slug'	=>	'settings_tabbed_sections',
				'title'			=>	__( 'Ownership', 'legull' ),
				'description'	=>	__( 'This is the first item of the tabbed section.', 'legull' ),
			),
			array(
				'section_id'	=>	'advertising',
				'title'			=>	__( 'Advertising', 'legull' ),
				'description'	=>	__( 'This is the first item of the tabbed section.', 'legull' ),
			),
			array(
				'section_id'	=>	'tracking',
				'title'			=>	__( 'Tracking & Collection', 'legull' ),
				'description'	=>	__( 'This is the first item of the tabbed section.', 'legull' ),
			),
			array(
				'section_id'	=>	'misc',
				'title'			=>	__( 'Misc', 'legull' ),
				'description'	=>	__( 'This is the first item of the tabbed section.', 'legull' ),
			)
		);

	    $this->addSettingFields(
	    	array(
				'field_id'	=>	'last_updated',
				'type'	=>	'hidden',
				'value'	=>	current_time( 'timestamp' )
			),
	    	array(
				'section_id'	=>	'ownership',
				'field_id'	=>	'siteurl',
				'title'	=>	__( 'Site Address (URL)', 'legull' ),
				'type'	=>	'text',
				'attributes'	=>	array(
					'size'	=>	20,
					'readonly'	=>	'ReadOnly'
				),
				'help'	=>	__( 'What is the URL (web address) of the site these terms will be applied? This value is driven by the <code>Site Address (URL)</code> in Settings > General.', 'legull' ),
				'value'	=>	get_option('siteurl'),
				'description'	=>	__( 'What is the URL (web address) of the site these terms will be applied?', 'legull' ),
			),	
			array(
				'field_id'	=>	'sitename',
				'title'	=>	__( 'Site Name', 'legull' ),
				'type'	=>	'text',
				// 'help'	=>	__( 'What is the URL (web address) of the site these terms will be applied? This value is driven by the <code>Site Address (URL)</code> in Settings > General.', 'legull' ),
				'value'	=>	get_option('siteurl'),
				'description'	=>	__( 'What is the URL (web address) of the site these terms will be applied?', 'legull' ),
			),	
			array(
				'field_id'	=>	'owner_name',
				'title'	=>	__( 'Owner Name', 'legull' ),
				'type'	=>	'text',
				'description'	=>	__( 'What is the name of the site owner or responsible legal party?', 'legull' ),
			),
			array(
				'field_id'	=>	'owner_email',
				'title'	=>	__( 'Owner Email', 'legull' ),
				'type'	=>	'text',
				'description'	=>	__( 'What is the contact email of the site owner or responsible legal party?', 'legull' ),
			),
			array(	// Multiple text fields
				'field_id'	=>	'owner_locality',
				'title'	=>	__( 'Physical Address', 'admin-page-framework-demo' ),
				'type'	=>	'text',			
				'description'	=>	__( 'Set the legal physical locality for the site. (i.e. City, State/Provence)', 'legull' ),
			),
			array(
				'field_id'	=>	'entity_type',
				'title'	=>	__( 'Entity Type', 'legull' ),
				'type'	=>	'select',
				'help'	=>	__( 'Is the owner an individual person, or business entity?', 'legull' ),
				'default'	=>	'individual',
				'label'	=>	array( 
					'individual'	=>	'Individual',		
					'corp'	=>	'Corporation',
					'llc'	=>	'Limited Liability Company (LLC)',
					'partner'	=>	'Partnership',
					'sole' => 'Sole Proprietor'
				),
				'description'	=>	__( 'Is the owner an individual person, or business entity?', 'legull' ),
			),
			array(
				'section_id'	=>	'advertising',
				'field_id'	=>	'has_advertising',
				'title'	=>	__( 'Site contains advertising', 'legull' ),
				'tip'	=>	__( 'The description key can be omitted though.', 'legull' ),
				'description'	=>	__( 'Does this site use advertising?', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'field_id'	=>	'has_advertising_network',
				'title'	=>	__( '3rd party advertising', 'legull' ),
				'tip'	=>	__( 'The description key can be omitted though.', 'legull' ),
				'description'	=>	__( 'Will this site use a 3rd party network to supply advertising?', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'field_id'	=>	'has_advertising_adsense',
				'title'	=>	__( 'Google AdSense', 'legull' ),
				'tip'	=>	__( 'The description key can be omitted though.', 'legull' ),
				'description'	=>	__( 'Will this site use Google AdSense to supply advertising?', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'section_id'	=>	'tracking',
				'field_id'	=>	'has_cookies',
				'title'	=>	__( 'Use cookies', 'legull' ),
				'description'	=>	__( 'Will this site use cookies beyond advertising tools? (i.e. Google Analytics)', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'field_id'	=>	'has_info_track',
				'title'	=>	__( 'Information Tracking', 'legull' ),
				'description'	=>	__( 'Will visitors be tracked when surfing the site? (i.e. Google Analytics)', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),			
			array(
				'field_id'	=>	'has_personalization',
				'title'	=>	__( 'User Personalization', 'legull' ),
				'description'	=>	__( 'Will visitors be able to personalize their expereience when surfing the site?', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'field_id'	=>	'has_anonymous',
				'title'	=>	__( 'Anonymous Surfing', 'legull' ),
				'description'	=>	__( 'Will visitors be able to surf the site anonymously?', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'field_id'	=>	'has_purchased_data',
				'title'	=>	__( 'Purchase User Data', 'legull' ),
				'tip'	=>	__( 'The description key can be omitted though.', 'legull' ),
				'description'	=>	__( 'Does this site purchase user data?', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'field_id'	=>	'has_data_buyer',
				'title'	=>	__( 'Sell User Data', 'legull' ),
				'tip'	=>	__( 'The description key can be omitted though.', 'legull' ),
				'description'	=>	__( 'Does this site sell or rent user data?', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'section_id' => 'misc',
				'field_id'	=>	'has_over18',
				'title'	=>	__( 'Over 18', 'legull' ),
				'tip'	=>	__( 'The description key can be omitted though.', 'legull' ),
				'description'	=>	__( 'Does this site require visitors to be over the age of 18?', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'field_id'	=>	'has_arbitration',
				'title'	=>	__( 'Arbitration', 'legull' ),
				'tip'	=>	__( 'The description key can be omitted though.', 'legull' ),
				'description'	=>	__( 'Does this site require an arbitration clause?', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			),
			array(
				'field_id'	=>	'has_SSL',
				'title'	=>	__( 'SSL', 'legull' ),
				'tip'	=>	__( 'The description key can be omitted though.', 'legull' ),
				'description'	=>	__( 'Does this site use SSL? (https://)', 'legull' ),	//' syntax fixer
				'type'	=>	'checkbox',
				'label'	=>	__( 'YES', 'legull' ),
				'default'	=>	false,
			)

		);

	}

	

	public function do_legull_dashboard() {
	    include LEGULL_PATH . 'template/dashboard.php';
	}

	public function do_legull_settings() {
		submit_button();
	}

	public function do_legull_addons() {
		include LEGULL_PATH . 'template/addons.php';
	}
}
