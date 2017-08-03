/**
 * @file
 * Javascript behaviors for Web form.
 */

(function ($, Drupal) {

    'use strict';

    /**
     * Validate email.
     * @param email
     * @returns {boolean}
     */
    function validateEmail(email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    /**
     * Validate input.
     *
     * @param object
     * @returns {boolean}
     */
    function validateInput(object) {
        var requiredValid = true;
        if (object.is('input')) {
            var inputType = object.prop('type');
            if (inputType == 'checkbox') {
                if (!object.is(':checked')) {
                    requiredValid = false;
                }
            }
            if (inputType == 'radio') {
                var name = object.prop('name');
                var selector = "input[name=" + name + "]:checked";
                var radioValue = $(selector).val();
                if (typeof radioValue === 'undefined') {
                    requiredValid = false;
                    object.closest('fieldset').addClass('error has-error');
                } else {
                    return requiredValid;
                }
            }
        }
        if (object.val() == '') {
            requiredValid = false;
        }
        if (!requiredValid) {
           object.closest('.form-item').addClass('error has-error validation-error');
        }
        return requiredValid;
    }

    /**
     * Validate pattern.
     * @param object
     * @returns {*}
     */
    function patternValidate(object) {
        if (!object.is(':input') || object.val() == '') {
            return true;
        }
        var patternValid;
        var pattern = object.prop('pattern');
        var patternRe = new RegExp(pattern);
        var value = object.val();
        patternValid = patternRe.test(value);
        if (!patternValid) {
            object.closest('.form-item').addClass('error has-error validation-error pattern-error');
        }
        return patternValid;
    }

    Drupal.behaviors.formValidation = {
        attach: function(context, settings) {
            if ($('.webform-submission-form', context).length == 0) {
                return;
            }
            $('.required, .pattern-validation').focus(function(){
                $(this).closest('.form-item').removeClass('error').removeClass('has-error').removeClass('invalid-email').removeClass('validation-error').removeClass('pattern-error');
                $(this).closest('fieldset').removeClass('error').removeClass('has-error').removeClass('validation-error').removeClass('pattern-error');
            });
            $('.required').blur(function(){
                if (!$(this).is(':input')) {
                    return;
                }
                var input = $(this);
                validateInput(input);
            });
            $('.pattern-validation').blur(function() {
                var input = $(this);
                if (input.hasClass('required') && input.val() == '') {
                    return;
                }
                patternValidate(input);
            });
            //$('.substitution').hide('fast');
            $('.webform-submission-form', context).submit(function(e) {
                var form = $(this);
                var valid = true;
                $('.required', form).each(function(){
                    if (!$(this).is(':input')) {
                        return;
                    }
                    var requiredValid;
                    var input = $(this);
                    requiredValid = validateInput(input);
                    if (!requiredValid) {
                        valid = false;
                    }
                });
                $('input[type="email"]', form).each(function(){
                   var email = $(this).val();
                   if ($(this).closest('.form-item').hasClass('error')) {
                       return;
                   }
                   if (!validateEmail(email)) {
                       valid = false;
                       $(this).closest('.form-item').addClass('error').addClass('has-error').addClass('invalid-email').removeClass('validation-error');
                   }
                });
                $('.pattern-validation').each(function(){
                   var input = $(this);
                   var patternValid;
                   patternValid = patternValidate(input);
                   if (!patternValid) {
                       valid = false;
                   }
                });
                if (valid) {
                    if ($('.validation-hide-me').length > 0) {
                        //$('.form-actions .form-submit').hide("fast");
                        //$('.validation-hide-me').show("fast");
                        $('.validation-hide-me').trigger("click");
                        e.preventDefault();
                        return false;
                    } else {
                        return valid;
                    }
                } else {
                    var target = $('.error').first();
                    $('html, body').animate({
                        scrollTop: target.offset().top
                    }, 1000);
                    e.preventDefault();
                    return valid;
                }
            });
        }
    }

})(jQuery, Drupal);
