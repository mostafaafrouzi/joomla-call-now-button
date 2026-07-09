/**
 * Call Now Button - admin icon selector (subform-safe)
 */
(function () {
    'use strict';

    function syncSelectedState(container) {
        var input = container.querySelector('input[type="hidden"]');
        if (!input) {
            return;
        }

        var value = input.value || 'phone';

        container.querySelectorAll('.cnb-icon-item').forEach(function (item) {
            item.classList.toggle('selected', item.getAttribute('data-icon') === value);
        });
    }

    function initContainer(container) {
        if (!container || container.dataset.cnbIconInit === '1') {
            return;
        }

        container.dataset.cnbIconInit = '1';
        syncSelectedState(container);
    }

    function initAll(root) {
        var scope = root || document;

        scope.querySelectorAll('.cnb-icon-selector').forEach(initContainer);
    }

    document.addEventListener('click', function (event) {
        var item = event.target.closest('.cnb-icon-item');

        if (!item) {
            return;
        }

        var container = item.closest('.cnb-icon-selector');

        if (!container) {
            return;
        }

        var input = container.querySelector('input[type="hidden"]');

        if (!input) {
            return;
        }

        container.querySelectorAll('.cnb-icon-item').forEach(function (el) {
            el.classList.remove('selected');
        });

        item.classList.add('selected');
        input.value = item.getAttribute('data-icon');

        if (typeof jQuery !== 'undefined') {
            jQuery(input).trigger('change');
        } else {
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    document.addEventListener('subform-row-add', function (event) {
        if (event.detail && event.detail.row) {
            initAll(event.detail.row);
        }
    });

    document.addEventListener('joomla:updated', function (event) {
        if (event.target) {
            initAll(event.target);
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initAll(document);
        });
    } else {
        initAll(document);
    }
})();
