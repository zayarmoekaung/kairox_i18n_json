<?php
if ( ! defined( 'NATIVE_I18N_COOKIE_NAME' ) ) {
	define( 'NATIVE_I18N_COOKIE_NAME', 'native_i18n_lang' );
}

if ( ! defined( 'COOKIEPATH' ) ) {
	define( 'COOKIEPATH', '/' );
}

if ( ! defined( 'COOKIE_DOMAIN' ) ) {
	define( 'COOKIE_DOMAIN', '' );
}

require_once __DIR__ . '/../includes/frontend/class-i18n-runtime.php';

class Dummy_Config {
	public function get_i18n_config() {
		return array(
			'default' => 'en',
			'allowed' => array( 'en', 'fr' ),
		);
	}

	public function is_allowed_language( $lang, $config = null ) {
		return in_array( $lang, array( 'en', 'fr' ), true );
	}
}

class Dummy_Storage {
	public function load_language_file( $lang ) {
		return array();
	}
}

class Runtime_Language_Attribute_Test {
	public static function assert_same( $expected, $actual, $message ) {
		if ( $expected !== $actual ) {
			fwrite( STDERR, $message . "\nExpected: $expected\nActual: $actual\n" );
			exit( 1 );
		}
	}
}

$runtime = new Native_JSON_i18n_Runtime( new Dummy_Config(), new Dummy_Storage() );

$_COOKIE[ NATIVE_I18N_COOKIE_NAME ] = 'fr';
$updated = $runtime->filter_language_attributes( 'lang="en"' );
Runtime_Language_Attribute_Test::assert_same( 'lang="fr"', $updated, 'The language attribute should reflect the active runtime language.' );

unset( $_COOKIE[ NATIVE_I18N_COOKIE_NAME ] );
$default = $runtime->filter_language_attributes( 'lang="en"' );
Runtime_Language_Attribute_Test::assert_same( 'lang="en"', $default, 'The default language should be preserved when no switch is active.' );

echo "Runtime language attribute test passed.\n";
