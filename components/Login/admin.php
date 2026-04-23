<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once dirname(__DIR__, 2) . '/database/db_connect.php';
require_once __DIR__ . '/remember_me.php';

if (
  (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true)
  && isset($conn)
  && $conn instanceof mysqli
) {
  remember_me_restore_session($conn);
}

if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
  header('Location: index.php?view=dashboard');
  exit;
}

$projectRoot = dirname(__DIR__, 2);

$documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
  ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR)
  : '';

$computeAppBasePath = static function (string $projectRootPath, string $docRoot): string {
  $normalizedProjectRoot = rtrim(str_replace('\\', '/', $projectRootPath), '/');
  $normalizedDocRoot = rtrim(str_replace('\\', '/', $docRoot), '/');

  if (
    $normalizedDocRoot !== '' &&
    strncasecmp($normalizedProjectRoot, $normalizedDocRoot, strlen($normalizedDocRoot)) === 0
  ) {
    $relative = trim(substr($normalizedProjectRoot, strlen($normalizedDocRoot)), '/');
    return $relative === '' ? '' : '/' . $relative;
  }

  $scriptName = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '';
  $scriptDirName = trim(str_replace('\\', '/', dirname($scriptName)), '/.');

  return $scriptDirName === '' ? '' : '/' . $scriptDirName;
};

$appBasePath = defined('APP_BASE_PATH')
  ? rtrim((string) APP_BASE_PATH, '/')
  : $computeAppBasePath($projectRoot, $documentRoot);

$toAppUrl = static function (string $relativePath) use ($appBasePath): string {
  $normalizedRelativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
  return ($appBasePath !== '' ? $appBasePath : '') . '/' . $normalizedRelativePath;
};

$documentRootRealPath = $documentRoot !== '' ? realpath($documentRoot) : false;
$projectPublicRealPath = realpath($projectRoot . DIRECTORY_SEPARATOR . 'public');
$isPublicDocumentRoot =
  $documentRootRealPath !== false &&
  $projectPublicRealPath !== false &&
  strcasecmp(str_replace('\\', '/', $documentRootRealPath), str_replace('\\', '/', $projectPublicRealPath)) === 0;
$logoRelativePath = $isPublicDocumentRoot
  ? 'images/imageBg/barcie_logo.jpg'
  : 'public/images/imageBg/barcie_logo.jpg';
$logoUrl = $toAppUrl($logoRelativePath);

// Drop a file into uploads/login/ with one of these names to override the fallback image.
$loginBackgroundCandidates = [
  'uploads/login/login-background.jpg',
  'uploads/login/login-background.jpeg',
  'uploads/login/login-background.png',
  'public/images/imageBg/Lcup-background.jpg',
];

$loginBackgroundUrl = '';
foreach ($loginBackgroundCandidates as $candidate) {
  $fullPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $candidate);
  if (!is_file($fullPath)) {
    continue;
  }

  $loginBackgroundUrl = $toAppUrl($candidate);
  break;
}

$loginBackgroundStyle = '';
if ($loginBackgroundUrl !== '') {
  $escapedBackgroundUrl = str_replace(['\\', "'"], ['\\\\', "\\'"], $loginBackgroundUrl);
  $loginBackgroundStyle = "background-image: linear-gradient(135deg, rgba(30, 60, 114, 0.72) 0%, rgba(42, 82, 152, 0.72) 100%), url('"
    . $escapedBackgroundUrl
    . "');";
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script>
    window.APP_BASE_PATH = <?php echo json_encode(defined('APP_BASE_PATH') ? APP_BASE_PATH : ''); ?>;
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" type="image/x-icon"
    href="<?php echo htmlspecialchars($toAppUrl('favicon.ico'), ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="shortcut icon" type="image/x-icon"
    href="<?php echo htmlspecialchars($toAppUrl('favicon.ico'), ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="apple-touch-icon" href="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <title>BarCIE Admin Login - Secure Access Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body
  class="min-h-screen bg-gradient-to-br from-[#1e3c72] to-[#2a5298] bg-cover bg-center bg-no-repeat bg-fixed px-4 py-6 font-sans sm:px-5 sm:py-8 flex items-center justify-center"
  <?php if ($loginBackgroundStyle !== ''): ?>style="<?php echo htmlspecialchars($loginBackgroundStyle, ENT_QUOTES, 'UTF-8'); ?>" <?php endif; ?>>
  <main class="w-full max-w-md">
    <div
      class="w-full rounded-3xl border border-white/20 bg-white/10 p-6 shadow-2xl shadow-slate-900/40 backdrop-blur-xl sm:p-8 md:p-10">
      <div class="mb-5 flex justify-center">
        <div class="h-20 w-20 overflow-hidden rounded-full">
          <img src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="BarCIE Logo"
            class="h-full w-full object-cover">
        </div>
      </div>

      <h1 class="text-center text-2xl font-bold tracking-tight text-white">BarCIE Admin Login</h1>
      <p class="mb-8 mt-2 text-center text-sm text-white/75">Access your unique admin portal</p>

      <?php
      $adminLoginErrorClass = 'mb-5 hidden rounded-xl border border-red-300/50 bg-red-500/25 px-4 py-3 text-sm font-medium text-red-100';
      $adminLoginFormClass = 'space-y-4';
      $adminLoginGroupClass = '';
      $adminLoginPasswordGroupClass = 'relative';
      $adminLoginLabelClass = 'mb-2 block text-sm font-semibold text-white';
      $adminLoginInputClass = 'w-full rounded-xl border border-white/30 bg-white/20 px-3 py-3 text-white placeholder-white/50 outline-none transition focus:border-amber-300 focus:bg-white/25 focus:ring-2 focus:ring-amber-200/35';
      $adminLoginPasswordInputClass = 'w-full rounded-xl border border-white/30 bg-white/20 px-3 py-3 pr-12 text-white placeholder-white/50 outline-none transition focus:border-amber-300 focus:bg-white/25 focus:ring-2 focus:ring-amber-200/35';
      $adminLoginPasswordToggleClass = 'absolute right-3 top-[2.35rem] z-10 rounded-md p-1 text-white/65 transition hover:text-white focus:outline-none focus:ring-2 focus:ring-amber-200/50';
      $adminLoginPasswordToggleContent = '<i class="far fa-eye"></i>';
      $adminLoginPasswordToggleContentIsHtml = true;
      $adminLoginShowRememberMe = true;
      $adminLoginRememberWrapperClass = 'mt-1 flex cursor-pointer items-center gap-2 text-sm text-white/85';
      $adminLoginRememberInputClass = 'h-4 w-4 rounded border border-white/35 bg-white/20 text-amber-300 focus:ring-2 focus:ring-amber-200/50';
      $adminLoginRememberTextClass = '';
      $adminLoginSubmitClass = 'mt-2 inline-flex w-full items-center justify-center rounded-xl bg-amber-300 px-4 py-3 text-base font-semibold text-slate-900 transition hover:-translate-y-0.5 hover:bg-amber-200 hover:shadow-lg hover:shadow-amber-200/40 disabled:cursor-not-allowed disabled:opacity-70';
      $adminLoginSubmitIconClass = 'fas fa-sign-in-alt mr-2';
      $adminLoginSubmitText = 'Sign In';
      include __DIR__ . '/admin_login_form_fields.php';
      ?>

      <div class="mt-6 text-center">
        <a href="<?php echo htmlspecialchars($toAppUrl('index.php'), ENT_QUOTES, 'UTF-8'); ?>"
          class="font-medium text-amber-300 transition hover:text-amber-200">
          <i class="fas fa-arrow-left mr-1"></i>Back to Home
        </a>
      </div>
    </div>
  </main>

  <script>
    const toggleAdminPasswordButton = document.getElementById('toggleAdminPassword');
    if (toggleAdminPasswordButton) {
      toggleAdminPasswordButton.addEventListener('click', function () {
        const passwordField = document.getElementById('admin-password');
        const icon = this.querySelector('i');
        if (!passwordField || !icon) {
          return;
        }

        if (passwordField.type === 'password') {
          passwordField.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          passwordField.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      });
    }

    function getProjectRoot() {
      const path = window.location.pathname || '';
      const lowerPath = path.toLowerCase();

      const markers = ['/components/login/', '/components/guest/', '/components/login', '/components/'];
      for (const marker of markers) {
        const idx = lowerPath.indexOf(marker);
        if (idx !== -1) {
          return path.substring(0, idx) || '';
        }
      }

      const parts = path.split('/').filter(Boolean);
      if (parts.length > 0 && parts[0].indexOf('.') === -1) {
        return '/' + parts[0];
      }

      return '';
    }

    const adminLoginForm = document.getElementById('admin-login-form');
    if (adminLoginForm) {
      adminLoginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const errorDiv = document.getElementById('admin-login-error');
        const projectRoot = (typeof window.APP_BASE_PATH === 'string' && window.APP_BASE_PATH.trim() !== '')
          ? window.APP_BASE_PATH.replace(/\/+$/, '')
          : getProjectRoot();
        const loginEndpoint = `${projectRoot}/database/index.php?endpoint=admin_login`;

        if (!submitButton || !errorDiv) {
          return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing In...';

        fetch(loginEndpoint, {
          method: 'POST',
          body: formData
        })
          .then(async response => {
            const raw = await response.text();
            let data;
            try {
              data = JSON.parse(raw);
            } catch (err) {
              throw new Error(`Invalid JSON response (${response.status})`);
            }
            return data;
          })
          .then(data => {
            if (data.success) {
              const redirectPath = (data.redirect || 'index.php?view=dashboard').replace(/^\/+/, '');
              window.location.href = `${projectRoot}/${redirectPath}`;
              return;
            }

            errorDiv.textContent = data.message || 'Invalid username or password';
            errorDiv.classList.remove('hidden');

            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Sign In';

            setTimeout(() => {
              errorDiv.classList.add('hidden');
            }, 5000);
          })
          .catch(error => {
            console.error('Login error:', error);
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.classList.remove('hidden');

            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Sign In';
          });
      });
    }
  </script>

</body>

</html>