<?php

class Legull extends AdminPageFramework {

	function custom_admin_notices() {
		$_sID      = md5( trim( 'The options have been updated.' ) );
		$_iUserID  = get_current_user_id();
		$_aNotices = $this->oUtil->getTransient( "apf_notices_{$_iUserID}" );
		if ( isset( $_aNotices[$_sID] ) ) {
			$screen = get_current_screen();
			// is edit list notify user docs have been generated
			if ( $screen->id == 'edit-' . LEGULL_CPT ) {
				$_aNotices[$_sID]['sMessage'] = __( 'Your site terms have been generated.', 'legull' );
			} else {
				$_aNotices[$_sID]['sMessage'] = __( 'Your site details have been saved.', 'legull' );
			}
			set_transient( "apf_notices_{$_iUserID}", $_aNotices );
		}
	}

	function onSubmit_redirects( $aNewInput ) {
		$redirect_to = '';
		$aErrors = array();
		if ( !empty( $this->oForm->sCurrentPageSlug ) ) {
			switch ( $this->oForm->sCurrentPageSlug ) {
				case 'legull_dashboard': // submitting the site details

					if ( empty( $aNewInput['ownership']['sitename'] ) ) {
						$aErrors['sitename'] = __( 'The site name may not be left blank.', 'legull' );
					}
					if ( empty( $aNewInput['ownership']['owner_name'] ) ) {
						$aErrors['owner_name'] = __( 'The site owner may not be left blank.', 'legull' );
					}

					// validated and redirect
					if ( empty( $aErrors ) ) {
						$redirect_to = get_admin_url() . 'admin.php?page=legull_generate';
					}
					break;
				case 'legull_generate': // generate terms
					if( legull_generate_terms_to_import() ){
						$redirect_to = get_admin_url() . 'edit.php?post_type=' . LEGULL_CPT;
					} else {
						$aErrors['legull_generate'] = __( 'The terms were not generated due to errors.', 'legull' );
					}
					break;
			}
		}
		if ( !empty( $redirect_to ) ) {
			// validated and redirect
			exit( wp_redirect( $redirect_to ) );
		} else {
			$this->setFieldErrors( $aErrors );
			$this->setSettingNotice( 'There was an error in your site details.' );
			if ( is_network_admin() ) {
				remove_action( 'network_admin_notices', array( $this, 'custom_admin_notices' ), 5 );
			} else {
				remove_action( 'admin_notices', array( $this, 'custom_admin_notices' ), 5 );
			}
		}
	}

	function setUp() {

		// wire up the revealer fields
		if( class_exists('RevealerCustomFieldType'))
			new RevealerCustomFieldType( __CLASS__ );

		// after saving details redirect to generation page
		add_action( 'submit_after_Legull', array( $this, 'onSubmit_redirects' ) );

		if ( is_network_admin() ) {
			add_action( 'network_admin_notices', array( $this, 'custom_admin_notices' ), 5 );
		} else {
			add_action( 'admin_notices', array( $this, 'custom_admin_notices' ), 5 );
		}

		$this->setRootMenuPage( __( 'Legull', 'legull' ), legull_icon( 20, true ) );
		$this->addSubMenuItems(
			array(
				'title'     => __( 'Getting Started', 'legull' ),
				'page_slug' => 'legull_dashboard',
				'order'     => 10
			),
			array(
				'title'     => __( 'Generate', 'legull' ),
				'page_slug' => 'legull_generate',
				'order'     => 20,
			),
			array(
				'title' => __( 'Terms', 'legull' ),
				'href'  => get_admin_url() . 'edit.php?post_type=' . LEGULL_CPT,
				'order' => 30
			),
			array(
				'title'     => __( 'Add-ons', 'legull' ),
				'page_slug' => 'legull_addons',
				'order'     => 100
			)
		);

	}

	public function load_Legull( $oAdminPage ) {

		$oAdminPage->addSettingSections(
			'legull_dashboard',
			array(
				'section_id'       => 'ownership',
				'section_tab_slug' => 'settings_tabbed_sections',
				'title'            => __( 'Ownership', 'legull' ),
				'description'      => __( 'Tell this site\'s users who owns the site, and provide a few basic details.', 'legull' ),
			),
			array(
				'section_id'  => 'tracking',
				'title'       => __( 'Data &amp; Privacy', 'legull' ),
				'description' => __( 'Explain how this site monitors its users, and what data it collects.', 'legull' ),
			),
			array(
				'section_id'  => 'usercontent',
				'title'       => __( 'User-Generated &amp; DMCA', 'legull' ),
				'description' => __( 'Explain the site\'s policies concerning the DMCA and user-generated content practices.', 'legull' ),
			),
			array(
				'section_id'  => 'advertising',
				'title'       => __( 'Advertising', 'legull' ),
				'description' => __( 'Help the site\'s visitors understand its advertising practices.', 'legull' ),
			),
			array(
				'section_id'  => 'misc',
				'title'       => __( 'Misc', 'legull' ),
				'description' => __( 'Inform this site\'s users about a few more general topics and terms.', 'legull' ),
			)
		);

		$oAdminPage->addSettingFields(
			'ownership',
			array(
				'field_id'    => 'siteurl',
				'title'       => __( 'Site Address (URL)', 'legull' ),
				'type'        => 'text',
				'attributes'  => array(
					'size'     => 20,
					'readonly' => 'ReadOnly'
				),
				'value'       => get_option( 'siteurl' ),
				'description' => __( 'What is the URL (web address) of the site these terms will be applied?', 'legull' ),
			),
			array(
				'field_id'    => 'sitename',
				'title'       => __( 'Site Name', 'legull' ),
				'type'        => 'text',
				'value'       => get_option( 'blogname' ),
				'description' => __( 'What is the URL (web address) of the site these terms will be applied?', 'legull' ),
			),
			array(
				'field_id'    => 'owner_name',
				'title'       => __( 'Owner Name', 'legull' ),
				'type'        => 'text',
				'description' => __( 'What is the name of the site owner or responsible legal party?', 'legull' ),
			),
			array(
				'field_id'    => 'owner_email',
				'title'       => __( 'Owner Email', 'legull' ),
				'type'        => 'text',
				'description' => __( 'What is the contact email of the site owner or responsible legal party?', 'legull' ),
			),
			array(
				'field_id'    => 'owner_locality',
				'title'       => __( 'Physical Address', 'legull' ),
				'type'        => 'text',
				'description' => __( 'Set the legal physical locality for the site. (i.e. City, State/Provence)', 'legull' ),
			),
			array(
				'field_id'    => 'has_california',
				'title'       => __( 'In California?', 'legull' ),
				'description' => __( 'Is site locality within the state of California?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'entity_type',
				'title'       => __( 'Entity Type', 'legull' ),
				'type'        => 'select',
				'default'     => 'individual',
				'label'       => array(
					'individual' => 'Individual',
					'corp'       => 'Corporation',
					'llc'        => 'Limited Liability Company (LLC)',
					'partner'    => 'Partnership',
					'sole'       => 'Sole Proprietor'
				),
				'description' => __( 'Is the owner an individual person, or business entity?', 'legull' ),
			)
		);
		$oAdminPage->addSettingFields(
			'usercontent',
			array(
				'field_id'    => 'has_usergenerated',
				'title'       => __( 'User-generated content', 'legull' ),
				'description' => __( 'Will this site allow user-generated content of any kind?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_3p_content',
				'title'       => __( 'Comments &amp; 3rd Parties', 'legull' ),
				'description' => __( 'Will this site allow users to add comments or content of any kind?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_DMCA_agent',
				'type'        => 'revealer',
				'select_type'   => 'checkbox',
				'title'       => __( 'Has DMCA Agent?', 'legull' ),
				'description' => __( 'In the U.S., safe harbor protection from copyright liability for site content added by your users can be had by designating and registering with the Copyright Office a Digital Millenium Copyright Act agent for notice and takedown procedures. Will this site have a designated DMCA agent?', 'legull' ),
				'label'         => array(
                    '#fieldrow-usercontent_DMCA_address,#fieldrow-usercontent_DMCA_telephone,#fieldrow-usercontent_DMCA_email' => __( 'YES', 'legull' )
                ),
			),
			array(
				'field_id'    => 'DMCA_address',
				'title'       => __( 'DMCA Address', 'legull' ),
				'type'        => 'text',
				'description' => __( 'What will be the postal mailing address of your DMCA agent?', 'legull' ),
				'hidden'        => true,
			),
			array(
				'field_id'    => 'DMCA_telephone',
				'title'       => __( 'DMCA Phone', 'legull' ),
				'type'        => 'text',
				'description' => __( 'What will be the telephone number of your DMCA agent?', 'legull' ),
				'hidden'        => true,
			),
			array(
				'field_id'    => 'DMCA_email',
				'title'       => __( 'DMCA Email', 'legull' ),
				'type'        => 'text',
				'description' => __( 'What will be the email address of your DMCA agent?', 'legull' ),
				'hidden'        => true,
			)
		);
		$oAdminPage->addSettingFields(
			'advertising',
			array(
				'field_id'    => 'has_advertising',
				'type'        => 'revealer',
				'select_type'   => 'checkbox',
				'title'       => __( 'Site contains advertising', 'legull' ),
				'description' => __( 'Does this site use advertising?', 'legull' ),
				'label'         => array(
                    '#fieldrow-advertising_has_advertising_network,#fieldrow-advertising_has_advertising_adsense' => __( 'YES', 'legull' )
                ),
			),
			array(
				'field_id'    => 'has_advertising_network',
				'title'       => __( '3rd party advertising', 'legull' ),
				'description' => __( 'Will this site use a 3rd party network to supply advertising?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_advertising_adsense',
				'title'       => __( 'Google AdSense', 'legull' ),
				'description' => __( 'Will this site use Google AdSense to supply advertising?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			)
		);
		$oAdminPage->addSettingFields(
			'tracking',
			array(
				'field_id'    => 'privacy_name',
				'title'       => __( 'Privacy Contact', 'legull' ),
				'type'        => 'text',
				'description' => __( 'What is the contact name for privacy matters?', 'legull' ),
			),
			array(
				'field_id'    => 'privacy_email',
				'title'       => __( 'Privacy Email', 'legull' ),
				'type'        => 'text',
				'description' => __( 'What is the contact email for privacy matters?', 'legull' ),
			),
			array(
				'field_id'    => 'has_cookies',
				'title'       => __( 'Use cookies', 'legull' ),
				'description' => __( 'Will this site use cookies beyond advertising tools? (i.e. Google Analytics)', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_info_track',
				'title'       => __( 'Information Tracking', 'legull' ),
				'description' => __( 'Will visitors be tracked when surfing the site? (i.e. Google Analytics)', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_personalization',
				'title'       => __( 'User Personalization', 'legull' ),
				'description' => __( 'Will visitors be able to personalize their expereience when surfing the site?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_anonymous',
				'title'       => __( 'Anonymous Surfing', 'legull' ),
				'description' => __( 'Will visitors be able to surf the site anonymously?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_purchased_data',
				'title'       => __( 'Purchase User Data', 'legull' ),
				'tip'         => __( 'The description key can be omitted though.', 'legull' ),
				'description' => __( 'Does this site purchase user data?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_data_buyer',
				'title'       => __( 'Sell User Data', 'legull' ),
				'description' => __( 'Does this site sell or rent user data?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_collectdata',
				'title'       => __( 'Collect User Data', 'legull' ),
				'description' => __( 'Will this site collect any data from its users?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_sharedata',
				'title'       => __( 'Sharing User Data', 'legull' ),
				'description' => __( 'Will any of the user data (individual data or aggregate data) be shared outside of the site owner itself?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_sharedata_aggregate',
				'title'       => __( 'Share User Data Aggregated', 'legull' ),
				'description' => __( 'Will all user data be shared only in grouped form, so that individual users are not identified and individual user data is not shared?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_sharedata_helpers',
				'title'       => __( 'Share User Data with Partners', 'legull' ),
				'description' => __( 'Will any user data be shared with those who help the site owner operate and manage the site?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_sharedata_ads',
				'title'       => __( 'Share User Data with Advertisers', 'legull' ),
				'description' => __( 'Will any user data be shared with advertisers or marketing partners?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_sharedata_unlimited',
				'title'       => __( 'Share User Data Unlimited', 'legull' ),
				'description' => __( 'Will any user data be shared with others other than those who help operate and manage the site, and other than advertisers or marketing partners?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			)
		);
		$oAdminPage->addSettingFields(
			'misc',
			array(
				'field_id'    => 'has_over18',
				'title'       => __( 'Over 18', 'legull' ),
				'tip'         => __( 'The description key can be omitted though.', 'legull' ),
				'description' => __( 'Does this site require visitors to be over the age of 18?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_no13',
				'title'       => __( 'Under 13', 'legull' ),
				'description' => __( 'Will this site allow users or visitors under the age of 13?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_arbitration',
				'title'       => __( 'Arbitration', 'legull' ),
				'tip'         => __( 'The description key can be omitted though.', 'legull' ),
				'description' => __( 'Does this site require an arbitration clause?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_SSL',
				'title'       => __( 'SSL', 'legull' ),
				'tip'         => __( 'The description key can be omitted though.', 'legull' ),
				'description' => __( 'Does this site use SSL? (https://)', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false,
			),
			array(
				'field_id'    => 'has_support_contact',
				'title'       => __( 'Support Contact', 'legull' ),
				'description' => __( 'Will this site offer support via phone or email?', 'legull' ),
				'type'        => 'revealer',
				'select_type'   => 'checkbox',
				'label'         => array(
                    '#fieldrow-misc_support_email,#fieldrow-misc_support_phone' => __( 'YES', 'legull' )
                ),
			),
			array(
				'field_id'    => 'support_email',
				'description' => __( 'What will be the email address for support?', 'legull' ),
				'type'        => 'text',
				'hidden' => true
			),
			array(
				'field_id'    => 'support_phone',
				'description' => __( 'What will be the phone number for support?', 'legull' ),
				'type'        => 'text',
				'hidden'      => true
			),
			array(
				'field_id' => 'last_updated',
				'type'     => 'hidden',
				'value'    => current_time( 'timestamp' )
			),
			array(
				'field_id'    => 'has_no_scrape',
				'title'       => __( 'Prevent Scraping', 'legull' ),
				'description' => __( 'Will this site prohibit the automatic collection of its data by others ("scraping")?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false
			),
			array(
				'field_id'    => 'has_password',
				'title'       => __( 'Passwords', 'legull' ),
				'description' => __( 'Will any part of this site require a password for access?', 'legull' ),
				'type'        => 'checkbox',
				'label'       => __( 'YES', 'legull' ),
				'default'     => false
			)
		);
	}

	public function do_form_legull_dashboard() {
		include LEGULL_PATH . 'template/dashboard.php';
	}

	public function do_legull_dashboard() {
		submit_button( __( 'Save All Tabs', 'legull' ) );
	}


	public function do_form_legull_generate() {
		include LEGULL_PATH . 'template/generate-documents.php';
	}

	public function do_legull_generate() {
		if ( get_option( 'Legull' ) ) {
			submit_button( __( 'Generate Terms', 'legull' ) );
		} else {
			printf( '<h2>%s</h2>', __( 'You must save your site details before generation of terms may occur.', 'legull' ) );
		}
	}

	public function do_legull_addons() {
		include LEGULL_PATH . 'template/addons.php';
	}
}
