<?php

namespace App\Roadbook;

/**
 * Formats geocache log texts: Markdown, then the geocaching.com BBCode
 * dialect (colors and smileys). Ported from the legacy Roadbook
 * parseMarkdown()/parseBBcode() DOM passes.
 */
class LogTextFormatter
{
    private const array SMILEYS = [
        ':)'  => 'icon_smile.gif',
        ':D'  => 'icon_smile_big.gif',
        '8D'  => 'icon_smile_cool.gif',
        ':I'  => 'icon_smile_blush.gif',
        ':P'  => 'icon_smile_tongue.gif',
        '}:)' => 'icon_smile_evil.gif',
        ';)'  => 'icon_smile_wink.gif',
        ':o)' => 'icon_smile_clown.gif',
        'B)'  => 'icon_smile_blackeye.gif',
        '8'   => 'icon_smile_8ball.gif',
        ':('  => 'icon_smile_sad.gif',
        '8)'  => 'icon_smile_shy.gif',
        ':O'  => 'icon_smile_shock.gif',
        ':(!' => 'icon_smile_angry.gif',
        'xx(' => 'icon_smile_dead.gif',
        '|)'  => 'icon_smile_sleepy.gif',
        ':X'  => 'icon_smile_kisses.gif',
        '^'   => 'icon_smile_approve.gif',
        'V'   => 'icon_smile_dissapprove.gif',
        '?'   => 'icon_smile_question.gif',
    ];

    private const array COLORS = [
        'black', 'blue', 'gold', 'green', 'maroon', 'navy', 'orange',
        'pink', 'purple', 'red', 'teal', 'white', 'yellow',
    ];

    private ?\JBBCode\Parser $bbcode = null;

    public function format(string $text): string
    {
        $html = new \cebe\markdown\Markdown()->parse($text);

        $parser = $this->bbcodeParser();
        $parser->parse($html);
        $html = $parser->getAsHtml();

        return $this->replaceSmileys($html);
    }

    private function bbcodeParser(): \JBBCode\Parser
    {
        if ($this->bbcode !== null) {
            return $this->bbcode;
        }

        $parser = new \JBBCode\Parser();
        $parser->addCodeDefinitionSet(new \JBBCode\DefaultCodeDefinitionSet());
        foreach (self::COLORS as $color) {
            $builder = new \JBBCode\CodeDefinitionBuilder($color, '<span style="color: ' . $color . ';">{param}</span>');
            $parser->addCodeDefinition($builder->build());
        }

        return $this->bbcode = $parser;
    }

    private function replaceSmileys(string $html): string
    {
        $search  = [];
        $replace = [];
        foreach (self::SMILEYS as $code => $image) {
            $search[]  = '[' . $code . ']';
            $replace[] = '<img src="/images/icons/' . $image . '" alt="' . htmlspecialchars((string) $code, ENT_QUOTES) . '" />';
        }

        return str_replace($search, $replace, $html);
    }
}
