<?php

if ( !defined( 'MEDIAWIKI' ) ) {
        die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once( dirname(__FILE__) . "/../CustomData/CustomData.php" );

$dir = dirname(__FILE__) . '/';
$wgExtensionMessagesFiles['Insider'] = $dir . 'Insider.i18n.php';

$wgExtensionFunctions[] = 'wfSetupInsider';
$wgExtensionCredits['parserhook']['Insider'] = array( 'name' => 'Insider', 'url' => 
'http://wikivoyage.org/tech/Insider-Extension', 'author' => 'Roland Unger/Hans Musil',
'descriptionmsg' => 'ins-desc' );

$wgHooks['LanguageGetMagic'][]       = 'wfInsiderParserFunction_Magic';


class Insider
{
	var $mInsiderSet = array();

  function Insider()
  {
		# wfDebug( "Call to Insider constructor\n");
		$this->mInsiderSet = array();
  }

  function onParserClearState( &$parser)
  {
    # wfDebug( "Insider::onParserClearState\n");

    $this->mInsiderSet = array();

    return true;
  }

	# function onFuncInsider( &$parser, $insider)
	function onFuncInsider()
	{
		$args = func_get_args();
		array_shift( $args);
		# $parser = array_shift( $args);

		foreach( $args as $insider)
		{
			# wfDebug( "Insider::onFuncInsider: insider = $insider\n");

			$this->mInsiderSet[] = $insider;
		};

		return '';
	}

	#
	#	After parsing is done, store the $mInsiderSet in $wgCustomData.
	#
  function onParserBeforeTidy( &$parser, &$text)
  {
    global $wgCustomData;

    if( $this->mInsiderSet)
    {
      $wgCustomData->setParserData( $parser->mOutput, 'Insider', $this->mInsiderSet);
    };

    return true;
  }


	#
	# Hooked in from hook SkinTemplateOutputPageBeforeExec.
	# Preprocess insider links.
	#
	function onSkinTemplateOutputPageBeforeExec( &$SkTmpl, &$QuickTmpl)
	{
		global $wgCustomData, $wgOut;

		# wfDebug( "Insider::onSkinTemplateOutputPageBeforeExec\n");
		$Insider_urls = array();

    #
    # Fill the Insider array.
    #
		$ins = $wgCustomData->getPageData( $wgOut, 'Insider');
		foreach( $ins as $l)
		{
      // Tribute to Evan
      $l = urldecode( $l);

			$class = 'interwiki-insider';
			$nt = Title::newFromText( $l, NS_USER);
			if( $nt)
			{
				$Insider_urls[] = array(
						'href' => $nt->getLocalURL(),
						'text' => $nt->getText(),
						# 'text' => $nt->getPrefixedText(),
						'class' => $class
				);
			};

			# wfDebug( "l: $l\n");
		};
		$wgCustomData->setSkinData( $QuickTmpl, 'Insider', $Insider_urls);

		return true;
	}

	#
	# Write out HTML-code.
	#
	function onSkinTemplateToolboxEnd( &$skTemplate)
	{
		global $wgCustomData;

		# wfDebug( "Insider::onMonoBookTemplateToolboxEnd\n");

		$ins = $wgCustomData->getSkinData( $skTemplate, 'Insider');
		if( $ins ) 
		{ ?>
                        </ul>
                </div>
        </div>
        <div id="p-lang" class="portal">
                <h5><?php $skTemplate->msg('ins-Insider') ?></h5>
                <div class="body">
                        <ul>
<?php

			foreach( $ins as $inslink)
			{ ?>
					<li class="<?php echo htmlspecialchars($inslink['class'])?>"><?php
				?><a href="<?php echo htmlspecialchars($inslink['href']) ?>"><?php echo $inslink['text'] ?></a></li>
<?php }

			$nt = Title::newFromText( wfMsgForContent( 'ins-Aboutpage' ) );
			if ( $nt ) { ?>
					<li class="<?php echo htmlspecialchars('interwiki-insider')?>"><?php
				?><a href="<?php echo htmlspecialchars( $nt->getLocalURL() ) ?>"><?php echo wfMsg( 'ins-About' ) ?></a></li>
<?php }

		};

	return true;
	}

};



function wfSetupInsider()
{
	global $wgParser, $wgHooks;

	global $wgInsider;
	$wgInsider     = new Insider;

	$wgParser->setFunctionHook( 'insider', array( &$wgInsider, 'onFuncInsider' ));

	$wgHooks['SkinTemplateToolboxEnd'][] = 
					array( &$wgInsider, 'onSkinTemplateToolboxEnd' );
	$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 
					array( &$wgInsider, 'onSkinTemplateOutputPageBeforeExec' );
	$wgHooks['ParserClearState'][] = array( &$wgInsider, 'onParserClearState' );
	$wgHooks['ParserBeforeTidy'][] = array( &$wgInsider, 'onParserBeforeTidy' );
}

function wfInsiderParserFunction_Magic( &$magicWords, $langCode )
{
	# wfDebug( "Call to wfInsiderParserFunction_Magic\n");

	$magicWords['insider'] = array( 0, 'insider' );

	return true;
}

?>
