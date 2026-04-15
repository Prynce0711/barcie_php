<?php
$adminLoginErrorClass = $adminLoginErrorClass ?? 'alert alert-danger d-none';
$adminLoginFormClass = $adminLoginFormClass ?? '';
$adminLoginGroupClass = $adminLoginGroupClass ?? 'mb-3';
$adminLoginPasswordGroupClass = $adminLoginPasswordGroupClass ?? 'mb-3 position-relative';
$adminLoginLabelClass = $adminLoginLabelClass ?? 'form-label text-white';
$adminLoginInputClass = $adminLoginInputClass ?? 'form-control';
$adminLoginPasswordInputClass = $adminLoginPasswordInputClass ?? $adminLoginInputClass;
$adminLoginPasswordToggleClass = $adminLoginPasswordToggleClass
  ?? 'position-absolute top-50 end-0 translate-middle-y me-3 btn btn-link text-white p-0';
$adminLoginPasswordToggleStyle = $adminLoginPasswordToggleStyle ?? '';
$adminLoginPasswordToggleContent = $adminLoginPasswordToggleContent ?? '👁️';
$adminLoginPasswordToggleContentIsHtml = isset($adminLoginPasswordToggleContentIsHtml)
  ? (bool) $adminLoginPasswordToggleContentIsHtml
  : false;
$adminLoginShowRememberMe = isset($adminLoginShowRememberMe) ? (bool) $adminLoginShowRememberMe : true;
$adminLoginRememberWrapperClass = $adminLoginRememberWrapperClass ?? 'form-check mt-2';
$adminLoginRememberInputClass = $adminLoginRememberInputClass ?? 'form-check-input';
$adminLoginRememberTextClass = $adminLoginRememberTextClass ?? 'form-check-label';
$adminLoginSubmitClass = $adminLoginSubmitClass ?? 'btn btn-primary-custom w-100';
$adminLoginSubmitIconClass = $adminLoginSubmitIconClass ?? '';
$adminLoginSubmitText = $adminLoginSubmitText ?? 'Sign In';
?>

<div id="admin-login-error" class="<?php echo htmlspecialchars($adminLoginErrorClass, ENT_QUOTES, 'UTF-8'); ?>"></div>

<form id="admin-login-form"<?php if ($adminLoginFormClass !== ''): ?> class="<?php echo htmlspecialchars($adminLoginFormClass, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
  <div<?php if ($adminLoginGroupClass !== ''): ?> class="<?php echo htmlspecialchars($adminLoginGroupClass, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
    <label for="admin-username" class="<?php echo htmlspecialchars($adminLoginLabelClass, ENT_QUOTES, 'UTF-8'); ?>">Username</label>
    <input type="text" id="admin-username" name="username" placeholder="admin" required
      class="<?php echo htmlspecialchars($adminLoginInputClass, ENT_QUOTES, 'UTF-8'); ?>">
  </div>

  <div<?php if ($adminLoginPasswordGroupClass !== ''): ?> class="<?php echo htmlspecialchars($adminLoginPasswordGroupClass, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
    <label for="admin-password" class="<?php echo htmlspecialchars($adminLoginLabelClass, ENT_QUOTES, 'UTF-8'); ?>">Password</label>
    <input type="password" id="admin-password" name="password" placeholder="••••••••" required
      class="<?php echo htmlspecialchars($adminLoginPasswordInputClass, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="button" id="toggleAdminPassword" class="<?php echo htmlspecialchars($adminLoginPasswordToggleClass, ENT_QUOTES, 'UTF-8'); ?>"<?php if ($adminLoginPasswordToggleStyle !== ''): ?> style="<?php echo htmlspecialchars($adminLoginPasswordToggleStyle, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
      <?php if ($adminLoginPasswordToggleContentIsHtml): ?>
        <?php echo $adminLoginPasswordToggleContent; ?>
      <?php else: ?>
        <?php echo htmlspecialchars($adminLoginPasswordToggleContent, ENT_QUOTES, 'UTF-8'); ?>
      <?php endif; ?>
    </button>
  </div>

  <?php if ($adminLoginShowRememberMe): ?>
    <label for="admin-remember-me"<?php if ($adminLoginRememberWrapperClass !== ''): ?> class="<?php echo htmlspecialchars($adminLoginRememberWrapperClass, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
      <input type="checkbox" value="1" id="admin-remember-me" name="remember_me"
        class="<?php echo htmlspecialchars($adminLoginRememberInputClass, ENT_QUOTES, 'UTF-8'); ?>">
      <span<?php if ($adminLoginRememberTextClass !== ''): ?> class="<?php echo htmlspecialchars($adminLoginRememberTextClass, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>Remember me on this device</span>
    </label>
  <?php endif; ?>

  <button type="submit" class="<?php echo htmlspecialchars($adminLoginSubmitClass, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($adminLoginSubmitIconClass !== ''): ?>
      <i class="<?php echo htmlspecialchars($adminLoginSubmitIconClass, ENT_QUOTES, 'UTF-8'); ?>"></i>
    <?php endif; ?>
    <?php echo htmlspecialchars($adminLoginSubmitText, ENT_QUOTES, 'UTF-8'); ?>
  </button>
</form>