<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

// autoloader
$wgAutoloadClasses['Insider'] = __DIR__ . '/Insider.class.php';

// extension & magic words i18n
$wgExtensionMessagesFiles['Insider'] = __DIR__ . '/Insider.i18n.php';
$wgExtensionMessagesFiles['InsiderMagic'] = __DIR__ . '/Insider.i18n.magic.php';

// hooks
$wgInsider = new Insider;
$wgHooks['ParserFirstCallInit'][] = 'Insider::parserHooks';
$wgHooks['SkinTemplateOutputPageBeforeExec'][] = array( &$wgInsider, 'onSkinTemplateOutputPageBeforeExec' );
$wgHooks['ParserClearState'][] = array( &$wgInsider, 'onParserClearState' );
$wgHooks['ParserBeforeTidy'][] = array( &$wgInsider, 'onParserBeforeTidy' );

// 2 same hooks, with different position though - enable what you want
// the first one is a "clean" solution, but has its content inserted _before_ the toolbox
//$wgHooks['SkinBuildSidebar'][] = array( &$wgInsider, 'onSkinBuildSidebar' );
// the second one is nasty: echo'ing raw html _after_ the regular toolbox
$wgHooks['SkinTemplateToolboxEnd'][] = array( &$wgInsider, 'onSkinTemplateToolboxEnd' );

// credits
$wgExtensionCredits['parserhook']['Insider'] = array(
	'path' => __FILE__,
	'name' => 'Insider',
	'url' => '//www.mediawiki.org/wiki/Extension:Insider',
	'descriptionmsg' => 'insider-desc',
	'author' => array( 'Roland Unger', 'Hans Musil', 'Matthias Mullie' ),
	'version' => '1.01'
);
