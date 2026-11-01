<?php

namespace Otago\SharedPolicyLibrary;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\FieldType\DBDate;

/**
 * The governance facts every policy has, on either website. Apply it to the site's
 * policy page. The columns are those the page's own table already has, so applying it
 * changes nothing in the database.
 */
class PolicyFacts extends Extension
{
    private static array $db = [
        'ApprovedBy' => 'Varchar(255)',
        'ApprovalDate' => DBDate::class,
        'NextReview' => DBDate::class,
        'Responsibility' => 'Text',
        'VersionHistory' => 'Text',
    ];
}
