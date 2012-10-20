<?php

class Insider {
	var $mInsiderSet = array();

	function Insider() {
		$this->mInsiderSet = array();
	}

	function onParserClearState( &$parser ) {
		$this->mInsiderSet = array();

		return true;
	}

	function onFuncInsider() {
		$args = func_get_args();
		array_shift( $args );

		foreach ( $args as $insider ) {
			$this->mInsiderSet[] = $insider;
		}

		return '';
	}

	#
	#	After parsing is done, store the $mInsiderSet in $wgCustomData.
	#
	function onParserBeforeTidy( &$parser, &$text ) {
		global $wgCustomData;

		if ( $this->mInsiderSet ) {
			$wgCustomData->setParserData( $parser->mOutput, 'Insider', $this->mInsiderSet );
		}

		return true;
	}

	#
	# Hooked in from hook SkinTemplateOutputPageBeforeExec.
	# Preprocess insider links.
	#
	function onSkinTemplateOutputPageBeforeExec( &$SkTmpl, &$QuickTmpl ) {
		global $wgCustomData, $wgOut;

		$Insider_urls = array();

		#
		# Fill the Insider array.
		#
		$ins = $wgCustomData->getPageData( $wgOut, 'Insider' );
		foreach ( $ins as $l ) {
			// Tribute to Evan
			$l = urldecode( $l );

			$class = 'interwiki-insider';
			$nt = Title::newFromText( $l, NS_USER );
			if ( $nt ) {
				$Insider_urls[] = array(
					'href' => $nt->getLocalURL(),
					'text' => $nt->getText(),
					'class' => $class
				);
			}
		}
		$wgCustomData->setSkinData( $QuickTmpl, 'Insider', $Insider_urls );

		return true;
	}

	#
	# Write out HTML-code.
	#
	function onSkinTemplateToolboxEnd( &$skTemplate ) {
		global $wgCustomData;

		$ins = $wgCustomData->getSkinData( $skTemplate, 'Insider' );
		if ( $ins ) {
			?>
        </ul>
        </div>
        </div>
		<div id="p-lang" class="portal">
				<h5><?php $skTemplate->msg( 'insider-insider' ) ?></h5>
				<div class="body">
						<ul>
<?php

			foreach ( $ins as $inslink ) {
				?>
                <li class="<?php echo htmlspecialchars( $inslink['class'] )?>"><?php
					?>
                    <a href="<?php echo htmlspecialchars( $inslink['href'] ) ?>"><?php echo $inslink['text'] ?></a>
                </li>
				<?php
			}

			$nt = Title::newFromText( wfMessage( 'insider-aboutpage' )->inContentLanguage()->text() );
			if ( $nt ) {
				?>
                <li class="<?php echo htmlspecialchars( 'interwiki-insider' )?>"><?php
					?><a
                            href="<?php echo htmlspecialchars( $nt->getLocalURL() ) ?>"><?php echo wfMessage( 'insider-about' )->text() ?></a>
                </li>
			<?php
			}

		}

		return true;
	}
}
