<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Fetch store settings from store_settings table (key-value schema)
$settings = [];
$query = "SELECT setting_key, setting_value FROM store_settings";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Fallback default values if not yet set in database
$brand_name              = $settings['brand_name'] ?? 'NOVA FORM';
$helpline_phone          = $settings['helpline_phone'] ?? '+92 300 8472911';
$support_email           = $settings['support_email'] ?? 'concierge@novaformshoes.pk';
$currency_timezone       = $settings['currency_timezone'] ?? 'PKR (Pakistan Standard Time - PKT)';
$delivery_fee            = $settings['delivery_fee'] ?? '250';
$free_delivery_threshold = $settings['free_delivery_threshold'] ?? '10000';
$delivery_notice         = $settings['delivery_notice'] ?? '2-3 business days for major cities (Lahore, Karachi, Islamabad); 3-5 days nationwide.';
$exchange_policy         = $settings['exchange_policy'] ?? '15-Day Complimentary Doorstep Exchange for sizing adjustment.';

$admin_name = $_SESSION['user_name'] ?? 'M. Asif';
$admin_role = $_SESSION['user_role'] ?? 'Store Director';
?>

<?php require_once __DIR__ . "/partials/header.php" ?>

<body>

  <div class="admin-wrapper">

    <!-- Admin Sidebar -->
    <?php require_once __DIR__ . "/partials/sidebar.php" ?>

    <div class="admin-main">

      <!-- Topbar -->
      <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link text-dark p-0 d-lg-none" id="sidebarToggleBtn" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-3"></i>
          </button>
          <div class="topbar-search">
            <i class="bi bi-search search-icon"></i>
            <input type="text" class="form-control" id="tableSearchInput" placeholder="Quick search inventory, orders...">
          </div>
        </div>

        <div class="topbar-actions">
          <a href="../front-site/index.html" target="_blank" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1">
            <i class="bi bi-eye"></i> View Site
          </a>

          <div class="dropdown">
            <a href="#" class="topbar-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
              <i class="bi bi-bell"></i>
              <span class="topbar-badge-dot"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width: 280px;">
              <li>
                <h6 class="dropdown-header text-uppercase small fw-bold">Recent Alerts</h6>
              </li>
              <li><a class="dropdown-item small py-2" href="orders.html"><i class="bi bi-bag-check text-success me-2"></i> New Order #NF-88291 received (PKR 14,999)</a></li>
              <li><a class="dropdown-item small py-2" href="inventory.html"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Low stock: Sahara Suede Chukka (9 left)</a></li>
              <li><a class="dropdown-item small py-2" href="reviews.html"><i class="bi bi-star text-primary me-2"></i> New 5-star review pending approval</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-center small text-primary fw-semibold" href="orders.html">View All Notifications</a></li>
            </ul>
          </div>

          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop" class="admin-avatar me-2" alt="Admin">
              <div class="d-none d-sm-block text-start">
                <div class="fw-semibold small leading-none"><?= htmlspecialchars($admin_name) ?></div>
                <div class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($admin_role) ?></div>
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
              <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2 text-muted"></i> My Profile</a></li>
              <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2 text-muted"></i> Store Settings</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="actions/logout_action.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </header>

      <main class="admin-content">

        <!-- Error & Success Alerts -->
        <?php if (!empty($errors)) : ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
              <?php foreach ($errors as $error) : ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="page-title">Storefront Configuration & Logistics Settings</h1>
            <p class="page-subtitle">Configure Pakistani delivery thresholds, store contact details, and policies.</p>
          </div>
        </div>

        <form action="actions/update_settings_action.php" method="POST">
          <div class="row g-4">

            <div class="col-lg-8">

              <!-- Store Info -->
              <div class="admin-card mb-4">
                <div class="admin-card-header">
                  <h5 class="admin-card-title">Store Identity</h5>
                </div>
                <div class="admin-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Brand Name *</label>
                      <input type="text" name="brand_name" class="form-control" value="<?= htmlspecialchars($brand_name) ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Helpline Phone *</label>
                      <input type="text" name="helpline_phone" class="form-control" value="<?= htmlspecialchars($helpline_phone) ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Support Email *</label>
                      <input type="email" name="support_email" class="form-control" value="<?= htmlspecialchars($support_email) ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Currency & Timezone</label>
                      <input type="text" name="currency_timezone" class="form-control bg-light" value="<?= htmlspecialchars($currency_timezone) ?>" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Logistics Settings -->
              <div class="admin-card mb-4">
                <div class="admin-card-header">
                  <h5 class="admin-card-title">Delivery Fees & Nationwide Logistics</h5>
                </div>
                <div class="admin-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Standard Delivery Fee (PKR) *</label>
                      <input type="number" step="0.01" min="0" name="delivery_fee" class="form-control" value="<?= htmlspecialchars($delivery_fee) ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Free Delivery Threshold (PKR) *</label>
                      <input type="number" step="0.01" min="0" name="free_delivery_threshold" class="form-control" value="<?= htmlspecialchars($free_delivery_threshold) ?>" required>
                    </div>
                    <div class="col-12">
                      <label class="form-label small fw-bold">Delivery Estimate Notice</label>
                      <input type="text" name="delivery_notice" class="form-control" value="<?= htmlspecialchars($delivery_notice) ?>">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Policies -->
              <div class="admin-card mb-4">
                <div class="admin-card-header">
                  <h5 class="admin-card-title">Exchange & Warranty Policies</h5>
                </div>
                <div class="admin-card-body">
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Exchange Period</label>
                    <input type="text" name="exchange_policy" class="form-control" value="<?= htmlspecialchars($exchange_policy) ?>">
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-dark px-4 py-2">
                <i class="bi bi-check-lg me-1"></i> Save Settings
              </button>

            </div>

            <div class="col-lg-4">
              <div class="admin-card mb-4">
                <div class="admin-card-header">
                  <h5 class="admin-card-title">Quick Status</h5>
                </div>
                <div class="admin-card-body small">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Primary Gateway:</span>
                    <strong>Cash on Delivery (Active)</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Standard Delivery:</span>
                    <strong>PKR <?= number_format(floatval($delivery_fee)) ?></strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Free Delivery Above:</span>
                    <strong class="text-success">PKR <?= number_format(floatval($free_delivery_threshold)) ?></strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">SSL Encryption:</span>
                    <strong class="text-success">Enabled</strong>
                  </div>
                </div>
              </div>

              <div class="admin-card">
                <div class="admin-card-header">
                  <h5 class="admin-card-title">Notice</h5>
                </div>
                <div class="admin-card-body small text-muted">
                  Logistics and contact configurations updated here apply across your storefront checkout, contact, and policy pages.
                </div>
              </div>
            </div>

          </div>
        </form>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/css/dashboard.js"></script>
  <script src="assets/js/dashboard.js"></script>
</body>

</html>
