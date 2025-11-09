<?php
if (!defined('APP_TITLE')) {
    define('APP_TITLE', 'iSCSS');
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h(APP_TITLE); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
      body { padding-top: 4.5rem; }
      .message-bubble { border-radius: .5rem; padding: .75rem; }
      .message-out { background:#e7f1ff; }
      .message-in { background:#f8f9fa; }
    </style>
  </head>
  <body>
