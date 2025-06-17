<?php
if (!function_exists('word_limiter')) {
  function word_limiter($text, $limit = 30) {
    $words = explode(' ', strip_tags($text));
    if (count($words) > $limit) {
      return htmlspecialchars(implode(' ', array_slice($words, 0, $limit)) . '...');
    } else {
      return htmlspecialchars($text);
    }
  }
}
?>
