// The print action on a policy page. The button is rendered hidden and revealed here,
// so a visitor without JavaScript never sees a control that cannot do anything.
(function () {
    function bind() {
        document.querySelectorAll('[data-policy-print]').forEach(function (button) {
            button.hidden = false;
            button.addEventListener('click', function () {
                window.print();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
