/**
 * Multibutton JavaScript
 * Handles the expanding/collapsing of multibutton menu
 */

(function() {
    'use strict';
    
    let initialized = false;
    const containers = new Map();
    
    function toggleMenu(container, optionsList) {
        const isOpen = optionsList.classList.contains('active');
        if (isOpen) {
            optionsList.classList.remove('active');
        } else {
            // Close all other menus first
            containers.forEach(function(otherList) {
                if (otherList !== optionsList) {
                    otherList.classList.remove('active');
                }
            });
            optionsList.classList.add('active');
        }
    }
    
    function initMultibutton() {
        const multibuttonContainers = document.querySelectorAll('.cnb-multibutton-container:not([data-initialized])');
        
        if (multibuttonContainers.length === 0) {
            return;
        }
        
        multibuttonContainers.forEach(function(container) {
            const mainButton = container.querySelector('.cnb-multibutton-main');
            const optionsList = container.querySelector('.cnb-multibutton-options');
            
            if (!mainButton || !optionsList) {
                return;
            }
            
            // Mark as initialized
            container.setAttribute('data-initialized', 'true');
            
            // Store reference
            containers.set(container, optionsList);
            
            // Main button click handler
            mainButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleMenu(container, optionsList);
            });
            
            // Close when clicking on an option item (allow navigation)
            const optionItems = optionsList.querySelectorAll('a');
            optionItems.forEach(function(item) {
                item.addEventListener('click', function(e) {
                    // Don't prevent default - allow navigation
                    // Close menu after a short delay
                    setTimeout(function() {
                        optionsList.classList.remove('active');
                    }, 200);
                });
            });
        });
        
        // Global click handler to close menus when clicking outside (only add once)
        if (!initialized) {
            document.addEventListener('click', function(e) {
                let clickedInside = false;
                containers.forEach(function(optionsList, container) {
                    if (container.contains(e.target)) {
                        clickedInside = true;
                    } else if (optionsList.classList.contains('active')) {
                        optionsList.classList.remove('active');
                    }
                });
            });
            initialized = true;
        }
    }
    
    // Initialize immediately if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initMultibutton();
            // Also try after a delay for dynamically loaded content
            setTimeout(initMultibutton, 300);
        });
    } else {
        // DOM already loaded
        initMultibutton();
        // Also try after a delay for dynamically loaded content
        setTimeout(initMultibutton, 300);
    }
    
    // Use MutationObserver to handle dynamically added content
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            let shouldInit = false;
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(function(node) {
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

