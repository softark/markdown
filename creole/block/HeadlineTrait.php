<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt / 2015 Nobuo Kihara
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\creole\block;

/**
 * Adds the headline blocks
 */
trait HeadlineTrait
{
	/**
	 * identify a line as a headline
	 */
	protected function identifyHeadline($line, $lines, $current)
	{
		return (
			// heading with =
			$line[0] === '='
		);
	}

	/**
	 * Consume lines for a headline
	 */
	protected function consumeHeadline($lines, $current)
	{
		if ($lines[$current][0] === '=') {
			// ATX headline
			$level = 1;
			while (isset($lines[$current][$level]) && $lines[$current][$level] === '=' && $level < 6) {
				$level++;
			}
			$block = [
				'headline',
                // doesn't parse header line content.
                // 'content' => $this->parseInline(trim($lines[$current], "= \t")),
                'content' => [['text', trim($lines[$current], "= \t")]],
				'level' => $level,
			];
			return [$block, $current];
		}
	}

	/**
	 * Renders a headline
	 */
	protected function renderHeadline($block)
	{
		$tag = 'h' . $block['level'];
		return "<$tag>" . $this->renderAbsy($block['content']) . "</$tag>\n";
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($absy);
}
