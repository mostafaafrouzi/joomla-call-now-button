/**
 * Custom form validation for Call Now Button module
 * 
 * @package     Call Now Button
 * @subpackage  mod_callnowbutton
 * @copyright   Copyright (C) 2024 Call Now Button. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.querySelector('form[name="adminForm"]');
        if (!form) {
            return;
        }
        
        // Custom validation before form submit
        form.addEventListener('submit', function(e) {
            var buttonType = form.querySelector('[name="jform[params][button_type]"]');
            var linkType = form.querySelector('[name="jform[params][link_type]"]');
            var phoneNumber = form.querySelector('[name="jform[params][phone_number]"]');
            var customUrl = form.querySelector('[name="jform[params][custom_url]"]');
            
            // Check if single button type
            if (buttonType && buttonType.value === 'single') {
                if (linkType) {
                    var linkTypeValue = linkType.value;
                    
                    // Check phone number for whatsapp or phone
                    if ((linkTypeValue === 'whatsapp' || linkTypeValue === 'phone') && phoneNumber) {
                        var phoneValue = phoneNumber.value ? phoneNumber.value.trim() : '';
                        if (phoneValue === '') {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Show error message
                            alert('Please enter a phone number.');
                            
                            // Focus on phone number field
                            phoneNumber.focus();
                            var phoneGroup = phoneNumber.closest('.control-group') || phoneNumber.closest('.form-group');
                            if (phoneGroup) {
                                phoneGroup.classList.add('error');
                            }
                            
                            return false;
                        }
                        
                        // Additional validation for WhatsApp - must have country code (at least 10 digits)
                        if (linkTypeValue === 'whatsapp') {
                            // Remove all non-numeric characters
                            var phoneDigits = phoneValue.replace(/[^0-9]/g, '');
                            
                            // WhatsApp requires country code - minimum 10 digits, maximum 15 digits
                            if (phoneDigits.length < 10) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                // Show error message
                                alert('WhatsApp phone number must include country code and be at least 10 digits long (e.g., 989123456789 for Iran).');
                                
                                // Focus on phone number field
                                phoneNumber.focus();
                                phoneNumber.closest('.control-group')?.classList.add('error');
                                
                                return false;
                            }
                            
                            if (phoneDigits.length > 15) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                // Show error message
                                alert('Phone number is too long. Maximum 15 digits allowed.');
                                
                                // Focus on phone number field
                                phoneNumber.focus();
                                phoneNumber.closest('.control-group')?.classList.add('error');
                                
                                return false;
                            }
                        }
                    }
                    
                    // Check custom URL
                    if (linkTypeValue === 'custom' && customUrl) {
                        if (!customUrl.value || customUrl.value.trim() === '') {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Show error message
                            alert('Please enter a custom URL.');
                            
                            // Focus on custom URL field
                            customUrl.focus();
                            var urlGroup = customUrl.closest('.control-group') || customUrl.closest('.form-group');
                            if (urlGroup) {
                                urlGroup.classList.add('error');
                            }
                            
                            return false;
                        }
                    }
                }
            }
        });
        
        // Dynamic validation on field change
        // Try different possible field name formats
        var buttonTypeField = form.querySelector('[name="jform[params][button_type]"]') || 
                             form.querySelector('[name="params[button_type]"]') ||
                             form.querySelector('[id*="button_type"]');
        var linkTypeField = form.querySelector('[name="jform[params][link_type]"]') || 
                           form.querySelector('[name="params[link_type]"]') ||
                           form.querySelector('[id*="link_type"]');
        
        if (buttonTypeField) {
            buttonTypeField.addEventListener('change', function() {
                updateRequiredFields();
            });
        }
        
        if (linkTypeField) {
            linkTypeField.addEventListener('change', function() {
                updateRequiredFields();
            });
        }
        
        function updateRequiredFields() {
            // Try different possible field name formats
            var buttonType = form.querySelector('[name="jform[params][button_type]"]') || 
                            form.querySelector('[name="params[button_type]"]') ||
                            form.querySelector('[id*="button_type"]');
            var linkType = form.querySelector('[name="jform[params][link_type]"]') || 
                          form.querySelector('[name="params[link_type]"]') ||
                          form.querySelector('[id*="link_type"]');
            var phoneNumber = form.querySelector('[name="jform[params][phone_number]"]') || 
                             form.querySelector('[name="params[phone_number]"]') ||
                             form.querySelector('[id*="phone_number"]');
            var customUrl = form.querySelector('[name="jform[params][custom_url]"]') || 
                           form.querySelector('[name="params[custom_url]"]') ||
                           form.querySelector('[id*="custom_url"]');
            
            // Remove required attributes first
            if (phoneNumber) {
                phoneNumber.removeAttribute('required');
                phoneNumber.removeAttribute('aria-required');
            }
            if (customUrl) {
                customUrl.removeAttribute('required');
                customUrl.removeAttribute('aria-required');
            }
            
            // Add required based on conditions
            if (buttonType && buttonType.value === 'single' && linkType) {
                if ((linkType.value === 'whatsapp' || linkType.value === 'phone') && phoneNumber) {
                    phoneNumber.setAttribute('required', 'required');
                    phoneNumber.setAttribute('aria-required', 'true');
                } else if (linkType.value === 'custom' && customUrl) {
                    customUrl.setAttribute('required', 'required');
                    customUrl.setAttribute('aria-required', 'true');
                }
            }
        }
        
        // Function to toggle field visibility based on button_type and link_type
        // This works together with Joomla's showon attribute
        function toggleFieldVisibility() {
            // Try different possible field name formats
            var buttonType = form.querySelector('[name="jform[params][button_type]"]') || 
                            form.querySelector('[name="params[button_type]"]') ||
                            form.querySelector('[id*="button_type"]');
            var linkTypeField = form.querySelector('[name="jform[params][link_type]"]') || 
                              form.querySelector('[name="params[link_type]"]') ||
                              form.querySelector('[id*="link_type"]');
            
            // Find field containers using multiple methods
            var linkTypeContainer = null;
            if (linkTypeField) {
                // Try to find the parent container
                var parent = linkTypeField.parentElement;
                while (parent && parent !== form) {
                    if (parent.classList && (parent.classList.contains('control-group') || 
                                             parent.classList.contains('form-group') ||
                                             parent.classList.contains('control-wrapper'))) {
                        linkTypeContainer = parent;
                        break;
                    }
                    parent = parent.parentElement;
                }
            }
            
            var phoneNumberElement = form.querySelector('[id*="phone_number"]');
            var phoneNumberField = null;
            if (phoneNumberElement) {
                var parent = phoneNumberElement.parentElement;
                while (parent && parent !== form) {
                    if (parent.classList && (parent.classList.contains('control-group') || 
                                             parent.classList.contains('form-group') ||
                                             parent.classList.contains('control-wrapper'))) {
                        phoneNumberField = parent;
                        break;
                    }
                    parent = parent.parentElement;
                }
            }
            
            var customUrlElement = form.querySelector('[id*="custom_url"]');
            var customUrlField = null;
            if (customUrlElement) {
                var parent = customUrlElement.parentElement;
                while (parent && parent !== form) {
                    if (parent.classList && (parent.classList.contains('control-group') || 
                                             parent.classList.contains('form-group') ||
                                             parent.classList.contains('control-wrapper'))) {
                        customUrlField = parent;
                        break;
                    }
                    parent = parent.parentElement;
                }
            }
            
            if (!buttonType) {
                return;
            }
            
            var buttonTypeValue = buttonType.value;
            if (!buttonTypeValue && buttonType.options && buttonType.selectedIndex >= 0) {
                var selectedOption = buttonType.options[buttonType.selectedIndex];
                if (selectedOption) {
                    buttonTypeValue = selectedOption.value;
                }
            }
            
            // Show/hide link_type field - should be visible when button_type is 'single'
            // Let Joomla's showon handle this completely - don't interfere
            if (linkTypeField) {
                if (buttonTypeValue === 'single') {
                    // Ensure field is enabled, but don't override Joomla's showon
                    linkTypeField.removeAttribute('disabled');
                }
            }
            
            // Only process phone_number and custom_url if button_type is 'single'
            if (buttonTypeValue !== 'single') {
                // Hide these fields if not single mode
                if (phoneNumberField) {
                    phoneNumberField.style.display = 'none';
                    phoneNumberField.classList.add('hidden');
                }
                if (customUrlField) {
                    customUrlField.style.display = 'none';
                    customUrlField.classList.add('hidden');
                }
                return;
            }
            
            // Get link_type value only if button_type is 'single'
            var linkTypeValue = '';
            if (linkTypeField) {
                linkTypeValue = linkTypeField.value;
                if (!linkTypeValue && linkTypeField.options && linkTypeField.selectedIndex >= 0) {
                    var selectedOption = linkTypeField.options[linkTypeField.selectedIndex];
                    if (selectedOption) {
                        linkTypeValue = selectedOption.value;
                    }
                }
                // If no value, check default or set to 'phone'
                if (!linkTypeValue) {
                    // Check if there's a default value in the option
                    if (linkTypeField.options && linkTypeField.options.length > 0) {
                        // Try to find the default option (usually first 'phone' option)
                        for (var i = 0; i < linkTypeField.options.length; i++) {
                            if (linkTypeField.options[i].value === 'phone') {
                                linkTypeField.selectedIndex = i;
                                linkTypeValue = 'phone';
                                break;
                            }
                        }
                        // If still no value, use first option
                        if (!linkTypeValue && linkTypeField.options[0]) {
                            linkTypeField.selectedIndex = 0;
                            linkTypeValue = linkTypeField.options[0].value;
                        }
                    } else {
                        linkTypeValue = 'phone';
                    }
                }
            } else {
                // If link_type field doesn't exist yet, default to 'phone'
                linkTypeValue = 'phone';
            }
            
            // Show/hide phone_number field based on link_type
            if (phoneNumberField) {
                if (linkTypeValue === 'whatsapp' || linkTypeValue === 'phone') {
                    // Show the field - remove all hiding mechanisms
                    phoneNumberField.style.display = '';
                    phoneNumberField.style.visibility = '';
                    phoneNumberField.classList.remove('hidden');
                    phoneNumberField.classList.remove('hide');
                    phoneNumberField.removeAttribute('hidden');
                    // Also check the input element
                    if (phoneNumberElement) {
                        phoneNumberElement.removeAttribute('hidden');
                        phoneNumberElement.removeAttribute('disabled');
                        phoneNumberElement.style.display = '';
                    }
                } else {
                    // Hide the field
                    phoneNumberField.style.display = 'none';
                    phoneNumberField.classList.add('hidden');
                }
            }
            
            // Show/hide custom_url field based on link_type
            if (customUrlField) {
                if (linkTypeValue === 'custom') {
                    // Show the field - remove all hiding mechanisms
                    customUrlField.style.display = '';
                    customUrlField.style.visibility = '';
                    customUrlField.classList.remove('hidden');
                    customUrlField.classList.remove('hide');
                    customUrlField.removeAttribute('hidden');
                    // Also check the input element
                    if (customUrlElement) {
                        customUrlElement.removeAttribute('hidden');
                        customUrlElement.removeAttribute('disabled');
                        customUrlElement.style.display = '';
                    }
                } else {
                    // Hide the field
                    customUrlField.style.display = 'none';
                    customUrlField.classList.add('hidden');
                }
            }
            
            // Show/hide custom_url_target and custom_url_rel fields based on link_type
            var customUrlTargetElement = form.querySelector('[id*="custom_url_target"]') || 
                                        form.querySelector('[name*="custom_url_target"]');
            var customUrlTargetField = null;
            if (customUrlTargetElement) {
                var parent = customUrlTargetElement.parentElement;
                while (parent && parent !== form) {
                    if (parent.classList && (parent.classList.contains('control-group') || 
                                             parent.classList.contains('form-group') ||
                                             parent.classList.contains('control-wrapper'))) {
                        customUrlTargetField = parent;
                        break;
                    }
                    parent = parent.parentElement;
                }
            }
            
            var customUrlRelElement = form.querySelector('[id*="custom_url_rel"]') || 
                                     form.querySelector('[name*="custom_url_rel"]');
            var customUrlRelField = null;
            if (customUrlRelElement) {
                var parent = customUrlRelElement.parentElement;
                while (parent && parent !== form) {
                    if (parent.classList && (parent.classList.contains('control-group') || 
                                             parent.classList.contains('form-group') ||
                                             parent.classList.contains('control-wrapper'))) {
                        customUrlRelField = parent;
                        break;
                    }
                    parent = parent.parentElement;
                }
            }
            
            // Show/hide custom_url_target field
            if (customUrlTargetField) {
                if (buttonTypeValue === 'single' && linkTypeValue === 'custom') {
                    customUrlTargetField.style.display = '';
                    customUrlTargetField.style.visibility = '';
                    customUrlTargetField.classList.remove('hidden');
                    customUrlTargetField.classList.remove('hide');
                    customUrlTargetField.removeAttribute('hidden');
                    if (customUrlTargetElement) {
                        customUrlTargetElement.removeAttribute('hidden');
                        customUrlTargetElement.removeAttribute('disabled');
                        customUrlTargetElement.style.display = '';
                    }
                } else {
                    customUrlTargetField.style.display = 'none';
                    customUrlTargetField.classList.add('hidden');
                }
            }
            
            // Show/hide custom_url_rel field
            if (customUrlRelField) {
                if (buttonTypeValue === 'single' && linkTypeValue === 'custom') {
                    customUrlRelField.style.display = '';
                    customUrlRelField.style.visibility = '';
                    customUrlRelField.classList.remove('hidden');
                    customUrlRelField.classList.remove('hide');
                    customUrlRelField.removeAttribute('hidden');
                    if (customUrlRelElement) {
                        customUrlRelElement.removeAttribute('hidden');
                        customUrlRelElement.removeAttribute('disabled');
                        customUrlRelElement.style.display = '';
                    }
                } else {
                    customUrlRelField.style.display = 'none';
                    customUrlRelField.classList.add('hidden');
                }
            }
            
            // Show/hide WhatsApp hint note field
            // Try multiple selectors to find the note field
            var whatsappHintField = form.querySelector('[id*="phone_number_whatsapp_hint"]') || 
                                   form.querySelector('[name*="phone_number_whatsapp_hint"]') ||
                                   form.querySelector('[id*="jform_params_phone_number_whatsapp_hint"]') ||
                                   form.querySelector('[name="jform[params][phone_number_whatsapp_hint]"]');
            
            // Also try to find by label text
            if (!whatsappHintField) {
                var labels = form.querySelectorAll('label');
                for (var i = 0; i < labels.length; i++) {
                    if (labels[i].getAttribute('for') && labels[i].getAttribute('for').indexOf('phone_number_whatsapp_hint') !== -1) {
                        whatsappHintField = document.getElementById(labels[i].getAttribute('for'));
                        if (whatsappHintField) break;
                    }
                }
            }
            
            // Try to find by class or data attribute
            if (!whatsappHintField) {
                var allFields = form.querySelectorAll('[class*="note"], [data-field-type="note"], .note, [class*="text-danger"]');
                for (var j = 0; j < allFields.length; j++) {
                    var fieldText = allFields[j].textContent || allFields[j].innerText || '';
                    if (fieldText.indexOf('country code') !== -1 || 
                        fieldText.indexOf('country code and') !== -1 ||
                        fieldText.indexOf('989123456789') !== -1 ||
                        fieldText.indexOf('971501234567') !== -1) {
                        whatsappHintField = allFields[j];
                        break;
                    }
                }
            }
            
            // Try to find by position - after phone_number field
            if (!whatsappHintField && phoneNumberField) {
                var nextSibling = phoneNumberField.nextElementSibling;
                var checkCount = 0;
                while (nextSibling && checkCount < 3) {
                    checkCount++;
                    var siblingText = nextSibling.textContent || nextSibling.innerText || '';
                    if (siblingText.indexOf('country code') !== -1 || 
                        siblingText.indexOf('989123456789') !== -1 ||
                        (nextSibling.classList && (nextSibling.classList.contains('text-danger') || nextSibling.classList.contains('note')))) {
                        whatsappHintField = nextSibling;
                        break;
                    }
                    nextSibling = nextSibling.nextElementSibling;
                }
            }
            
            if (whatsappHintField) {
                // Find the parent container - try multiple container types
                var hintContainer = whatsappHintField;
                var parent = whatsappHintField.parentElement;
                var maxDepth = 10; // Prevent infinite loops
                var depth = 0;
                
                while (parent && parent !== form && depth < maxDepth) {
                    depth++;
                    if (parent.classList) {
                        if (parent.classList.contains('control-group') || 
                            parent.classList.contains('form-group') ||
                            parent.classList.contains('control-wrapper') ||
                            parent.classList.contains('field-wrapper') ||
                            parent.classList.contains('note-field')) {
                            hintContainer = parent;
                            break;
                        }
                    }
                    // Also check if it's a direct child of a fieldset or div with specific classes
                    if (parent.tagName === 'FIELDSET' || 
                        (parent.tagName === 'DIV' && parent.classList && parent.classList.length > 0)) {
                        hintContainer = parent;
                        break;
                    }
                    parent = parent.parentElement;
                }
                
                // If we couldn't find a container, use the field itself
                if (hintContainer === whatsappHintField && whatsappHintField.parentElement) {
                    hintContainer = whatsappHintField.parentElement;
                }
                
                // Show hint only when button_type is 'single' and link_type is 'whatsapp'
                if (buttonTypeValue === 'single' && linkTypeValue === 'whatsapp') {
                    hintContainer.style.display = '';
                    hintContainer.style.visibility = '';
                    hintContainer.style.opacity = '';
                    hintContainer.classList.remove('hidden');
                    hintContainer.classList.remove('hide');
                    hintContainer.removeAttribute('hidden');
                    // Also ensure all child elements are visible
                    var children = hintContainer.querySelectorAll('*');
                    for (var k = 0; k < children.length; k++) {
                        children[k].style.display = '';
                        children[k].style.visibility = '';
                        children[k].classList.remove('hidden');
                        children[k].classList.remove('hide');
                        children[k].removeAttribute('hidden');
                    }
                } else {
                    hintContainer.style.display = 'none';
                    hintContainer.classList.add('hidden');
                }
            }
        }
        
        // Function to ensure proper initialization
        function initializeFields() {
            toggleFieldVisibility();
            updateRequiredFields();
        }
        
        // Track previous values to only trigger on actual changes
        var previousButtonType = buttonTypeField ? buttonTypeField.value : null;
        var previousLinkType = linkTypeField ? linkTypeField.value : null;
        
        // Toggle visibility on field change with proper timing
        // Only trigger when value actually changes, not on dropdown open/close
        if (buttonTypeField) {
            // Store previous value on focus
            buttonTypeField.addEventListener('focus', function() {
                previousButtonType = this.value;
            });
            
            // Only process if value actually changed
            buttonTypeField.addEventListener('change', function() {
                var currentValue = this.value;
                
                // Only proceed if value actually changed
                if (currentValue !== previousButtonType) {
                    previousButtonType = currentValue;
                    
                    // Multiple delays to ensure Joomla's showon has processed
                    setTimeout(function() {
                        initializeFields();
                    }, 100);
                    setTimeout(function() {
                        initializeFields();
                    }, 300);
                    setTimeout(function() {
                        initializeFields();
                    }, 500);
                }
            });
            
            // Also handle blur event to ensure fields are updated
            buttonTypeField.addEventListener('blur', function() {
                var currentValue = this.value;
                if (currentValue !== previousButtonType) {
                    previousButtonType = currentValue;
                    setTimeout(function() {
                        initializeFields();
                    }, 200);
                }
            });
        }
        
        if (linkTypeField) {
            // Store previous value on focus
            linkTypeField.addEventListener('focus', function() {
                previousLinkType = this.value;
            });
            
            // Only process if value actually changed
            linkTypeField.addEventListener('change', function() {
                var currentValue = this.value;
                
                // Only proceed if value actually changed
                if (currentValue !== previousLinkType) {
                    previousLinkType = currentValue;
                    
                    // Multiple delays to ensure Joomla's showon has processed
                    setTimeout(function() {
                        initializeFields();
                    }, 100);
                    setTimeout(function() {
                        initializeFields();
                    }, 300);
                }
            });
            
            // Also handle blur event to ensure fields are updated
            linkTypeField.addEventListener('blur', function() {
                var currentValue = this.value;
                if (currentValue !== previousLinkType) {
                    previousLinkType = currentValue;
                    setTimeout(function() {
                        initializeFields();
                    }, 200);
                }
            });
        }
        
        // Watch for showon changes using Joomla's form events if available
        // Use jQuery delegation but only trigger on actual value changes
        if (typeof jQuery !== 'undefined' && jQuery && jQuery.fn) {
            try {
                var jqButtonType = jQuery('[name*="button_type"]', form);
                var jqLinkType = jQuery('[name*="link_type"]', form);
                
                // Track values for jQuery handlers
                var jqPrevButtonType = jqButtonType.length ? jqButtonType.val() : null;
                var jqPrevLinkType = jqLinkType.length ? jqLinkType.val() : null;
                
                // Use jQuery's change event with value check
                jQuery(form).on('change', '[name*="button_type"]', function() {
                    var currentValue = jQuery(this).val();
                    
                    // Only proceed if value actually changed
                    if (currentValue !== jqPrevButtonType) {
                        jqPrevButtonType = currentValue;
                        
                        setTimeout(function() {
                            initializeFields();
                        }, 150);
                        setTimeout(function() {
                            initializeFields();
                        }, 400);
                    }
                });
                
                jQuery(form).on('change', '[name*="link_type"]', function() {
                    var currentValue = jQuery(this).val();
                    
                    // Only proceed if value actually changed
                    if (currentValue !== jqPrevLinkType) {
                        jqPrevLinkType = currentValue;
                        
                        setTimeout(function() {
                            initializeFields();
                        }, 150);
                    }
                });
                
                // Also listen for Joomla's showon events if available
                // These are triggered by Joomla itself, so safe to handle
                if (jQuery.fn.showon) {
                    jQuery(form).on('joomla.showon', function() {
                        setTimeout(function() {
                            initializeFields();
                        }, 200);
                    });
                }
            } catch (e) {
                // Silently handle errors
                if (typeof console !== 'undefined' && console.error) {
                    console.error('Error setting up jQuery events:', e);
                }
            }
        }
        
        // Initial setup - multiple attempts with increasing delays
        // This ensures Joomla's showon has finished processing
        setTimeout(function() {
            initializeFields();
        }, 50);
        
        setTimeout(function() {
            initializeFields();
        }, 200);
        
        setTimeout(function() {
            initializeFields();
        }, 500);
        
        setTimeout(function() {
            initializeFields();
        }, 1000);
        
        // Also trigger when form is fully loaded
        if (document.readyState === 'complete') {
            setTimeout(function() {
                initializeFields();
            }, 1500);
        } else {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    initializeFields();
                }, 500);
            });
        }
    });
})();

