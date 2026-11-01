<?php

namespace Otago\SharedPolicyLibrary;

use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * What the shared policy page reads from a site's policy page. A site says where its
 * text comes from, and which other addresses count as its own; the rest is the same on
 * either website.
 */
trait PolicyPresentation
{
    /**
     * The policy's sections, as PolicyText::sections() gives them.
     */
    abstract protected function policyBodySource(): string;

    /**
     * "Read in conjunction with", or the links of a policy with no sections.
     */
    abstract protected function policyReadWithSource(): string;

    /**
     * Other addresses the site counts as its own. See PolicyText::withExternalLinksMarked().
     *
     * @return string[]
     */
    abstract protected function policyOwnHosts(): array;

    public function getHasBody(): bool
    {
        return trim($this->policyBodySource()) !== '';
    }

    public function getHasReadWith(): bool
    {
        return trim($this->policyReadWithSource()) !== '';
    }

    public function BodyHTML(): DBHTMLText
    {
        return $this->preparedPolicyHTML(PolicyText::body($this->policyBodySource(), $this->policyOwnHosts()));
    }

    public function ReadWithHTML(): DBHTMLText
    {
        return $this->preparedPolicyHTML(PolicyText::aside($this->policyReadWithSource(), $this->policyOwnHosts()));
    }

    public function getPolicyVersion(): string
    {
        return PolicyText::versionFrom((string) $this->VersionHistory);
    }

    public function getPolicyPurpose(): string
    {
        return PolicyText::purpose($this->policyBodySource());
    }

    public function getPolicySummary(): string
    {
        return PolicyText::summary($this->getPolicyPurpose());
    }

    public function getApprovalDateISO(): string
    {
        return $this->dbObject('ApprovalDate')?->Format('y-MM-dd') ?: '';
    }

    public function getNextReviewISO(): string
    {
        return $this->dbObject('NextReview')?->Format('y-MM-dd') ?: '';
    }

    /**
     * Prepared text that still resolves any file and page links in it.
     */
    private function preparedPolicyHTML(string $html): DBHTMLText
    {
        return DBHTMLText::create()->setProcessShortcodes(true)->setValue($html);
    }
}
