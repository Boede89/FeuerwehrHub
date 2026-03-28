<?php
/**
 * FeuerwehrHub-ähnliches Chrome: fixer Header + Sidebar (nur bei ui_theme=hub).
 * Erwartet Session, includes/functions.php; setzt ggf. admin-menu-Variablen.
 */
if (!function_exists('is_hub_ui_theme') || !is_hub_ui_theme()) {
    return;
}

if (isset($db) && file_exists(__DIR__ . '/einheiten-setup.php')) {
    require_once __DIR__ . '/einheiten-setup.php';
}

$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
$ff_script_in_admin = (bool) preg_match('#/admin/#', $script_path);

$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$sn = $script_name;

$app_title = ff_setting_get('app_name', 'Feuerwehr App');
$hub_user_label = '';
if (isset($_SESSION['user_id'])) {
    $fn = $_SESSION['first_name'] ?? '';
    $ln = $_SESSION['last_name'] ?? '';
    $un = $_SESSION['username'] ?? '';
    $hub_user_label = trim($fn . ' ' . $ln);
    if ($hub_user_label === '') {
        $hub_user_label = $un;
    }
}

$ff_hub_show_sidebar = isset($_SESSION['user_id']) && function_exists('is_system_user') && !is_system_user();

// Menü-Berechtigungen wie admin-menu.inc.php
if ($ff_hub_show_sidebar) {
    if (!isset($can_reservations)) {
        $u = $GLOBALS['user'] ?? null;
        if (!$u && isset($db)) {
            $st = $db->prepare("SELECT is_admin, can_reservations, can_atemschutz, can_settings, can_members, can_forms FROM users WHERE id = ?");
            $st->execute([$_SESSION['user_id']]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
        }
        $is_adm = $u && (!empty($u['is_admin']) || $u['is_admin'] == 1);
        $can_reservations = $is_adm ? true : (function_exists('has_permission') && has_permission('reservations'));
        $can_atemschutz = $is_adm ? true : (function_exists('has_permission') && has_permission('atemschutz'));
        $can_settings = $is_adm ? true : (function_exists('has_permission') && has_permission('settings'));
        $can_members = $is_adm ? true : (function_exists('has_permission') && has_permission('members'));
        $can_auswertung = $is_adm ? true : (function_exists('has_permission') && has_permission('auswertung'));
        $can_forms = $is_adm ? true : (function_exists('has_permission') && has_permission('forms'));
        $can_forms_fill = $is_adm ? true : (function_exists('has_form_fill_permission') && has_form_fill_permission());
    }
    if (!isset($can_auswertung)) {
        $can_auswertung = function_exists('has_permission') && has_permission('auswertung');
    }
    if (!isset($can_forms_fill)) {
        $can_forms_fill = function_exists('has_form_fill_permission') && has_form_fill_permission();
    }
}

$dashboard_einheit = function_exists('get_current_einheit_id') ? get_current_einheit_id() : (function_exists('get_current_unit_id') ? get_current_unit_id() : null);
$dash_q = ($dashboard_einheit ?? null) ? ('?einheit_id=' . (int) $dashboard_einheit) : '';
$can_switch = function_exists('can_switch_einheit') && can_switch_einheit();
$cur_eid = function_exists('get_current_einheit_id') ? get_current_einheit_id() : null;
$user_eins = ($can_switch && function_exists('get_user_einheiten')) ? get_user_einheiten() : [];

$base = $admin_menu_base ?? '';
$logout_url = $admin_menu_logout ?? '../logout.php';
$index_url = $admin_menu_index ?? '../index.php';

$ff_hub_emblem = '';
if (function_exists('get_pdf_logo_html')) {
    $raw = get_pdf_logo_html();
    if ($raw !== '') {
        $ff_hub_emblem = '<span class="ff-hub-header__emblem-img">' . $raw . '</span>';
    }
}
if ($ff_hub_emblem === '') {
    $ff_hub_emblem = '<span class="ff-hub-header__emblem-fallback" aria-hidden="true">🚒</span>';
}
?>
<header class="ff-hub-header">
    <button type="button" class="ff-hub-sidebar-toggle d-lg-none" id="ff-hub-sidebar-toggle" aria-label="Menü">
        <i class="fas fa-bars"></i>
    </button>
    <a class="ff-hub-header__brand-block" href="<?php echo htmlspecialchars($ff_brand_href ?? $index_url); ?>">
        <span class="ff-hub-header__emblem"><?php echo $ff_hub_emblem; ?></span>
        <span class="ff-hub-header__titles">
            <span class="ff-hub-header__title"><?php echo htmlspecialchars($app_title); ?></span>
            <span class="ff-hub-header__subtitle">FeuerwehrHub</span>
        </span>
    </a>
    <div class="ff-hub-header__right">
        <?php if (isset($_SESSION['user_id']) && !is_system_user()): ?>
            <span class="ff-hub-header__user"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($hub_user_label ?: '…'); ?></span>
            <a class="ff-hub-header__logout" href="<?php echo htmlspecialchars($logout_url); ?>">Abmelden</a>
        <?php elseif (isset($_SESSION['user_id']) && is_system_user()): ?>
            <?php include __DIR__ . '/system-user-nav.inc.php'; ?>
        <?php else: ?>
            <a class="ff-hub-header__logout" href="<?php echo $ff_script_in_admin ? '../login.php' : 'login.php'; ?>">Anmelden</a>
        <?php endif; ?>
    </div>
</header>

<?php if ($ff_hub_show_sidebar): ?>
<aside class="ff-hub-sidebar" id="ff-hub-sidebar">
    <nav class="ff-hub-sidebar__nav" aria-label="Hauptnavigation">
        <?php if ($can_switch && !empty($user_eins)): ?>
        <div class="ff-hub-sidebar__module">Einheit</div>
        <?php foreach ($user_eins as $ue): ?>
        <a class="ff-hub-sidebar__item<?php echo ($cur_eid && (int)$ue['id'] === (int)$cur_eid) ? ' active' : ''; ?>"
           href="<?php echo htmlspecialchars($base); ?>set-einheit.php?einheit_id=<?php echo (int)$ue['id']; ?>">
            <i class="fas fa-sitemap ff-hub-sidebar__icon"></i><?php echo htmlspecialchars($ue['name']); ?>
        </a>
        <?php endforeach; ?>
        <div class="ff-hub-sidebar__divider"></div>
        <?php endif; ?>

        <a class="ff-hub-sidebar__item<?php echo ff_hub_nav_active('/index.php') && !ff_hub_nav_active('/admin/') ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($index_url); ?>">
            <i class="fas fa-home ff-hub-sidebar__icon"></i>Startseite
        </a>
        <a class="ff-hub-sidebar__item<?php echo ff_hub_nav_active('dashboard.php') ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>dashboard.php<?php echo htmlspecialchars($dash_q); ?>">
            <i class="fas fa-tachometer-alt ff-hub-sidebar__icon"></i>Dashboard
        </a>
        <div class="ff-hub-sidebar__divider"></div>

        <?php if (!empty($can_reservations)): ?>
        <div class="ff-hub-sidebar__module">Einsatz &amp; Vermietung</div>
        <a class="ff-hub-sidebar__item<?php echo ff_hub_nav_active('reservations.php') ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>reservations.php">
            <i class="fas fa-calendar ff-hub-sidebar__icon"></i>Reservierungen
        </a>
        <?php endif; ?>

        <?php if (!empty($can_atemschutz)): ?>
        <div class="ff-hub-sidebar__module">Atemschutz</div>
        <a class="ff-hub-sidebar__item<?php echo ff_hub_nav_active('atemschutz.php') && !ff_hub_nav_active('settings-atemschutz') ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>atemschutz.php">
            <i class="fas fa-user-shield ff-hub-sidebar__icon"></i>Atemschutz
        </a>
        <?php endif; ?>

        <?php if (!empty($can_members) || !empty($can_auswertung)): ?>
        <div class="ff-hub-sidebar__module">Personal</div>
        <?php if (!empty($can_members)): ?>
        <a class="ff-hub-sidebar__item<?php echo ff_hub_nav_active('members.php') ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>members.php">
            <i class="fas fa-users ff-hub-sidebar__icon"></i>Mitgliederverwaltung
        </a>
        <?php endif; ?>
        <?php if (!empty($can_auswertung)): ?>
        <a class="ff-hub-sidebar__item<?php echo ff_hub_nav_active('members-auswertung.php') ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>members-auswertung.php">
            <i class="fas fa-chart-pie ff-hub-sidebar__icon"></i>Auswertung
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php
        $ff_eid = function_exists('get_current_einheit_id') ? get_current_einheit_id() : null;
        $ff_einheit_q = ($ff_eid && (int)$ff_eid > 0) ? ('?einheit_id=' . (int)$ff_eid) : '';
        ?>
        <?php if (!empty($can_forms_fill)): ?>
        <div class="ff-hub-sidebar__divider"></div>
        <a class="ff-hub-sidebar__item<?php echo ff_hub_nav_active('/formulare.php') ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>../formulare.php<?php echo htmlspecialchars($ff_einheit_q); ?>">
            <i class="fas fa-edit ff-hub-sidebar__icon"></i>Formulare ausfüllen
        </a>
        <?php endif; ?>
        <?php if (!empty($can_forms)): ?>
        <a class="ff-hub-sidebar__item<?php echo ff_hub_nav_active('formularcenter.php') ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>formularcenter.php<?php echo htmlspecialchars($ff_einheit_q); ?>">
            <i class="fas fa-file-alt ff-hub-sidebar__icon"></i>Formularcenter
        </a>
        <?php endif; ?>

        <?php if (!empty($can_settings)): ?>
        <div class="ff-hub-sidebar__divider"></div>
        <div class="ff-hub-sidebar__module">Verwaltung</div>
        <a class="ff-hub-sidebar__item<?php echo (strpos($sn, 'settings') !== false && strpos($sn, 'feedback.php') === false) ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>settings.php">
            <i class="fas fa-cog ff-hub-sidebar__icon"></i>Einstellungen
        </a>
        <a class="ff-hub-sidebar__item<?php echo ff_hub_nav_active('feedback.php') ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>feedback.php">
            <i class="fas fa-comment-dots ff-hub-sidebar__icon"></i>Feedback
        </a>
        <?php endif; ?>

        <div class="ff-hub-sidebar__divider"></div>
        <a class="ff-hub-sidebar__item<?php echo (strpos($sn, 'profile.php') !== false) ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($base); ?>profile.php">
            <i class="fas fa-user-edit ff-hub-sidebar__icon"></i>Profil
        </a>
    </nav>
    <div class="ff-hub-sidebar__footer">Feuerwehr App</div>
</aside>
<div class="ff-hub-sidebar-backdrop d-lg-none" id="ff-hub-sidebar-backdrop" hidden></div>
<?php endif; ?>

<script>
(function(){
  var t = document.getElementById('ff-hub-sidebar-toggle');
  var s = document.getElementById('ff-hub-sidebar');
  var b = document.getElementById('ff-hub-sidebar-backdrop');
  if (!t || !s) return;
  function close() {
    s.classList.remove('ff-hub-sidebar--open');
    if (b) { b.hidden = true; }
  }
  t.addEventListener('click', function() {
    s.classList.toggle('ff-hub-sidebar--open');
    if (b) { b.hidden = !s.classList.contains('ff-hub-sidebar--open'); }
  });
  if (b) b.addEventListener('click', close);
  s.querySelectorAll('a').forEach(function(a) {
    a.addEventListener('click', close);
  });
})();
</script>
