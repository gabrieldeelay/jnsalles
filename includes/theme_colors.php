<?php

if (!function_exists('jnsalles_theme_defaults')) {
    function jnsalles_theme_defaults()
    {
        return [
            'primary' => '#b42c63',
            'secondary' => '#6f2445',
            'header' => '#472536',
            'background' => '#faf8f9',
            'surface' => '#ffffff',
            'text' => '#34242c',
        ];
    }

    function jnsalles_normalize_theme_color($value, $fallback)
    {
        $value = strtolower(trim((string) $value));
        if (!preg_match('/^#[0-9a-f]{6}$/', $value)) {
            return strtolower($fallback);
        }

        return $value;
    }

    function jnsalles_hex_rgb($hex)
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    function jnsalles_mix_theme_color($first, $second, $firstWeight)
    {
        $firstRgb = jnsalles_hex_rgb($first);
        $secondRgb = jnsalles_hex_rgb($second);
        $firstWeight = max(0, min(1, (float) $firstWeight));
        $mixed = [];

        for ($index = 0; $index < 3; $index++) {
            $mixed[$index] = (int) round(($firstRgb[$index] * $firstWeight) + ($secondRgb[$index] * (1 - $firstWeight)));
        }

        return sprintf('#%02x%02x%02x', $mixed[0], $mixed[1], $mixed[2]);
    }

    function jnsalles_theme_contrast($hex)
    {
        $rgb = jnsalles_hex_rgb($hex);
        $luminance = (($rgb[0] * 299) + ($rgb[1] * 587) + ($rgb[2] * 114)) / 1000;
        return $luminance >= 150 ? '#171717' : '#ffffff';
    }

    function jnsalles_theme_colors($settings)
    {
        $defaults = jnsalles_theme_defaults();
        $colors = [];

        foreach ($defaults as $name => $fallback) {
            $stored = $settings ? $settings->info('theme_' . $name . '_color') : null;
            $colors[$name] = jnsalles_normalize_theme_color($stored, $fallback);
        }

        $colors['primary_hover'] = jnsalles_mix_theme_color($colors['primary'], '#000000', 0.82);
        $colors['accent'] = jnsalles_mix_theme_color($colors['primary'], '#ffffff', 0.82);
        $colors['soft'] = jnsalles_mix_theme_color($colors['primary'], $colors['surface'], 0.12);
        $colors['border'] = jnsalles_mix_theme_color($colors['secondary'], $colors['surface'], 0.18);
        $colors['muted'] = jnsalles_mix_theme_color($colors['text'], $colors['background'], 0.58);
        $colors['on_primary'] = jnsalles_theme_contrast($colors['primary']);
        $colors['on_header'] = jnsalles_theme_contrast($colors['header']);
        $colors['primary_rgb'] = implode(', ', jnsalles_hex_rgb($colors['primary']));
        $colors['text_rgb'] = implode(', ', jnsalles_hex_rgb($colors['text']));

        return $colors;
    }
}

