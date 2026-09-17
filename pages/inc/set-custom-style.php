<?php

require_once __DIR__ . '/../../includes/theme_colors.php';
$themeColors = jnsalles_theme_colors($_settings);

$theme = $_settings->info('theme');

if ($theme == '2') {
	echo '    <style>' . "\r\n" . '        :root{--incrivel-bg:#0f121a;--incrivel-border:#d1d1d1;--incrivel-bgColor:#fff;--incrivel-bgLink:#fff;--incrivel-bgLinkHover:var(--incrivel-primaria);--incrivel-rgba:255,255,255;--incrivel-rgbaInvert:#fff;--incrivel-formBg:#fff;--incrivel-formBgHover:#fff;--incrivel-formBgHoverColor:#fff;--incrivel-formBorder:#c9c9c9;--incrivel-formColor:#fff;--incrivel-cardBg:#0f121a;--incrivel-cardColor:#fff;--incrivel-cardLink:#fff;--incrivel-modalBg:#fff;--incrivel-modalBorder:#eee;--incrivel-modalColor:#333;--incrivel-primaria:#000;--incrivel-primariaColornew:#000000;--incrivel-primariaColor:#cfcfcf;--incrivel-primariaLink:#fff;--incrivel-primariaLinkHover:#fff;--incrivel-primariaDarken:#343a40;--incrivel-primariaDarkenColor:#323232;--incrivel-primariaDarkenLink:#fff;--incrivel-primariaDarkenLinkHover:#fff}' . "\r\n" . '    </style>' . "\r\n";
}
else if ($theme == '3') {
	echo '    <style>' . "\r\n" . '        :root{--incrivel-bg:#727e8c;--incrivel-border:#d1d1d1;--incrivel-bgColor:#fff;--incrivel-bgLink:#fff;--incrivel-bgLinkHover:var(--incrivel-primaria);--incrivel-rgba:255,255,255;--incrivel-rgbaInvert:#fff;--incrivel-formBg:#fff;--incrivel-formBgHover:#d9d9d9;--incrivel-formBgHoverColor:#545454;--incrivel-formBorder:#c9c9c9;--incrivel-formColor:#5a5a5a;--incrivel-cardBg:#212b36;--incrivel-cardColor:#fff;--incrivel-cardLink:#000;--incrivel-modalBg:#fff;--incrivel-modalBorder:#eee;--incrivel-modalColor:#333;--incrivel-primaria:#212b36;--incrivel-primariaColor:#cfcfcf;--incrivel-primariaLink:#fff;--incrivel-primariaLinkHover:#fff;--incrivel-primariaDarken:#435365;--incrivel-primariaDarkenColor:#323232;--incrivel-primariaDarkenLink:#fff;--incrivel-primariaDarkenLinkHover:#fff}' . "\r\n" . '    </style>' . "\r\n";
}
else if ($theme == '4') {
	echo '    <style>' . "\r\n" . '        :root{--incrivel-bg:#36175b;--incrivel-border:#d1d1d1;--incrivel-bgColor:#fff;--incrivel-bgLink:#fff;--incrivel-bgLinkHover:var(--incrivel-primaria);--incrivel-rgba:255,255,255;--incrivel-rgbaInvert:#fff;--incrivel-formBg:#fff;--incrivel-formBgHover:#d9d9d9;--incrivel-formBgHoverColor:#545454;--incrivel-formBorder:#c9c9c9;--incrivel-formColor:#5a5a5a;--incrivel-cardBg:#1f0d35;--incrivel-cardColor:#fff;--incrivel-cardLink:#000;--incrivel-modalBg:#fff;--incrivel-modalBorder:#eee;--incrivel-modalColor:#333;--incrivel-primaria:#1f0d35;--incrivel-primariaColor:#cfcfcf;--incrivel-primariaLink:#fff;--incrivel-primariaLinkHover:#fff;--incrivel-primariaDarken:#250043;--incrivel-primariaDarkenColor:#323232;--incrivel-primariaDarkenLink:#fff;--incrivel-primariaDarkenLinkHover:#fff}' . "\r\n" . '    </style>' . "\r\n";
}
else if ($theme == '5') {
	echo '    <style>' . "\r\n" . '        :root{--incrivel-bg:#161C24;--incrivel-border:#d1d1d1;--incrivel-bgColor:#fff;--incrivel-bgLink:#fff;--incrivel-bgLinkHover:var(--incrivel-primaria);--incrivel-rgba:255,255,255;--incrivel-rgbaInvert:#fff;--incrivel-formBg:#fff;--incrivel-formBgHover:#d9d9d9;--incrivel-formBgHoverColor:#545454;--incrivel-formBorder:#c9c9c9;--incrivel-formColor:#5a5a5a;--incrivel-cardBg:#161C24;--incrivel-cardColor:#fff;--incrivel-cardLink:#000;--incrivel-modalBg:#fff;--incrivel-modalBorder:#eee;--incrivel-modalColor:#333;--incrivel-primaria:#11151B;--incrivel-primariaColor:#cfcfcf;--incrivel-primariaLink:#fff;--incrivel-primariaLinkHover:#fff;--incrivel-primariaDarken:#212B36;--incrivel-primariaDarkenColor:#323232;--incrivel-primariaDarkenLink:#fff;--incrivel-primariaDarkenLinkHover:#fff}' . "\r\n" . '    </style>' . "\r\n";
}

?>
<style id="site-theme-variables">
  :root {
    --theme-primary: <?= $themeColors['primary'] ?>;
    --theme-primary-hover: <?= $themeColors['primary_hover'] ?>;
    --theme-secondary: <?= $themeColors['secondary'] ?>;
    --theme-header: <?= $themeColors['header'] ?>;
    --theme-background: <?= $themeColors['background'] ?>;
    --theme-surface: <?= $themeColors['surface'] ?>;
    --theme-text: <?= $themeColors['text'] ?>;
    --theme-muted: <?= $themeColors['muted'] ?>;
    --theme-border: <?= $themeColors['border'] ?>;
    --theme-soft: <?= $themeColors['soft'] ?>;
    --theme-on-primary: <?= $themeColors['on_primary'] ?>;
    --theme-on-header: <?= $themeColors['on_header'] ?>;
    --hot-pink: <?= $themeColors['accent'] ?>;
    --hot-pink-strong: <?= $themeColors['primary'] ?>;
    --hot-pink-dark: <?= $themeColors['primary_hover'] ?>;
    --hot-pink-soft: <?= $themeColors['soft'] ?>;
    --hot-pink-surface: <?= $themeColors['background'] ?>;
    --hot-pink-card: <?= $themeColors['surface'] ?>;
    --hot-pink-text: <?= $themeColors['text'] ?>;
    --incrivel-bg: <?= $themeColors['background'] ?>;
    --incrivel-border: <?= $themeColors['border'] ?>;
    --incrivel-bgColor: <?= $themeColors['text'] ?>;
    --incrivel-bgLink: <?= $themeColors['secondary'] ?>;
    --incrivel-bgLinkHover: <?= $themeColors['primary'] ?>;
    --incrivel-rgba: 255, 255, 255;
    --incrivel-rgbaInvert: <?= $themeColors['text_rgb'] ?>;
    --incrivel-formBg: <?= $themeColors['surface'] ?>;
    --incrivel-formBgHover: <?= $themeColors['soft'] ?>;
    --incrivel-formBgHoverColor: <?= $themeColors['text'] ?>;
    --incrivel-formBorder: <?= $themeColors['border'] ?>;
    --incrivel-formColor: <?= $themeColors['text'] ?>;
    --incrivel-cardBg: <?= $themeColors['surface'] ?>;
    --incrivel-cardColor: <?= $themeColors['text'] ?>;
    --incrivel-cardLink: <?= $themeColors['secondary'] ?>;
    --incrivel-modalBg: <?= $themeColors['surface'] ?>;
    --incrivel-modalBorder: <?= $themeColors['border'] ?>;
    --incrivel-modalColor: <?= $themeColors['text'] ?>;
    --incrivel-primaria: <?= $themeColors['primary'] ?>;
    --incrivel-primariaColor: <?= $themeColors['on_primary'] ?>;
    --incrivel-primariaColornew: <?= $themeColors['soft'] ?>;
    --incrivel-primariaLink: <?= $themeColors['on_primary'] ?>;
    --incrivel-primariaLinkHover: <?= $themeColors['on_primary'] ?>;
    --incrivel-primariaDarken: <?= $themeColors['soft'] ?>;
    --incrivel-primariaDarkenColor: <?= $themeColors['text'] ?>;
    --incrivel-primariaDarkenLink: <?= $themeColors['secondary'] ?>;
    --incrivel-primariaDarkenLinkHover: <?= $themeColors['primary'] ?>;
    --incrivel-secundaria: <?= $themeColors['secondary'] ?>;
    --cor-secundaria: <?= $themeColors['secondary'] ?>;
    --cor-primaria-light: <?= $themeColors['accent'] ?>;
    --cor-primaria-lighten: <?= $themeColors['soft'] ?>;
    --primary-color: <?= $themeColors['primary'] ?>;
    --primary-text-color: <?= $themeColors['on_primary'] ?>;
    --secondary-color: <?= $themeColors['secondary'] ?>;
    --tertiary-color: <?= $themeColors['primary'] ?>;
    --header-bg-color: <?= $themeColors['header'] ?>;
    --button-color: <?= $themeColors['primary'] ?>;
    --button-border: <?= $themeColors['accent'] ?>;
    --title-color: <?= $themeColors['on_header'] ?>;
    --text-color: <?= $themeColors['soft'] ?>;
    --bs-primary: <?= $themeColors['primary'] ?>;
    --bs-primary-rgb: <?= $themeColors['primary_rgb'] ?>;
    --bs-success: <?= $themeColors['primary'] ?>;
    --bs-success-rgb: <?= $themeColors['primary_rgb'] ?>;
  }

  body,.app-main,.app-footer{color:var(--theme-text);background-color:var(--theme-background)}
  .header-app-header .header-app-header-container,.header-app-header.campanha .header-app-header-container,.app-header,.navbar{color:var(--theme-on-header)!important;background-color:var(--theme-header)!important}
  .navbar.scrolled{background-color:var(--theme-header)!important}
  .black-bar,.black-bar.campanha,.bg-azul-personalizado{color:var(--theme-on-primary)!important;background-color:var(--theme-primary)!important}
  .home-hero{background:linear-gradient(135deg,var(--theme-header),var(--theme-secondary))!important}
  .app-card,.home-info-card,.app-vendas-express,.modal-content,.accordion-item,.vendasExpressNumsSelect.v2 .item .item-content{background-color:var(--theme-surface)!important;color:var(--theme-text)}
  .form-control,.form-select,.purchase-quantity-control .qty{border-color:var(--theme-border);background-color:var(--theme-surface);color:var(--theme-text)}
  .app-title-desc,.text-muted,.purchase-helper-card p,.vendasExpressNumsSelect.v2 .item .item-content p{color:var(--theme-muted)!important}
  .btn-primary,.btn-success,.bg-primary,.bg-success,.purchase-submit-button,.home-info-actions a{color:var(--theme-on-primary)!important;border-color:var(--theme-primary)!important;background:var(--theme-primary)!important}
  .btn-primary:hover,.btn-success:hover,.purchase-submit-button:hover{color:var(--theme-on-primary)!important;background:var(--theme-primary-hover)!important}
</style>
