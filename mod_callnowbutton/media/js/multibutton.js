/**
 * Multibutton JavaScript
 * Handles the expanding/collapsing of multibutton menu
 */

(function () {
    'use strict';

    let initialized = false;
    const containers = new Map();

    function setMenuOpen(mainButton, optionsList, open) {
        optionsList.classList.toggle('active', open);
        mainButton.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function toggleMenu(container, mainButton, optionsList) {
        const isOpen = optionsList.classList.contains('active');

        if (isOpen) {
            setMenuOpen(mainButton, optionsList, false);
            return;
        }

        containers.forEach(function (entry) {
            if (entry.optionsList !== optionsList) {
                setMenuOpen(entry.mainButton, entry.optionsList, false);
            }
        });

        setMenuOpen(mainButton, optionsList, true);
    }

    function initMultibutton() {
        const multibuttonContainers = document.querySelectorAll('.cnb-multibutton-container:not([data-initialized])');

        if (multibuttonContainers.length === 0) {
            return;
        }

        multibuttonContainers.forEach(function (container) {
            const mainButton = container.querySelector('.cnb-multibutton-main');
            const optionsList = container.querySelector('.cnb-multibutton-options');

            if (!mainButton || !optionsList) {
                return;
            }

            container.setAttribute('data-initialized', 'true');
            containers.set(container, { mainButton: mainButton, optionsList: optionsList });

            mainButton.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleMenu(container, mainButton, optionsList);
            });

            mainButton.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    setMenuOpen(mainButton, optionsList, false);
                }
            });

            const optionItems = optionsList.querySelectorAll('a');
            optionItems.forEach(function (item) {
                item.addEventListener('click', function () {
                    setTimeout(function () {
                        setMenuOpen(mainButton, optionsList, false);
                    }, 200);
                });
            });
        });

        if (!initialized) {
            document.addEventListener('click', function (e) {
                containers.forEach(function (entry) {
                    if (!entry.optionsList.classList.contains('active')) {
                        return;
                    }

                    if (!entry.mainButton.contains(e.target) && !entry.optionsList.contains(e.target)) {
                        setMenuOpen(entry.mainButton, entry.optionsList, false);
                    }
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') {
                    return;
                }

                containers.forEach(function (entry) {
                    if (entry.optionsList.classList.contains('active')) {
                        setMenuOpen(entry.mainButton, entry.optionsList, false);
                        entry.mainButton.focus();
                    }
                });
            });

            initialized = true;
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initMultibutton();
            setTimeout(initMultibutton, 300);
        });
    } else {
        initMultibutton();
        setTimeout(initMultibutton, 300);
    }

    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function (mutations) {
            let shouldInit = false;

            mutations.forEach(function (mutation) {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType === 1) {
                            if (node.classList && node.classList.contains('cnb-multibutton-container')) {
                                shouldInit = true;
                            } else if (node.querySelector && node.querySelector('.cnb-multibutton-container')) {
                                shouldInit = true;
                            }
                        }
                    });
                }
            });

            if (shouldInit) {
                setTimeout(initMultibutton, 100);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();
