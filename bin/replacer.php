<?php
/**
 * Library fixer.
 *
 * thewirecutter/paapi5-php-sdk has classes that call parent::listInvalidProperties()
 * without extending a parent class. This causes PHP Fatal errors.
 *
 * Affected files:
 * - Properties.php
 * - OfferListings.php
 * - OfferListingsV2.php
 */

$needle   = '$invalidProperties = parent::listInvalidProperties();

        return $invalidProperties;';
$replaced = 'return [];';

$list_to_fix = [
	[ 'vendor/thewirecutter/paapi5-php-sdk/src/com/amazon/paapi5/v1/Properties.php', $needle, $replaced ],
	[ 'vendor/thewirecutter/paapi5-php-sdk/src/com/amazon/paapi5/v1/OfferListings.php', $needle, $replaced ],
	[ 'vendor/thewirecutter/paapi5-php-sdk/src/com/amazon/paapi5/v1/OfferListingsV2.php', $needle, $replaced ],
];

$done = [];

foreach ( $list_to_fix as list( $path, $needle, $replaced ) ) {
	if ( ! file_exists( $path ) ) {
		printf( 'File not found: %s', $path );
		exit( 1 );
	}
	$contents = file_get_contents( $path );
	if ( false === strpos( $contents, $needle ) ) {
		printf( 'Needle not found: %s %s', $path . "\n\n", $needle );
		exit( 1 );
	}
	$content = str_replace( $needle, $replaced, $contents );
	if( file_put_contents( $path, $content ) ) {
		$done[] = $path;
	}
}

printf( "These files are replaced: \n%s\n", implode( "\n", $done ) );
exit( 0 );
