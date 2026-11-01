<%-- A policy, on either website, as the policy board draws it at 1920: a 280px column
     of governance facts beside an 800px column holding the trail, the title and the
     policy itself. The facts begin level with the first heading of the policy, and
     "Back to library" closes their column at the foot of the policy. On a narrow screen
     the four stack in reading order. See the README for what a site's page provides. --%>
<div class="policy">
    <div class="policy__page">

        <%-- A short trail of its own: library, then policy. --%>
        <div class="policy__head">
            <nav class="policy__breadcrumbs" aria-label="Breadcrumb">
                <ol class="policy__crumbs">
                    <% if $Library %>
                        <li class="policy__crumb">
                            <a href="$Library.Link" class="no-link-style">$Library.MenuTitle.XML</a>
                        </li>
                    <% end_if %>
                    <li class="policy__crumb" aria-current="page">$Title.XML</li>
                </ol>
            </nav>

            <h1 class="policy__title">$Title</h1>
        </div>

        <%-- The governance facts, as a sidebar beside the policy rather than a band
             above it: they are reference material someone checks while reading, not an
             introduction they read once. --%>
        <aside class="policy__meta" aria-label="Policy details">
            <%-- Approval facts and the print action are one block: the board rules off
                 after the print link, not before it. --%>
            <div class="policy__meta-block policy__meta-block--first">
                <dl class="policy__meta-facts">
                    <% if $ApprovedBy %>
                        <div class="policy__meta-row">
                            <dt>Approved by</dt>
                            <dd>$ApprovedBy</dd>
                        </div>
                    <% end_if %>
                    <%-- Approval date and next review are one fact in two halves, so the
                         board sets them close together and away from the rest. --%>
                    <% if $ApprovalDate %>
                        <div class="policy__meta-row policy__meta-row--tight">
                            <dt>Approval date</dt>
                            <dd>$ApprovalDate.Format('d MMMM y')</dd>
                        </div>
                    <% end_if %>
                    <% if $NextReview %>
                        <div class="policy__meta-row">
                            <dt>Next review</dt>
                            <dd>$NextReview.Format('d MMMM y')</dd>
                        </div>
                    <% end_if %>
                    <% if $PolicyVersion %>
                        <div class="policy__meta-row">
                            <dt>Version</dt>
                            <dd>$PolicyVersion</dd>
                        </div>
                    <% end_if %>
                </dl>

                <%-- Hidden until a script binds it: printing is the one thing here that
                     needs JavaScript, and a dead button is worse than no button. --%>
                <p class="policy__meta-actions">
                    <button type="button" class="policy__print" data-policy-print hidden>
                        <span class="policy__print-link link link--content font-bodyHeavy">Print this page</span>
                    </button>
                </p>
            </div>

            <% if $PolicySection || $Responsibility || $PastoralCareOutcome %>
                <dl class="policy__meta-block">
                    <% if $PolicySection %>
                        <div class="policy__meta-stack">
                            <dt>Section</dt>
                            <dd>$PolicySection</dd>
                        </div>
                    <% end_if %>
                    <% if $Responsibility %>
                        <div class="policy__meta-stack">
                            <dt>Policy owner</dt>
                            <dd>$Responsibility</dd>
                        </div>
                    <% end_if %>
                    <% if $PastoralCareOutcome %>
                        <div class="policy__meta-stack">
                            <dt>Pastoral Care Code outcomes</dt>
                            <dd>$PastoralCareOutcome</dd>
                        </div>
                    <% end_if %>
                </dl>
            <% end_if %>

            <% if $HasReadWith && $HasBody %>
                <div class="policy__meta-block policy__read-with">
                    <h2 class="policy__meta-heading">Read in conjunction with</h2>
                    <div class="policy__read-with-body">$ReadWithHTML</div>
                </div>
            <% end_if %>
        </aside>

        <div class="policy__content">
            <% if $HasBody %>
                <div class="policy__body typography">
                    $BodyHTML
                </div>
            <%-- A policy kept as a document has no sections, only links to download it,
                 filed under "read in conjunction with". Those links are the policy, so
                 they are its body here rather than a footnote beside an empty page. --%>
            <% else_if $HasReadWith %>
                <div class="policy__body typography">
                    $ReadWithHTML
                </div>
            <% end_if %>
        </div>

        <% if $Library %>
            <p class="policy__back">
                <a href="$Library.Link" class="policy__back-link link link--content font-bodyHeavy">
                    Back to library
                </a>
            </p>
        <% end_if %>

    </div>
</div>
