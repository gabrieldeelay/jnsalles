<?php



if ($_settings->userdata('type') != '1') {
	echo 'Você não tem permissão para acessar essa página.';
	exit();
}

$enable_cpf = $_settings->info('enable_cpf');
$enable_email = $_settings->info('enable_email');
$enable_address = $_settings->info('enable_address');
$enable_birth = $_settings->info('enable_birth');
$enable_legal_age = $_settings->info('enable_legal_age');
$enable_instagram = $_settings->info('enable_instagram');
$enable_share = $_settings->info('enable_share');
$enable_groups = $_settings->info('enable_groups');
$enable_footer = $_settings->info('enable_footer');
$enable_password = $_settings->info('enable_password');
$enable_two_phone = $_settings->info('enable_two_phone');
$enable_multiple_order = $_settings->info('enable_multiple_order');
$text_footer = $_settings->info('text_footer');
$telegram_group_url = $_settings->info('telegram_group_url');
$whatsapp_group_url = $_settings->info('whatsapp_group_url');
$theme = $_settings->info('theme');
require_once dirname(__DIR__, 2) . '/includes/theme_colors.php';
$themeColors = jnsalles_theme_colors($_settings);
$brandImageUrl = validate_image($_settings->info('logo'));
$enable_pixel = $_settings->info('enable_pixel');
$facebook_access_token = $_settings->info('facebook_access_token');
$facebook_pixel_id = $_settings->info('facebook_pixel_id');
$enable_hide_numbers = $_settings->info('enable_hide_numbers');
$enable_ga4 = $_settings->info('enable_ga4');
$google_ga4_id = $_settings->info('google_ga4_id');
$enable_gtm = $_settings->info('enable_gtm');
$google_gtm_id = $_settings->info('google_gtm_id');
$enable_dwapi = $_settings->info('enable_dwapi');
$whatsapp_footer = $_settings->info('whatsapp_footer');
$instagram_footer = $_settings->info('instagram_footer');
$facebook_footer = $_settings->info('facebook_footer');
$twitter_footer = $_settings->info('twitter_footer');
$youtube_footer = $_settings->info('youtube_footer');
?>
<script>
$(function () {
    window.setTimeout(function () {
    var form = $('#manage-system');
    if (!form.length) return;
    var feedback = $('<div id="settings-feedback" class="settings-feedback" role="status" aria-live="polite"></div>');
    form.before(feedback);

    var themeEditor = $('#theme-editor');
    var activeThemeColor = null;

    function normalizeThemeHex(value) {
        var clean = String(value || '').trim().replace(/^#/, '');
        if (/^[0-9a-f]{3}$/i.test(clean)) {
            clean = clean.split('').map(function (character) { return character + character; }).join('');
        }
        return /^[0-9a-f]{6}$/i.test(clean) ? '#' + clean.toLowerCase() : null;
    }

    function hexToRgb(hex) {
        var normalized = normalizeThemeHex(hex) || '#000000';
        return {
            r: parseInt(normalized.slice(1, 3), 16),
            g: parseInt(normalized.slice(3, 5), 16),
            b: parseInt(normalized.slice(5, 7), 16)
        };
    }

    function rgbToHsl(rgb) {
        var r = rgb.r / 255;
        var g = rgb.g / 255;
        var b = rgb.b / 255;
        var max = Math.max(r, g, b);
        var min = Math.min(r, g, b);
        var h = 0;
        var s = 0;
        var l = (max + min) / 2;
        var delta = max - min;
        if (delta) {
            s = l > 0.5 ? delta / (2 - max - min) : delta / (max + min);
            if (max === r) h = ((g - b) / delta + (g < b ? 6 : 0));
            if (max === g) h = ((b - r) / delta + 2);
            if (max === b) h = ((r - g) / delta + 4);
            h *= 60;
        }
        return { h: Math.round(h), s: Math.round(s * 100), l: Math.round(l * 100) };
    }

    function hslToHex(h, s, l) {
        h = ((Number(h) % 360) + 360) % 360;
        s = Math.max(0, Math.min(100, Number(s))) / 100;
        l = Math.max(0, Math.min(100, Number(l))) / 100;
        var c = (1 - Math.abs(2 * l - 1)) * s;
        var x = c * (1 - Math.abs((h / 60) % 2 - 1));
        var m = l - c / 2;
        var rgb = [0, 0, 0];
        if (h < 60) rgb = [c, x, 0];
        else if (h < 120) rgb = [x, c, 0];
        else if (h < 180) rgb = [0, c, x];
        else if (h < 240) rgb = [0, x, c];
        else if (h < 300) rgb = [x, 0, c];
        else rgb = [c, 0, x];
        return '#' + rgb.map(function (channel) {
            return Math.round((channel + m) * 255).toString(16).padStart(2, '0');
        }).join('');
    }

    function readableColor(hex) {
        var rgb = hexToRgb(hex);
        var luminance = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
        return luminance > 158 ? '#17151a' : '#ffffff';
    }

    function updateFineTuning(input) {
        if (!input) return;
        var hsl = rgbToHsl(hexToRgb(input.value));
        $('#theme_hue').val(hsl.h);
        $('#theme_saturation').val(hsl.s);
        $('#theme_lightness').val(hsl.l);
        $('#theme_hue_value').text(hsl.h + '°');
        $('#theme_saturation_value').text(hsl.s + '%');
        $('#theme_lightness_value').text(hsl.l + '%');
    }

    function setActiveThemeColor(input) {
        if (!input) return;
        activeThemeColor = input;
        themeEditor.find('.theme-color-field').removeClass('is-active');
        var field = $(input).closest('.theme-color-field').addClass('is-active');
        $('#theme-active-color-name').text(field.data('label'));
        updateFineTuning(input);
    }

    function refreshThemePreview() {
        if (!themeEditor.length) return;
        var primary = $('#theme_primary_color').val();
        var secondary = $('#theme_secondary_color').val();
        var header = $('#theme_header_color').val();
        var background = $('#theme_background_color').val();
        var surface = $('#theme_surface_color').val();
        var text = $('#theme_text_color').val();
        themeEditor.css({
            '--preview-primary': primary,
            '--preview-secondary': secondary,
            '--preview-header': header,
            '--preview-background': background,
            '--preview-surface': surface,
            '--preview-text': text,
            '--preview-on-primary': readableColor(primary),
            '--preview-on-header': readableColor(header)
        });
        themeEditor.find('input[type="color"]').each(function () {
            var hexInput = themeEditor.find('.theme-hex-input[data-for="' + this.id + '"]');
            if (!hexInput.is(':focus')) hexInput.val(this.value.slice(1).toUpperCase());
            $(this).closest('.theme-color-field').find('.theme-color-swatch').css('background', this.value);
        });
    }

    function applyThemeColor(input, color, updateSliders) {
        var normalized = normalizeThemeHex(color);
        if (!input || !normalized) return false;
        input.value = normalized;
        themeEditor.find('.theme-hex-input[data-for="' + input.id + '"]').val(normalized.slice(1).toUpperCase()).removeClass('is-invalid');
        refreshThemePreview();
        if (updateSliders !== false && activeThemeColor === input) updateFineTuning(input);
        return true;
    }

    themeEditor.on('click focusin', '.theme-color-field', function () {
        setActiveThemeColor(document.getElementById($(this).data('color-input')));
    });
    themeEditor.on('input change', 'input[type="color"]', function () {
        setActiveThemeColor(this);
        applyThemeColor(this, this.value, true);
    });
    themeEditor.on('input', '.theme-hex-input', function () {
        var input = document.getElementById($(this).data('for'));
        var normalized = normalizeThemeHex($(this).val());
        $(this).toggleClass('is-invalid', !normalized);
        if (normalized) {
            setActiveThemeColor(input);
            applyThemeColor(input, normalized, true);
        }
    });
    themeEditor.on('blur change', '.theme-hex-input', function () {
        var input = document.getElementById($(this).data('for'));
        if (!applyThemeColor(input, $(this).val(), true)) {
            $(this).val(input.value.slice(1).toUpperCase()).removeClass('is-invalid');
        }
    });
    themeEditor.on('input', '.theme-hsl-range', function () {
        if (!activeThemeColor) return;
        var h = $('#theme_hue').val();
        var s = $('#theme_saturation').val();
        var l = $('#theme_lightness').val();
        $('#theme_hue_value').text(h + '°');
        $('#theme_saturation_value').text(s + '%');
        $('#theme_lightness_value').text(l + '%');
        applyThemeColor(activeThemeColor, hslToHex(h, s, l), false);
    });
    themeEditor.on('click', '.theme-preset', function () {
        if (!activeThemeColor) return;
        applyThemeColor(activeThemeColor, $(this).data('color'), true);
    });
    $('#reset-theme-colors').on('click', function () {
        var defaults = $(this).data('defaults');
        Object.keys(defaults).forEach(function (name) {
            var input = document.getElementById('theme_' + name + '_color');
            applyThemeColor(input, defaults[name], false);
        });
        refreshThemePreview();
        updateFineTuning(activeThemeColor);
    });
    setActiveThemeColor(document.getElementById('theme_primary_color'));
    refreshThemePreview();

    var currentBrandPreview = $('.js-brand-preview').first().attr('src');
    $('#customFile1').off('change.themePreview').on('change.themePreview', function () {
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            $('.js-brand-preview').attr('src', currentBrandPreview);
            return;
        }
        var reader = new FileReader();
        reader.onload = function (event) {
            $('.js-brand-preview').attr('src', event.target.result);
        };
        reader.readAsDataURL(file);
    });

    function prepareSettingsData(formElement) {
        var data = new FormData(formElement);
        var imageInput = formElement.querySelector('input[type="file"][name="img"]');
        var imageFile = imageInput && imageInput.files ? imageInput.files[0] : null;

        if (!imageFile) return Promise.resolve({ data: data, hasImage: false });
        if (!/^image\/(png|jpeg)$/.test(imageFile.type)) {
            return Promise.reject(new Error('Escolha uma imagem PNG ou JPG.'));
        }
        if (imageFile.size > 4 * 1024 * 1024) {
            return Promise.reject(new Error('A imagem deve ter no máximo 4 MB.'));
        }

        // O preview do Plesk pode interromper uploads grandes antes de eles
        // chegarem ao PHP. Otimizamos apenas quando necessário e mantemos o
        // arquivo enviado abaixo de 700 KB.
        if (imageFile.size <= 700 * 1024) return Promise.resolve({ data: data, hasImage: true });

        feedback.attr('class', 'settings-feedback info').text('Otimizando a imagem antes de salvar...');
        return optimizeBrandImage(imageFile).then(function (optimizedImage) {
            var originalName = imageFile.name.replace(/\.[^.]+$/, '') || 'logo';
            data.set('img', optimizedImage.blob, originalName + '-otimizada.' + optimizedImage.extension);
            return { data: data, hasImage: true };
        });
    }

    function optimizeBrandImage(file) {
        return new Promise(function (resolve, reject) {
            var objectUrl = URL.createObjectURL(file);
            var image = new Image();

            image.onload = function () {
                URL.revokeObjectURL(objectUrl);
                var preserveTransparency = file.type === 'image/png';
                var outputType = preserveTransparency ? 'image/png' : 'image/jpeg';
                var extension = preserveTransparency ? 'png' : 'jpg';
                var originalMax = Math.max(image.naturalWidth, image.naturalHeight);
                var targetMax = Math.min(1000, originalMax);

                function encodeNextSize() {
                    var scale = Math.min(1, targetMax / originalMax);
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
                    canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
                    var context = canvas.getContext('2d');

                    if (!preserveTransparency) {
                        context.fillStyle = '#ffffff';
                        context.fillRect(0, 0, canvas.width, canvas.height);
                    }
                    context.drawImage(image, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            reject(new Error('Não foi possível preparar a imagem. Escolha outro arquivo.'));
                            return;
                        }
                        if (blob.size > 600 * 1024 && targetMax > 360) {
                            targetMax = Math.max(360, Math.floor(targetMax * 0.82));
                            encodeNextSize();
                            return;
                        }
                        if (blob.size > 700 * 1024) {
                            reject(new Error('Não foi possível reduzir a imagem sem perder qualidade. Escolha um PNG menor.'));
                            return;
                        }
                        resolve({ blob: blob, extension: extension });
                    }, outputType, preserveTransparency ? undefined : 0.82);
                }

                encodeNextSize();
            };
            image.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Não foi possível abrir a imagem escolhida.'));
            };
            image.src = objectUrl;
        });
    }

    form.off('submit').on('submit', function (event) {
        event.preventDefault();
        var button = form.find('button[form="manage-system"]');
        button.prop('disabled', true).text('Salvando...');
        feedback.attr('class', 'settings-feedback info').text('Salvando configurações...');
        var preparedUpload = null;
        prepareSettingsData(this).then(function (prepared) {
            preparedUpload = prepared;
            $.ajax({
                url: _base_url_ + 'class/System.php?action=update_system',
                data: prepared.data,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                dataType: 'json'
            }).done(function (response) {
                if (response.status === 'success') {
                    if (preparedUpload.hasImage && !response.brand_updated) {
                        feedback.attr('class', 'settings-feedback error').text('Os textos foram salvos, mas o servidor não confirmou a nova imagem. Tente novamente.');
                        return;
                    }
                    if (response.logo_url) {
                        $('#cimg, .js-brand-preview').attr('src', response.logo_url);
                    }
                    feedback.attr('class', 'settings-feedback success').text(response.msg || 'Configurações salvas e verificadas no servidor.');
                    window.setTimeout(function () {
                        var freshUrl = new URL(window.location.href);
                        freshUrl.searchParams.set('_saved', response.saved_at || Date.now());
                        window.location.replace(freshUrl.toString());
                    }, 1100);
                } else {
                    feedback.attr('class', 'settings-feedback error').text(response.msg || 'Não foi possível salvar as configurações.');
                }
            }).fail(function (request) {
                var message;
                if (request.responseJSON && request.responseJSON.msg) {
                    message = request.responseJSON.msg;
                } else if (request.status === 413) {
                    message = 'A imagem ultrapassou o limite de envio do servidor. Escolha uma imagem menor.';
                } else {
                    var status = request.status ? ' (HTTP ' + request.status + ')' : '';
                    message = 'O servidor não concluiu o salvamento' + status + '. Tente novamente.';
                }
                feedback.attr('class', 'settings-feedback error').text(message);
            }).always(function () {
                button.prop('disabled', false).text('Salvar');
            });
        }).catch(function (error) {
            feedback.attr('class', 'settings-feedback error').text(error.message || 'Não foi possível preparar a imagem.');
            button.prop('disabled', false).text('Salvar');
        });
    });
    }, 0);
});
</script>
<style>
body>div.flex.h-screen>div.flex.flex-col.flex-1{min-width:0}body main.h-full{min-width:0;overflow-x:hidden}body main.h-full>.container{display:block!important;width:100%;min-width:0;max-width:1180px;padding:30px 24px 52px}body main.h-full>.container>h2{margin:0 0 20px!important;color:#f8fafc!important;font-size:30px!important;font-weight:800!important;letter-spacing:-.035em}body main.h-full>.container>h2:before{display:block;margin-bottom:5px;color:#a78bfa;content:'IDENTIDADE E PREFERÊNCIAS';font-size:11px;font-weight:800;letter-spacing:.14em}body main.h-full>.container>.px-4{overflow:hidden;padding:0!important;border:1px solid #2d3748;border-radius:16px!important;background:linear-gradient(145deg,rgba(30,41,59,.76),rgba(17,24,39,.96))!important;box-shadow:0 18px 45px rgba(0,0,0,.16)!important}body main.h-full>.container>.px-4>.flex{max-width:100%;overflow-x:auto;padding:14px 16px 0;border-bottom:1px solid #2d3748;background:rgba(15,23,42,.45)}#tabs{min-width:max-content;flex-wrap:nowrap!important;gap:6px}#tabs li{margin:0!important}#tabs a{border:1px solid transparent!important;border-radius:9px 9px 0 0!important;background:transparent!important;color:#94a3b8!important;font-size:12px!important;transition:.18s}#tabs a:hover{background:#202838!important;color:#fff!important}#tabs a.active-tab{border-color:#3f4d63!important;border-bottom-color:#171d28!important;background:#171d28!important;color:#fff!important}#manage-system{min-width:0;padding:20px 22px 24px}#manage-system .tabcontent{min-width:0}#manage-system input.form-input,#manage-system select.form-select,#manage-system textarea.form-textarea{width:100%;min-height:44px!important;border:1px solid #3f4d63!important;border-radius:9px!important;background:#0f172a!important;color:#f8fafc!important;box-shadow:none!important}#manage-system input:focus,#manage-system select:focus,#manage-system textarea:focus{border-color:#8b5cf6!important;box-shadow:0 0 0 3px rgba(139,92,246,.15)!important}#manage-system label>span{font-size:12px!important;font-weight:650}#manage-system img{max-width:100%;height:auto}.groups,.groups_social,.footer-text,.pixel-facebook,.pixel-google,.pixel-google-gtm{max-width:100%}.settings-feedback{display:none;position:sticky;top:12px;z-index:20;margin:0 22px 14px;padding:12px 14px;border-radius:10px;font-size:12px;font-weight:700}.settings-feedback.success{display:block;background:#064e3b;color:#d1fae5}.settings-feedback.error{display:block;background:#7f1d1d;color:#fee2e2}.settings-feedback.info{display:block;background:#312e81;color:#ede9fe}@media(max-width:640px){body main.h-full>.container{padding:22px 14px 42px}body main.h-full>.container>h2{font-size:25px!important}#manage-system{padding:16px}#manage-system .grid{grid-template-columns:1fr!important}#manage-system button[form="manage-system"]{width:100%}.can-toggle label .can-toggle__switch{max-width:134px}}
</style>
<style>
#tabs li:has(>a[href="#tab3"]),#tabs li:has(>a[href="#tab5"]),#tabs li:has(>a[href="#tab7"]),#tab3,#tab5,#tab7,.social-rodape,#tab4 .groups,#tab4 .groups_social{display:none!important}
#manage-system label:has(+ .can-toggle #enable_instagram),#manage-system .can-toggle:has(#enable_instagram){display:none!important}
.theme-editor{--preview-primary:#b42c63;--preview-secondary:#6f2445;--preview-header:#472536;--preview-background:#faf8f9;--preview-surface:#fff;--preview-text:#34242c;--preview-on-primary:#fff;--preview-on-header:#fff;margin-top:22px;padding:20px;border:1px solid #3f4d63;border-radius:16px;background:rgba(15,23,42,.72)}
.theme-editor__heading{margin:0 0 5px;color:#f8fafc;font-size:17px;font-weight:800}.theme-editor__help{margin:0 0 17px;color:#94a3b8;font-size:12px;line-height:1.55}
.theme-preset-bar{display:flex;align-items:center;gap:8px;overflow-x:auto;margin-bottom:14px;padding-bottom:3px}.theme-preset-bar__label{flex:0 0 auto;margin-right:3px;color:#94a3b8;font-size:11px;font-weight:750}.theme-preset{flex:0 0 auto;width:28px;height:28px;border:2px solid rgba(255,255,255,.72);border-radius:999px;background:var(--preset-color);box-shadow:0 0 0 1px rgba(15,23,42,.9);cursor:pointer;transition:transform .16s,box-shadow .16s}.theme-preset:hover,.theme-preset:focus{transform:scale(1.12);box-shadow:0 0 0 3px rgba(139,92,246,.28);outline:none}
.theme-color-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.theme-color-field{display:block!important;padding:12px;border:1px solid #334155;border-radius:12px;background:#111827;cursor:pointer;transition:border-color .17s,transform .17s,box-shadow .17s}.theme-color-field:hover{border-color:#64748b;transform:translateY(-1px)}.theme-color-field.is-active{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.16)}.theme-color-field__top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}.theme-color-field__label{margin:0;color:#cbd5e1!important;font-size:12px!important;font-weight:750!important}.theme-color-swatch{width:22px;height:22px;border:2px solid rgba(255,255,255,.8);border-radius:7px;box-shadow:0 0 0 1px rgba(0,0,0,.28)}
.theme-color-control{display:grid;grid-template-columns:54px minmax(0,1fr);align-items:center;gap:9px}.theme-color-control input[type=color]{width:54px!important;min-width:54px;height:43px!important;min-height:43px!important;padding:3px!important;border:1px solid #475569!important;border-radius:9px!important;background:#0f172a!important;cursor:pointer}.theme-hex-control{display:flex;align-items:center;height:43px;border:1px solid #3f4d63;border-radius:9px;background:#0b1220;transition:border-color .16s,box-shadow .16s}.theme-hex-control:focus-within{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.14)}.theme-hex-prefix{padding-left:10px;color:#64748b;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;font-weight:800}.theme-hex-input{width:100%;min-width:0;height:40px!important;min-height:40px!important;padding:0 9px 0 3px!important;border:0!important;background:transparent!important;color:#f8fafc!important;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;font-weight:750;text-transform:uppercase;outline:none!important;box-shadow:none!important}.theme-hex-input.is-invalid{color:#fecaca!important}
.theme-fine-tuning{margin-top:14px;padding:14px;border:1px solid #334155;border-radius:12px;background:#0b1220}.theme-fine-tuning__head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}.theme-fine-tuning__title{color:#f8fafc;font-size:12px;font-weight:800}.theme-fine-tuning__active{padding:4px 8px;border-radius:999px;background:#312e81;color:#ddd6fe;font-size:10px;font-weight:750}.theme-slider-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.theme-slider{display:grid;grid-template-columns:1fr auto;gap:5px 8px;align-items:center}.theme-slider label{color:#94a3b8!important;font-size:11px!important;font-weight:700!important}.theme-slider output{color:#e2e8f0;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10px;font-weight:700}.theme-slider input[type=range]{grid-column:1/-1;width:100%;height:5px;accent-color:var(--preview-primary);cursor:pointer}.theme-slider--hue input[type=range]{height:8px;border-radius:999px;background:linear-gradient(90deg,#f00,#ff0,#0f0,#0ff,#00f,#f0f,#f00);appearance:none}.theme-slider--hue input[type=range]::-webkit-slider-thumb{width:16px;height:16px;border:2px solid #fff;border-radius:50%;background:#111827;appearance:none}
.theme-preview-title{display:flex;align-items:end;justify-content:space-between;gap:12px;margin:18px 0 9px}.theme-preview-title strong{color:#f8fafc;font-size:13px}.theme-preview-title span{color:#94a3b8;font-size:10px}.theme-preview-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(190px,.75fr);gap:12px}.theme-site-preview{overflow:hidden;border:1px solid #334155;border-radius:13px;background:var(--preview-background);box-shadow:0 14px 32px rgba(0,0,0,.2)}.theme-browser-bar{display:flex;align-items:end;height:31px;padding:0 8px;background:#111827}.theme-browser-tab{display:flex;align-items:center;gap:6px;max-width:190px;height:25px;padding:0 10px;border-radius:7px 7px 0 0;background:#293244;color:#dbe4f0;font-size:9px}.theme-browser-tab img{width:14px!important;height:14px!important;object-fit:contain}.theme-browser-tab span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.theme-browser-accent{height:3px;background:var(--preview-primary)}.theme-site-header{display:flex;align-items:center;justify-content:space-between;min-height:54px;padding:8px 14px;background:var(--preview-header);color:var(--preview-on-header)}.theme-site-header img{width:auto!important;max-width:78px!important;height:37px!important;object-fit:contain}.theme-site-menu{font-size:16px;font-weight:900;letter-spacing:2px}.theme-site-body{padding:15px;color:var(--preview-text);background:var(--preview-background)}.theme-site-kicker{color:var(--preview-secondary);font-size:8px;font-weight:850;letter-spacing:.12em;text-transform:uppercase}.theme-site-hero{margin:3px 0 12px;font-size:16px;font-weight:850;line-height:1.15}.theme-site-card{display:grid;grid-template-columns:54px 1fr auto;align-items:center;gap:10px;padding:10px;border-radius:10px;background:var(--preview-surface);box-shadow:0 3px 12px rgba(0,0,0,.1)}.theme-site-card__image{height:45px;border-radius:8px;background:linear-gradient(135deg,var(--preview-primary),var(--preview-secondary))}.theme-site-card strong{display:block;font-size:10px}.theme-site-card small{display:block;margin-top:2px;opacity:.72;font-size:8px}.theme-preview__button{display:inline-block;padding:7px 10px;border-radius:8px;background:var(--preview-primary);color:var(--preview-on-primary);font-size:8px;font-weight:850}.theme-logo-preview{display:grid;grid-template-rows:auto 1fr 1fr;overflow:hidden;border:1px solid #334155;border-radius:13px;background:#0b1220}.theme-logo-preview__label{padding:9px 11px;color:#cbd5e1;font-size:10px;font-weight:800}.theme-logo-stage{display:flex;align-items:center;justify-content:center;min-height:76px;padding:10px}.theme-logo-stage--header{background:var(--preview-header)}.theme-logo-stage--surface{background:var(--preview-surface)}.theme-logo-stage img{width:auto!important;max-width:100%!important;height:54px!important;object-fit:contain}.theme-editor__actions{display:flex;justify-content:flex-end;margin-top:12px}.theme-reset{padding:8px 12px;border:1px solid #475569;border-radius:9px;background:#1e293b;color:#e2e8f0;font-size:11px;font-weight:700;cursor:pointer}.theme-reset:hover{background:#273449}
@media(max-width:860px){.theme-color-grid{grid-template-columns:1fr 1fr}.theme-preview-grid{grid-template-columns:1fr}.theme-logo-preview{grid-template-columns:auto 1fr 1fr;grid-template-rows:auto}.theme-logo-preview__label{display:flex;align-items:center}.theme-logo-stage{min-height:82px}}
@media(max-width:580px){.theme-editor{padding:14px}.theme-preset-bar{flex-wrap:wrap;overflow-x:visible}.theme-preset-bar__label{flex-basis:100%;margin-bottom:2px}.theme-color-grid,.theme-slider-grid{grid-template-columns:1fr}.theme-preview-title{align-items:start;flex-direction:column}.theme-logo-preview{grid-template-columns:1fr 1fr;grid-template-rows:auto 1fr}.theme-logo-preview__label{grid-column:1/-1}.theme-site-card{grid-template-columns:46px 1fr}.theme-preview__button{grid-column:1/-1;text-align:center}}
</style>
<?php
echo '<style>' . "\r\n\t" . '.active-tab{border-bottom:none!important}.can-toggle{position:relative;margin-bottom:20px}.can-toggle *,.can-toggle :after,.can-toggle :before{box-sizing:border-box}.can-toggle input[type=checkbox]{opacity:0;position:absolute;top:0;left:0}.can-toggle input[type=checkbox]:checked~label .can-toggle__switch:before{content:attr(data-unchecked);left:0}.can-toggle label{cursor:pointer;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;position:relative;display:flex;align-items:center;font-size:14px}.can-toggle label .can-toggle__switch{position:relative;transition:background-color .3s cubic-bezier(0, 1, .5, 1);background:#848484;height:36px;flex:0 0 134px;border-radius:4px}.can-toggle label .can-toggle__switch:before{content:attr(data-checked);position:absolute;top:0;text-transform:uppercase;text-align:center;color:rgba(255,255,255,.5);left:67px;font-size:12px;line-height:36px;width:67px;padding:0 12px}.can-toggle label .can-toggle__switch:after{content:attr(data-unchecked);position:absolute;z-index:5;text-transform:uppercase;text-align:center;background:#fff;transform:translate3d(0,0,0);transition:transform .3s cubic-bezier(0, 1, .5, 1);color:#777;top:2px;left:2px;border-radius:2px;width:65px;line-height:32px;font-size:12px}.can-toggle input[type=checkbox]:focus~label .can-toggle__switch,.can-toggle input[type=checkbox]:hover~label .can-toggle__switch{background-color:#777}.can-toggle input[type=checkbox]:focus~label .can-toggle__switch:after,.can-toggle input[type=checkbox]:hover~label .can-toggle__switch:after{color:#5e5e5e;box-shadow:0 3px 3px rgba(0,0,0,.4)}.can-toggle input[type=checkbox]:hover~label{color:#6a6a6a}.can-toggle input[type=checkbox]:checked~label:hover{color:#55bc49}.can-toggle input[type=checkbox]:checked~label .can-toggle__switch{background-color:#70c767}.can-toggle input[type=checkbox]:checked~label .can-toggle__switch:after{content:attr(data-checked);color:#4fb743;transform:translate3d(65px,0,0)}.can-toggle input[type=checkbox]:checked:focus~label .can-toggle__switch,.can-toggle input[type=checkbox]:checked:hover~label .can-toggle__switch{background-color:#5fc054}.can-toggle input[type=checkbox]:checked:focus~label .can-toggle__switch:after,.can-toggle input[type=checkbox]:checked:hover~label .can-toggle__switch:after{color:#47a43d;box-shadow:0 3px 3px rgba(0,0,0,.4)}.can-toggle label .can-toggle__switch:hover:after{box-shadow:0 3px 3px rgba(0,0,0,.4)}@media all and (max-width:40em){#tabs{flex-wrap:wrap}#tabs .mr-1{margin-bottom:15px}}#cimg{max-width:100%;max-height:25em;object-fit:scale-down;object-position:center center}h2.social-rodape{font-weight:700;margin-top:20px}' . "\r\n" . '</style>' . "\r\n" . '<main class="h-full pb-16 overflow-y-auto">' . "\r\n\t" . '<div class="container px-6 mx-auto grid">' . "\r\n\t\t" . '<h2' . "\t" . 'class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Configuração</h2>' . "\r\n\r\n\t" . '<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">' . "\r\n\t\t" . '<div class="flex">' . "\r\n\t\t\t" . '<ul class="flex" id="tabs">' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab1" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700 active-tab">Configurações</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab2" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Cadastro</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab3" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Social</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab4" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Rodapé</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab6" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Ocultar Cotas</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab7" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">WhatsApp</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab8" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Email</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab9" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">FAQ</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab10" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Termos</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab5" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Facebook</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\t\t\t\t" . '<li class="mr-1">' . "\r\n\t\t\t\t\t" . '<a href="#tab11" class="dark:text-gray-300 dark:border-gray-600 dark:bg-gray-800 inline-block py-2 px-4 font-semibold border rounded-t text-gray-700">Google</a>' . "\r\n\t\t\t\t" . '</li>' . "\r\n\r\n\t\t\t" . '</ul>' . "\r\n\t\t" . '</div>' . "\r\n\r\n\r\n\r\n\t\t" . '<form action="" id="manage-system">' . "\r\n\r\n\t\t\t" . '<div class="mt-4">' . "\t\r\n\r\n\r\n\t\t\t\t" . '<div id="tab1" class="tabcontent text-gray-700 dark:text-gray-400">' . "\r\n\r\n\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Titulo do site</span>' . "\r\n\t\t\t\t\t\t" . '<input name="name" id="name" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t" . 'placeholder="Titulo" value="';
echo htmlspecialchars((string) $_settings->info('name'), ENT_QUOTES, 'UTF-8');
echo '" required maxlength="120"/>' . "\r\n\t\t\t\t\t" . '</label>' . "\r\n\r\n\r\n\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">E-mail</span>' . "\r\n\t\t\t\t\t\t" . '<input name="email" id="email" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t" . 'placeholder="admin@admin.com" value="';
echo $_settings->info('email');
echo '"/>' . "\r\n\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Telefone</span>' . "\r\n\t\t\t\t\t\t" . '<input name="phone" id="phone" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t" . 'placeholder="(00) 00000-0000" value="';
echo $_settings->info('phone');
echo '"/>' . "\r\n\t\t\t\t\t" . '</label>';
echo '<input type="hidden" name="theme" value="1">';
echo '<section class="theme-editor" id="theme-editor">';
echo '<h3 class="theme-editor__heading">Cores do tema do site</h3>';
echo '<p class="theme-editor__help">Clique em uma cor para editá-la. Use o seletor visual, digite um código HEX exato ou faça o ajuste fino pelos controles de matiz, saturação e luminosidade.</p>';
echo '<div class="theme-preset-bar" aria-label="Cores rápidas">';
echo '<span class="theme-preset-bar__label">Cores rápidas</span>';
$themePresets = [
	'#b42c63' => 'Rosa',
	'#7c3aed' => 'Roxo',
	'#2563eb' => 'Azul',
	'#0891b2' => 'Ciano',
	'#059669' => 'Verde',
	'#ea580c' => 'Laranja',
	'#dc2626' => 'Vermelho',
	'#334155' => 'Grafite',
];
foreach ($themePresets as $presetColor => $presetLabel) {
	echo '<button type="button" class="theme-preset" data-color="' . $presetColor . '" style="--preset-color:' . $presetColor . '" aria-label="Aplicar ' . htmlspecialchars($presetLabel, ENT_QUOTES, 'UTF-8') . ' à cor selecionada" title="' . htmlspecialchars($presetLabel, ENT_QUOTES, 'UTF-8') . '"></button>';
}
echo '</div>';
echo '<div class="theme-color-grid">';
$themeFieldLabels = [
	'primary' => 'Cor principal',
	'secondary' => 'Cor secundária',
	'header' => 'Cabeçalho',
	'background' => 'Fundo do site',
	'surface' => 'Cartões e modais',
	'text' => 'Textos',
];
foreach ($themeFieldLabels as $themeField => $themeLabel) {
	$themeInputId = 'theme_' . $themeField . '_color';
	$themeColorValue = htmlspecialchars($themeColors[$themeField], ENT_QUOTES, 'UTF-8');
	echo '<div class="theme-color-field" data-color-field data-color-input="' . $themeInputId . '" data-label="' . htmlspecialchars($themeLabel, ENT_QUOTES, 'UTF-8') . '">';
	echo '<div class="theme-color-field__top"><label class="theme-color-field__label" for="' . $themeInputId . '">' . htmlspecialchars($themeLabel, ENT_QUOTES, 'UTF-8') . '</label><span class="theme-color-swatch" aria-hidden="true" style="background:' . $themeColorValue . '"></span></div>';
	echo '<div class="theme-color-control">';
	echo '<input type="color" id="' . $themeInputId . '" name="' . $themeInputId . '" value="' . $themeColorValue . '" aria-label="Abrir seletor para ' . htmlspecialchars($themeLabel, ENT_QUOTES, 'UTF-8') . '">';
	echo '<label class="theme-hex-control" for="' . $themeInputId . '_hex"><span class="theme-hex-prefix">#</span><input type="text" id="' . $themeInputId . '_hex" class="theme-hex-input" data-for="' . $themeInputId . '" value="' . strtoupper(ltrim($themeColors[$themeField], '#')) . '" maxlength="7" spellcheck="false" inputmode="text" aria-label="Código hexadecimal de ' . htmlspecialchars($themeLabel, ENT_QUOTES, 'UTF-8') . '"></label>';
	echo '</div></div>';
}
echo '</div>';
echo '<div class="theme-fine-tuning" aria-label="Ajuste fino da cor selecionada">';
echo '<div class="theme-fine-tuning__head"><span class="theme-fine-tuning__title">Ajuste fino</span><span class="theme-fine-tuning__active" id="theme-active-color-name">Cor principal</span></div>';
echo '<div class="theme-slider-grid">';
echo '<div class="theme-slider theme-slider--hue"><label for="theme_hue">Matiz</label><output id="theme_hue_value">0°</output><input class="theme-hsl-range" id="theme_hue" type="range" min="0" max="359" step="1" value="0"></div>';
echo '<div class="theme-slider"><label for="theme_saturation">Saturação</label><output id="theme_saturation_value">0%</output><input class="theme-hsl-range" id="theme_saturation" type="range" min="0" max="100" step="1" value="0"></div>';
echo '<div class="theme-slider"><label for="theme_lightness">Luminosidade</label><output id="theme_lightness_value">0%</output><input class="theme-hsl-range" id="theme_lightness" type="range" min="0" max="100" step="1" value="0"></div>';
echo '</div></div>';
echo '<div class="theme-preview-title"><strong>Prévia ao vivo</strong><span>A logo e as cores abaixo mudam antes de você salvar.</span></div>';
echo '<div class="theme-preview-grid" aria-label="Prévia da página principal e da logo">';
echo '<div class="theme-site-preview">';
echo '<div class="theme-browser-bar"><div class="theme-browser-tab"><img class="js-brand-preview" src="' . htmlspecialchars($brandImageUrl, ENT_QUOTES, 'UTF-8') . '" alt=""><span>' . htmlspecialchars((string) $_settings->info('name'), ENT_QUOTES, 'UTF-8') . '</span></div></div>';
echo '<div class="theme-browser-accent"></div>';
echo '<div class="theme-site-header"><img class="js-brand-preview" src="' . htmlspecialchars($brandImageUrl, ENT_QUOTES, 'UTF-8') . '" alt="Logo aplicada no cabeçalho"><span class="theme-site-menu">☰</span></div>';
echo '<div class="theme-site-body"><span class="theme-site-kicker">Sua sorte começa aqui</span><div class="theme-site-hero">Campanhas transparentes,<br>participação simples.</div><div class="theme-site-card"><span class="theme-site-card__image"></span><span><strong>Campanha em destaque</strong><small>Escolha suas cotas e participe.</small></span><span class="theme-preview__button">Participar</span></div></div>';
echo '</div>';
echo '<div class="theme-logo-preview"><div class="theme-logo-preview__label">Logo nos fundos reais</div><div class="theme-logo-stage theme-logo-stage--header"><img class="js-brand-preview" src="' . htmlspecialchars($brandImageUrl, ENT_QUOTES, 'UTF-8') . '" alt="Logo sobre a cor do cabeçalho"></div><div class="theme-logo-stage theme-logo-stage--surface"><img class="js-brand-preview" src="' . htmlspecialchars($brandImageUrl, ENT_QUOTES, 'UTF-8') . '" alt="Logo sobre a cor dos cartões"></div></div>';
echo '</div>';
echo '<div class="theme-editor__actions"><button type="button" class="theme-reset" id="reset-theme-colors" data-defaults="' . htmlspecialchars(json_encode(jnsalles_theme_defaults()), ENT_QUOTES, 'UTF-8') . '">Restaurar rosa padrão</button></div>';
echo '</section>';
echo '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Logo do site e favicon:</span>' . "\r\n\t\t\t\t\t\t" . '<p class="mb-2" style="font-size:13px;color: orange;font-style:italic;">A mesma imagem ser&aacute; aplicada no cabe&ccedil;alho e no &iacute;cone da aba do navegador. Use PNG ou JPG de at&eacute; 4 MB. A transpar&ecirc;ncia de arquivos PNG ser&aacute; preservada.</p>' . "\r\n\t\t\t\t\t\t" . '<input id="customFile1" name="img" onchange="displayImg(this,$(this))" type="file" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" accept="image/png, image/jpeg">' . "\r\n\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t" . '<img src="';
echo validate_image($_settings->info('logo'));
echo '" alt="Pr&eacute;via da logo do site" id="cimg" class="img-fluid img-thumbnail">' . "\r\n\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Bloquear múltiplos pedidos?</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p class="mb-2" style="font-size:13px;color: orange;font-style:italic;">Ao habilitar esta opção, o cliente só poderá realizar um novo pedido após efetuar o pagamento do pedido anterior ou o mesmo expirar.</p>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_multiple_order" id="enable_multiple_order" ';
echo (isset($enable_multiple_order) && $enable_multiple_order == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_multiple_order">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t\r\n\r\n\r\n\r\n\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '<div id="tab2" class="tabcontent text-gray-700 dark:text-gray-400 hidden">' . "\r\n\t\t\t\t\t\t" . '<p>Os dados habilitados abaixo serão obrigatórios no formulário de cadastro do site.</p>' . "\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar dupla verificação de telefone?</span>' . "\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Quando essa opção estiver ativa, o usuário deverá informar o telefone duas vezes na criação de conta</p>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_two_phone" id="enable_two_phone" ';
echo (isset($enable_two_phone) && $enable_two_phone == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_two_phone">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar senha?</span>' . "\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Quando essa opção estiver desabilitada, não será necessário inserir uma senha durante o processo de cadastro e também para fazer o login no sistema.</p>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_password" id="enable_password" ';
echo (isset($enable_password) && $enable_password == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_password">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\t\t\t\t\t\t\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar CPF?</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Ao habilitar esta opção, o sistema irá exigir que o usuário forneça seu CPF para efetuar um cadastro/compra e também para buscar seus números.</p>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_cpf" id="enable_cpf" ';
echo (isset($enable_cpf) && $enable_cpf == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_cpf">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar E-mail?</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Ao habilitar esta opção, o sistema irá exigir que o usuário forneça um email válido para efetuar um cadastro/compra.</p>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_email" id="enable_email" ';
echo (isset($enable_email) && $enable_email == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_email">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar data de nascimento?</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Ao habilitar esta opção, o sistema irá exigir que o usuário forneça sua data de nascimento para efetuar um cadastro/compra.</p>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_birth" id="enable_birth" ';
echo (isset($enable_birth) && $enable_birth == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_birth">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar usuários apenas maiores de 18 anos?</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Ao habilitar esta opção, o sistema irá aceitar apenas cadastros de clientes maiores de 18 anos.</p>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_legal_age" id="enable_legal_age" ';
echo (isset($enable_legal_age) && $enable_legal_age == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_legal_age">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar instagram?</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Ao habilitar esta opção, o sistema irá exigir que o usuário forneça seu instagram para efetuar um cadastro/compra.</p>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_instagram" id="enable_instagram" ';
echo (isset($enable_instagram) && $enable_instagram == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_instagram">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar Endereço?</span>' . "\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Ao habilitar esta opção, o sistema irá exibir opções de endereço na página de atualização de cadastro do usuário</p>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_address" id="enable_address" ';
echo (isset($enable_address) && $enable_address == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_address">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '<div id="tab3" class="tabcontent text-gray-700 dark:text-gray-400 hidden">' . "\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar botões de compartilhamento?</span>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_share" id="enable_share" ';
echo (isset($enable_share) && $enable_share == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_share">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar botão para acessar os grupos?</span>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_groups" id="enable_groups" ';
echo (isset($enable_groups) && $enable_groups == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_groups">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<div class="groups_social">' . "\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Link do grupo Telegram:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="telegram_group_url" id="telegram_group_url"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="https://telegram.org" value="';
echo (isset($telegram_group_url) ? $telegram_group_url : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\t\r\n\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Link do grupo WhatsApp:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="whatsapp_group_url" id="whatsapp_group_url"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="https://whatsapp.com/" value="';
echo (isset($whatsapp_group_url) ? $whatsapp_group_url : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\t\r\n\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\r\n\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t" . '<div id="tab4" class="tabcontent text-gray-700 dark:text-gray-400

hidden">' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar rodapé?</span>' . "\t\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_footer" id="enable_footer" ';
echo (isset($enable_footer) && $enable_footer == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_footer">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<div class="footer-text">' . "\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Texto do rodapé:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="text_footer" id="text_footer"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="ex: Todos os direitos reservados." value="';
echo (isset($text_footer) ? $text_footer : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<h2 class="social-rodape">Redes Sociais - Rodapé</h2>' . "\r\n\t\t\t\t\t\t" . '<p>Preencha os campos abaixo para exibir as redes sociais no rodapé ou deixe em branco para não exibir.</p>' . "\r\n\t\t\t\t\t\t\t" . '<div class="groups">' . "\r\n\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">WhatsApp:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="whatsapp_footer" id="whatsapp_footer"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="https://api.whatsapp.com/send?l=pt_br&phone=00000" value="';
echo (isset($whatsapp_footer) ? $whatsapp_footer : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\t\r\n\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Instagram:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="instagram_footer" id="instagram_footer"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="https://instagram.com/" value="';
echo (isset($instagram_footer) ? $instagram_footer : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\t\r\n\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Facebook:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="facebook_footer" id="facebook_footer"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="https://facebook.com/" value="';
echo (isset($facebook_footer) ? $facebook_footer : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\t\r\n\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Twitter:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="twitter_footer" id="twitter_footer"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="https://twitter.com/" value="';
echo (isset($twitter_footer) ? $twitter_footer : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\t\r\n\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Youtube:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="youtube_footer" id="youtube_footer"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="https://youtube.com/" value="';
echo (isset($youtube_footer) ? $youtube_footer : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\t\r\n\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\r\n\r\n\t\t\t\t\t" . '</div>' . "\t\t\t\r\n\r\n\t\t\t\t\t" . '<div id="tab5" class="tabcontent text-gray-700 dark:text-gray-400 hidden">' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar API de conversão?</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p>Área destinada ao gestor de tráfego para implantação da API de conversão do Facebook ADS.</p>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_pixel" id="enable_pixel" ';
echo (isset($enable_pixel) && $enable_pixel == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_pixel">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t\t" . '<div class="pixel-facebook">' . "\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Access Token (Facebook) *:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="facebook_access_token" id="facebook_access_token"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="Informe o ACCESS TOKEN do facebook" value="';
echo (isset($facebook_access_token) ? $facebook_access_token : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Pixel ID (Facebook) *:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="facebook_pixel_id" id="facebook_pixel_id"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="Informe o PIXEL ID do facebook" value="';
echo (isset($facebook_pixel_id) ? $facebook_pixel_id : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '<div id="tab11" class="tabcontent text-gray-700 dark:text-gray-400 hidden">' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar Google Analytics?</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p>Área destinada ao gestor de tráfego para implantação do Google Analytics (GA4)</p>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_ga4" id="enable_ga4" ';
echo (isset($enable_ga4) && $enable_ga4 == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_ga4">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<div class="pixel-google">' . "\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">ID (GA4)*:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="google_ga4_id" id="google_ga4_id"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="Informe o ID do analytics" value="';
echo (isset($google_ga4_id) ? $google_ga4_id : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t\t" . '<br><hr>' . "\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Habilitar Google Tag Manager?</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p>Área destinada ao gestor de tráfego para implantação do Google Tag Manager (GTM)</p>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_gtm" id="enable_gtm" ';
echo (isset($enable_gtm) && $enable_gtm == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_gtm">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<div class="pixel-google-gtm">' . "\r\n\t\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">ID (GTM)*:</span>' . "\r\n\t\t\t\t\t\t\t\t" . '<input name="google_gtm_id" id="google_gtm_id"' . "\r\n\t\t\t\t\t\t\t\t" . 'class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t\t" . 'placeholder="Informe o ID do GTM" value="';
echo (isset($google_gtm_id) ? $google_gtm_id : '');
echo '" />' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '<div id="tab6" class="tabcontent text-gray-700 dark:text-gray-400 hidden">' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Ocultar cotas</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p>Ao habilitar essa opção as cotas das <strong>campanhas automáticas</strong> só serão geradas e exibidas quando o pagamento for aprovado.</p>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_hide_numbers" id="enable_hide_numbers" ';
echo (isset($enable_hide_numbers) && $enable_hide_numbers == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_hide_numbers">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '<div id="tab7" class="tabcontent text-gray-700 dark:text-gray-400 hidden">' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Ativar integração</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p>Ao habilitar essa opção o sistema irá enviar automaticamente uma mensagem para o WhatsApp do cliente ao efetuar um novo pedido.</p>' . "\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Essa integração é feita através da <a href="https://whatsjet.cloud/" target="_blank">Whats-Jet-API</a>. É necessário um plano ativo na mesma para poder utilizar. Você pode testar por 3 dias grátis.</p>' . "\r\n\t\t\t\t\t\t\t" . '<br>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<div class="can-toggle">' . "\r\n\t\t\t\t\t\t\t" . '<input type="checkbox" name="enable_dwapi" id="enable_dwapi" ';
echo (isset($enable_dwapi) && $enable_dwapi == 1 ? 'checked' : '');
echo '>' . "\r\n\t\t\t\t\t\t\t" . '<label for="enable_dwapi">' . "\r\n\t\t\t\t\t\t\t\t" . '<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div>' . "\r\n\t\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[CAMPANHA]</b> - Irá exibir o nome da campanha</p>' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[CLIENTE]</b> - Irá exibir o nome do cliente</p>' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[COTAS]</b> - Irá exibir as cotas do pedido</p>' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[TOTAL]</b> - Irá exibir o valor total do pedido</p>' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[PIX]</b> - Irá exibir o código copia e cola do PIX</p>' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[N]</b> - Irá inserir uma quebra de linha</p>
<p><b>[LINK]</b> - Irá exibir o link da compra do cliente</p>' . "\r\n\t\t\t\t\t\t\t" . '<br>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Token</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="token_dwapi" id="token_dwapi" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('token_dwapi');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Número que irá fazer os envios</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="numero_dwapi" id="numero_dwapi" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('numero_dwapi');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Mensagem que será enviada para o cliente quando um pedido for feito</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="mensagem_novo_pedido_dwapi" id="mensagem_novo_pedido_dwapi" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('mensagem_novo_pedido_dwapi');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Mensagem que será enviada para o cliente quando um pedido for pago</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="mensagem_pedido_pago_dwapi" id="mensagem_pedido_pago_dwapi" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('mensagem_pedido_pago_dwapi');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '<div id="tab8" class="tabcontent text-gray-700 dark:text-gray-400 hidden">' . "\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Servidor de email customizado</span>' . "\t\r\n\t\t\t\t\t\t\t" . '<p>Se deseja utilizar um servidor de email personalizado, preencha os dados do mesmo abaixo, caso contrário, deixe os campos em branco.</p>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Servidor SMTP</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="smtp_host" id="smtp_host" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="smtp.yourhost.com" value="';
echo $_settings->info('smtp_host');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Porta</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="smtp_port" id="smtp_port" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="465" value="';
echo $_settings->info('smtp_port');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Usuário</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="smtp_user" id="smtp_user" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="Usuário" value="';
echo $_settings->info('smtp_user');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Senha</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="smtp_pass" id="smtp_pass" type="password" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="*****" value="';
echo $_settings->info('smtp_pass');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<hr class="mt-4 mb-4">' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<p class="mb-2">Shortcodes disponíveis</p>' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[CAMPANHA]</b> - Irá exibir o nome da campanha</p>' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[CLIENTE]</b> - Irá exibir o nome do cliente</p>' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[COTAS]</b> - Irá exibir as cotas do pedido</p>' . "\r\n\t\t\t\t\t\t\t" . '<p><b>[TOTAL]</b> - Irá exibir o valor total do pedido</p>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Corpo do email que será enviado para o cliente ao efetuar uma compra</span>' . "\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Você pode utilizar tags html na descrição para uma melhor formatação</p>' . "\r\n\t\t\t\t\t\t\t" . '<textarea name="email_order" id="email_order" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" rows="6">';
echo $_settings->info('email_order');
echo '</textarea>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block mt-4 text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Corpo do email que será enviado para o cliente ao efetuar um pagamento</span>' . "\r\n\t\t\t\t\t\t\t" . '<p style="font-size:13px;color: orange;font-style:italic;">Você pode utilizar tags html na descrição para uma melhor formatação</p>' . "\r\n\t\t\t\t\t\t\t" . '<textarea name="email_purchase" id="email_purchase" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" rows="6">';
echo $_settings->info('email_purchase');
echo '</textarea>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t\r\n\t\t\t\t\t" . '<div id="tab9" class="tabcontent text-gray-700 dark:text-gray-400 hidden">' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm mb-2">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Pergunta 1</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="question1" id="question1" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('question1');
echo '"/>' . "\r\n\t\t\t\t\t\t\t" . '<input name="answer1" id="answer1" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('answer1');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm mb-2">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Pergunta 2</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="question2" id="question2" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('question2');
echo '"/>' . "\r\n\t\t\t\t\t\t\t" . '<input name="answer2" id="answer2" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('answer2');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm mb-2">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Pergunta 3</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="question3" id="question3" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('question3');
echo '"/>' . "\r\n\t\t\t\t\t\t\t" . '<input name="answer3" id="answer3" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('answer3');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm mb-2">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Pergunta 4</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="question4" id="question4" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('question4');
echo '"/>' . "\r\n\t\t\t\t\t\t\t" . '<input name="answer4" id="answer4" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('answer4');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t\t\t" . '<div id="tab10" class="tabcontent text-gray-700 dark:text-gray-400 hidden">' . "\r\n\r\n\t\t\t\t\t\t" . '<label class="block text-sm mb-2">' . "\r\n\t\t\t\t\t\t\t" . '<span class="text-gray-700 dark:text-gray-400">Termos de uso</span>' . "\r\n\t\t\t\t\t\t\t" . '<input name="terms" id="terms" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"' . "\r\n\t\t\t\t\t\t\t" . 'placeholder="" value="';
echo $_settings->info('terms');
echo '"/>' . "\r\n\t\t\t\t\t\t" . '</label>' . "\r\n\r\n\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t\r\n\t\t\t\t" . '</div>' . "\r\n\r\n\r\n\r\n\r\n\t\t\t\t" . '<div style="margin-top:20px;"> ' . "\r\n\t\t\t\t\t" . '<button form="manage-system" class="px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">' . "\r\n\t\t\t\t\t\t" . 'Salvar' . "\r\n\t\t\t\t\t" . '</button>' . "\r\n\t\t\t\t" . '</div>' . "\r\n\r\n\t\t\t" . '</form>' . "\r\n\r\n\t\t" . '</div>' . "\r\n\r\n\r\n\t" . '</div>' . "\r\n" . '</main>' . "\r\n" . '<span id="openModal" href="javascript:void(0)" @click="openModal"></span>' . "\r\n" . '<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center" style="display: none;">' . "\r\n\t" . '<!-- Modal -->' . "\r\n\t" . '<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform translate-y-1/2" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0  transform translate-y-1/2" @click.away="closeModal" @keydown.escape="closeModal" class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl" role="dialog" id="modal" style="display: none;">' . "\r\n\t\t" . '<!-- Remove header if you don\'t want a close icon. Use modal body to place modal tile. -->' . "\r\n\t\t" . '<header class="flex justify-end">' . "\r\n\t\t\t" . '<button class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover: hover:text-gray-700" aria-label="close" @click="closeModal">' . "\r\n\t\t\t\t" . '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" role="img" aria-hidden="true">' . "\r\n\t\t\t\t\t" . '<path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path>' . "\r\n\t\t\t\t" . '</svg>' . "\r\n\t\t\t" . '</button>' . "\r\n\t\t" . '</header>' . "\r\n\t\t" . '<div class="mt-4 mb-6">' . "\r\n\t\t\t" . '<p class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">' . "\r\n\t\t\t\t" . 'Parabéns!' . "\r\n\t\t\t" . '</p>' . "\r\n\t\t\t" . '<p class="text-sm text-gray-700 dark:text-gray-400">' . "\r\n\t\t\t\t" . 'Alterações salvas com sucesso!' . "\r\n\t\t\t" . '</p>' . "\r\n\t\t" . '</div>' . "\r\n\r\n\t" . '</div>' . "\r\n" . '</div>' . "\r\n" . '<script>' . "\r\n\t" . 'if($(\'#enable_groups\').is(":checked")){' . "\r\n\t\t" . '$(\'.groups_social\').show();' . "\r\n\t" . '}else{' . "\r\n\t\t" . '$(\'.groups_social\').hide();' . "\t\r\n\t" . '}' . "\r\n\t" . '$(\'#enable_groups\').change(function() {' . "\r\n\t\t" . 'if($(\'#enable_groups\').is(":checked")){' . "\r\n\t\t\t" . '$(\'.groups_social\').show();' . "\r\n\t\t" . '}else{' . "\r\n\t\t\t" . '$(\'.groups_social\').hide();' . "\t\r\n\t\t" . '}' . "\r\n\t" . '}); ' . "\r\n\r\n\t" . 'if($(\'#enable_footer\').is(":checked")){' . "\r\n\t\t" . '$(\'.footer-text\').show();' . "\r\n\t" . '}else{' . "\r\n\t\t" . '$(\'.footer-text\').hide();' . "\t\r\n\t" . '}' . "\r\n\t" . '$(\'#enable_footer\').change(function() {' . "\r\n\t\t" . 'if($(\'#enable_footer\').is(":checked")){' . "\r\n\t\t\t" . '$(\'.footer-text\').show();' . "\r\n\t\t" . '}else{' . "\r\n\t\t\t" . '$(\'.footer-text\').hide();' . "\t\r\n\t\t" . '}' . "\r\n\t" . '}); ' . "\r\n\t" . '$(\'#enable_pixel\').change(function() {' . "\r\n\t\t" . 'if($(\'#enable_pixel\').is(":checked")){' . "\r\n\t\t\t" . '$(\'.pixel-facebook\').show();' . "\r\n\t\t" . '}else{' . "\r\n\t\t\t" . '$(\'.pixel-facebook\').hide();' . "\t\r\n\t\t" . '}' . "\r\n\t" . '}); ' . "\r\n\t" . '$(\'#enable_ga4\').change(function() {' . "\r\n\t\t" . 'if($(\'#enable_ga4\').is(":checked")){' . "\r\n\t\t\t" . '$(\'.pixel-google\').show();' . "\r\n\t\t" . '}else{' . "\r\n\t\t\t" . '$(\'.pixel-google\').hide();' . "\t\r\n\t\t" . '}' . "\r\n\t" . '});' . "\r\n\t" . '$(\'#enable_gtm\').change(function() {' . "\r\n\t\t" . 'if($(\'#enable_gtm\').is(":checked")){' . "\r\n\t\t\t" . '$(\'.pixel-google-gtm\').show();' . "\r\n\t\t" . '}else{' . "\r\n\t\t\t" . '$(\'.pixel-google-gtm\').hide();' . "\t\r\n\t\t" . '}' . "\r\n\t" . '}); ' . "\r\n\t" . 'function displayImg(input,_this) {' . "\r\n\t\t" . 'if (input.files && input.files[0]) {' . "\r\n\t\t\t" . 'var reader = new FileReader();' . "\r\n\t\t\t" . 'reader.onload = function (e) {' . "\r\n\t\t\t\t" . '$(\'#cimg\').attr(\'src\', e.target.result);' . "\r\n\t\t\t" . '}' . "\r\n\r\n\t\t\t" . 'reader.readAsDataURL(input.files[0]);' . "\r\n\t\t" . '}else{' . "\r\n\t\t\t" . '$(\'#cimg\').attr(\'src\', "';
echo validate_image($_settings->info('logo'));
echo '");' . "\r\n\t\t" . '}' . "\r\n\t" . '}' . "\r\n\t" . 'function displayFavicon(input,_this) {' . "\r\n\t\t" . 'if (input.files && input.files[0]) {' . "\r\n\t\t\t" . 'var reader = new FileReader();' . "\r\n\t\t\t" . 'reader.onload = function (e) {' . "\r\n\t\t\t\t" . '$(\'#favicon\').attr(\'src\', e.target.result);' . "\r\n\t\t\t" . '}' . "\r\n\r\n\t\t\t" . 'reader.readAsDataURL(input.files[0]);' . "\r\n\t\t" . '}else{' . "\r\n\t\t\t" . '$(\'#favicon\').attr(\'src\', "';
echo validate_image($_settings->info('favicon'));
echo '");' . "\r\n\t\t" . '}' . "\r\n\t" . '}' . "\r\n\r\n\t" . 'var pageToken = \'system_info\'; ' . "\r\n\t" . '$("#tabs a").click(function() {' . "\r\n\t\t" . 'var selectedTab = $(this).attr("href");' . "\r\n\t\t" . '$("#tabs a").removeClass("active-tab");' . "\r\n\t\t" . '$(this).addClass("active-tab");' . "\r\n\t\t" . '$(".tabcontent").hide();' . "\r\n\t\t" . '$(selectedTab).show();' . "\r\n\t\t" . 'localStorage.setItem(\'selectedTab_\' + pageToken, pageToken + \'_\' + selectedTab);' . "\r\n\t\t" . 'return false;' . "\r\n\t" . '});' . "\r\n\t" . '$(document).ready(function(){' . "\r\n\r\n\t\t" . 'var storedTab = localStorage.getItem(\'system_info\' + pageToken);' . "\r\n\t\t" . 'if (storedTab) {' . "\r\n\t\t\t" . 'var selectedTab = storedTab.substring(pageToken.length + 1);' . "\r\n\t\t\t" . '$("#tabs a").removeClass("active-tab");' . "\r\n\t\t\t" . '$(selectedTab).addClass("active-tab");' . "\r\n\t\t\t" . '$(".tabcontent").hide();' . "\r\n\t\t\t" . '$(selectedTab).show();' . "\r\n\t\t" . '}' . "\r\n\r\n\r\n\t\t" . '$(\'#manage-system\').submit(function(e){' . "\r\n\t\t\t" . 'e.preventDefault();' . "\r\n\t\t\t" . '$.ajax({' . "\r\n\t\t\t\t" . 'url:_base_url_+\'class/System.php?action=update_system\',' . "\r\n\t\t\t\t" . 'data: new FormData($(this)[0]),' . "\r\n\t\t\t\t" . 'cache: false,' . "\r\n\t\t\t\t" . 'contentType: false,' . "\r\n\t\t\t\t" . 'processData: false,' . "\r\n\t\t\t\t" . 'method: \'POST\',' . "\r\n\t\t\t\t" . 'type: \'POST\',' . "\r\n\t\t\t\t" . 'success:function(resp){' . "\r\n\t\t\t\t\t" . 'var returnedData = JSON.parse(resp);' . "\r\n\t\t\t\t\t" . 'if(returnedData.status == \'success\'){' . "\r\n\t\t\t\t\t\t" . 'alert(\'Configurações salvas com sucesso!\');' . "\r\n\t\t\t\t\t\t" . 'location.reload();' . "\r\n\t\t\t\t\t" . '}else{' . "\r\n\t\t\t\t\t\t" . 'alert(\'Ops\');' . "\r\n\t\t\t\t\t" . '}' . "\r\n\t\t\t\t" . '}' . "\r\n\t\t\t" . '})' . "\r\n\t\t" . '})' . "\r\n\r\n\t" . '});' . "\r\n\r\n" . '</script>';

?>
