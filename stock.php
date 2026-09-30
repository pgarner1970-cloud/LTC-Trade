<?php
// Legacy tyre search address: preserve existing bookmarks and links.
// The active Trade tyre search is stock-multi.php.
$query = $_SERVER['QUERY_STRING'] ?? '';
$target = 'stock-multi.php' . ($query !== '' ? '?' . $query : '');
header('Location: ' . $target, true, 302);
exit;
