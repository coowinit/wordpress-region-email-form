# WordPress Contact Form by State Routing

This repository stores a lightweight Contact Us form setup for WordPress.

## Features

- Ajax form submission without page refresh
- Route enquiries to different agent emails based on Australian state
- Send a second copy to the company inbox
- Use WordPress `wp_mail()`
- Support logged-in and logged-out visitors
- Include basic validation and nonce security

## Files

- `contact-form-template.php` — contact form markup only
- `functions-contact-mail.php` — Ajax handler for `functions.php` or a custom include file

## How to use

### 1. Add the form markup

Copy the contents of `contact-form-template.php` into your Contact Us page template.

This file only contains the form block and Ajax script. It does not include:

- header
- footer
- `wp_head()`
- `wp_footer()`

### 2. Add the mail handler

Copy the contents of `functions-contact-mail.php` into your theme `functions.php`, or include it from a custom file.

Example:

```php
require_once get_template_directory() . '/inc/functions-contact-mail.php';
```

### 3. Update the recipient emails

In `functions-contact-mail.php`, edit:

- `$region_emails`
- `$company_email`

### 4. Make sure SMTP already works

This code assumes your site can already send mail using `wp_mail()`.

## Notes

- The form requires jQuery.
- The Ajax endpoint uses `admin-ajax.php`.
- The form sends two separate emails:
  - one to the regional recipient
  - one to the company email
- `Reply-To` is set to the visitor's email address.

## Example state mapping

```php
$region_emails = [
    'Queensland'        => 'coowin111@gmail.com',
    'New South Wales'   => 'coowin222@gmail.com',
    'Victoria'          => 'coowin333@gmail.com',
    'South Australia'   => 'coowin444@gmail.com',
    'Western Australia' => 'coowin555@gmail.com',
];
```

## Optional cleanup before production

You can remove these debug logs after testing:

```php
error_log('CONTACT MAIL STATE: ' . $state);
error_log('CONTACT MAIL TO REGION: ' . $to_region . ' RESULT=' . ($sent_region ? 'true' : 'false'));
error_log('CONTACT MAIL TO COMPANY: ' . $company_email . ' RESULT=' . ($sent_company ? 'true' : 'false'));
```

## License

This project is licensed under the MIT License.