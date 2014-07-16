<?php
class Legull_MetaBox_For_Dashboard extends AdminPageFramework_MetaBox_Page {
		
	/*
	 * ( optional ) Use the setUp() method to define settings of this meta box.
	 */
	public function setUp() {

		/*
		 * ( optional ) Adds a contextual help pane at the top right of the page that the meta box resides.
		 */
		$this->addHelpText( 
			__( 'This text will appear in the contextual help pane.', 'legull' ), 
			__( 'This description goes to the sidebar of the help pane.', 'legull' )
		);			
		
	}

	public function do_Legull_MetaBox_For_Dashboard() {
		include LEGULL_PATH . 'template/dashboard-meta.php';
	}
	
}