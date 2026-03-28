<?php
/**
 * Zentrale Navbar: Classic (Bootstrap) oder FeuerwehrHub-Chrome.
 *
 * Optional vor dem Include setzen:
 * - $ff_brand_href — z. B. index.php?einheit_id=… oder ../index.php
 * - $ff_nav_container_fluid — true/false (Default: true im admin/-Skript)
 * - $admin_menu_base, $admin_menu_logout, $admin_menu_index — wie bisher
 */
require_once __DIR__ . '/ui-theme.php';

$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
$ff_script_in_admin = (bool) preg_match('#/admin/#', $script_path);

if (!isset($ff_brand_href)) {
    $ff_brand_href = $ff_script_in_admin ? '../index.php' : 'index.php';
}
if (!isset($admin_menu_base)) {
    $admin_menu_base = $ff_script_in_admin ? '' : 'admin/';
}
if (!isset($admin_menu_logout)) {
    $admin_menu_logout = $ff_script_in_admin ? '../logout.php' : 'logout.php';
}
if (!isset($admin_menu_index)) {
    $admin_menu_index = $ff_script_in_admin ? '../index.php' : 'index.php';
}
if (!isset($ff_nav_container_fluid)) {
    $ff_nav_container_fluid = $ff_script_in_admin;
}

if (is_hub_ui_theme()) {
    if (!defined('FF_HUB_UI_FLAG')) {
        define('FF_HUB_UI_FLAG', true);
        echo '<script>document.documentElement.classList.add("ff-hub-ui");</script>';
    }
    include __DIR__ . '/hub-chrome.inc.php';
    return;
}

$nav_container_class = !empty($ff_nav_container_fluid) ? 'container-fluid' : 'container';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="<?php echo $nav_container_class; ?>">
        <a class="navbar-brand" href="<?php echo htmlspecialchars($ff_brand_href); ?>"><i class="fas fa-fire"></i> Feuerwehr App</a>
        <?php if (isset($_SESSION['user_id']) && !is_system_user()): ?>
            <div class="d-flex ms-auto">
                <?php
                $admin_menu_in_navbar = true;
                include __DIR__ . '/../admin/includes/admin-menu.inc.php';
                ?>
            </div>
        <?php else: ?>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="d-flex ms-auto align-items-center">
                    <a class="btn btn-outline-light btn-sm px-3 py-2 d-flex align-items-center gap-2" href="<?php echo $ff_script_in_admin ? '../login.php' : 'login.php'; ?>">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="fw-semibold">Anmelden</span>
                    </a>
                </div>
            <?php else: ?>
                <?php include __DIR__ . '/system-user-nav.inc.php'; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</nav>
