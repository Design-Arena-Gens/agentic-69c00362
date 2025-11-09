<?php
// Vercel PHP serverless router to serve our app from /api
// Route all paths to this file via vercel.json

// Ensure we run from project root so relative includes work
chdir(__DIR__ . '/..');

// Map incoming path to a PHP file in the repo
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === '' || $path === false) $path = '/';

// Normalize directory traversal
$path = '/' . ltrim($path, '/');
$path = preg_replace('#/+#','/',$path);

// Default file
if ($path === '/' || $path === '') {
  $file = 'index.php';
} else {
  // Only allow .php files within allowed directories
  $allowed_roots = ['','student','admin'];
  $file = ltrim($path, '/');
  // prevent access to api and includes/partials directly
  if (str_starts_with($file, 'api/') || str_starts_with($file, 'includes/') || str_starts_with($file, 'partials/')) {
    http_response_code(404); echo 'Not found'; exit;
  }
  // Allow only php files
  if (!str_ends_with($file, '.php')) {
    // support path without .php e.g., /login
    if (is_file($file . '.php')) {
      $file = $file . '.php';
    } else {
      // fallback to index.php for any other path
      $file = 'index.php';
    }
  }
  // verify within allowed directories
  $dir = explode('/', $file)[0];
  if (!in_array($dir, $allowed_roots, true) && strpos($file, '/') !== false) {
    http_response_code(404); echo 'Not found'; exit;
  }
}

if (!is_file($file)) {
  http_response_code(404); echo 'Not found'; exit;
}

require $file;
