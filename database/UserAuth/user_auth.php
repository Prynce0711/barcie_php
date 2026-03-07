<?php

declare(strict_types=1);

// Bootstrap shared env, DB, and helper functions.
require_once __DIR__ . '/bootstrap/bootstrap.php';

// Handle all GET actions first (each route exits when matched).
require_once __DIR__ . '/routes/get_routes.php';

// Prepare POST action context and security guards.
require_once __DIR__ . '/routes/post_prelude.php';

// Dispatch POST handlers (each handler exits when matched).
require_once __DIR__ . '/routes/post_routes.php';

// Fallback JSON response for unmatched requests.
require_once __DIR__ . '/routes/default_response.php';
