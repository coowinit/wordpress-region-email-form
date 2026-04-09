<?php
// Ajax handler for Contact Us form
add_action('wp_ajax_send_contact_us_email', 'send_contact_us_email_callback');
add_action('wp_ajax_nopriv_send_contact_us_email', 'send_contact_us_email_callback');

function send_contact_us_email_callback() {
    check_ajax_referer('contact_us_nonce', 'contact_us_nonce');

    $name     = sanitize_text_field($_POST['name'] ?? '');
    $email    = sanitize_email($_POST['email'] ?? '');
    $phone    = sanitize_text_field($_POST['phone'] ?? '');
    $state    = sanitize_text_field($_POST['state'] ?? '');
    $subject  = sanitize_text_field($_POST['subject'] ?? '');
    $message  = sanitize_textarea_field($_POST['message'] ?? '');
    $page_url = esc_url_raw($_POST['page_url'] ?? '');

    if (!$name || !$email || !$state || !$subject || !$message) {
        wp_send_json_error(['msg' => 'Please complete all required fields.']);
    }

    if (!is_email($email)) {
        wp_send_json_error(['msg' => 'Please enter a valid email address.']);
    }

    // Map each state to its regional recipient email
    $region_emails = [
        'Queensland'        => 'coowin111@gmail.com',
        'New South Wales'   => 'coowin222@gmail.com',
        'Victoria'          => 'coowin333@gmail.com',
        'South Australia'   => 'coowin444@gmail.com',
        'Western Australia' => 'coowin555@gmail.com',
    ];

    if (empty($region_emails[$state])) {
        wp_send_json_error(['msg' => 'No email address is configured for the selected state.']);
    }

    $to_region     = $region_emails[$state];
    $company_email = 'info@evodekco.com';

    // Get visitor IP
    $client_ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $client_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    $mail_subject = 'Contact Us - ' . $state . ' - ' . $subject;

    $body = "
        <h2>New Contact Us Submission</h2>
        <p><strong>Name:</strong> " . esc_html($name) . "</p>
        <p><strong>Email:</strong> " . esc_html($email) . "</p>
        <p><strong>Phone:</strong> " . esc_html($phone) . "</p>
        <p><strong>State:</strong> " . esc_html($state) . "</p>
        <p><strong>Subject:</strong> " . esc_html($subject) . "</p>
        <p><strong>Message:</strong><br>" . nl2br(esc_html($message)) . "</p>
        <hr>
        <p><strong>IP Address:</strong> <a target=\"_blank\" href=\"https://www.ip138.com/iplookup.php?ip=" . rawurlencode($client_ip) . "\">" . esc_html($client_ip) . "</a></p>
        <p><strong>Page URL:</strong> <a href=\"" . esc_url($page_url) . "\" target=\"_blank\">" . esc_html($page_url) . "</a></p>
        <p><strong>Submitted At:</strong> " . esc_html(current_time('mysql')) . "</p>
    ";

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: <' . $email . '>',
    ];

    // Send one email to the regional inbox
    $sent_region = wp_mail($to_region, $mail_subject, $body, $headers);

    // Send a second email to the company inbox
    $sent_company = wp_mail($company_email, $mail_subject, $body, $headers);

    // Debug logs for testing
    error_log('CONTACT MAIL STATE: ' . $state);
    error_log('CONTACT MAIL TO REGION: ' . $to_region . ' RESULT=' . ($sent_region ? 'true' : 'false'));
    error_log('CONTACT MAIL TO COMPANY: ' . $company_email . ' RESULT=' . ($sent_company ? 'true' : 'false'));

    if ($sent_region && $sent_company) {
        wp_send_json_success([
            'msg' => 'Thank you! Your form has been submitted successfully.'
        ]);
    } else {
        wp_send_json_error([
            'msg' => 'Email sending failed, please try again later.'
        ]);
    }

    wp_die();
}
