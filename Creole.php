<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt / 2015 Nobuo Kihara
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown;

/**
 * Creole wiki parser for [Creole 1.0 spec](http://www.wikicreole.org/wiki/Creole1.0).
 *
 * @author Nobuo Kihara <softark@gmail.com>
 */
class Creole extends Parser
{
// include block element parsing using traits
//	use block\CodeTrait;
	use creole\block\HeadlineTrait;
//	use block\HtmlTrait {
//		parseInlineHtml as private;
//	}
    use creole\block\ListTrait;
    use creole\block\TableTrait;
//	use block\QuoteTrait;
	use creole\block\RuleTrait;

// include inline element parsing using traits
//	use inline\CodeTrait;
	use creole\inline\EmphStrongTrait;
	use creole\inline\LinkTrait;

	/**
	 * @var boolean whether to format markup according to HTML5 spec.
	 * Defaults to `false` which means that markup is formatted as HTML4.
	 */
	public $html5 = false;

	/**
	 * @var array these are "escapeable" characters. When using one of these prefixed with a
	 * backslash, the character will be outputted without the backslash and is not interpreted
	 * as markdown.
	 */
	protected $escapeCharacters = [
	];


	/**
	 * Consume lines for a paragraph
	 *
	 * Allow headlines and code to break paragraphs
	 */
	protected function consumeParagraph($lines, $current)
	{
		// consume until newline
		$content = [];
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];
			if (!empty($line) && ltrim($line) !== '' &&
				!$this->identifyHeadline($line, $lines, $i) &&
				!$this->identifyHr($line, $lines, $i) &&
				!$this->identifyUl($line, $lines, $i) &&
                !$this->identifyOl($line, $lines, $i) &&
                !$this->identifyTable($line, $lines, $i)
			) {
				$content[] = $line;
			} else {
				break;
			}
		}
		$block = [
			'paragraph',
			'content' => $this->parseInline(implode("\n", $content)),
		];
		return [$block, --$i];
	}


	/**
	 * Parses escaped special characters.
	 * Creole uses tilde (~) for the escaping marker.
	 * @marker ~
	 */
	protected function parseEscape($text)
	{
		if (isset($text[1])) {
			return [['text', $text[1]], 2];
		}
		return [['text', $text[0]], 1];
	}

    /**
	 * @inheritdocs
	 *
	 * Parses a newline indicated by two backslashes.
	 */
	protected function renderText($text)
	{
		return str_replace(
			['&', '<', '>', "\\\\"],
			['&amp;', '&lt;', '&gt;', $this->html5 ? "<br>" : "<br />"],
			$text[1]);
	}
}
