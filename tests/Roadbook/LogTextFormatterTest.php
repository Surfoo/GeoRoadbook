<?php

namespace App\Tests\Roadbook;

use App\Roadbook\LogTextFormatter;
use PHPUnit\Framework\TestCase;

class LogTextFormatterTest extends TestCase
{
    private LogTextFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new LogTextFormatter();
    }

    public function testMarkdown(): void
    {
        $this->assertStringContainsString('<strong>great</strong>', $this->formatter->format('A **great** cache'));
    }

    public function testBbcodeColors(): void
    {
        $this->assertStringContainsString(
            '<span style="color: red;">warning</span>',
            $this->formatter->format('[red]warning[/red]'),
        );
    }

    public function testSmileys(): void
    {
        $html = $this->formatter->format('Thanks [:)] TFTC');
        $this->assertStringContainsString('/images/icons/icon_smile.gif', $html);
    }
}
