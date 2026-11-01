<?php

namespace Otago\SharedPolicyLibrary\Tests;

use Otago\SharedPolicyLibrary\PolicyFeed;
use Otago\SharedPolicyLibrary\PolicySections;
use Otago\SharedPolicyLibrary\PolicyText;
use SilverStripe\Dev\SapphireTest;

class PolicyTextTest extends SapphireTest
{
    public function testWordStylingTakenOffAndTheRestKept(): void
    {
        $html = PolicyText::withoutWordStyling(
            '<p style="font-family: Arial, sans-serif; font-size: 12pt; mso-fareast-language: EN-NZ; text-align: center;">'
            . '<span style="color: black; font-weight: bold;">Heading</span></p>'
            . '<table width="948"><tr><td style="width: 25%;">Cell</td></tr></table>'
            . '<p><font face="Times">Old</font> text</p>'
        );

        foreach (['font-family', 'font-size', 'mso-', 'color', 'width', '<font'] as $gone) {
            $this->assertStringNotContainsString($gone, $html);
        }
        $this->assertStringContainsString('text-align: center', $html);
        $this->assertStringContainsString('font-weight: bold', $html);
        $this->assertStringContainsString('Old text', $html);
    }

    public function testTextWithoutStylingComesBackAsItWent(): void
    {
        $this->assertSame('<p>Plain</p>', PolicyText::withoutWordStyling('<p>Plain</p>'));
    }

    public function testClauseNumbersHangWithALevelEvenSplitAcrossRuns(): void
    {
        $html = PolicyText::body(
            '<p>1.&nbsp;The main roles</p>'
            . '<p><span>1</span><span>.1</span><span>.</span> Provide advice</p>'
            . '<p>a. A lettered item</p>'
            . '<p>2005 was a year, not a clause.</p>'
        );

        $this->assertStringContainsString('<p class="policy__clause policy__clause--1"><span class="policy__clause-number">1.</span>The main roles</p>', $html);
        $this->assertStringContainsString('policy__clause--2"><span class="policy__clause-number">1.1.</span>', $html);
        $this->assertStringContainsString('policy__clause--3"><span class="policy__clause-number">a.</span>A lettered item', $html);
        $this->assertStringContainsString('<p>2005 was a year, not a clause.</p>', $html);
    }

    public function testAnAsideHasNoClauses(): void
    {
        $this->assertStringNotContainsString('policy__clause', PolicyText::aside('<p>1. Not a clause here</p>'));
    }

    public function testNonBreakingSpacesMadeOrdinary(): void
    {
        $this->assertSame('<p>Te Pūkenga Policy</p>', PolicyText::withBreakableSpaces("<p>Te&nbsp;Pūkenga\u{00A0}Policy</p>"));
    }

    public function testOnlyLinksThatLeaveTheSiteAreMarked(): void
    {
        $html = PolicyText::withExternalLinksMarked(
            '<a href="https://example.com/a">Off</a>'
            . '<a href="https://www.op.ac.nz/b">Own host</a>'
            . '<a href="/c">Relative</a>'
            . '<a href="mailto:a@example.com">Email</a>'
            . '<a href="https://example.com/d"><img src="a.png" alt=""></a>',
            ['www.op.ac.nz']
        );

        $this->assertSame(1, substr_count($html, 'link--external'));
        $this->assertStringContainsString('<a href="https://example.com/a" class="link--external">Off</a>', $html);
    }

    public function testPlainTextKeepsWordsApart(): void
    {
        $this->assertSame('One Two & three', PolicyText::plain("<p>One</p><p>Two&nbsp;&amp; \n three</p>"));
    }

    public function testVersionReadOffTheFrontOfTheHistory(): void
    {
        $this->assertSame('1.1', PolicyText::versionFrom('1.1 — reviewed March'));
        $this->assertSame('1', PolicyText::versionFrom('Version 1'));
        $this->assertSame('2.3', PolicyText::versionFrom('v2.3'));
        $this->assertSame('', PolicyText::versionFrom('Reviewed in 2020'));
    }

    public function testSectionsInOrderUnderPlainHeadings(): void
    {
        $this->assertSame(
            '<section class="policy__section"><h2>First &amp; foremost</h2><p>One</p></section>'
            . '<section class="policy__section"><p>Two</p></section>',
            PolicyText::sections([
                ['title' => '', 'content' => '<p>Two</p>', 'sort' => 2],
                ['title' => '', 'content' => '  ', 'sort' => 3],
                ['title' => 'First & foremost', 'content' => '<p>One</p>', 'sort' => 1],
            ])
        );
    }

    public function testPurposeIsThePurposeSectionOrTheFirst(): void
    {
        $this->assertSame('Why', PolicyText::purpose(PolicyText::sections([
            ['title' => 'Scope', 'content' => '<p>Who</p>', 'sort' => 1],
            ['title' => 'Purpose and aims', 'content' => '<p>Why</p>', 'sort' => 2],
        ])));
        $this->assertSame('Who', PolicyText::purpose(PolicyText::sections([
            ['title' => 'Scope', 'content' => '<p>Who</p>', 'sort' => 1],
        ])));
        $this->assertSame('Loose text', PolicyText::purpose('<p>Loose text</p>'));
        $this->assertSame('', PolicyText::purpose(''));
    }

    public function testSummaryCutsOnlyWhatIsTooLong(): void
    {
        $this->assertSame('Short', PolicyText::summary('Short', 10));
        $this->assertSame('Too long…', PolicyText::summary('Too long by far', 9));
    }

    public function testTheQueryAsksForEveryField(): void
    {
        $query = PolicyFeed::query();
        foreach (PolicyFeed::FIELDS as $field) {
            $this->assertStringContainsString($field, $query);
        }
        $this->assertStringContainsString('sections { title content sort }', $query);
    }

    public function testSectionRules(): void
    {
        $this->assertTrue(PolicySections::isHidden('EAG'));
        $this->assertFalse(PolicySections::isHidden('Academic'));
        $this->assertSame('Te Pūkenga/NZIST', PolicySections::label('Te Pūkenga Council'));
        $this->assertSame('Academic', PolicySections::label('Academic'));
        $this->assertSame(
            ['hidden' => ['EAG'], 'labels' => ['Te Pūkenga Council' => 'Te Pūkenga/NZIST']],
            json_decode(PolicySections::rulesJSON(), true)
        );
    }
}
