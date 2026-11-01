<?php

namespace Otago\SharedPolicyLibrary;

/**
 * The rules for the library's Section filter, from the notes on the design
 * boards: EAG is not offered, and Te Pūkenga Council is shown under its current
 * name.
 *
 * Renaming changes only the label: the stored value is the intranet's. Hiding
 * removes the option, not the policies, which stay listed and searchable.
 */
class PolicySections
{
    public const HIDDEN = ['EAG'];

    public const LABELS = ['Te Pūkenga Council' => 'Te Pūkenga/NZIST'];

    public static function isHidden(string $section): bool
    {
        return in_array($section, self::HIDDEN, true);
    }

    public static function label(string $section): string
    {
        return self::LABELS[$section] ?? $section;
    }

    /**
     * The rules as JSON, for a site whose filter is drawn in the browser.
     */
    public static function rulesJSON(): string
    {
        return json_encode(['hidden' => self::HIDDEN, 'labels' => self::LABELS], JSON_UNESCAPED_UNICODE);
    }
}
