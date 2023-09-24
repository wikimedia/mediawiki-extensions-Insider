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

namespace MediaWiki\Extension\Insider;

use MediaWiki\Hook\OutputPageParserOutputHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\SidebarBeforeOutputHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use OutputPage;
use Parser;
use ParserOutput;
use Skin;

class Hooks implements
	ParserFirstCallInitHook,
	OutputPageParserOutputHook,
	SidebarBeforeOutputHook
{
	/**
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'insider', [ self::class, 'onFuncInsider' ] );
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
	 */
	public function onOutputPageParserOutput( $out, $parserOutput ): void {
		$related = $parserOutput->getExtensionData( 'Insider' );

		if ( $related ) {
			$out->setProperty( 'Insider', $related );
		}
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
	 */
	public function onSidebarBeforeOutput( $skin, &$bar ): void {
		$out = $skin->getOutput();
		$insiders = $out->getProperty( 'Insider' );

		if ( !$insiders ) {
			return;
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
	}
}
