#!/usr/bin/env php
<?php
/**
 * SDK PHP Lint Checker.
 *
 * Runs `php -l` on all PHP files in app/Amazon/ directory
 * to ensure there are no syntax errors before deployment.
 *
 * Usage: php bin/lint-sdk.php
 */

$sdk_dir = dirname( __DIR__ ) . '/app/Amazon';

if ( ! is_dir( $sdk_dir ) ) {
	echo "Error: SDK directory not found: {$sdk_dir}\n";
	exit( 1 );
}

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $sdk_dir, RecursiveDirectoryIterator::SKIP_DOTS )
);

$errors  = [];
$checked = 0;

foreach ( $iterator as $file ) {
	if ( $file->getExtension() !== 'php' ) {
		continue;
	}

	$path   = $file->getPathname();
	$output = [];
	$return = 0;

	exec( sprintf( 'php -l %s 2>&1', escapeshellarg( $path ) ), $output, $return );

	++$checked;

	if ( $return !== 0 ) {
		$errors[ $path ] = implode( "\n", $output );
	}
}

echo "Checked {$checked} PHP files in app/Amazon/\n";

if ( empty( $errors ) ) {
	echo "All files passed syntax check.\n";
	exit( 0 );
}

echo "\nSyntax errors found:\n";
echo str_repeat( '-', 60 ) . "\n";

foreach ( $errors as $path => $error ) {
	echo "\n{$path}:\n{$error}\n";
}

echo str_repeat( '-', 60 ) . "\n";
echo sprintf( "\n%d file(s) with errors.\n", count( $errors ) );

exit( 1 );
