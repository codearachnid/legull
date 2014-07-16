<?php

if( !class_exists( 'Legull_Conf' ) ){
	class Legull_Conf {
		private static $_this;

		public $addons = array(
			array(
				'name' => 'Digitial Ecommerce',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'Product Ecommerce',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'Auction/Deal Ecommerce',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'Classifieds Ecommerce',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'Digital Membership',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'Social Networking',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'User Generated/Upload',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'News & Information',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'Review & Opinion',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'Dating',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'Financial',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			),
			array(
				'name' => 'Curation',
				'remote_url' => 'http://',
				'description' => 'Praesent eget nisi velit. In hac habitasse platea dictumst. Nulla facilisi. Nulla sed augue vehicula, dignissim ipsum a, fermentum augue. Nulla facilisi. Donec vitae gravida elit, ac sagittis eros. Quisque feugiat tincidunt arcu, ut pulvinar metus vestibulum eget. Nullam venenatis sapien porttitor pretium semper. Nunc posuere, ante at faucibus imperdiet, lacus mauris dictum libero, varius volutpat quam nulla varius lectus.',
				'active' => false
			)
		);

		public static function retrieve() {
			if ( !isset( self::$_this ) ) {
				$className = __CLASS__;
				self::$_this = new $className;
			}
			return self::$_this;
		}
	}
}