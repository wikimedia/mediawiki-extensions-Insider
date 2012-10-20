<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgExtensionCredits['parserhook']['Insider'] = array(
	'name' => 'Insider',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Insider',
	'author' => 'Roland Unger/Hans Musil',
	'descriptionmsg' => 'insider-desc'
);

$dir = __DIR__ . '/';
require_once( $dir . "../CustomData/CustomData.php" );

$wgAutoloadClasses['Insider'] = $dir . 'Insider.class.php';
$wgExtensionFunctions[] = 'wfSetupInsider';
$wgExtensionMessagesFiles['Insider'] = $dir . 'Insider.i18n.php';
$wgExtensionMessagesFiles['InsiderMagic'] = $dir . 'Insider.i18n.magic.php';

function wfSetupInsider() {
	global $wgParser, $wgHooks;

	global $wgInsider;
	$wgInsider = new Insider;

	$wgParser->setFunctionHook( 'insider', array( &$wgInsider, 'onFuncInsider' ) );

	$wgHooks['SkinTemplateToolboxEnd'][] = array( &$wgInsider, 'onSkinTemplateToolboxEnd' );
	$wgHooks['SkinTemplateOutputPageBeforeExec'][] = array( &$wgInsider, 'onSkinTemplateOutputPageBeforeExec' );
	$wgHooks['ParserClearState'][] = array( &$wgInsider, 'onParserClearState' );
	$wgHooks['ParserBeforeTidy'][] = array( &$wgInsider, 'onParserBeforeTidy' );
}
