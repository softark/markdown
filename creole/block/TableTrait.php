<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt / 2015 Nobuo Kihara
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\creole\block;

/**
 * Adds the table blocks
 */
trait TableTrait
{
	/**
	 * identify a line as the beginning of a table block.
	 */
	protected function identifyTable($line, $lines, $current)
	{
		return strpos($line, '|') !== false && preg_match('/^\s*\|.*/', $line);
	}

	/**
	 * Consume lines for a table
	 */
	protected function consumeTable($lines, $current)
	{
        $pattern =<<< REGEXP
/(?<=\|)(
    [^\|]*?{{{.*?}}}[^\|]*|
    ([^\|]*~\|[^\|]*)+|
    [^\|]*
)(?=\|)/x
REGEXP;
        // regexp pattern should be in the order of from specific/long/complicated to general/short/simple.

		$block = [
			'table',
			'rows' => [],
		];
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = trim($lines[$i]);
			if ($line === '' || $line[0] !== '|') {
				break;
			}
            $header = $i === $current;
            preg_match_all($pattern, '|' . trim($line, '| ') . '|', $matches);
            $texts = $matches[0];
            $row = [];
            foreach ($texts as $text) {
                $text = trim($text);
                if (isset($text[0]) && $text[0] === '=') {
                    $cell['tag'] = 'th';
                    $cell['text'] = $this->parseInline(substr($text, 1));
                } else {
                    $cell['tag'] = 'td';
                    $cell['text'] = $this->parseInline($text);
                    $header = false;
                }
                $row['cells'][] = $cell;
            }
            $row['header'] = $header;
			$block['rows'][] = $row;
		}

		return [$block, --$i];
	}

	/**
	 * render a table block
	 */
	protected function renderTable($block)
	{
		$content = "";
		$first = true;
		foreach($block['rows'] as $row) {
            if ($first) {
                if ($row['header']) {
                    $content .= "<thead>\n";
                } else {
                    $content .= "<tbody>\n";
                }
            }
            $content .= "<tr>\n";
            foreach ($row['cells'] as $cell) {
                $tag = $cell['tag'];
                $content .= "<$tag>" . $this->renderAbsy($cell['text']) . "</$tag>\n";
            }
            $content .= "</tr>\n";
            if ($first) {
                if ($row['header']) {
                    $content .= "</thead>\n<tbody>\n";
                }
                $first = false;
            }
		}
		return "<table>\n$content</tbody>\n</table>\n";
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($absy);
}
