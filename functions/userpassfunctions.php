<?php
include('db.php');
include_once(__DIR__ . '/email_template.php');

function useremail($username, $token, $hash, $email) {
    $linkurl = "https://trade.tyres4sale.com/userpassupd.php?otp=" . urlencode($hash);

    $body  = "<p>Please reset your Trade Account password using the passcode below:</p>";
    $body .= "<p><a href='" . htmlspecialchars($linkurl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($linkurl, ENT_QUOTES, 'UTF-8') . "</a></p>";
    $body .= "<p>Your passcode is: <b>" . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . "</b></p>";
    $body .= "<p>Best regards<br>The LTC Team</p>";

    $to = $email;
    $subject = 'Forgotten password for LTC Trade account';

    $html = function_exists('email_wrap_html') ? email_wrap_html($body, 'Forgotten password') : $body;
    $headers = function_exists('email_headers') ? email_headers('webmaster@tyres4sale.com') : ("From: webmaster@tyres4sale.com\r\nMIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n");

    @mail($to, $subject, $html, $headers);
}