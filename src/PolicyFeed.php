<?php

namespace Otago\SharedPolicyLibrary;

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
}
