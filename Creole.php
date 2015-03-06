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
//	use block\ListTrait {
//		// Check Ul List before headline
//		identifyUl as protected identifyBUl;
//		consumeUl as protected consumeBUl;
//	}
//	use block\QuoteTrait;
//	use block\RuleTrait {
//		// Check Hr before checking lists
//		identifyHr as protected identifyAHr;
//		consumeHr as protected consumeAHr;
//	}
//
// include inline element parsing using traits
//	use inline\CodeTrait;
	use creole\inline\EmphStrongTrait;
//	use inline\LinkTrait;

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
//		'\\', // backslash
//		'`', // backtick
//		'*', // asterisk
//		'_', // underscore
//		'{', '}', // curly braces
//		'[', ']', // square brackets
//		'(', ')', // parentheses
//		'#', // hash mark
//		'+', // plus sign
//		'-', // minus sign (hyphen)
//		'.', // dot
//		'!', // exclamation mark
//		'<', '>',
	];


	/**
	 * @inheritDoc
	 */
	protected function prepare()
	{
		// reset references in LinkTrait
		// $this->references = [];
	}

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
			if (!empty($line) && ltrim($line) !== '' /* &&
				!($line[0] === "\t" || $line[0] === " " && strncmp($line, '    ', 4) === 0) &&
				!$this->identifyHeadline($line, $lines, $i)*/)
			{
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
		if (isset($text[1]) /* && in_array($text[1], $this->escapeCharacters) */ ) {
			return [['text', $text[1]], 2];
		}
		return [['text', $text[0]], 1];
	}

    /**
	 * @inheritdocs
	 *
	 * Parses a newline indicated by two backslashes on the end of a creole line.
	 */
	protected function renderText($text)
	{
		return str_replace("\\\\\n", $this->html5 ? "<br>\n" : "<br />\n", $text[1]);
	}
}
