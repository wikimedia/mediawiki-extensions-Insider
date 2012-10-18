<?php

class Insider {
	var $mInsiderSet = array();

	/**
	 * @param Parser $parser
	 * @return bool
	 */
	public static function parserHooks( Parser &$parser ) {
		global $wgInsider;
		$parser->setFunctionHook( 'insider', array( &$wgInsider, 'onFuncInsider' ) );
		return true;
	}

	/**
	 * @return CustomData
	 */
	public function getCustomData() {
		global $wgCustomData;

		if ( !$wgCustomData instanceof CustomData ) {
			throw new Exception( 'CustomData extension is not properly installed.' );
		}

		return $wgCustomData;
	}

	/**
	 * @param Parser $parser
	 * @return bool
	 */
	public function onParserClearState( Parser &$parser ) {
		$this->mInsiderSet = array();
		return true;
	}

	public function onFuncInsider() {
		$args = func_get_args();
		array_shift( $args );

		foreach ( $args as $insider ) {
			$this->mInsiderSet[] = $insider;
		}

		return '';
	}

	/**
	 * After parsing is done, store the $mInsiderSet in $wgCustomData.
	 *
	 * @param Parser $parser
	 * @param string $text
	 * @return bool
	 */
	public function onParserBeforeTidy( Parser &$parser, &$text ) {
		if ( $this->mInsiderSet ) {
			$this->getCustomData()->setParserData( $parser->mOutput, 'Insider', $this->mInsiderSet );
		}

		return true;
	}

	/**
	 * Preprocess insider links.
	 *
	 * @param SkinTemplate $skinTpl
	 * @param QuickTemplate $QuickTmpl
	 * @return bool
	 */
	public function onSkinTemplateOutputPageBeforeExec( SkinTemplate &$skinTpl, &$QuickTmpl ) {
		global $wgOut;

		$customData = $this->getCustomData();

		// Fill the Insider array.
		$insiders = $customData->getPageData( $wgOut, 'Insider' );
		$customData->setSkinData( $QuickTmpl, 'Insider', $insiders );

		return true;
	}

	/**
	 * @param array $insiders
	 * @return array
	 */
	protected function getInsiderUrls( array $insiders ) {
		$insiderUrls = array();

		foreach ( $insiders as $insider ) {
			// Tribute to Evan
			$insider = urldecode( $insider );

			$userTitle = Title::newFromText( $insider, NS_USER );
			if ( $userTitle ) {
				$insiderUrls[] = array(
					'href' => $userTitle->getLocalURL(),
					'text' => $userTitle->getText(),
					'class' => 'interwiki-insider'
				);
			}
		}

		return $insiderUrls;
	}

	/**
	 * Write out HTML-code.
	 *
	 * @param Skin $skin
	 * @param array $bar
	 * @return bool
	 */
	public function onSkinBuildSidebar( $skin, &$bar ) {
		$out = $skin->getOutput();
		$insiders = $this->getCustomData()->getParserData( $out, 'Insider' );

		if ( count( $insiders ) == 0 ) {
			return true;
		}

		$insiderUrls = $this->getInsiderUrls( $insiders );

		// build insider <li>'s
		$insiders = array();
		foreach ( (array) $insiderUrls as $url ) {
			$insiders[] =
				Html::rawElement( 'li', array( 'class' => htmlspecialchars( $url['class'] ) ),
					Html::rawElement( 'a', array( 'href' => htmlspecialchars( $url['href'] ) ),
						$url['text']
					)
				);
		}

		// add general "insiders" entry
		$title = Title::newFromText( wfMessage( 'insider-about-page' )->inContentLanguage()->plain() );
		if ( $title ) {
			$insiders[] =
				Html::rawElement( 'li', array( 'class' => htmlspecialchars( 'interwiki-insider' ) ),
					Html::rawElement( 'a', array( 'href' => htmlspecialchars( $title->getLocalURL() ) ),
						wfMessage( 'insider-about' )->text()
					)
				);
		}

		// build complete html
		$bar[$skin->msg( 'insider-title' )->text()] =
			Html::rawElement( 'ul', array(),
				implode( '', $insiders )
			);

		return true;
	}

	/**
	 * Write out HTML-code.
	 *
	 * @param SkinTemplate|VectorTemplate $skinTpl
	 * @return bool
	 */
	public function onSkinTemplateToolboxEnd( &$skinTpl ) {
		$insiders = $this->getCustomData()->getSkinData( $skinTpl, 'Insider' );

		if ( count( $insiders ) == 0 ) {
			return true;
		}

		$insiderUrls = $this->getInsiderUrls( $insiders );

		// build insider <li>'s
		$insiders = array();
		foreach ( (array) $insiderUrls as $url ) {
			$insiders[] =
				Html::rawElement( 'li', array( 'class' => htmlspecialchars( $url['class'] ) ),
					Html::rawElement( 'a', array( 'href' => htmlspecialchars( $url['href'] ) ),
						$url['text']
					)
				);
		}

		// add general "insiders" entry
		$title = Title::newFromText( wfMessage( 'insider-about-page' )->inContentLanguage()->plain() );
		if ( $title ) {
			$insiders[] =
				Html::rawElement( 'li', array( 'class' => htmlspecialchars( 'interwiki-insider' ) ),
					Html::rawElement( 'a', array( 'href' => htmlspecialchars( $title->getLocalURL() ) ),
						wfMessage( 'insider-about' )->text()
					)
				);
		}

		// build complete html
		echo
			Html::closeElement( 'ul' ) .
			Html::closeElement( 'div' ) .
			Html::closeElement( 'div' ) .
			Html::openElement( 'div', array( 'id' => 'p-lang', 'class' => 'portal' ) ) .
			Html::element( 'h5', array(), wfMessage( 'insider-title' )->text() ) .
			Html::openElement( 'div', array( 'class' => 'body' ) ) .
			Html::openElement( 'ul', array( 'class' => 'body' ) ) .
			implode( '', $insiders );

		return true;
	}
}
