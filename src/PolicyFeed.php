<?php

namespace Otago\SharedPolicyLibrary;

use SilverStripe\Control\Director;
use SilverStripe\View\Parsers\HTMLValue;
use SilverStripe\View\Parsers\ShortcodeParser;

/**
 * What the intranet's policy feed sends and the marketing website asks for, in one
 * place so the two can't drift apart.
 */
class PolicyFeed
{
    public const FIELDS = [
        'id',
        'title',
        'urlSegment',
        'intranetURL',
        'content',
        'plainContent',
        'keywords',
        'responsibility',
        'approvedBy',
        'approvalDate',
        'nextReview',
        'versionHistory',
        'section',
        'documentType',
        'pastoralCareOutcome',
        'lastEdited',
        'sections',
    ];

    public const SECTION_FIELDS = ['title', 'content', 'sort'];

    /**
     * The GraphQL query for a page of policies.
     */
    public static function query(): string
    {
        $fields = array_map(
            fn ($field) => $field === 'sections' ? 'sections { ' . implode(' ', self::SECTION_FIELDS) . ' }' : $field,
            self::FIELDS
        );

        return 'query readPolicies($limit: Int!, $offset: Int!) { readPolicies(limit: $limit, offset: $offset) { '
            . implode(' ', $fields) . ' } }';
    }

    /**
     * HTML whose links and pictures work on another website, for sending.
     *
     * Content is stored with shortcodes standing in for links to files and pages,
     * which only the sending site can resolve, and resolves to addresses relative to
     * it. So the shortcodes are resolved here and the addresses made full, in a
     * picture's srcset too: browsers prefer it to src.
     */
    public static function portableHTML(string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        $value = HTMLValue::create(ShortcodeParser::get_active()->parse($html));
        $document = $value->getDocument();
        foreach (['a' => 'href', 'img' => 'src'] as $tag => $attribute) {
            foreach ($document->getElementsByTagName($tag) as $element) {
                $url = trim((string) $element->getAttribute($attribute));
                if (self::isSiteRelative($url)) {
                    $element->setAttribute($attribute, Director::absoluteURL($url));
                }
            }
        }

        foreach ($document->getElementsByTagName('img') as $image) {
            if ($image->hasAttribute('srcset')) {
                $image->setAttribute('srcset', self::portableSrcset((string) $image->getAttribute('srcset')));
            }
        }

        return $value->getContent();
    }

    /**
     * What arrived, in the shape a policy page holds: the sections as one document, and
     * the policy's own content as "Read in conjunction with".
     */
    public static function flatten(array $payload): array
    {
        $payload['readWith'] = (string) ($payload['content'] ?? '');
        $payload['body'] = PolicyText::sections($payload['sections'] ?? []);

        return $payload;
    }

    private static function portableSrcset(string $srcset): string
    {
        $candidates = [];
        foreach (explode(',', $srcset) as $candidate) {
            $parts = preg_split('/\s+/', trim($candidate), 2);
            if ($parts[0] !== '') {
                $url = self::isSiteRelative($parts[0]) ? Director::absoluteURL($parts[0]) : $parts[0];
                $candidates[] = trim($url . ' ' . ($parts[1] ?? ''));
            }
        }

        return implode(', ', $candidates);
    }

    private static function isSiteRelative(string $url): bool
    {
        return str_starts_with($url, '/') && !str_starts_with($url, '//');
    }
}
