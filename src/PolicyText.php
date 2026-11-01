<?php

namespace Otago\SharedPolicyLibrary;

use DOMNode;
use DOMText;
use SilverStripe\Control\Director;
use SilverStripe\View\Parsers\HTMLValue;

/**
 * A policy's text prepared for the page. Policies are written on the intranet,
 * mostly pasted from Word, and both websites show them. The stored text is
 * never changed, so the intranet's feed always sends it as it was written.
 */
class PolicyText
{
    /**
     * A policy's sections as one document, in order, each under a plain heading.
     * Empty sections are left out.
     *
     * @param array<array{title?: string, content?: string, sort?: int}> $sections
     */
    public static function sections(array $sections): string
    {
        usort($sections, fn ($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        $html = '';
        foreach ($sections as $section) {
            $title = trim((string) ($section['title'] ?? ''));
            $content = trim((string) ($section['content'] ?? ''));

            if ($title === '' && $content === '') {
                continue;
            }

            $html .= '<section class="policy__section">';
            if ($title !== '') {
                $html .= '<h2>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
            }
            $html .= $content . '</section>';
        }

        return $html;
    }

    /**
     * A policy's body: spaces made breakable, Word's styling taken off, clause
     * numbers hanging and off-site links marked.
     *
     * @param string[] $ownHosts see withExternalLinksMarked()
     */
    public static function body(string $html, array $ownHosts = []): string
    {
        return self::withExternalLinksMarked(self::withHangingNumbers(self::prepared($html)), $ownHosts);
    }

    /**
     * Text beside the body, such as "Read in conjunction with": as body(), but
     * with no clause numbers to hang.
     *
     * @param string[] $ownHosts see withExternalLinksMarked()
     */
    public static function aside(string $html, array $ownHosts = []): string
    {
        return self::withExternalLinksMarked(self::prepared($html), $ownHosts);
    }

    private static function prepared(string $html): string
    {
        return self::withoutWordStyling(self::withBreakableSpaces($html));
    }

    /**
     * Word puts a non-breaking space between words almost at random. Each one
     * glues two words together, so lines break a word or a whole phrase early.
     */
    public static function withBreakableSpaces(string $html): string
    {
        return str_replace(['&nbsp;', "\u{00A0}"], ' ', $html);
    }

    /**
     * Word's own typesetting taken off, so a policy reads in the site's type.
     *
     * Policies pasted from Word carry their formatting in style attributes:
     * Times New Roman at 12pt, Word's colours and line heights, and fixed widths
     * on table cells. Those are dropped, along with `width` attributes and
     * <font> tags. Anything else a style says, such as bold, italics or
     * alignment, is kept.
     */
    public static function withoutWordStyling(string $html): string
    {
        if (trim($html) === '' || !preg_match('~style=|<font|\swidth=~i', $html)) {
            return $html;
        }

        $value = HTMLValue::create($html);
        $document = $value->getDocument();

        foreach (iterator_to_array($document->getElementsByTagName('*')) as $element) {
            if ($element->hasAttribute('width')) {
                $element->removeAttribute('width');
            }
            if (!$element->hasAttribute('style')) {
                continue;
            }

            $kept = [];
            foreach (explode(';', (string) $element->getAttribute('style')) as $declaration) {
                $property = strtolower(trim(strstr($declaration, ':', true) ?: ''));
                if ($property === '' || str_starts_with($property, 'mso-') || preg_match(
                    '~^(font|font-family|font-size|line-height|color|width|height|padding|margin|tab-stops|text-indent)$~',
                    $property
                )) {
                    continue;
                }
                $kept[] = trim($declaration);
            }

            if ($kept) {
                $element->setAttribute('style', implode('; ', $kept));
            } else {
                $element->removeAttribute('style');
            }
        }

        foreach (iterator_to_array($document->getElementsByTagName('font')) as $font) {
            while ($font->firstChild) {
                $font->parentNode->insertBefore($font->firstChild, $font);
            }
            $font->parentNode->removeChild($font);
        }

        return $value->getContent();
    }

    /**
     * Numbered clauses with their numbers hanging, indented by level.
     *
     * The intranet's numbered clauses are paragraphs with the number typed at
     * the front ("1.1. Empower ākonga…"), not lists, so nothing lines their text
     * up. A paragraph that opens with a clause number gets the number set apart
     * and a level: "1." is 1, "1.1." is 2, and a lettered item ("a.") is 3, the
     * clause under the one before it. The numbering itself is left as written.
     */
    public static function withHangingNumbers(string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        // Word splits a typed number across runs ("1" ".1" "."), so it is read
        // from each paragraph's text and cut from the front of its text nodes.
        $value = HTMLValue::create($html);
        $doc = $value->getDocument();
        foreach (iterator_to_array($doc->getElementsByTagName('p')) as $p) {
            $text = str_replace("\u{00A0}", ' ', $p->textContent);
            if (!preg_match('~^\s*((?:\d{1,2}\.)+\d{0,2}|[a-z]\.)\s+~u', $text, $m)) {
                continue;
            }

            $marker = $m[1];
            $level = ctype_alpha($marker[0])
                ? 3
                : min(3, count(array_filter(explode('.', $marker), 'strlen')));

            self::cutLeadingText($p, mb_strlen($m[0]));
            $number = $doc->createElement('span', $marker);
            $number->setAttribute('class', 'policy__clause-number');
            $p->insertBefore($number, $p->firstChild);
            $p->setAttribute('class', 'policy__clause policy__clause--' . $level);
        }

        return $value->getContent();
    }

    /**
     * Off-site links marked `link--external`, so each site can draw its arrow.
     *
     * Off-site means an http(s) link to anywhere but this site and the hosts
     * passed in, which are other addresses the site counts as its own: synced
     * content links to the live site by its full address, which a test server
     * would otherwise take for somewhere else. A link that wraps an image, or
     * has no text, is left alone.
     *
     * @param string[] $ownHosts
     */
    public static function withExternalLinksMarked(string $html, array $ownHosts = []): string
    {
        if (trim($html) === '' || stripos($html, '<a') === false) {
            return $html;
        }

        $value = HTMLValue::create($html);
        foreach (iterator_to_array($value->getDocument()->getElementsByTagName('a')) as $link) {
            $href = trim((string) $link->getAttribute('href'));
            $host = strtolower((string) parse_url($href, PHP_URL_HOST));
            if (
                !preg_match('~^https?://~i', $href)
                || in_array($host, $ownHosts, true)
                || Director::is_site_url($href)
                || $link->getElementsByTagName('img')->length > 0
                || trim($link->textContent) === ''
            ) {
                continue;
            }

            $classes = preg_split('/\s+/', trim((string) $link->getAttribute('class')), -1, PREG_SPLIT_NO_EMPTY);
            if (!in_array('link--external', $classes, true)) {
                $classes[] = 'link--external';
            }
            $link->setAttribute('class', implode(' ', $classes));
        }

        return $value->getContent();
    }

    /**
     * HTML as one line of plain text, for summaries and searching.
     */
    public static function plain(string $html): string
    {
        $spaced = preg_replace('/<\/(p|div|h[1-6]|li|tr|section|td|th)>/i', ' ', $html);
        $plain = html_entity_decode(strip_tags((string) $spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', $plain)));
    }

    /**
     * The version number off the front of a policy's free-text version history,
     * written newest first as "1.1 — reviewed …". Nothing where it doesn't start
     * with one, rather than a guess.
     *
     * The word "Version" has to be matched before the bare "v", or the "v" is
     * spent on the V of "Version" and the digit is looked for at the "e".
     */
    public static function versionFrom(string $history): string
    {
        $history = self::plain($history);

        return preg_match('~^\s*(?:version\b|v)?\.?\s*(\d+(?:\.\d+)*)~i', $history, $m) ? $m[1] : '';
    }

    /**
     * Remove the first $count characters of a node's text, however many text
     * nodes they are spread across. Returns how many were left to remove.
     */
    private static function cutLeadingText(DOMNode $node, int $count): int
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($count <= 0) {
                break;
            }
            if ($child instanceof DOMText) {
                $length = mb_strlen($child->data);
                $child->data = $length <= $count ? '' : mb_substr($child->data, $count);
                $count -= min($length, $count);
            } else {
                $count = self::cutLeadingText($child, $count);
            }
        }

        return $count;
    }
}
