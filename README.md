# Shared Policy Library

What the Policy Library shares between the marketing website and the intranet, so
each is written once. Policies are written on the intranet and shown on both.

## What's here

**`PolicyText`** prepares a policy's text for the page. The stored text is never
changed.

| Method | Does |
|---|---|
| `body($html, $ownHosts)` | All of the below, for a policy's body |
| `aside($html, $ownHosts)` | The same without clause numbers, for "Read in conjunction with" |
| `withBreakableSpaces($html)` | Word's non-breaking spaces made ordinary |
| `withoutWordStyling($html)` | Word's fonts, sizes, colours and widths taken off; bold, italics and alignment kept |
| `withHangingNumbers($html)` | Typed clause numbers set apart as `.policy__clause-number`, with `.policy__clause--1` to `--3` on the paragraph |
| `withExternalLinksMarked($html, $ownHosts)` | Links off the site get `.link--external` |
| `plain($html)` | One line of plain text |
| `versionFrom($history)` | The version number off the front of a version history |

`$ownHosts` lists other addresses a site counts as its own. The marketing website
passes `www.op.ac.nz`, the intranet `online.op.ac.nz`.

**`PolicySections`** holds the Section filter's rules: which sections aren't offered,
and which show under another name.

## What isn't here

- **Styles and templates.** Each site draws these classes itself: the marketing
  website's stylesheet is built on its Tailwind theme and the intranet's isn't.
- **Search.** The marketing website uses Algolia, the intranet its own matching.

## Tests

From a site that requires this module:

```sh
vendor/bin/phpunit vendor/otago/shared-policy-library/tests
```
