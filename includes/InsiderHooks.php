<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

use MediaWiki\MediaWikiServices;

class InsiderHooks {
	/**
	 * @param Parser $parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'insider', 'InsiderHooks::onFuncInsider' );
		return true;
	}

	/**
	 * @param Parser $parser
	 * @param string ...$args
	 * @return string
	 */
	public static function onFuncInsider( Parser $parser, ...$args ) {
		$parserOutput = $parser->getOutput();
		$insiders = $parserOutput->getExtensionData( 'Insider' ) ?: [];

		foreach ( $args as $insider ) {
			$insiders[] = $insider;
		}

		$parserOutput->setExtensionData( 'Insider', $insiders );

		return '';
	}

	/**
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 * @return true
	 */
	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ) {
		$related = $parserOutput->getExtensionData( 'Insider' );

		if ( $related ) {
			$out->setProperty( 'Insider', $related );
		}

		return true;
	}

	/**
	 * @param array $insiders
	 * @return array
	 */
	protected static function getInsiderUrls( array $insiders ) {
		$insiderUrls = [];

		foreach ( $insiders as $insider ) {
			// Tribute to Evan
			$insider = urldecode( $insider );

			$userTitle = Title::newFromText( $insider, NS_USER );
			if ( $userTitle ) {
				$insiderUrls[] = [
					'href' => $userTitle->getLocalURL(),
					'text' => $userTitle->getText(),
					'class' => 'interwiki-insider'
				];
			}
		}

		return $insiderUrls;
	}

	/**
	 * Write out HTML-code.
	 *
	 * @param Skin $skin
	 * @param array &$bar
	 * @return bool
	 */
	public static function onSidebarBeforeOutput( $skin, &$bar ) {
		$out = $skin->getOutput();
		$insiders = $out->getProperty( 'Insider' );

		if ( !$insiders ) {
			return true;
		}

		$insiderUrls = self::getInsiderUrls( $insiders );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();

		// build insider <li>'s
		$list = [];
		foreach ( $insiders as $insider ) {
			// Tribute to Evan
			$insider = urldecode( $insider );

			$userTitle = Title::newFromText( $insider, NS_USER );
			if ( $userTitle ) {
				$list[] = [
					'text' => $userTitle->getText(),
					'class' => 'interwiki-insider',
					'href' => $userTitle->getLocalUrl(),
				];
			}
		}

		// add general "insiders" entry
		$title = Title::newFromText( wfMessage( 'insider-about-page' )->inContentLanguage()->plain() );
		if ( $title ) {
			$list[] = [
				'class' => 'interwiki-insider',
				'text' => $skin->msg( 'insider-about' )->text(),
				'href' => $title->getLocalUrl(),
			];
		}

		// build complete html
		$bar[$skin->msg( 'insider-title' )->text()] = $list;

		return true;
	}
}
