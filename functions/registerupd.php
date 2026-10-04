<?php
// Retired: public live username/email enumeration is intentionally disabled.
// Registration performs authoritative uniqueness checks server-side.
http_response_code(410);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
echo 'Unavailable';
