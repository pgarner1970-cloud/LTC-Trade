<?php
// Retired diagnostic endpoint. Never expose password verification diagnostics publicly.
http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
echo 'Not found';
