# Shared Policy Library

What the Policy Library shares between the marketing website and the intranet, so
each is written once. Policies are written on the intranet and shown on both.

## What's here

**`PolicyText`** prepares a policy's text for the page. The stored text is never
changed.

| Method | Does |
|---|---|
| `sections($sections)` | A policy's sections as one document |
| `body($html, $ownHosts)` | All of the below, for a policy's body |
| `aside($html, $ownHosts)` | The same without clause numbers, for "Read in conjunction with" |
| `withBreakableSpaces($html)` | Word's non-breaking spaces made ordinary |
| `withoutWordStyling($html)` | Word's fonts, sizes, colours and widths taken off; bold, italics and alignment kept |
| `withHangingNumbers($html)` | Typed clause numbers set apart as `.policy__clause-number`, with `.policy__clause--1` to `--3` on the paragraph |
| `withExternalLinksMarked($html, $ownHosts)` | Links off the site get `.link--external` |
| `purpose($body)` | What the policy is for, from its Purpose section or its first |
| `summary($text, $limit)` | Text cut to length for a listing |
| `plain($html)` | One line of plain text |
| `versionFrom($history)` | The version number off the front of a version history |

`$ownHosts` lists other addresses a site counts as its own. The marketing website
passes `www.op.ac.nz`, the intranet `online.op.ac.nz`.

**`PolicySections`** holds the Section filter's rules: which sections aren't offered,
and which show under another name. `rulesJSON()` gives them to a filter drawn in the
browser.

**The policy page.** `templates/Otago/SharedPolicyLibrary/Includes/PolicyPage.ss` is the whole
page, and `client/scss/_policy-page.scss` lays it out. A site's layout template includes the first:

```
<% include Otago\SharedPolicyLibrary\Includes\PolicyPage %>
```

and its stylesheet includes the second with the site's colours, adding its own page padding, links
and print button:

```scss
@import 'policy-page';

.policy {
    @include policy-page($blue, $text, $muted);
}
```

The page reads these from the site's policy page:

| Field | Is |
|---|---|
| `Library` | The library page, for the trail and "Back to library" |
| `Title`, `ApprovedBy`, `Responsibility` | Text |
| `ApprovalDate`, `NextReview` | Dates |
| `PolicyVersion`, `PolicySection`, `PastoralCareOutcome` | Text, shown when there is any |
| `HasBody`, `BodyHTML` | Whether it has sections, and the policy text |
| `HasReadWith`, `ReadWithHTML` | Whether it has "Read in conjunction with", and that text |

`client/javascript/policy-print.js` reveals the print button and binds it. A site loads it as a
script (it's exposed), or imports it into its bundle.

**`PolicyFeed`** is the contract between the two sites: the fields the intranet's feed sends, and the
GraphQL query the marketing website asks with.

**The library's controls.** `client/scss/_library-controls.scss` has mixins for what both libraries
draw alike: the view buttons, the "Load more" circle, the filter's checkboxes and the no-results
message.

## What isn't here

- **The library page.** The marketing website draws its listing in the browser from Algolia, the
  intranet on the server with its own matching.
- **Each site's links and buttons,** which come from its own styles.

## Tests

From a site that requires this module:

```sh
vendor/bin/phpunit vendor/otago/shared-policy-library/tests
```
