<?php

class Insider {
	public static $mInsiderSet = array();

	/**
	 * @param Parser $parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'insider', array( 'Insider', 'onFuncInsider' ) );
		return true;
	}

	/**
	 * @return CustomData
	 */
	public static function getCustomData() {
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
	public static function onParserClearState( Parser &$parser ) {
		self::$mInsiderSet = array();
		return true;
	}

	public static function onFuncInsider() {
		$args = func_get_args();
		array_shift( $args );

		foreach ( $args as $insider ) {
			self::$mInsiderSet[] = $insider;
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
	public static function onParserBeforeTidy( Parser &$parser, &$text ) {
		if ( self::$mInsiderSet ) {
			self::getCustomData()->setParserData( $parser->mOutput, 'Insider', self::$mInsiderSet );
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
	public static function onSkinTemplateOutputPageBeforeExec( SkinTemplate &$skinTpl, &$QuickTmpl ) {
		global $wgOut;

		$customData = self::getCustomData();

		// Fill the Insider array.
		$insiders = $customData->getPageData( $wgOut, 'Insider' );
		$customData->setSkinData( $QuickTmpl, 'Insider', $insiders );

		return true;
	}

	/**
	 * @param array $insiders
	 * @return array
	 */
	protected static function getInsiderUrls( array $insiders ) {
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
	public static function onSkinBuildSidebar( $skin, &$bar ) {
		$out = $skin->getOutput();
		$insiders = self::getCustomData()->getParserData( $out, 'Insider' );

		if ( count( $insiders ) == 0 ) {
			return true;
		}

		$insiderUrls = self::getInsiderUrls( $insiders );

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
	public static function onSkinTemplateToolboxEnd( &$skinTpl ) {
		$insiders = self::getCustomData()->getSkinData( $skinTpl, 'Insider' );

		if ( count( $insiders ) == 0 ) {
			return true;
		}

		$insiderUrls = self::getInsiderUrls( $insiders );

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
			Html::openElement( 'div', array(
				'class' => 'portal',
				'role' => 'navigation',
				'id' => 'p-insiders'
			) ) .
			Html::element( 'h3', array(), wfMessage( 'insider-title' )->text() ) .
			Html::openElement( 'div', array( 'class' => 'body' ) ) .
			Html::openElement( 'ul' ) .
			implode( '', $insiders );

		return true;
	}
}
