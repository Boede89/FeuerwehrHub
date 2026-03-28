<?php
/**
 * UI-Themes: „classic“ (Standard) und „hub“ (FeuerwehrHub-Optik aus lokalem Referenzprojekt).
 */

function feuerwehr_ensure_ui_theme_setting(PDO $db): void {
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('ui_theme', 'classic')");
        $stmt->execute();
    } catch (Exception $e) {
        // Tabelle/Key kann je nach Installation variieren
    }
}

/**
 * @return 'classic'|'hub'
 */
function get_ui_theme(): string {
    global $db;
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = 'classic';
    if (!$db) {
        return $cache;
    }
    try {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'ui_theme' LIMIT 1");
        $stmt->execute();
        $v = strtolower(trim((string) $stmt->fetchColumn()));
        if ($v === 'hub') {
            $cache = 'hub';
        }
    } catch (Exception $e) {
    }
    return $cache;
}

function is_hub_ui_theme(): bool {
    return get_ui_theme() === 'hub';
}

function ff_setting_get(string $key, string $default = ''): string {
    global $db;
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $cache[$key] = $default;
    if (!$db) {
        return $default;
    }
    try {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $v = trim((string) $stmt->fetchColumn());
        if ($v !== '') {
            $cache[$key] = $v;
        }
    } catch (Exception $e) {
    }
    return $cache[$key];
}
