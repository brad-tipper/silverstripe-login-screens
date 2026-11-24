<?php

namespace BradTipper\LoginScreens;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ThemeResourceLoader;

/**
 * Reads the login screen settings from YAML and turns them into CSS and a logo URL.
 */
class LoginScreen
{
    use Configurable;
    use Injectable;

    /**
     * Logo path inside the theme (e.g. images/logo.svg), a project or module file (e.g. app/client/dist/brand/logo.svg),
     * or a full URL. A file that cannot be found means no logo, not an error.
     */
    private static ?string $logo = null;

    /** Optional logo for dark mode. Same forms as `logo`. */
    private static ?string $logo_dark = null;

    private static ?int $logo_max_width = null;

    private static ?int $logo_max_height = null;

    private static bool $hide_silverstripe_brand = true;

    /** @var array<string, string> Hex colours, keyed by the names in COLOR_VARS. */
    private static array $colors = [];

    /** @var array<string, string> Same keys, used when the visitor prefers dark mode. */
    private static array $dark_colors = [];

    /** YAML key => CSS variable defined by silverstripe/login-forms. */
    private const COLOR_VARS = [
        'page_background' => '--color-page-background',
        'page_text' => '--color-page-text',
        'form_background' => '--color-form-background',
        'form_text' => '--color-form-text',
        'field_background' => '--color-field-background',
        'field_border' => '--color-field-border',
        'field_text' => '--color-field-text',
        'link' => '--color-link-primary',
        'button_background' => '--color-button-primary-background',
        'button_text' => '--color-button-primary-text',
        'button_hover_background' => '--color-button-primary-hover-background',
        'button_hover_text' => '--color-button-primary-hover-text',
        'error' => '--color-message-error',
        'warning' => '--color-message-warning',
    ];

    public static function css(): string
    {
        $cfg = static::config();
        $light = self::variables((array) $cfg->get('colors'));
        $dark = self::variables((array) $cfg->get('dark_colors'));
        $css = '';

        if ($light) {
            // Scoped away from dark mode when dark colours exist, so the light set cannot win over them.
            $css .= $dark
                ? '@media (prefers-color-scheme: light), (prefers-color-scheme: no-preference){:root{' . $light . '}}'
                : ':root{' . $light . '}';
        }
        if ($dark) {
            $css .= '@media (prefers-color-scheme: dark){:root{' . $dark . '}}';
        }

        $size = '';
        if (($w = (int) $cfg->get('logo_max_width')) > 0) {
            $size .= "--sizing-app-brand-max-width:{$w}px;";
        }
        if (($h = (int) $cfg->get('logo_max_height')) > 0) {
            $size .= "--sizing-app-brand-max-height:{$h}px;";
        }
        if ($size) {
            $css .= ':root{' . $size . '}';
        }
        if ($cfg->get('hide_silverstripe_brand')) {
            $css .= '.silverstripe-brand{display:none}';
        }
        return $css;
    }

    public static function logoUrl(bool $dark = false): string
    {
        $path = trim((string) static::config()->get($dark ? 'logo_dark' : 'logo'));
        if ($path === '') {
            return '';
        }
        if (preg_match('#^(https?:)?//#', $path) === 1) {
            return $path;
        }

        // Theme assets first (project themes, then the login themes), then a plain module/resource path.
        $themes = array_unique(array_merge(
            (array) SSViewer::config()->get('themes'),
            SSViewer::get_themes()
        ));
        $found = ThemeResourceLoader::inst()->findThemedResource($path, $themes);
        $resource = $found ?: $path;

        // A missing file must not break the login page: fall back to no logo.
        $relative = ltrim($resource, '/');
        if (is_file(Director::baseFolder() . '/' . $relative)) {
            return (string) ModuleResourceLoader::singleton()->resolveURL($relative);
        }
        if (is_file(Director::publicFolder() . '/' . $relative)) {
            return Director::baseURL() . $relative;
        }
        return '';
    }

    /** @param array<string, mixed> $colors */
    private static function variables(array $colors): string
    {
        $out = '';
        foreach (self::COLOR_VARS as $key => $var) {
            $hex = self::hex($colors[$key] ?? null);
            if ($hex !== null) {
                $out .= "{$var}:{$hex};";
            }
        }
        // One button colour is enough: derive the hover state unless it was set.
        $bg = self::hex($colors['button_background'] ?? null);
        if ($bg !== null && self::hex($colors['button_hover_background'] ?? null) === null) {
            $out .= '--color-button-primary-hover-background:' . self::shade($bg, 0.85) . ';';
        }
        // Secondary (outline) buttons follow the link colour.
        $link = self::hex($colors['link'] ?? null);
        if ($link !== null) {
            $out .= "--color-button-secondary-text:{$link};--color-button-secondary-hover-text:{$link};";
        }
        return $out;
    }

    private static function hex(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';
        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) === 1 ? $value : null;
    }

    /** Multiply each channel by $factor (below 1 darkens). */
    private static function shade(string $hex, float $factor): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $rgb = array_map(
            static fn(string $c): int => max(0, min(255, (int) round(hexdec($c) * $factor))),
            str_split($hex, 2)
        );
        return sprintf('#%02x%02x%02x', ...$rgb);
    }
}
