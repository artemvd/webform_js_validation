# Webform JS Validation

This module adds a webform handler that allows to add js validation to your form on before submit. It tries to find all 
validation rules that were provided with form builder and add them to the page. Validation is possible for required fields,
emails and also field pattern

# Make it work

This module provides only webform handler and its functionality is not applied to all webforms by default. To make it 
work you need to navigate to "Emails/Handlers" tab on your webform edit screen and click on "Add handler" button, 
select "Javascript Form Validation" handler from the list and "Save". Only after this your webform will support 
javascript validation.
