<?php


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
<style id="hot-pink-theme-variables">
  :root {
    --incrivel-bg: #faf8f9;
    --incrivel-border: #eadde3;
    --incrivel-bgColor: #34242c;
    --incrivel-bgLink: #6f2445;
    --incrivel-bgLinkHover: #b42c63;
    --incrivel-rgba: 255, 255, 255;
    --incrivel-rgbaInvert: 52, 36, 44;
    --incrivel-formBg: #fff;
    --incrivel-formBgHover: #f8edf2;
    --incrivel-formBgHoverColor: #5c3145;
    --incrivel-formBorder: #ded0d7;
    --incrivel-formColor: #34242c;
    --incrivel-cardBg: #fff;
    --incrivel-cardColor: #34242c;
    --incrivel-cardLink: #6f2445;
    --incrivel-modalBg: #fff;
    --incrivel-modalBorder: #eadde3;
    --incrivel-modalColor: #34242c;
    --incrivel-primaria: #b42c63;
    --incrivel-primariaColor: #fff;
    --incrivel-primariaColornew: #f8edf2;
    --incrivel-primariaLink: #fff;
    --incrivel-primariaLinkHover: #fff;
    --incrivel-primariaDarken: #f7f1f4;
    --incrivel-primariaDarkenColor: #5c3145;
    --incrivel-primariaDarkenLink: #6f2445;
    --incrivel-primariaDarkenLinkHover: #b42c63;
  }
</style>
