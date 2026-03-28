# -*- coding: utf-8 -*-
import pathlib
import re

root = pathlib.Path(__file__).resolve().parents[1]

chrome_inc = "<?php include __DIR__ . '/../includes/chrome-navbar.inc.php'; ?>\n"
chrome_dash = "<?php include __DIR__ . '/../includes/chrome-navbar.inc.php'; ?>\n"

patterns = [
    (
        re.compile(
            r'<nav class="navbar navbar-expand-lg navbar-dark bg-primary">\s*'
            r'<div class="container-fluid">\s*'
            r'<a class="navbar-brand" href="\.\./index\.php"><i class="fas fa-fire"></i> Feuerwehr App</a>\s*'
            r'<div class="d-flex ms-auto align-items-center">\s*'
            r'<\?php \$admin_menu_in_navbar = true; include __DIR__ \. \'/includes/admin-menu\.inc\.php\'; \?>\s*'
            r'</div>\s*</div>\s*</nav>',
            re.MULTILINE | re.DOTALL,
        ),
        chrome_inc,
    ),
    (
        re.compile(
            r'<nav class="navbar navbar-expand-lg navbar-dark bg-primary">\s*'
            r'<div class="container-fluid">\s*'
            r'<a class="navbar-brand" href="\.\./index\.php">\s*'
            r'<i class="fas fa-fire"></i> Feuerwehr App\s*'
            r'</a>\s*'
            r'<div class="d-flex ms-auto align-items-center">\s*'
            r'<\?php \$admin_menu_in_navbar = true; include __DIR__ \. \'/includes/admin-menu\.inc\.php\'; \?>\s*'
            r'</div>\s*</div>\s*</nav>',
            re.MULTILINE | re.DOTALL,
        ),
        chrome_inc,
    ),
    (
        re.compile(
            r'<nav class="navbar navbar-expand-lg navbar-dark bg-primary">\s*'
            r'<div class="container-fluid">\s*'
            r'<a class="navbar-brand" href="dashboard\.php"><i class="fas fa-fire"></i> Feuerwehr App</a>\s*'
            r'<div class="d-flex ms-auto align-items-center">\s*'
            r'<\?php \$admin_menu_in_navbar = true; include __DIR__ \. \'/includes/admin-menu\.inc\.php\'; \?>\s*'
            r'</div>\s*</div>\s*\s*</nav>',
            re.MULTILINE | re.DOTALL,
        ),
        "<?php $ff_brand_href = 'dashboard.php'; include __DIR__ . '/../includes/chrome-navbar.inc.php'; ?>\n",
    ),
    (
        re.compile(
            r'<nav class="navbar navbar-expand-lg navbar-dark bg-primary">\s*'
            r'<div class="container">\s*'
            r'<a class="navbar-brand" href="index\.php<\?php echo \$einheit_param; \?>"><i class="fas fa-fire"></i> Feuerwehr App</a>\s*'
            r'<\?php if \(isset\(\$_SESSION\[\'user_id\'\]\) && !is_system_user\(\)\): \?>\s*'
            r'<div class="d-flex ms-auto">\s*'
            r'<\?php\s*'
            r'\$admin_menu_in_navbar = true;\s*'
            r'\$admin_menu_base = \'admin/\';\s*'
            r'\$admin_menu_logout = \'logout\.php\';\s*'
            r'\$admin_menu_index = \'index\.php\' \. \$einheit_param;\s*'
            r'include __DIR__ \. \'/admin/includes/admin-menu\.inc\.php\';\s*'
            r'\?>\s*'
            r'</div>\s*'
            r'<\?php else: \?>\s*'
            r'<\?php if \(!isset\(\$_SESSION\[\'user_id\'\]\)\): \?>\s*'
            r'<div class="d-flex ms-auto align-items-center">\s*'
            r'<a class="btn btn-outline-light btn-sm px-3 py-2 d-flex align-items-center gap-2" href="login\.php">\s*'
            r'<i class="fas fa-sign-in-alt"></i>\s*'
            r'<span class="fw-semibold">Anmelden</span>\s*'
            r'</a>\s*'
            r'</div>\s*'
            r'<\?php else: \?>\s*'
            r'<\?php include __DIR__ \. \'/includes/system-user-nav\.inc\.php\'; \?>\s*'
            r'<\?php endif; \?>\s*'
            r'<\?php endif; \?>\s*'
            r'</div>\s*'
            r'</nav>',
            re.MULTILINE | re.DOTALL,
        ),
        "<?php\n"
        "$ff_brand_href = 'index.php' . $einheit_param;\n"
        "$admin_menu_base = 'admin/';\n"
        "$admin_menu_logout = 'logout.php';\n"
        "$admin_menu_index = 'index.php' . $einheit_param;\n"
        "$ff_nav_container_fluid = false;\n"
        "include __DIR__ . '/includes/chrome-navbar.inc.php';\n"
        "?>\n",
    ),
]

n_files = 0
for p in root.rglob("*.php"):
    if "tools" in p.parts and p.name == "replace-chrome-nav.py":
        continue
    t = p.read_text(encoding="utf-8", errors="ignore")
    orig = t
    for rx, repl in patterns:
        t = rx.sub(repl, t, count=1)
    if t != orig:
        p.write_text(t, encoding="utf-8")
        n_files += 1
        print("updated", p.relative_to(root))

print("done, files:", n_files)
