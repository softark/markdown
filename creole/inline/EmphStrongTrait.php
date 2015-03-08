<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt / 2015 Nobuo Kihara
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\creole\inline;

/**
 * Adds inline emphasizes and strong elements
 */
trait EmphStrongTrait
{
	/**
	 * Parses strong element.
	 * @marker **
	 */
	protected function parseStrong($text)
	{
		if (preg_match('/^\*\*(.+?)\*\*/s', $text, $matches)) {
			return [
				[
					'strong',
					$this->parseInline($matches[1])
				],
				strlen($matches[0])
			];
		}
		return [['text', '**'], 2];
	}

	/**
	 * Parses strong element.
	 * @marker //
	 */
	protected function parseEmph($text)
	{
		if (preg_match('/^\/\/(.+?)(?<!:)\/\/(?!\/)/s', $text, $matches)) {
			return [
				[
					'emph',
					$this->parseInline($matches[1])
				],
				strlen($matches[0])
			];
		}
		return [['text', '//'], 2];
	}

	protected function renderStrong($block)
	{
		return '<strong>' . $this->renderAbsy($block[1]) . '</strong>';
	}

	protected function renderEmph($block)
	{
		return '<em>' . $this->renderAbsy($block[1]) . '</em>';
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($absy);
}
