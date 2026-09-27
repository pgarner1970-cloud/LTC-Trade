<?php
/**
 * Standard email wrapper for the Trade system.
 * Adds the logo (logo.png in web root) and a simple container so emails look consistent.
 */

function email_base_url(): string {
  // Prefer the current request host if available, otherwise fall back to production host.
  $host = $_SERVER['HTTP_HOST'] ?? 'tradeconnect.tyres4sale.com';
  // Most email clients block mixed content; force https.
  return 'https://' . $host;
}

function email_logo_url(): string {
  return email_base_url() . '/logo.png';
}

function email_header_html(string $title = ''): string {
  $logo = htmlspecialchars(email_logo_url(), ENT_QUOTES, 'UTF-8');
  $t = $title ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : '';

  // Use tables for maximum email client compatibility.
  $html = '';
  $html .= "<table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#ffffff;\">";
  $html .= "<tr><td align=\"center\" style=\"padding:20px 10px;\">";
  $html .= "<img src=\"{$logo}\" alt=\"LTC Tyres\" width=\"200\" style=\"display:block;width:200px;max-width:200px;height:auto;border:0;outline:none;text-decoration:none;\">";
  $html .= "</td></tr>";
  if ($t) {
    $html .= "<tr><td align=\"center\" style=\"font-family:Arial,Helvetica,sans-serif;font-size:18px;color:#0b3d91;padding:0 10px 10px;\"><b>{$t}</b></td></tr>";
  }
  $html .= "<tr><td align=\"center\" style=\"padding:0 10px 10px;\">";
  $html .= "<div style=\"max-width:640px;border-top:1px solid #e6e6e6;\"></div>";
  $html .= "</td></tr>";
  $html .= "</table>";

  return $html;
}

function email_footer_html(): string {
  $year = date('Y');
  $html = '';
  $html .= "<table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"background:#ffffff;\">";
  $html .= "<tr><td align=\"center\" style=\"padding:18px 10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#777;\">";
  $html .= "© {$year} LTC Tyres - Your Local Tyre Centre";
  $html .= "</td></tr>";
  $html .= "</table>";
  return $html;
}

function email_wrap_html(string $bodyHtml, string $title = ''): string {
  // A simple container for the main content
  $body = "<table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr><td align=\"center\" style=\"padding:0 10px 10px;\">";
  $body .= "<div style=\"max-width:640px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111;line-height:1.45;\">";
  $body .= $bodyHtml;
  $body .= "</div>";
  $body .= "</td></tr></table>";

  return email_header_html($title) . $body . email_footer_html();
}

function email_headers(string $from = 'no-reply@tyres4sale.com'): string {
  $headers  = "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=UTF-8\r\n";
  $headers .= "From: {$from}\r\n";
  return $headers;
}
