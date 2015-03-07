<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt / 2015 Nobuo Kihara
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\creole\block;

/**
 * Adds horizontal rules
 */
trait RuleTrait
{
	/**
	 * identify a line as a horizontal rule.
	 */
	protected function identifyHr($line)
	{
		// '----' makes a hr, with possible white spaces before or after it.
		return preg_match('/^\s*----\s*$/', $line);
	}

	/**
	 * Consume a horizontal rule
	 */
	protected function consumeHr($lines, $current)
	{
		return [['hr'], $current];
	}

	/**
	 * Renders a horizontal rule
	 */
	protected function renderHr($block)
	{
		return $this->html5 ? "<hr>\n" : "<hr />\n";
	}

} 