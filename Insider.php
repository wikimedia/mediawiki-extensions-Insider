<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Insider' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Insider'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['InsiderMagic'] = __DIR__ . '/Insider.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for Insider extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the Insider extension requires MediaWiki 1.25+' );
}
