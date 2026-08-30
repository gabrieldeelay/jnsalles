<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style2.css">
<div id="loadingSystem" style="display: none;"></div>


<?php

$string = isset($tipo_auto_cota) ? $tipo_auto_cota : '';
$numbers = explode(',', $string);
$cotas_reservadas = count($numbers);

if (substr($string, -1) == ',') {
    $cotas_reservadas--;
}

$safeQtyNumbers = max(1, (int) $qty_numbers);
$paid_and_pending = (int) $pending_numbers + (int) $paid_numbers;
$total_reservadas = (int) $paid_numbers;

if ($status_auto_cota == 0) {
    $cotas_reservadas = 0;
}

$available = max(0, (int) $qty_numbers - $paid_and_pending - $cotas_reservadas);
$percent = (($paid_and_pending + $cotas_reservadas) * 100) / $safeQtyNumbers;
try {
    $rankingTimer = ranking_timer_configuration((int) $id);
} catch (Throwable $rankingError) {
    error_log('[campaign] ranking timer unavailable for product=' . (int) $id);
    $rankingTimer = [
        'enabled' => false,
        'configured' => false,
        'start' => '',
        'end' => '',
        'state' => 'disabled',
        'paused_at' => '',
        'window_start' => date('Y-m-d 00:00:00'),
        'window_end' => date('Y-m-d 23:59:59'),
        'uses_reset' => false,
    ];
}
$rankingTimerVisible = $rankingTimer['enabled'] && $rankingTimer['configured'];
$rankingTimerStart = $rankingTimer['start'];
$rankingTimerEnd = $rankingTimer['end'];
$rankingTimerState = $rankingTimer['state'];
$rankingTimerPausedAt = $rankingTimer['paused_at'];
$rankingWindowStart = $rankingTimerVisible ? $rankingTimer['window_start'] : date('Y-m-d 00:00:00');
$rankingWindowEnd = $rankingTimerVisible ? $rankingTimer['window_end'] : date('Y-m-d 23:59:59');
$rankingWindowUsesReset = $rankingTimerVisible && $rankingTimer['uses_reset'];

$max_discount = 0;
if ($available < $min_purchase) {
    $min_purchase = $available;
}
$enable_cpf = $_settings->info('enable_cpf');

if ($enable_cpf == 1) {
    $search_type = 'search_orders_by_cpf';
} else {
    $search_type = 'search_orders_by_phone';
}

?>

<style>

    .hr {
        border: 0;
        height: 1px;
        background-image: linear-gradient(to right, rgba(0, 0, 0, 0), #343a40, rgba(0, 0, 0, 0));
        margin-block: 4px;
    }

    .lessons__category {
        margin-bottom: 16px;

        background: green;

        display: inline-block;
        padding: 8px 8px 6px;
        border-radius: 4px;
        font-size: 1.2rem;
        text-align: center;
        line-height: 1;
        font-weight: 700;
        text-transform: uppercase;
        color: #FCFCFD;
    }

    .maior,
    .menor {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column
    }

    .hidden {
        display: none !important;
    }


    .skeleton {
        background-color: #343a40;
        border-radius: 0.2rem;
        font-weight: 600;
        animation: blink 1s infinite;
        cursor: pointer;
        width: 98%;
        height: 12px;
        margin: 6px;


    }

    #overlay,
    .carousel-item {
        width: 100%;
        display: none
    }


    .visually-hidden-focusable:not(:focus):not(:focus-within) {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important
    }

    .d-block {
        display: block !important
    }

    .mt-3 {
        margin-top: 1rem !important
    }

    .sorteio_sorteioShare__247_t {
        position: fixed;
        bottom: 120px;
        right: 12px;
        display: -moz-box;
        display: flex;
        -moz-box-orient: vertical;
        -moz-box-direction: normal;
        flex-direction: column
    }

    .top-compradores {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 20px;
        margin-top: 20px
    }

    .comprador {
        margin-right: 3px;
        margin-bottom: 8px;
        border: 1px solid #198754;
        padding: 22px;
        text-align: center;
        margin-left: 10px;
        background: #fff;
        border-radius: 6px;
        min-width: 160px
    }

    .ranking {
        margin-bottom: 5px;
        font-weight: 700;
        font-size: 18px
    }

    .customer-details {
        text-transform: uppercase;
        font-weight: 700;
        font-size: 14px
    }

    #overlay {
        position: fixed;
        top: 0;
        height: 100%;
        background: rgba(0, 0, 0, .8);
        z-index: 99999999
    }

    .cv-spinner {
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #ddd;
        border-top: 4px solid <?= $color ?>;
        border-radius: 50%;
        animation: .8s linear infinite sp-anime
    }

    @keyframes sp-anime {
        100% {
            transform: rotate(360deg)
        }
    }

    .is-hide {
        display: none
    }

    @media only screen and (max-width:600px) {
        .custom-image {
            height: 450px !important
        }
    }

    @media only screen and (min-width:768px) {
        .custom-image {
            height: 550px !important
        }
    }

    @keyframes pulsar {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 rgba(255, 255, 255, 0.4);
        }

        50% {
            transform: scale(1.03);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.7);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 0 rgba(255, 255, 255, 0.4);
        }
    }

    .pulsando {
        animation: pulsar 1.5s infinite;
    }

    .btn-primary {
        background-color: #7e3af2;
        border-color: #7e3af2;
    }

    .btn-primary:hover {
        background-color: #7e3af2;
        border-color: #7e3af2;
        opacity: 0.8;
    }

    .btn-primary:focus {
        background-color: #7e3af2;
        border-color: #7e3af2;
    }

    .bg-app-primary-latte {
        --tw-bg-opacity: 1;
        background-color: rgb(245 242 235 /1);
    }

    .rounded-3xl {
        border-radius: 1.5rem !important;
    }

    .overflow-hidden {
        overflow: hidden;
    }

    .w-full {
        width: 100%;
    }

    .mb-6 {
        margin-bottom: 1.5rem;
    }

    .relative {
        position: relative;
    }

    .w-\[400px\] {
        width: 400px;
    }

    .aspect-square {
        aspect-ratio: 1 / 1;
    }

    .z-0 {
        z-index: 0;
    }

    .-right-\[160px\] {
        right: -160px;
    }

    .-top-\[40px\] {
        top: -40px;
    }

    .absolute {
        position: absolute;
    }

    .w-\[150px\] {
        width: 150px;
    }

    .bottom-0 {
        bottom: 0;
    }

    .right-0 {
        right: 0;
    }

    .py-6 {
        padding-top: 1.5rem;
        padding-bottom: 1.5rem;
    }

    .px-6 {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    .flex-col {
        flex-direction: column;
    }

    .flex {
        display: flex;
    }

    .z-10 {
        z-index: 10;
    }

    .top-0 {
        top: 0;
    }

    .text-app-primary-blue {
        --tw-text-opacity: 1;
        color: rgb(40 128 254 / var(--tw-text-opacity));
    }

    .font-bold {
        font-weight: 700;
    }

    .wd-1 {
        width: 35px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-2 {
        width: 36px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-3 {
        width: 42px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-4 {
        width: 48px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-5 {
        width: 60px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-6 {
        width: 66px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-7 {
        width: 72px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-8 {
        width: 78px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-9 {
        width: 84px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .wd-10 {
        width: 90px !important;
        letter-spacing: 0.2px;
        text-align: center;
        white-space: nowrap;

    }

    .text-base {
        font-size: 1rem;
        line-height: 1.5rem;
    }

    .text-app-neutral-dark-1 {
        --tw-text-opacity: 1;
        color: rgb(3 29 39 / var(--tw-text-opacity));
    }

    .font-bold {
        font-weight: 700;
    }

    .text-base {
        font-size: 1rem;
        line-height: 1.5rem;
    }

    .mb-3 {
        margin-bottom: .75rem;
    }

    .px-3 {
        padding-left: .75rem;
        padding-right: .75rem;
    }

    .bg-app-neutral-dark-1 {
        --tw-bg-opacity: 1;
        background-color: rgb(3 29 39 / var(--tw-bg-opacity));
    }

    .rounded-2xl {
        border-radius: 1rem;
    }

    .justify-around {
        justify-content: space-around;
    }

    .items-center {
        align-items: center;
    }

    .w-fit {
        width: -moz-fit-content;
        width: fit-content;
    }

    .h-4 {
        height: 1rem;
    }

    .inline {
        display: inline;
    }

    .ml-1 {
        margin-left: .25rem;
    }

    #cotas-container {
        background-color: transparent !important;
        border: none !important;
        max-height: max-content;
    }
   .btn-custom {
    background-color: #198754;
    color: white;
    transition: background-color 0.3s ease;
  }

  .btn-custom:hover {
    background-color: #23935f; /* Azul mais claro */
    color: white; /* Mantém o texto branco */
  }
   .btn-hover-blue:hover {
  background: linear-gradient(90deg, #198754, #198754) !important;
}
.bg-azul-personalizado {
  background-color: #198754;
}
.animation-r {
        transition: 0.5s ease-in-out;
    }

      .animation-r {
        transition: 0.5s ease-in-out;
    }

    .accordion-collapse {
        transition: 0.7s ease-in-out !important;
    }

    .rotate {
        transform: rotate(180deg);
    }

    .ranking-spotlight {
        width: 100%;
        min-height: 44px;
        margin-bottom: 7px;
        padding: 6px 7px 6px 9px;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 10px;
        background: linear-gradient(135deg, #198754 0%, #117343 100%);
        box-shadow: 0 5px 14px rgba(17, 115, 67, .2);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        font-family: inherit;
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }

    .ranking-spotlight:hover,
    .ranking-spotlight:focus-visible {
        color: #fff;
        filter: brightness(1.04);
        transform: translateY(-1px);
        box-shadow: 0 7px 18px rgba(17, 115, 67, .25);
    }

    .ranking-spotlight:focus-visible {
        outline: 3px solid rgba(25, 135, 84, .25);
        outline-offset: 2px;
    }

    .ranking-spotlight.is-event {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        box-shadow: 0 5px 14px rgba(180, 83, 9, .24);
    }

    .ranking-spotlight.is-event:hover,
    .ranking-spotlight.is-event:focus-visible {
        box-shadow: 0 7px 18px rgba(180, 83, 9, .3);
    }

    .ranking-spotlight.is-event:focus-visible {
        outline-color: rgba(217, 119, 6, .28);
    }

    .ranking-spotlight__title {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.1;
        text-align: left;
        white-space: nowrap;
    }

    .ranking-spotlight__icon {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        border-radius: 8px;
        background: rgba(255, 255, 255, .16);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .ranking-spotlight__event {
        min-height: 30px;
        padding: 4px 9px;
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 999px;
        background: rgba(3, 55, 32, .24);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        color: rgba(255, 255, 255, .9);
        font-size: .69rem;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
    }

    .ranking-spotlight__event-dot {
        width: 6px;
        height: 6px;
        flex: 0 0 6px;
        border-radius: 50%;
        background: #d8ffe9;
        box-shadow: 0 0 0 3px rgba(216, 255, 233, .13);
    }

    .ranking-spotlight__event strong {
        color: #fff;
        font-size: .82rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        letter-spacing: .025em;
    }

    .ranking-spotlight__arrow {
        flex: 0 0 auto;
        opacity: .8;
    }

    .ranking-spotlight.is-warning{background:linear-gradient(135deg,#d97706,#b45309);box-shadow:0 7px 18px rgba(180,83,9,.28)}
    .ranking-spotlight.is-urgent{background:linear-gradient(135deg,#dc2626,#991b1b);box-shadow:0 7px 20px rgba(185,28,28,.34);animation:rankingUrgency 1.35s ease-in-out infinite}
    .ranking-spotlight.is-ended{background:linear-gradient(135deg,#64748b,#475569);box-shadow:none}
    .ranking-buyer-row{display:flex;align-items:center;gap:10px;padding:8px 9px;border-bottom:1px solid #eef0f3}.ranking-buyer-row:last-child{border-bottom:0}.ranking-buyer-medal{width:50px;flex:0 0 50px;color:#5b6470;text-align:center}.ranking-buyer-info{display:flex;min-width:0;flex:1;align-items:center;justify-content:space-between;gap:12px}.ranking-buyer-name{overflow:hidden;color:#24272c;font-size:1rem;font-weight:700;text-overflow:ellipsis;white-space:nowrap}.ranking-buyer-total{flex:0 0 auto;padding:5px 9px;border-radius:999px;background:#e9f7ef;color:#117343;font-size:.72rem;font-weight:800;white-space:nowrap}@media(max-width:420px){.ranking-buyer-row{gap:6px;padding-inline:3px}.ranking-buyer-medal{width:42px;flex-basis:42px}.ranking-buyer-name{font-size:.9rem}.ranking-buyer-total{padding:4px 7px;font-size:.65rem}}
    @keyframes rankingUrgency{50%{filter:brightness(1.13);transform:translateY(-1px)}}
    @media (prefers-reduced-motion:reduce){.ranking-spotlight.is-urgent{animation:none}}

    @media (max-width: 420px) {
        .ranking-spotlight {
            gap: 5px;
            padding-left: 7px;
        }

        .ranking-spotlight__title {
            gap: 5px;
            font-size: .71rem;
        }

        .ranking-spotlight__icon {
            width: 26px;
            height: 26px;
            flex-basis: 26px;
        }

        .ranking-spotlight__event {
            padding-inline: 8px;
        }
    }

</style>

<?php
echo '<div id="overlay">' .
    "\r\n" .
    ' <div class="cv-spinner">' .
    "\r\n" .
    '  <div class="card" style="border:none; padding:10px;background: transparent;color: #fff !important;font-weight: 800;">' .
    "\r\n" .
    '  <span class="spinner mb-2" style="align-self:center;"></span>' .
    "\r\n" .
    '   <div class="text-center font-xs">' .
    "\r\n" .
    '      Estamos gerando seu pedido, aguarde...' .
    "\r\n" .
    '   </div>' .
    "\r\n" .
    '</div>' .
    "\r\n" .
    '</div>' .
    "\r\n" .
    '</div>' .
    "\r\n" .
    '<style>' .
    "\r\n" .
    '.carousel,.carousel-inner,.carousel-item{position:relative}#overlay,.carousel-item{width:100%;display:none}@media (min-width:1200px){h3{font-size:1.75rem}}p{margin-top:0;margin-bottom:1rem}img{vertical-align:middle}button{border-radius:0;margin:0;font-family:inherit;font-size:inherit;line-height:inherit;text-transform:none}button:focus:not(:focus-visible){outline:0}[type=button],button{-webkit-appearance:button}.form-control-color:not(:disabled):not([readonly]),.form-control[type=file]:not(:disabled):not([readonly]),[type=button]:not(:disabled),[type=reset]:not(:disabled),[type=submit]:not(:disabled),button:not(:disabled){cursor:pointer}::-moz-focus-inner{padding:0;border-style:none}::-webkit-datetime-edit-day-field,::-webkit-datetime-edit-fields-wrapper,::-webkit-datetime-edit-hour-field,::-webkit-datetime-edit-minute,::-webkit-datetime-edit-month-field,::-webkit-datetime-edit-text,::-webkit-datetime-edit-year-field{padding:0}::-webkit-inner-spin-button{height:auto}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-color-swatch-wrapper{padding:0}::-webkit-file-upload-button{font:inherit;-webkit-appearance:button}::file-selector-button{font:inherit;-webkit-appearance:button}.container-fluid{--bs-gutter-x:1.5rem;--bs-gutter-y:0;width:100%;padding-right:calc(var(--bs-gutter-x) * .5);padding-left:calc(var(--bs-gutter-x) * .5);margin-right:auto;margin-left:auto}.form-control::file-selector-button{padding:.375rem .75rem;margin:-.375rem -.75rem;-webkit-margin-end:.75rem;margin-inline-end:.75rem;color:#212529;background-color:#e9ecef;pointer-events:none;border:0 solid;border-inline-end-width:1px;border-radius:0;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;border-color:inherit}.form-control:hover:not(:disabled):not([readonly])::-webkit-file-upload-button{background-color:#dde0e3}.form-control:hover:not(:disabled):not([readonly])::file-selector-button{background-color:#dde0e3}.form-control-sm::file-selector-button{padding:.25rem .5rem;margin:-.25rem -.5rem;-webkit-margin-end:.5rem;margin-inline-end:.5rem}.form-control-lg::file-selector-button{padding:.5rem 1rem;margin:-.5rem -1rem;-webkit-margin-end:1rem;margin-inline-end:1rem}.form-floating>.form-control-plaintext:not(:-moz-placeholder-shown),.form-floating>.form-control:not(:-moz-placeholder-shown){padding-top:1.625rem;padding-bottom:.625rem}.form-floating>.form-control:not(:-moz-placeholder-shown)~label{opacity:.65;transform:scale(.85) translateY(-.5rem) translateX(.15rem)}.input-group>.form-control:not(:focus).is-valid,.input-group>.form-floating:not(:focus-within).is-valid,.input-group>.form-select:not(:focus).is-valid,.was-validated .input-group>.form-control:not(:focus):valid,.was-validated .input-group>.form-floating:not(:focus-within):valid,.was-validated .input-group>.form-select:not(:focus):valid{z-index:3}.input-group>.form-control:not(:focus).is-invalid,.input-group>.form-floating:not(:focus-within).is-invalid,.input-group>.form-select:not(:focus).is-invalid,.was-validated .input-group>.form-control:not(:focus):invalid,.was-validated .input-group>.form-floating:not(:focus-within):invalid,.was-validated .input-group>.form-select:not(:focus):invalid{z-index:4}.btn:focus-visible{color:var(--bs-btn-hover-color);background-color:var(--bs-btn-hover-bg);border-color:var(--bs-btn-hover-border-color);outline:0;box-shadow:var(--bs-btn-focus-box-shadow)}.btn-check:focus-visible+.btn{border-color:var(--bs-btn-hover-border-color);outline:0;box-shadow:var(--bs-btn-focus-box-shadow)}.btn-check:checked+.btn:focus-visible,.btn.active:focus-visible,.btn.show:focus-visible,.btn:first-child:active:focus-visible,:not(.btn-check)+.btn:active:focus-visible{box-shadow:var(--bs-btn-focus-box-shadow)}.btn-link:focus-visible{color:var(--bs-btn-color)}.carousel-inner{width:100%;overflow:hidden}.carousel-inner::after{display:block;clear:both;content:""}.carousel-item{float:left;margin-right:-100%;-webkit-backface-visibility:hidden;backface-visibility:hidden;transition:transform .6s ease-in-out}.carousel-item.active{display:block}.carousel-control-next,.carousel-control-prev{position:absolute;top:0;bottom:0;z-index:1;display:flex;align-items:center;justify-content:center;width:15%;padding:0;color:#fff;text-align:center;background:0 0;border:0;opacity:.5;transition:opacity .15s}.carousel-control-next:focus,.carousel-control-next:hover,.carousel-control-prev:focus,.carousel-control-prev:hover{color:#fff;text-decoration:none;outline:0;opacity:.9}.carousel-control-prev{left:0}.carousel-control-next{right:0}.carousel-control-next-icon,.carousel-control-prev-icon{display:inline-block;width:2rem;height:2rem;background-repeat:no-repeat;background-position:50%;background-size:100% 100%}.carousel-control-prev-icon{background-image:url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\' fill=\'%23fff\'%3e%3cpath d=\'M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z\'/%3e%3c/svg%3e")}.carousel-control-next-icon{background-image:url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\' fill=\'%23fff\'%3e%3cpath d=\'M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z\'/%3e%3c/svg%3e")}.carousel-indicators{position:absolute;right:0;bottom:0;left:0;z-index:2;display:flex;justify-content:center;padding:0;margin-right:15%;margin-bottom:1rem;margin-left:15%;list-style:none}.carousel-indicators [data-bs-target]{box-sizing:content-box;flex:0 1 auto;width:30px;height:3px;padding:0;margin-right:3px;margin-left:3px;text-indent:-999px;cursor:pointer;background-color:#fff;background-clip:padding-box;border:0;border-top:10px solid transparent;border-bottom:10px solid transparent;opacity:.5;transition:opacity .6s}@media (prefers-reduced-motion:reduce){.form-control::file-selector-button{transition:none}.carousel-control-next,.carousel-control-prev,.carousel-indicators [data-bs-target],.carousel-item{transition:none}}.carousel-indicators .active{opacity:1}.visually-hidden-focusable:not(:focus):not(:focus-within){position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}.d-block{display:block!important}.mt-3{margin-top:1rem!important}.sorteio_sorteioShare__247_t{position:fixed;bottom:120px;right:12px;display:-moz-box;display:flex;-moz-box-orient:vertical;-moz-box-direction:normal;flex-direction:column}.top-compradores{display:flex;flex-wrap:wrap;margin-bottom:20px;margin-top:20px}.comprador{margin-right:3px;margin-bottom:8px;border:1px solid #198754;padding:22px;text-align:center;margin-left:10px;background:#fff;border-radius:6px;min-width:160px}.ranking{margin-bottom:5px;font-weight:700;font-size:18px}.customer-details{text-transform:uppercase;font-weight:700;font-size:14px}#overlay{position:fixed;top:0;height:100%;background:rgba(0,0,0,.8);z-index:99999999}.cv-spinner{height:100%;display:flex;justify-content:center;align-items:center}.spinner{width:40px;height:40px;border:4px solid #ddd;border-top:4px solid #2e93e6;border-radius:50%;animation:.8s linear infinite sp-anime}@keyframes sp-anime{100%{transform:rotate(360deg)}}.is-hide{display:none}@media only screen and (max-width:600px){.custom-image{height:350px!important}}@media only screen and (min-width:768px){.custom-image{height:450px!important}}' .
    "\r\n" .
    '</style>' .
    "\r\n" .
    '<div class="container app-main">' .
    "\r\n" .
    '   <div class="campanha-header SorteioTpl_sorteioTpl__2s2Wu SorteioTpl_destaque__3vnWR pointer custom-highlight-card">' .
    "\r\n" .
    '   <div style="bottom: 87px !important; " class="custom-badge-display">' .
    "\r\n" .
    '      ';

if ($status_display == 1) {
    echo '         <span class="badge bg-azul-personalizado blink font-xsss text-white">Adquira já!</span>' . "\r\n" . '      ';
}

echo '      ';

if ($status_display == 2) {
    echo '         <span class="badge bg-dark blink font-xsss mobile badge-status-1">Corre que está acabando!</span>' . "\r\n" . '      ';
}

echo '      ';

if ($status_display == 3) {
    echo '         <span class="badge bg-dark font-xsss mobile badge-status-3">Aguarde a campanha!</span>' . "\r\n" . '      ';
}

echo '      ';

if ($status_display == 4) {
    echo '         <span class="badge bg-dark font-xsss">Finalizada</span>' . "\r\n" . '      ';
}

echo '      ';

if ($status_display == 5) {
    echo '         <span class="badge bg-dark font-xsss">Em breve!</span>' . "\r\n" . '      ';
}

echo '      ';

if ($status_display == 6) {
    echo '         <span class="badge bg-dark font-xsss">Aguarde o sorteio!</span>' . "\r\n" . '      ';
}

echo '   </div>' . "\r\n" . '   <div class="SorteioTpl_imagemContainer__2-pl4 col-auto">' . "\r\n" . '      <div id="carouselSorteio640d0a84b1fef407920230311" class="carousel slide carousel-dark carousel-fade" data-bs-ride="carousel">' . "\r\n" . '         <div class="carousel-inner">' . "\r\n" . '            ';
$image_gallery = isset($image_gallery) ? $image_gallery : '';
if ($image_gallery != '[]' && !empty($image_gallery)) {
    $image_gallery = json_decode($image_gallery, true);
    array_unshift($image_gallery, $image_path);
    echo '               ';
    $slide = 0;

    foreach ($image_gallery as $image) {
        ++$slide;
        echo '                  <div class="custom-image carousel-item ';

        if ($slide == 1) {
            echo 'active';
        }

        echo '">' . "\r\n" . '                     <img alt="';
        echo isset($name) ? $name : '';
        echo '" src="';
        echo validate_image($image);
        echo '" decoding="async" data-nimg="fill" class="SorteioTpl_imagem__2GXxI">' . "\r\n" . '                  </div>' . "\r\n" . '               ';
    }

    echo '            ';
} else {
    echo '               <div class="custom-image carousel-item active">' . "\r\n" . '                  <img src="';
    echo validate_image(isset($image_path) ? $image_path : '');
    echo '" alt="';
    echo isset($name) ? $name : '';
    echo '" class="SorteioTpl_imagem__2GXxI" style="width:100%">' . "\r\n" . '               </div>' . "\r\n" . '            ';
}

echo '         </div>' . "\r\n" . '      </div>' . "\r\n" . '      ';
if ($image_gallery != '[]' && !empty($image_gallery)) {
    echo '         <button class="carousel-control-prev" type="button"' . "\r\n" . '            data-bs-target="#carouselSorteio640d0a84b1fef407920230311" data-bs-slide="prev">' . "\r\n" . '            <span class="carousel-control-prev-icon"></span>' . "\r\n" . '         </button>' . "\r\n" . '         <button class="carousel-control-next" type="button"' . "\r\n" . '            data-bs-target="#carouselSorteio640d0a84b1fef407920230311" data-bs-slide="next">' . "\r\n" . '            <span class="carousel-control-next-icon"></span>' . "\r\n" . '         </button>' . "\r\n" . '      ';
}

echo '   </div>' . "\r\n" . '   <div class="SorteioTpl_info__t1BZr custom-content-wrapper ';
echo $status_display != '4' && $status_display != '5' ? 'custom-content-wrapper-details' : '';
echo '"style="background: linear-gradient(254deg, rgba(29, 86, 255, 0.01) 0px, rgb(0, 0, 0));">' . "\r\n" . '      <h1 class="SorteioTpl_title__3RLtu">';
echo isset($name) ? $name : '';
echo '</h1>' . "\r\n" . '      <p class="SorteioTpl_descricao__1b7iL" style="margin-bottom:1px">';
echo isset($subtitle) ? $subtitle : '';
echo '</p>' . "\r\n" . '      ';

if ($status_display != '4' && $status_display != '5') {
    echo '        <div class="btn btn-sm bg-gradient-dark text-white border-0 font-xs py-1 box-shadow-08 w-100 rounded-0" data-bs-toggle="modal" data-bs-target="#modal-consultaCompras">' . "\r\n" . '            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-1j8ryas iconify iconify--mdi" sx="[object Object]" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M9 20c0 1.1-.9 2-2 2s-2-.9-2-2s.9-2 2-2s2 .9 2 2m8-2c-1.1 0-2 .9-2 2s.9 2 2 2s2-.9 2-2s-.9-2-2-2m-9.8-3.2v-.1l.9-1.7h7.4c.7 0 1.4-.4 1.7-1l3.9-7l-1.7-1l-3.9 7h-7L4.3 2H1v2h2l3.6 7.6L5.2 14c-.1.3-.2.6-.2 1c0 1.1.9 2 2 2h12v-2H7.4c-.1 0-.2-.1-.2-.2M18 2.8l-1.4-1.4l-4.8 4.8l-2.6-2.6L7.8 5l4 4z"></path></svg>Meus títulos' . "\r\n" . '         </div>' . "\r\n" . '      ';
}

echo '
    </div>
    </div>

    <div class="campanha-buscas mt-2">
        <div class="row row-gutter-sm">
            <div class="col">
                <div>
';

if ($percent > 0 && $enable_progress_bar == 1) {
    // Início da barra de progresso
    echo '                    <div class="progress">' . PHP_EOL;
    echo '                        <div class="progress-bar bg-info progress-bar-striped fw-bold progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' . PHP_EOL;

    // Verifica se é barra de progresso fake
    if ($enable_progress_bar_fake == 1) {
        $progress_value = number_format($enable_progress_bar_fake_value, 1, '.', '');
    } else {
        $progress_value = number_format($percent, 1, '.', '');
    }

    echo '                        <div class="progress-bar bg-success progress-bar-striped fw-bold progress-bar-animated" role="progressbar" aria-valuenow="' . $progress_value . '" aria-valuemin="0" aria-valuemax="' . $qty_numbers . '" style="width: ' . $progress_value . '%;">' . $progress_value . '%</div>' . PHP_EOL;
    echo '                    </div>' . PHP_EOL;
}

echo '
                </div>
            </div>
        </div>
    </div>
';
?>

<?php 
$agora = date('Y-m-d H:i:s');

if ($enable_double == 1 && strtotime($double_ini) < strtotime($agora) && strtotime($agora) < strtotime($double_fim)) {
    $timestamp_ini = strtotime($double_ini);
    $timestamp_fim = strtotime($double_fim);
    $timestamp_hoje = time(); // Timestamp atual

    if ($timestamp_hoje < $timestamp_fim && $timestamp_hoje > $timestamp_ini) {
        $progresso = ($timestamp_hoje - $timestamp_ini) / ($timestamp_fim - $timestamp_ini) * 100;
    } else {
        $progresso = 0; // Se não estiver no intervalo, a barra fica em 0
    }
?>
    <style>
        .barra {
            width: 100%;
            background-color: #0f161a6b;
            border: 1px solid #ddd;
            height: 30px;
            border-radius: 5px;
        }

        .progresso {
            height: 100%;
            background: repeating-linear-gradient(45deg,
                    rgb(6, 79, 45),
                    rgb(28, 129, 82) 10px,
                    rgb(129, 191, 162) 10px,
                    rgb(129, 191, 162) 20px);
            border-radius: 5px;
            text-align: center;
            color: white;
            line-height: 30px;
            font-weight: 800;
        }
    </style>
    <div class="card bg-gradient-red2 my-3">
        <div class="card-body text-white text-center">
            <h5 class="fw-bolder ">🔥 Chance em dobro! 🔥</h5>
            <div class="barra my-2">
                <div class="progresso text-nowrap" style="width: <?= $progresso ?>%;"><?= round($progresso, 2) ?>%</div>
            </div>
            <div>
                <div class="fw-bold">Compre agora e ganhe o dobro!</div>
                <div>
                    Válido de <?= $data_formatada_ini = date('d/m/Y H:i', strtotime($double_ini)) ?> a <?= $data_formatada_fim = date('d/m/Y H:i', strtotime($double_fim)) ?>
                </div>
            </div>
        </div>
    </div>

<?php
}
if ($status == '1') {
    echo '<div class="campanha-preco porApenas font-xs d-flex align-items-center justify-content-center mt-2 mb-2 font-weight-500">' . "\r\n" . '   <div class="item d-flex align-items-center font-xs me-2">' . "\r\n" . '      ';

    if (!empty($date_of_draw)) {
        echo '         <span class="ms-2 me-1">Sorteio</span>' . "\r\n" . '         <div class="tag btn btn-sm bg-opacity-50 font-xss box-shadow-08" style="height: 22px; min-width: 22px; line-height: 0; border-radius: 6px; align-items: center; white-space: nowrap; display: inline-flex; justify-content: center; color: rgb(33, 43, 54);     font-family: Montserrat; font-weight: 600; border: 1px solid rgba(145, 158, 171, 0.32); font-size: 11px; cursor: pointer; text-transform: uppercase; padding: 8px; background-color: rgb(255, 255, 255);">' . "\r\n" . '            ';
        $dataFormatada = date('d/m/y', strtotime($date_of_draw));
        $horaFormatada = date('H:i', strtotime($date_of_draw));
        $date_of_draw = $dataFormatada . ' às ' . $horaFormatada;
        echo $date_of_draw;
        echo ' ' . "\r\n" . '         </div>' . "\r\n" . '      ';
    }

    echo '   </div>' . "\r\n" . '   <div class="item d-flex align-items-center font-xs">' . "\r\n" . '      <div class="me-1">por apenas</div>' . "\r\n" . '      <div class="tag btn btn-sm text-white box-shadow-08" style="background-color: #000000;">R$ ';
    echo isset($price) ? format_num($price, 2) : '';
    echo '</div>' . "\r\n" . '   </div>' . "\r\n" . '</div>' . "\r\n";
}
echo "\r\n";

if ($status_display == '6') {
    echo '<div class="alert alert-warning font-xss mb-2 mt-2">Todos os números já foram reservados ou pagos</div>' . "\r\n";
}

echo "\r\n";
$discount_qty = isset($discount_qty) ? $discount_qty : '';
$discount_amount = isset($discount_amount) ? $discount_amount : '';
if ($available > 0 && $discount_qty && $discount_amount && $enable_discount == 1) {
    $discount_qty = json_decode($discount_qty, true);
    $discount_amount = json_decode($discount_amount, true);
    $discounts = [];

    foreach ($discount_qty as $qty_index => $qty) {
        foreach ($discount_amount as $amount_index => $amount) {
            if ($qty_index === $amount_index) {
                $discounts[$qty_index] = ['qty' => $qty, 'amount' => $amount];
            }
        }
    }

    if (isset($discounts)) {
        $max_discount = count($discounts);
    } else {
        $max_discount = 0;
    }

    echo "\r\n";

    if ($available > 0 && $status == '1') {
        echo '<div class="app-promocao-numeros mb-2">' . "\r\n" . '   <div class="app-title mb-2">' . "\r\n" . '      <h1>📣 Promoção</h1>' . "\r\n" . '      <div class="app-title-desc">Compre mais barato!</div>' . "\r\n" . '   </div>' . "\r\n" . '   <div class="app-card card">' . "\r\n" . '      <div class="card-body pb-1">' . "\r\n" . '         <div class="row px-2">' . "\r\n" . '          ';
        $count = 0;

        foreach ($discounts as $discount) {
            echo '            <div class="col-auto px-1 mb-2">' . "\r\n" . '               ';

            if ($user_id) {
                echo '                  <button onclick="qtyRaffle(\'';
                echo $discount['qty'];
                echo '\', true);" class="btn btn-success w-100 btn-sm py-0 px-2 text-nowrap font-xss">' . "\r\n" . '               ';
            } else {
                echo '                  <span id="add_to_cart"></span>' . "\r\n" . '                  <button data-bs-toggle="modal" data-bs-target="#newCheckoutModal" onclick="qtyRaffle(\'';
                echo $discount['qty'];
                echo '\', true);" class="btn btn-success w-100 btn-sm py-0 px-2 text-nowrap font-xss">' . "\r\n" . '               ';
            }

            echo '               <span class="font-weight-500">' . "\r\n" . '                  <b class="font-weight-600"><span id="discount_qty_';
            echo $count;
            echo '">';
            echo $discount['qty'];
            echo '</span></b> cotas <small>por R$</small> <span class="font-weight-600"><span id="discount_amount_';
            echo $count;
            echo '" style="display:none">';
            echo $discount['amount'];
            echo '</span>' . "\r\n" . '                  ';
            $discount_price = $price * $discount['qty'] - $discount['amount'];
            echo number_format($discount_price, 2, ',', '.');
            echo '</span></span></button>' . "\r\n" . '            </div>' . "\r\n" . '            ';
            ++$count;
        }

        echo '         </div>' . "\r\n" . '      </div>' . "\r\n" . '   </div>' . "\r\n" . '</div>' . "\r\n";
    }
}

echo "\r\n";
if ($available > 0 && $enable_sale == 1 && $enable_discount == 0 && $status == '1') {
    echo ' <div class="app-promocao-numeros mb-2">' . "\r\n" . '   <div class="app-title mb-2">' . "\r\n" . '      <h1>📣 Promoção</h1>' . "\r\n" . '      <div class="app-title-desc">Compre mais barato!</div>' . "\r\n" . '   </div>' . "\r\n" . '   <div class="app-card card">' . "\r\n" . '      <div class="card-body pb-1">' . "\r\n" . '         <div class="row px-2">' . "\r\n" . '            <div class="col-auto px-1 mb-2">' . "\r\n" . '               <button onclick="qtyRaffle(\'';
    echo $sale_qty;
    echo '\', false);" class="btn btn-success w-100 btn-sm py-0 px-2 text-nowrap font-xss"><span class="font-weight-500">Comprando' . "\r\n" . '                  <b class="font-weight-600"><span>';
    echo $sale_qty;
    echo ' cotas</span></b> sai por apenas<small> R$</small> <span class="font-weight-600">' . "\r\n" . '                     ';
    echo number_format($sale_price, 2, ',', '.');
    echo '</span> cada</span></button>' . "\r\n" . '               </div>' . "\r\n" . '            </div>' . "\r\n" . '         </div>' . "\r\n" . '      </div>' . "\r\n" . '   </div>' . "\r\n";
}

echo "\r\n";
if ($status == '1') { ?>
<button type="button" class="ranking-spotlight<?= $rankingTimerVisible ? ' is-event' : '' ?>" data-bs-toggle="modal" data-bs-target="#modal-premios" aria-label="Abrir Top Compradores Diário">
    <span class="ranking-spotlight__title">
        <span class="ranking-spotlight__icon" aria-hidden="true">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 4h8v3.5a4 4 0 0 1-8 0V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <path d="M8 6H5.5v1A3.5 3.5 0 0 0 9 10.5M16 6h2.5v1a3.5 3.5 0 0 1-3.5 3.5M12 11.5V16m-3 4h6m-5-4h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span>Top Compradores Diário</span>
    </span>
    <?php if ($rankingTimerVisible): ?>
        <span class="ranking-spotlight__event">
            <span class="ranking-spotlight__event-dot" aria-hidden="true"></span>
            <strong>Evento acontecendo!</strong>
        </span>
    <?php else: ?>
        <svg class="ranking-spotlight__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    <?php endif; ?>
</button>

<?php }

if ($available > 0 && $status == '1') {
    echo '<div class="app-card card mb-2">' . "\r\n" . '   <div class="card-body text-center">' . "\r\n" . '   <p class="font-xs">Quanto mais títulos, mais chances de ganhar!!</p>' . "\r\n" . '   </div>' . "\r\n" . '</div>' . "\r\n";
}

if ($available > 0 && $status == '1') {

?>

   <div class="app-vendas-express mb-2">
        <div class="numeros-select d-flex align-items-center justify-content-center flex-column">
            <div class="vendasExpressNumsSelect v2">
                <?php if ($qty_select_1 > 0): ?>
                    <div onclick="qtyRaffle('<?= $qty_select_1 ?>', false)" class="item mb-2">
                        <div class="item-content flex-column p-2">
                            <h3 class="mb-0"><small class="item-content-plus font-xsss">+</small>
                                <?= $qty_select_1 ?>
                            </h3>
                            <p class="item-content-txt font-xss text-uppercase mb-0">Selecionar</p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($qty_select_2 > 0): ?>
                     <div onclick="qtyRaffle('<?= $qty_select_2 ?>', false)" 
          class="item mb-2 bg-success"
     style="position: relative; 
            border: 2px solid transparent; 
            border-image: linear-gradient(90deg, #198754, #0a3420) 1; 
            border-radius: 4px; 
            overflow: hidden;
            -webkit-mask-image: radial-gradient(#fff, #000); 
            mask-image: radial-gradient(#fff, #000);">

       <div class="px-2 text-white text-center"
     style="background-color: rgb(25, 135, 84);
            position: absolute;
            font-family: Montserrat;
            font-size: 0.7em;
            padding: 2px;
            width: 100px;
            top: -2px;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;">
  Mais popular
</div>
        <div class="item-content flex-column p-2" 
             style="background-color: rgb(200, 250, 205); color: #000;">
            <h3 class="mb-0" style="color: #000;">
                <small class="item-content-plus font-xsss">+</small>
                                <?= $qty_select_2 ?>
                            </h3>
                            <p class="item-content-txt font-xss text-uppercase mb-0">Selecionar</p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($qty_select_3 > 0): ?>
                   <div onclick="qtyRaffle('<?= $qty_select_3 ?>', false)" class="item mb-2">
                        <div class="item-content flex-column p-2">
                            <h3 class="mb-0"><small class="item-content-plus font-xsss">+</small>
                              <?= $qty_select_3 ?>
                           </h3>
                           <p class="item-content-txt font-xss text-uppercase mb-0">Selecionar</p>
                      </div>
                 </div>
             <?php endif; ?>
                <?php if ($qty_select_4 > 0): ?>
                    <div onclick="qtyRaffle('<?= $qty_select_4 ?>', false)" class="item mb-2">
                        <div class="item-content flex-column p-2">
                            <h3 class="mb-0"><small class="item-content-plus font-xsss">+</small>
                                <?= $qty_select_4 ?>
                            </h3>
                            <p class="item-content-txt font-xss text-uppercase mb-0">Selecionar</p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($qty_select_5 > 0): ?>
                    <div onclick="qtyRaffle('<?= $qty_select_5 ?>', false)" class="item mb-2">
                        <div class="item-content flex-column p-2">
                            <h3 class="mb-0"><small class="item-content-plus font-xsss">+</small>
                                <?= $qty_select_5 ?>
                            </h3>
                            <p class="item-content-txt font-xss text-uppercase mb-0">Selecionar</p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($qty_select_6 > 0): ?>
                    <div onclick="qtyRaffle('<?= $qty_select_6 ?>', false)" class="item mb-2">
                        <div class="item-content flex-column p-2">
                            <h3 class="mb-0"><small class="item-content-plus font-xsss">+</small>
                                <?= $qty_select_6 ?>
                            </h3>
                            <p class="item-content-txt font-xss text-uppercase mb-0">Selecionar</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
              <div class="d-flex w-100 justify-content-center items-center ">
                <div class="vendasExpressNums app-card card mb-2 w-100 font-xs me-1">
                    <div class="card-body d-flex align-items-center justify-content-center font-xss p-1" style="height: 55px;">
                        <div class="left pointer">
                            <div class="removeNumero numeroChange"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-11elljy iconify iconify--lucide" sx="[object Object]" width="2em" height="2em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path></g></svg></div>
                        </div>
                        <div class="center">
                            <input class="form-control text-center qty" readonly value="<?= isset($min_purchase) ? $min_purchase : '' ?>" aria-label="Quantidade de números" placeholder="<?= isset($min_purchase) ? $min_purchase : '' ?>" style="font-size: 16px;">
                        </div>
                        <div class="right pointer">
                            <div class="addNumero numeroChange"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-11elljy iconify iconify--lucide" sx="[object Object]" width="2em" height="2em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8m-4-4v8"></path></g></svg></i></div>
                        </div>
                    </div>
                </div>
                
                <?php
                if ($user_id) { ?>
                    <button id="add_to_cart" data-bs-toggle="modal" data-bs-target="#newCheckoutModal" class="btn w-100 mb-2 btn-custom">
                    <?php } else { ?>
                        <span id="add_to_cart"></span>
                        
                        <button data-bs-toggle="modal" data-bs-target="#newCheckoutModal" class="btn w-100 mb-2 btn-custom">
                        <?php   }
                        ?>
                        <div class="d-flex align-items-center" style="display: flex; flex-direction:row; ">
                           <div class="me-4" style="display:flex; align-items:center">
                                  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-5zprff iconify iconify--prime" sx="[object Object]" width="2em" height="2em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M10.5 15.25A.74.74 0 0 1 10 15l-3-3a.75.75 0 0 1 1-1l2.47 2.47L19 5a.75.75 0 0 1 1 1l-9 9a.74.74 0 0 1-.5.25"></path><path fill="currentColor" d="M12 21a9 9 0 0 1-7.87-4.66a8.7 8.7 0 0 1-1.07-3.41a9 9 0 0 1 4.6-8.81a8.7 8.7 0 0 1 3.41-1.07a8.9 8.9 0 0 1 3.55.34a.75.75 0 1 1-.43 1.43a7.6 7.6 0 0 0-3-.28a7.4 7.4 0 0 0-2.84.89a7.5 7.5 0 0 0-2.2 1.84a7.42 7.42 0 0 0-1.64 5.51a7.4 7.4 0 0 0 .89 2.84a7.5 7.5 0 0 0 1.84 2.2a7.42 7.42 0 0 0 5.51 1.64a7.4 7.4 0 0 0 2.84-.89a7.5 7.5 0 0 0 2.2-1.84a7.42 7.42 0 0 0 1.64-5.51a.75.75 0 1 1 1.57-.15a9 9 0 0 1-4.61 8.81A8.7 8.7 0 0 1 12.93 21z"></path></svg>
                            <div style="flex-direction:column; display:flex; align-items: flex-start;">
                                <div class="col pe-0 text-nowrap">Quero participar</div>
                                <div class="col pe-0 text-nowrap price-mobile">
                                    <span id="total" style="opacity: 0.7; font-size: 0.75rem !important; color: white;">R$
                                    <?php
                                    if (isset($price)) {
                                        $price_total = $price * $min_purchase;
                                        echo format_num($price_total, 2);
                                    }

                                    echo '                     ' . "\r\n" . '                   </span>' . "\r\n" . '               </div>' . "\r\n" . '            </div>' . "\r\n" . '</div>' . "\r\n" . '         </button>' . "\r\n" . '    </div>' . "\r\n" . '   </div>' . "\r\n";
                                }

                                echo "\r\n";
                                if (!empty($draw_number)) {
                                    echo '   ';
                                    $winners_qty = 5;
                                    $draw_number = isset($draw_number) ? $draw_number : '';
                                    if ($winners_qty && $draw_number) {
                                        $draw_winner = json_decode($draw_winner, true);
                                        $draw_number = json_decode($draw_number, true);
                                        $winners = [];

                                        foreach ($draw_winner as $qty_index => $name) {
                                            foreach ($draw_number as $amount_index => $number) {
                                                $query = $conn->query('SELECT CONCAT(firstname, \' \', lastname) as name, avatar FROM customer_list WHERE phone = \'' . $name . '\'');
                                                $rowCustomer = $query->fetch_assoc();

                                                if ($qty_index === $amount_index) {
                                                    $winners[$qty_index] = [
                                                        'name' => $rowCustomer['name'],
                                                        'number' => $number,
                                                        'image' => $rowCustomer['avatar'] ? validate_image($rowCustomer['avatar']) : BASE_URL . 'assets/img/avatar.png',
                                                    ];
                                                }
                                            }
                                        }
                                    }

                                    echo '      ';
                                    $count = 0;

                                    foreach ($winners as $winner) {
                                        ++$count;
                                        echo "\r\n" . '         <div class="app-card card bg-success text-white mb-2 mt-2">' . "\r\n" . '            <div class="card-body">' . "\r\n" . '               <div class="row align-items-center">' . "\r\n" . '                  <div class="col-auto">' . "\r\n" . '                     <div class="rounded-pill" style="width: 56px; height: 56px; position: relative; overflow: hidden;">' . "\r\n" . '                        <div style="display: block; overflow: hidden; position: absolute; inset: 0px; box-sizing: border-box; margin: 0px;">' . "\r\n" . '                           <img alt="';
                                        echo $winner['name'];
                                        echo '" src="';
                                        echo $winner['image'];
                                        echo '" decoding="async" data-nimg="fill" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: none; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%;">' . "\r\n" . '                           <noscript></noscript>' . "\r\n" . '                        </div>' . "\r\n" . '                     </div>' . "\r\n" . '                  </div>' . "\r\n" . '                  <div class="col">' . "\r\n" . '                     <h5 class="mb-0" style="text-transform: uppercase;">';
                                        echo $count;
                                        echo 'º - ';
                                        echo $winner['name'];
                                        echo '&nbsp;<i class="bi bi-check-circle text-white-50"></i></h5>' . "\r\n" . '                     <div class="text-white-50"><small>Ganhador(a) com a cota ';
                                        echo $winner['number'];
                                        echo '</small></div>' . "\r\n" . '                  </div>' . "\r\n" . '               </div>' . "\r\n" . '            </div>' . "\r\n" . '         </div>' . "\r\n" . '      ';
                                    }

                                }
                                ?>

                            <?php

                        
                        if ($description) {
                            ?>
                                <div class="app-descricao-completa">
                                 <div class="app-card card font-xs mb-2 sorteio_sorteioDesc__TBYaL">
                                  <button class="app-descricao-completa--titulo font-xs collapseIcon btn btn-link text-decoration-none ps-0 text-cor-card-link collapsed" data-bs-toggle="collapse" data-bs-target="#collapseOne" role="button" aria-expanded="false" aria-controls="collapseDescricao">
                                       <i class="bi bi-arrow-up-square-fill forCollapse me-2"></i>Descrição/Regulamento</button>
                                        <div id="collapseOne" class="card-body sorteio_sorteioDescBody__3n4ko collapse" style="    max-height: 300px; overflow: scroll; card-body padding: 10px;">
                                            <div class="accordion-body <?= $bgTheme ?>">
                                                <?= blockHTML($description) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php
                            }

                         
                                echo '   ' . "\r\n\r\n";

                                echo "\r\n\r\n" . '<div class="modal fade" id="modal-consultaCompras">' . "\r\n" . '   <div class="modal-dialog modal-md">' . "\r\n" . '      <div class="modal-content">' . "\r\n" . '         <form id="consultMyNumbers">' . "\r\n" . '            <div class="modal-header">' . "\r\n" . '               <h6 class="modal-title">Consulta de compras</h6>' . "\r\n" . '               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' . "\r\n" . '            </div>' . "\r\n" . '            <div class="modal-body">' . "\r\n" . '               <div class="form-group">' . "\r\n" . '               ';

                                if ($enable_cpf != 1) {
                                    echo '                  <label class="form-label">Informe seu telefone</label>' . "\r\n" . '                  <div class="input-group mb-2">' . "\r\n" . '                     <input onkeyup="formatarTEL(this);" maxlength="15" class="form-control" aria-label="Número de telefone" maxlength="15" id="phone" name="phone" required="" value="">' . "\r\n" . '                     <button class="btn btn-secondary" type="submit" id="button-addon2">' . "\r\n" . '                        <div class=""><i class="bi bi-check-circle"></i></div>' . "\r\n" . '                     </button>' . "\r\n" . '                  </div>' . "\r\n" . '               ';
                                } else {
                                    echo '                  <label class="form-label">Informe seu CPF</label>' . "\r\n" . '                  <div class="input-group mb-2">' . "\r\n" . '                     <input name="cpf" class="form-control" id="cpf" value="" maxlength="14" minlength="14" placeholder="000.000.000-00" oninput="formatarCPF(this.value)" required>' . "\r\n" . '                     <button class="btn btn-secondary" type="submit" id="button-addon2">' . "\r\n" . '                        <div class=""><i class="bi bi-check-circle"></i></div>' . "\r\n" . '                     </button>' . "\r\n" . '                  </div>' . "\r\n" . '               ';
                                }

                                echo '               </div>' .
                                    "\r\n" .
                                    '            </div>' .
                                    "\r\n" .
                                    '         </form>' .
                                    "\r\n" .
                                    '      </div>' .
                                    "\r\n" .
                                    '   </div>' .
                                    "\r\n" .
                                    '</div>' .
                                    "\r\n" .
                                    '<!-- Modal checkout -->' .
                                    "\r\n" .
                                    '<div class="modal fade" id="modal-checkout">' .
                                    "\r\n" .
                                    '   <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-centered">' .
                                    "\r\n" .
                                    '      <div class="modal-content rounded-0">' .
                                    "\r\n" .
                                    '         <span class="d-none">Usuário não autenticado</span>' .
                                    "\r\n" .
                                    '         <div class="modal-header">' .
                                    "\r\n" .
                                    '            <h5 class="modal-title">Checkout</h5>' .
                                    "\r\n" .
                                    '            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' .
                                    "\r\n" .
                                    '         </div>' .
                                    "\r\n" .
                                    '         <div class="modal-body checkout">' .
                                    "\r\n" .
                                    '            <div class="alert alert-info p-2 mb-2 font-xs"><i class="bi bi-check-circle"></i> Você está adquirindo<span class="font-weight-500">&nbsp;<span id="qty_cotas"></span> cotas</span><span>&nbsp;da ação entre amigos</span><span class="font-weight-500">&nbsp;';
                                echo isset($name) ? $name : '';
                                echo '</span>,<span>&nbsp;seus números serão gerados</span><span>&nbsp;assim que concluir a compra.</span></div>' . "\r\n" . '            <div class="mb-3">' . "\r\n" . '               <div class="card app-card">' . "\r\n" . '                  <div class="card-body">' . "\r\n" . '                     <div class="row align-items-center">' . "\r\n" . '                        <div class="col-auto">' . "\r\n" . '                           <div class="rounded-pill p-1 bg-white box-shadow-08" style="width: 56px; height: 56px; position: relative; overflow: hidden;">' . "\r\n" . '                              <div style="display: block; overflow: hidden; position: absolute; inset: 0px; box-sizing: border-box; margin: 0px;">' . "\r\n" . '                                 <img src="';
                                echo validate_image($_settings->userdata('avatar'));
                                echo '" decoding="async" data-nimg="fill" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: none; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%;">' . "\r\n" . '                                 <noscript></noscript>' . "\r\n" . '                              </div>' . "\r\n" . '                           </div>' . "\r\n" . '                        </div>' . "\r\n" . '                        <div class="col">' . "\r\n" . '                           <h5 class="mb-1">';
                                echo $_settings->userdata('firstname');
                                echo ' ';
                                echo $_settings->userdata('lastname');
                                echo '</h5>' . "\r\n" . '                           <div><small>';
                                echo formatPhoneNumber($_settings->userdata('phone'));
                                echo '</small></div>' . "\r\n" . '                        </div>' . "\r\n" . '                        <div class="col-auto"><i class="bi bi-chevron-compact-right"></i></div>' . "\r\n" . '                     </div>' . "\r\n" . '                  </div>' . "\r\n" . '               </div>' . "\r\n" . '            </div>' . "\r\n" . '            <button id="place_order" data-id="';
                                echo $_SESSION['ref'] ? $_SESSION['ref'] : '';
                                echo '" class="btn btn-success w-100 mb-2">Concluir reserva <i class="bi bi-arrow-right-circle"></i></button>' . "\r\n" . '            <button type="button" class="btn btn-link btn-sm text-secondary text-decoration-none w-100 my-2"><a href="';
                                echo BASE_URL . 'logout?' . $_SERVER['REQUEST_URI'];
                                echo '">Utilizar outra conta</a></button>' .
                                    "\r\n\r\n" .
                                    '         </div>' .
                                    "\r\n" .
                                    '      </div>' .
                                    "\r\n" .
                                    '   </div>' .
                                    "\r\n" .
                                    '</div>' .
                                    "\r\n" .
                                    '<!-- Modal checkout -->' .
                                    "\r\n\r\n\r\n" .
                                    '<!-- Modal Aviso -->' .
                                    "\r\n" .
                                    '<button id="aviso_sorteio" data-bs-toggle="modal" data-bs-target="#modal-aviso" class="btn btn-success w-100 py-2" style="display:none"></button>' .
                                    "\r\n" .
                                    '<div class="modal fade" id="modal-aviso">' .
                                    "\r\n" .
                                    '   <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-centered">' .
                                    "\r\n" .
                                    '      <div class="modal-content rounded-0">' .
                                    "\r\n" .
                                    '         <div class="modal-header">' .
                                    "\r\n" .
                                    '            <h5 class="modal-title">Aviso</h5>' .
                                    "\r\n" .
                                    '            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' .
                                    "\r\n" .
                                    '         </div>' .
                                    "\r\n" .
                                    '         <div class="modal-body checkout">' .
                                    "\r\n" .
                                    '            <div class="alert alert-danger p-2 mb-2 font-xs aviso-content">' .
                                    "\r\n\r\n\r\n" .
                                    '            </div>' .
                                    "\r\n" .
                                    '         </div>' .
                                    "\r\n" .
                                    '      </div>' .
                                    "\r\n" .
                                    '   </div>' .
                                    "\r\n" .
                                    '</div>' .
                                    "\r\n" .
                                    '<!-- Modal Aviso -->' .
                                    "\r\n\r\n\r\n" .
                                    '<div class="modal fade" id="modal-indique">' .
                                    "\r\n" .
                                    '   <div class="modal-dialog modal-dialog-centered modal-lg">' .
                                    "\r\n" .
                                    '      <div class="modal-content">' .
                                    "\r\n" .
                                    '         <div class="modal-header">' .
                                    "\r\n" .
                                    '            <h5 class="modal-title">Indique e ganhe!</h5>' .
                                    "\r\n" .
                                    '            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' .
                                    "\r\n" .
                                    '         </div>' .
                                    "\r\n" .
                                    '         <div class="modal-body text-center">Faça login para ter seu link de indicao, e ganhe at 0,00% de créditos nas compras aprovadas!</div>' .
                                    "\r\n" .
                                    '      </div>' .
                                    "\r\n" .
                                    '   </div>' .
                                    "\r\n" .
                                    '</div>' .
                                    "\r\n";
                                if (false) {
                                    echo '   <div class="sorteio_sorteioShare__247_t" style="z-index:10;">' . "\r\n" . '      <div class="campanha-share d-flex mb-1 justify-content-between align-items-center">' . "\r\n" . '            ';

                                    if ($enable_share == 1) {
                                        echo '               <div class="item d-flex align-items-center">' . "\r\n" . '                  <a href="https://www.facebook.com/sharer/sharer.php?u=';
                                        echo BASE_URL;
                                        echo 'campanha/';
                                        echo $slug;
                                        echo '" target="_blank">' . "\r\n" . '                     <div alt="Compartilhe no Facebook" class="sorteio_sorteioShareLinkFacebook__2McKU" style="margin-right:5px;">' . "\r\n" . '                        <i class="bi bi-facebook"></i>' . "\r\n" . '                     </div>' . "\r\n" . '                  </a>' . "\r\n\r\n" . '                  <a href="https://t.me/share/url?url=';
                                        echo BASE_URL;
                                        echo 'campanha/';
                                        echo $slug;
                                        echo 'text=';
                                        echo $name;
                                        echo '" target="_blank">' . "\r\n" . '                     <div alt="Compartilhe no Telegram" class="sorteio_sorteioShareLinkTelegram__3a2_s" style="margin-right:5px;">' . "\r\n" . '                        <i class="bi bi-telegram"></i>' . "\r\n" . '                     </div>' . "\r\n" . '                  </a>' . "\r\n\r\n" . '                  <a href="https://www.twitter.com/share?url=';
                                        echo BASE_URL;
                                        echo 'campanha/';
                                        echo $slug;
                                        echo '" target="_blank">' . "\r\n" . '                     <div alt="Compartilhe no Twitter" class="sorteio_sorteioShareLinkTwitter__1E4XC" style="margin-right:5px;">' . "\r\n" . '                        <i class="bi bi-twitter"></i>' . "\r\n" . '                     </div>' . "\r\n" . '                  </a>' . "\r\n\r\n" . '                  <a href="https://api.whatsapp.com/send/?text=';
                                        echo $name;
                                        echo '%21%21%3A+';
                                        echo BASE_URL;
                                        echo 'campanha/';
                                        echo $slug;
                                        echo '&type=custom_url&app_absent=0" target="_blank"><div alt="Compartilhe no WhatsApp" class="sorteio_sorteioShareLinkWhatsApp__2Vqhy"><i class="bi bi-whatsapp"></i></div></a>' . "\r\n" . '               </div>' . "\r\n" . '            ';
                                    }

                                    echo '         </div>' . "\r\n" . '      ';

                                    if ($whatsapp_group_url) {
                                        echo '         <a href="';
                                        echo $whatsapp_group_url;
                                        echo '" target="_blank">   ' . "\r\n" . '            <div class="whatsapp-grupo">' . "\r\n" . '               <div class="btn btn-sm btn-success mb-1 w-100"><i class="bi bi-whatsapp"></i> Grupo</div>' . "\r\n" . '            </div>' . "\r\n" . '         </a>' . "\r\n" . '      ';
                                    }

                                    echo '      ';

                                    if ($telegram_group_url) {
                                        echo '         <a href="';
                                        echo $telegram_group_url;
                                        echo '" target="_blank">' . "\r\n" . '            <div class="telegram-grupo">' . "\r\n" . '               <div class="btn btn-sm btn-info btn-block text-white mb-1 w-100"><i class="bi bi-telegram"></i> Telegram</div>' . "\r\n" . '            </div>' . "\r\n" . '         </a>' . "\r\n" . '      ';
                                    }

                                    echo '      ';

                                    if ($instagram_url) {
                                        echo '         <a href="';
                                        echo $instagram_url;
                                        echo '" target="_blank">   ' . "\r\n" . '            <div class="instagram-grupo">' . "\r\n" . '               <div class="btn btn-sm btn-instagram mb-1 w-100"><i class="bi bi-instagram"></i> Seguir</div>' . "\r\n" . '            </div>' . "\r\n" . '         </a>' . "\r\n" . '      ';
                                    }

                                    echo '      ';

                                    if ($support_number) {
                                        echo '         <a href="https://api.whatsapp.com/send?phone=55';
                                        echo $support_number;
                                        echo '" target="_blank">   ' . "\r\n" . '            <div class="suporte">' . "\r\n" . '               <div class="btn btn-sm btn-warning mb-1 w-100"><i class="bi bi-headset"></i></i> Suporte</div>' . "\r\n" . '            </div>' . "\r\n" . '         </a>' . "\r\n" . '      ';
                                    }

                                    echo '   </div>' . "\r\n";
                                }

                                    ?>



                                    <?php
                                    // Roletas e caixas premiadas foram descontinuadas.
                                    if (false && $available > 0 && $status == '1') {
                                        if ($roleta || $box) {
                                            $roleta_qty = isset($roleta_qty) ? $roleta_qty : '';
                                            $roleta_amount = isset($roleta_amount) ? $roleta_amount : '';

                                            $roleta_qty = json_decode($roleta_qty, true);
                                            $roleta_amount = json_decode($roleta_amount, true);
                                            $roletas = [];

                                            foreach ($roleta_qty as $qty_index => $qty) {
                                                foreach ($roleta_amount as $amount_index => $amount) {
                                                    if ($qty_index === $amount_index) {
                                                        $roletas[$qty_index] = [
                                                            'qty' => $qty,
                                                            'amount' => $amount,
                                                        ];
                                                    }
                                                }
                                            }

                                            $box_qty = isset($box_qty) ? $box_qty : '';
                                            $box_amount = isset($box_amount) ? $box_amount : '';
                                            $box_qty = json_decode($box_qty, true);
                                            $box_amount = json_decode($box_amount, true);
                                            $boxs = [];

                                            foreach ($box_qty as $qty_index => $qty) {
                                                foreach ($box_amount as $amount_index => $amount) {
                                                    if ($qty_index === $amount_index) {
                                                        $boxs[$qty_index] = [
                                                            'qty' => $qty,
                                                            'amount' => $amount,
                                                        ];
                                                    }
                                                }
                                            }
                                        }


                                        if ($roleta) { ?>
                                            <div class="app-title mb-2 gap-2" style="align-items: flex-start;">

                                                <h1 style="font-size: 1.125rem;"><img src="/uploads/roleta-premio.png" style="width: 5%;"> Roletas premiadas</h1>
                                               
                                            </div>
                                            <div class="mb-3">
                                                <?php
                                                $count = 0;
                                                foreach ($roletas as $roleta) {
                                                    if ($roleta) { ?>
                                                        <div class="mb-2">
                                                            <?php if ($user_id) { ?>
                                                                <button onclick="qtyRaffle(<?= $roleta['amount'] ?> + 1, true)" class="pulsando btn w-100 text-center mb-1 lh-1 bg-gradient-dark text-white" style="border-radius: 8px;">
                                                                <?php } else { ?>
                                                                    <span></span>
                                                                    <button data-bs-toggle="modal" data-bs-target="#newCheckoutModal" onclick="qtyRaffle(<?= $roleta['amount'] ?> + 1, true)" class="pulsando btn w-100 text-center bg-gradient-dark mb-1 text-white" style="border-radius: 8px;">
                                                                    <?php } ?>
                                                                    <div class="row mb-1 font-xs">
                                                                        <div class="col text-start">
                                                                            <div class="mb-1"><span style="font-size: 0.75rem; font-weight: 400;line-height: 1.5">A partir de</div>
                                                                            <div class="mb-1"><span class="fs-6" style="font-size: 1rem;"><?= $roleta['amount'] ?></span> Títulos</div>
                                                                        </div>
                                                                        <div class="col-auto d-flex align-items-center">
                                                                            <div>Recebe <?= $roleta['qty'] ?> Roleta(s)</div>
                                                                            <div class="col-auto" style="font-size: 30px;">🎯</div>
                                                                        </div>
                                                                    </div>
                                                                    </button>
                                                                </button>
                                                        </div>


                                                <?php }
                                                    ++$count;
                                                }
                                                ?>

                                            </div>
                                        <?php } ?>
                                        <?php
                                         if ($roleta && $cotas_premiadas_roleta) {
                                            $cotas_premiadas_array_roleta = explode(',', $cotas_premiadas_roleta);

                                            foreach ($cotas_premiadas_array_roleta as $num) {
                                                if (empty($num)) {
                                                    continue;
                                                }
                                                $stmt = $conn->prepare('SELECT customer_id FROM order_list WHERE FIND_IN_SET(?, order_numbers) AND product_id = ? AND status = 2 ');
                                                $stmt->bind_param('si', $num, $id);
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                $row = $result->fetch_assoc();

                                                if ($result->num_rows > 0) {
                                                    $cotas_vendidas[] = ['cota' => $num, 'winner' => $row['customer_id']];
                                                }
                                            }
                                            if ($cotas_vendidas) {
                                                $all_lucky_numbers = array_column($cotas_vendidas, 'cota');
                                                $cotas_premiadas_all = $cotas_premiadas_array_roleta;
                                                $cotas_premiadas_sold_roleta = array_intersect($all_lucky_numbers, $cotas_premiadas_all);
                                            }

                                        ?>
                                            <div class="app-title mb-2 justify-content-between gap-2">
                                                <div class="d-flex align-items-center">
                                                    <h1 style="font-size: 1.125rem;"><img src="/uploads/roleta-premio.png" style="width: 5%;"> Roletas Premiadas</h1>
                                                    <div class="app-title-desc p-0 m-0"><?= $cotas_premiadas_descricao_roleta ?></div>
                                                </div>
                                                <div>
                                                    <span class="css-dtzixn">
                                                        <h6 class="MuiTypography-root MuiTypography-h6 css-1uw6jz8"><?= $cotas_premiadas_sold_roleta ? count($cotas_premiadas_sold_roleta) : 0 ?></h6> / <?= count($cotas_premiadas_array_roleta) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php
                                            if ($available > 0 && $status == '1') {
                                                if ($cotas_premiadas_roleta) {
                                            ?>
                                                    <div class="my-3">

                                                        <div id="cotas-container_roleta" class="mais app-titulos-premiados--lista d-flex flex-column mb-2 font-xs">

                                                        </div>
                                                        <?php if (count($cotas_premiadas_array_roleta) > 6) { ?>
                                                            <div id="cotas-container_roleta_mais">
                                                                <button class="btn_mais_roleta MuiButton-root MuiButton-text MuiButton-textPrimary MuiButton-sizeMedium MuiButton-textSizeMedium MuiButton-fullWidth MuiButtonBase-root  css-1mcv32d" tabindex="0" type="button"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-0 iconify iconify--mdi" sx="[object Object]" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                                                        <path fill="currentColor" d="M7.41 8.58L12 13.17l4.59-4.59L18 10l-6 6l-6-6z"></path>
                                                                    </svg>Mostrar mais<span class="MuiTouchRipple-root css-w0pj6f"></span></button>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                        <?php
                                                }
                                            }
                                        }
                                        ?>
                                        <?php
                                        if ($box) { ?>
                                            <div class="app-title mb-2 gap-2" style="align-items: flex-start;">

                                                <h1 style="font-size: 1.125rem;">🎁 Caixas premiadas</h1>
                                                <div class="app-title-desc">Instantâneas</div>

                                            </div>
                                            <div class="mb-3">
                                                <?php
                                                $count = 0;
                                                foreach ($boxs as $box) {
                                                    if ($box) { ?>
                                                        <div class="mb-2">
                                                            <?php if ($user_id) { ?>
                                                            
                                                                <div onclick="qtyRaffle(<?= $box['amount'] ?> + 1 , true)" class="pulsando btn w-100 text-center lh-1 bg-gradient-dark text-white" style="border-radius: 8px;">
                                                                <?php } else { ?>
                                                                    <div data-bs-toggle="modal" data-bs-target="#newCheckoutModal" onclick="qtyRaffle(<?= $box['amount'] ?> + 1, true)" class="pulsando btn w-100 text-center lh-1 bg-gradient-dark text-white" style="border-radius: 8px;">
                                                                    <?php } ?>
                                                                    <div class="row mb-1 font-xs">
                                                                        <div class="col text-start">
                                                                            <div class="mb-1"><span style="font-size: 0.75rem;font-weight: 400;line-height: 1.5">A partir de</div>
                                                                            <div class="mb-1"><span class="fs-6" style="font-size: 1rem;"><?= $box['amount'] ?></span> Títulos</div>
                                                                        </div>
                                                                        <div class="col-auto d-flex align-items-center">
                                                                            <div>Recebe <?= $box['qty'] ?> Caixa(s)</div>
                                                                            <div class="col-auto" style="font-size:30px">🎁</div>
                                                                        </div>
                                                                    </div>
                                                                    </div>
                                                                </div>
                                                            <?php } ?>
                                                        <?php
                                                        ++$count;
                                                    }
                                                        ?>

                                                        </div>
                                            </div>
                                        
                                           <?php
                                        }
                                        if ($box && $cotas_premiadas_box) {
                                            $cotas_premiadas_array_box = explode(',', $cotas_premiadas_box);
                                            foreach ($cotas_premiadas_array_box as $num) {
                                                if (empty($num)) {
                                                    continue;
                                                }

                                                $stmt = $conn->prepare('SELECT customer_id FROM order_list WHERE FIND_IN_SET(?, order_numbers) AND product_id = ? AND status = 2 ');
                                                $stmt->bind_param('si', $num, $id);
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                $row = $result->fetch_assoc();

                                                if ($result->num_rows > 0) {
                                                    $cotas_vendidas_box[] = ['cota' => $num, 'winner' => $row['customer_id']];
                                                }
                                            }
                                            if ($cotas_vendidas_box) {
                                                $all_lucky_numbers = array_column($cotas_vendidas_box, 'cota');
                                                $cotas_premiadas_all = $cotas_premiadas_array_box;
                                                $cotas_premiadas_sold_box = array_intersect($all_lucky_numbers, $cotas_premiadas_all);
                                            }
                                        ?>  
                                       
                                            <div class="app-title mb-2 justify-content-between gap-2">
                                                <div class="d-flex align-items-center">
                                                    <h1 style="font-size: 1.125rem;">🎁 Caixas Premiadas</h1>
                                                    <div class="app-title-desc"><?= $cotas_premiadas_descricao_box ? $cotas_premiadas_descricao_box : 'Instantâneos' ?></div>
                                                </div>
                                                <div>
                                                    <span class="css-dtzixn">
                                                        <h6 class="MuiTypography-root MuiTypography-h6 css-1uw6jz8"><?= $cotas_premiadas_sold_box ? count($cotas_premiadas_sold_box) : 0 ?></h6> / <?= count($cotas_premiadas_array_box) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php
                                            if ($available > 0 && $status == '1') {
                                                if ($cotas_premiadas_box) {
                                            ?>
                                                    <div class="my-3">

                                                        <div id="cotas-container_box" class="mais app-titulos-premiados--lista d-flex flex-column mb-2 font-xs">

                                                        </div>
                                                        <?php if (count($cotas_premiadas_array_box) > 6) { ?>
                                                            <div id="cotas-container_box_mais">
                                                                <button class="btn_mais_box MuiButton-root MuiButton-text MuiButton-textPrimary MuiButton-sizeMedium MuiButton-textSizeMedium MuiButton-fullWidth MuiButtonBase-root  css-1mcv32d" tabindex="0" type="button"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-0 iconify iconify--mdi" sx="[object Object]" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                                                        <path fill="currentColor" d="M7.41 8.58L12 13.17l4.59-4.59L18 10l-6 6l-6-6z"></path>
                                                                    </svg>Mostrar mais<span class="MuiTouchRipple-root css-w0pj6f"></span></button>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                            <?php
                                                }
                                            }
                                        }
                                    }

                                        if ($cotas_premiadas) {
                                            $cotas_premiada = explode(',', $cotas_premiadas);
                                        ?>
                                            <div class="app-promocao-numeros flex-column  mb-6 mt-6 ">
                                                <div class="sc-3f9a15f1-13 byugCZ" style="align-items:center;justify-content:space-between;">
                                                    <div style="display:flex; align-items:baseline;gap:4;">
                                                        <h5 style="font-size: 1.3em !important;color: rgba(var(--incrivel-rgbaInvert), 0.9);padding-right: 5px;font-weight: 600;margin: 0;"
                                                            class="sc-3f9a15f1-14 jQlWTy"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-1wit1pw iconify iconify--mdi" sx="[object Object]" width="25px" height="25px" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                                       <path fill="currentColor" d="m15.2 10.7l1.4 5.3l-4.6-3.8L7.4 16l1.4-5.2l-4.2-3.5L10 7l2-5l2 5l5.4.3zM14 19h-1v-3l-1-1l-1 1v3h-1c-1.1 0-2 .9-2 2v1h8v-1a2 2 0 0 0-2-2"></path></svg> Cotas Premiadas</h5>
                                                        <span
                                                            style="font-size: 12px;opacity:0.8">
                                                            Encontre e ganhe
                                                        </span>
                                                    </div>
                                                </div>
                                                <br>
                                                <?php
                                       if ($cotas_premiadas_descricao) {
                                       echo '
                                       <div class="app-card card mb-2">
                                        <div class="card-body text-center">
                                      <span style="font-size: 0.75rem; padding-left:2px;">' . 
                                        $cotas_premiadas_descricao . 
                                         '</span>
                                          </div>
                                          </div>';
                                             }
                                              ?>
                                             <div style="height: 1px !important" class="hr my-3"></div>                       
                                                <div id="cotas-container" class="iVtoES" style="padding:4px">

                                                    <div class="skeleton"></div>
                                                    <div class="hr"></div>
                                                    <div class="skeleton"></div>
                                                    <div class="hr"></div>
                                                    <div class="skeleton"></div>
                                                </div>



                                            </div>
                                        <?php
                                         }

                                        ?>

          <?php 
        if ($quantidade_auto_cota == 1 || $quantidade_auto_cota_diario == 1): ?>
            <div class="app-title mb-2 gap-2" style="align-items: flex-start;">
                <h1 style="font-size: 1.125rem; filter: blur(0px);">🔄  Bilhete Maior e Menor</h1>
                <div class="app-title-desc" style="filter: blur(0px);">Instantâneos</div>
            </div>

            <div class="card <?= $bgTheme ?> sc-3f9a15f1-2 eAApiE bottom-container rounded-3xl w-full relative  mb-6 mt-6" style="border: 2px solid transparent;border-image: linear-gradient(90deg, #198754, #0a3420) 1; border-radius: 4px; 
            overflow: hidden;
            -webkit-mask-image: radial-gradient(#fff, #000);  mask-image: radial-gradient(#fff, #000);">
                <div class="card-body ">
                    <?php if ($quantidade_auto_cota == 1): ?>
                        <div class="text-center">Geral</div>
                        <div class="d-flex justify-content-evenly align-items-center gap-2 text-center">
                            <div class="maior">
                                
                                <h4 style="text-align:center; font-size: 1em !important; margin-block:1rem"><strong>Menor Bilhete</strong></h4>
                                <div class="category-green btn btn-success mb-2" id="minor-cota">
                                    <div class="skeleton" style="width: 100%; display: inline-flex; height: 100%; border-radius: 10px; background-color: inherit !important;"></div>
                                </div>
                                <span class="mb-1" style="font-size: 16px" id="minor-winner">
                                    <div class="skeleton" style="display: inline-flex; width:  100% !important; min-width:75px"></div>
                                </span>
                                <span class="" style="font-size: 12px; opacity:0.8" id="minor-date">
                                    <div class="skeleton" style="display: inline-flex; width:  100% !important; min-width:75px"></div>
                                </span>
                            </div>
                            <div class="menor">
                                <h4 style="text-align:center; font-size: 1em !important; margin-block:1rem"><strong>Maior Bilhete</strong></h4>
                                <div class="category-green btn btn-success mb-2" id="major-cota">
                                    <div class="skeleton"
                                        style="width: 100%; display: inline-flex; height: 100%; border-radius: 10px; background-color: inherit !important;"></div>
                                </div>
                                <span class="mb-1" style="font-size: 16px" id="major-winner">
                                    <div class="skeleton" style="display: inline-flex; width:  100% !important; min-width:75px"></div>
                                </span>
                                <span class="" style="font-size: 12px; opacity:0.8" id="major-date">
                                    <div class="skeleton" style="display: inline-flex; width:  100% !important; min-width:75px"></div>
                                </span>
                            </div>
                        </div>
                        <hr>
                    <?php endif; ?>
                    <?php if ($quantidade_auto_cota_diario == 1): ?>
                        <!-- <div style="height: 1px !important" class="hr my-3"></div> -->
                        <div class="text-center">Hoje</div>
                        <?php if ($cota_diaria_ini != '0000-00-00 00:00:00' && $cota_diaria_fim != '0000-00-00 00:00:00') {

                            // Converte a data para um objeto DateTime
                            $data_ini = new DateTime($cota_diaria_ini);
                            $data_fim = new DateTime($cota_diaria_fim);

                            // Formata a data para o formato 'DD/MM/YYYY HH:MM:SS'
                            $data_formatada_ini = $data_ini->format('d/m/Y H:i:s');
                            $data_formatada_fim = $data_fim->format('d/m/Y H:i:s');

                        ?>
                            <div class="text-center  mb-1">Atenção esta geração de Maior e Menor Cota é contabilizado de <b><?= $data_formatada_ini ?></b> até <b><?= $data_formatada_fim ?></b></div>
                            <div class="text-center  fw-bolder">Promoção acaba em <button class="btn btn-sm btn-warning px-2 py-0 contadorseg" id="contadorseg"></button></div>
                        <?php } ?>
                        <div class="d-flex justify-content-evenly align-items-center gap-2 text-center">
                            <div class="maior ">
                                <h4 style="text-align:center; font-size: 1em !important; margin-block:1rem"><strong>Menor Bilhete</strong></h4>
                                <div class="category-green btn btn-success mb-2" id="minor-cota_today">
                                    <div class="skeleton" style="width: 100%; display: inline-flex; height: 100%; border-radius: 10px; background-color: inherit !important;"></div>
                                </div>
                                <span class=" mb-1" style="font-size: 16px" id="minor-winner_today">
                                    <div class="skeleton" style="display: inline-flex; width:  100% !important; min-width:75px"></div>
                                </span>
                                <span class="" style="font-size: 12px; opacity:0.8" id="minor-date_today">
                                    <div class="skeleton" style="display: inline-flex; width:  100% !important; min-width:75px"></div>
                                </span>
                            </div>
                            <div class="menor ">
                                <h4 style="text-align:center; font-size: 1em !important; margin-block:1rem"><strong>Maior Bilhete</strong></h4>
                                <div class="category-green btn btn-success mb-2" id="major-cota_today">
                                    <div class="skeleton"
                                        style="width: 100%; display: inline-flex; height: 100%; border-radius: 10px; background-color: inherit !important;"></div>
                                </div>
                                <span class=" mb-1" style="font-size: 16px" id="major-winner_today">
                                    <div class="skeleton" style="display: inline-flex; width:  100% !important; min-width:75px"></div>
                                </span>
                                <span class="" style="font-size: 12px; opacity:0.8" id="major-date_today">
                                    <div class="skeleton" style="display: inline-flex; width:  100% !important; min-width:75px"></div>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </div>

        <?php endif;
        ?>
        
                                     <?php if (false): ?><div class="mt-2 d-flex text-center justify-content-center mb-3">
    <div class="text-center">
        <center>
            🔗 
            <!-- Facebook -->
            <a class="btn btn-primary" style="background-color: #2760AE;border: none;font-size: 15px;"
                href="https://www.facebook.com/sharer/sharer.php?u=<?= BASE_URL ?>campanha/<?= $slug ?>" target="_blank"
                rel="noreferrer noopener" role="button"><i class="bi bi-facebook"></i></a>
            <!-- Telegram -->
            <a class="btn btn-primary" style="background-color: #2F9DDF;border: none;"
                href="https://telegram.me/share/url?url=<?= BASE_URL ?>campanha/<?= $slug ?>" text='<?= $name ?>' target="_blank" rel="noreferrer noopener"
                role="button"><i class="bi bi-telegram" style="font-size: 15px;"></i></a>
            <!-- Whatsapp -->
            <a class="btn btn-primary" style="background-color: #25d366;border: none;"
                href="https://api.whatsapp.com/send?text=<?= $name ?>%21%21%3A+<?= BASE_URL ?>campanha/<?= $slug ?>&type=custom_url&app_absent=0" target="_blank"
                rel="noreferrer noopener" role="button"><i class="bi bi-whatsapp" style="font-size: 15px;"></i></a>
            <!-- Twitter -->
            <a class="btn btn-primary" style="background-color: #34B3F7;border: none;"
                href="https://twitter.com/intent/tweet?text=<?= BASE_URL ?>campanha/<?= $slug ?>"
                target="_blank" rel="noreferrer noopener" role="button"><i class="bi bi-twitter"
                    style="font-size: 15px;"></i></a></h6>
        </center>

          </div>
       </div><?php endif; ?>
                                    
                                    <?php if ($status == '1' && $_settings->userdata('is_affiliate') == 1) {
                                    ?>
                                        <section style="margin-top: 20px;background-color:#0f121a "
                                            class=" rounded-3xl flex overflow-hidden w-full relative mb-6 bg-app-primary-latte">

                                            <div class="top-0 px-6 xl:px-12 py-6 xl:py-10 z-10 flex flex-col w-full items-center">

                                                <p style="color:white; margin-bottom:8px" class="font-bold text-base md:text-[32px] ">
                                                    Compartilhe com seus amigos
                                                </p>
                                                <p class="font-bold  md:text-[24px] " style="color:#157347; font-size:0.75rem; margin-bottom:16px ">
                                                    Ganhe comissões por cada venda!
                                                </p>
                                                <?php if ($_settings->userdata('id')) { ?>
                                                    <div data-bs-toggle="modal" data-bs-target="#modal-afiliado"
                                                        style="background-color:#157347; border-color:#157347;cursor:pointer;pointer-events:all; margin-inline:auto"
                                                        class="rounded-2xl py-2 px-3 text-caption bg-app-neutral-dark-1  hover:bg-app-neutral-dark-3 active:bg-app-neutral-dark-2  text-app-neutral-light-1 flex justify-around items-center  w-fit ">
                                                    <?php } else { ?>
                                                        <button data-bs-toggle="modal" data-bs-target="#loginModal"
                                                            style="background-color:#157347;color:#fff; border-color:#157347;cursor:pointer;pointer-events:all; "
                                                            class="rounded-2xl py-2 px-3 text-caption bg-app-neutral-dark-1  hover:bg-app-neutral-dark-3 active:bg-app-neutral-dark-2  text-app-neutral-light-1 flex justify-around items-center  w-fit ">
                                                        <?php } ?>


                                                        <p class="font-bold" style="margin-bottom:0">
                                                            <?php if ($_settings->userdata('id')) { ?>
                                                                Gerar link
                                                            <?php } else { ?>
                                                                Faça login para aproveitar
                                                            <?php } ?>

                                                        </p>
                                                        </button>
                                                    </div>
                                        </section>
                                    <?php
                                    } ?>
                                     <div class="modal fade" id="modal-premios">
                                        <div class="modal-dialog modal-dialog-centered modal-md">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">🏆 Top Compradores Diário</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                                                </div>
                                                
                                                <div class="modal-body">
                                                     
                                                 <p class="text-center"><small class="text-muted">Atualizado às <?= date('d/m/Y \à\s H:i') ?></small></p>
                                                    <?php if ($rankingTimerVisible): ?>
                                                        <div class="text-center text-white fw-bolder rounded py-2 px-3 mb-3" style="background:#198754">
                                                            Evento acontecendo!
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($enable_ranking_definido == 1 && false): ?>
                                                        <?php if ($ranking_ini != '0000-00-00 00:00:00' && $ranking_fim != '0000-00-00 00:00:00') {

                                                            // Converte a data para um objeto DateTime
                                                            $r_data_ini = new DateTime($ranking_ini);
                                                            $r_data_fim = new DateTime($ranking_fim);

                                                            // Formata a data para o formato 'DD/MM/YYYY HH:MM:SS'  
                                                            $r_data_formatada_ini = $r_data_ini->format('d/m/Y H:i:s');
                                                            $r_data_formatada_fim = $r_data_fim->format('d/m/Y H:i:s');

                                                        ?>
                                                            <div class="text-center text-dark mb-1">Atenção esta ranking é contabilizado de <br><b><?= $r_data_formatada_ini ?></b> até <b><?= $r_data_formatada_fim ?></b></div>
                                                           <div class="text-center d-flex justify-content-center align-items-center text-white fw-bolder btn btn-dark"><div>Ranking acaba em</div><button class="btn btn-sm btn-danger px-2 py-0 contadorsegranking ms-2" id="contadorsegranking"></button></div>

                                                    <?php }
                                                    endif;
                                                    ?>
                                                    <?php
                                                    $ranking_limit = 3;
                                                    $rankingConditions = $rankingTimerVisible
                                                        ? ranking_timer_sql_conditions('o', $rankingTimer, $conn)
                                                        : [
                                                            payment_ranking_datetime_sql('o') . " >= '" . $conn->real_escape_string($rankingWindowStart) . "'",
                                                            payment_ranking_datetime_sql('o') . " <= '" . $conn->real_escape_string($rankingWindowEnd) . "'",
                                                        ];
                                                    $requests = $conn->query(
                                                        'SELECT c.id, c.firstname, c.lastname, SUM(o.quantity) AS total_quantity ' .
                                                        'FROM order_list o ' .
                                                        'INNER JOIN customer_list c ON c.id = o.customer_id ' .
                                                        'WHERE o.product_id = ' . (int) $id . ' AND o.status = 2 AND ' . implode(' AND ', $rankingConditions) . ' ' .
                                                        'GROUP BY c.id, c.firstname, c.lastname ' .
                                                        'ORDER BY total_quantity DESC, c.firstname ASC, c.lastname ASC ' .
                                                        'LIMIT ' . $ranking_limit
                                                    );

                                                    $count = 0;

                                                    while ($row = $requests->fetch_assoc()) {
                                                        ++$count;
                                                        $ranking_name_parts = preg_split('/\s+/u', trim((string) $row['firstname']), -1, PREG_SPLIT_NO_EMPTY);
                                                        $ranking_first_name = $ranking_name_parts[0] ?? 'Cliente';

                                                        if ($count == 1) {
                                                            $medal = '1º 🥇';
                                                        } elseif ($count == 2) {
                                                            $medal = '2º 🥈';
                                                        } elseif ($count == 3) {
                                                            $medal = '3º 🥉';
                                                        } else {
                                                            $medal = '👤';
                                                        }

                                                    ?>
                                                        <div class="ranking-buyer-row">
                                                            <div class="ranking-buyer-medal"><?= $medal ?></div>
                                                            <div class="ranking-buyer-info">
                                                                <span class="ranking-buyer-name"><?= htmlspecialchars($ranking_first_name, ENT_QUOTES, 'UTF-8') ?></span>
                                                                <span class="ranking-buyer-total"><?= number_format((int) $row['total_quantity'], 0, ',', '.') ?> cotas</span>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    }

                                                    if ($count === 0) {
                                                        echo '<p class="text-center text-muted my-3">Ainda não há pagamentos confirmados hoje.</p>';
                                                    }

                                                    ?>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="color:#fff;max-height:100%" class="modal fade" tabindex="-1" id="modal-cotas">
                                        <div class="modal-dialog cotas">
                                            <div style="background-color:#343a40" class="modal-content">
                                                <div class="modal-header">
                                                    <div class="sc-3f9a15f1-13 byugCZ" style="gap:12px">

                                                        <div class="sc-3f9a15f1-28 kfFTzL line">🔥</div>

                                                        <h5 style="font-size: 1.3em !important;color: rgba(var(--incrivel-rgbaInvert), 0.9);padding-right: 5px;font-weight: 600;margin: 0;"
                                                            class="sc-3f9a15f1-14 jQlWTy">Cotas premiadas</h5>

                                                    </div>
                                                    <button type="button" class=" btn btn-link text-white menu-mobile--button pe-0 font-lgg" style=""
                                                        data-bs-dismiss="modal">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>

                                                <div class="modal-body" style="padding: 4px">
                                                    <div class="cotas_modal" style="padding:4px; height:100%">

                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="modal-afiliado" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                                        <div style="" class="modal-dialog modal-dialog-centered">

                                            <div class="modal-content">
                                                <div style="justify-content: space-between" class="modal-header">

                                                    <button style="z-index:99999999999999" type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <div class="congrats__details">
                                                    <div class="congrats__title h1">
                                                        Quase lá!
                                                    </div>
                                                    <div class="congrats__content"
                                                        style="align-items: center; display: flex; flex-direction: column; justify-content: center;">

                                                        Compartilhe seu link com todo mundo!

                                                        <button data-bs-toggle="modal" data-bs-target="#modal-afiliado-link"
                                                            style="background-color:#157347;color:#fff; border-color:#157347;cursor:pointer;pointer-events:all; margin-block: 10px;"
                                                            class="rounded-2xl py-2 px-3 text-caption bg-app-neutral-dark-1  hover:bg-app-neutral-dark-3 active:bg-app-neutral-dark-2  text-app-neutral-light-1 flex justify-around items-center  w-fit ">
                                                            <span class="text-caption">Compartilhar</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="modal-afiliado-link" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div style="justify-content: space-between" class="modal-header">

                                                    <button style="z-index:99999999999999" type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">

                                                    <div style="text-align: center; margin-top: -25px; " class="congrats__title h1">
                                                        Link gerado!
                                                    </div>

                                                    <div class="congrats__content"
                                                        style="align-items: center; display: flex; flex-direction: column; justify-content: center;">

                                                        Agora é só compartilhar!
                                                        <div class="share__field mt-4">
                                                            <input id="affiliate_url" class="share__input" type="text" name="site" disabled
                                                                value="<?php echo BASE_URL . '?&ref=' . $id; ?>">
                                                            <button class="share__copy" onclick="copyPix()">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                                    fill="currentColor" class="bi bi-copy icon icon-copy" viewBox="0 0 16 16">
                                                                    <path fill-rule="evenodd"
                                                                        d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z" />
                                                                </svg>
                                                            </button>
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php
                            if (isset($price)) {
                                $price_total_upsell = intval($qtd_upsell) * (floatval($price) - ((floatval($price) * floatval($desconto_upsell)) / 100));
                            }
                            ?>
                            <script>
                                $(function() {
                                    $('.btn-descricao').click(function() {
                                        $('.animation-r').toggleClass('rotate')
                                    })
                                    $('#add_to_cart').click(function() {
                                        add_cart();
                                    })
                                    $('#place_order').click(function() {
                                        var ref = $(this).attr("data-id");
                                        place_order(ref);
                                    })
                                    $(".addNumero").click(function() {
                                        let value = parseInt($(".qty").val());
                                        value++;
                                        $(".qty").val(value);
                                        calculatePrice(value);
                                    })
                                    $(".removeNumero").click(function() {
                                        let value = parseInt($(".qty").val());
                                        if (value <= 1) {
                                            value = 1;
                                        } else {
                                            value--;
                                        }
                                        $(".qty").val(value);
                                        calculatePrice(value);
                                    })

                                    function place_order($ref, queueAttempt) {
                                        queueAttempt = Number(queueAttempt || 0);
                                        $("#overlay").fadeIn(300);
                                        let valorUpsell = 0
                                        let qtdUpsell = 0
                                        if ($('#upsell').is(':checked')) {
                                            valorUpsell = parseFloat('<?= $price_total_upsell ?>');
                                            qtdUpsell = parseInt('<?= $qtd_upsell ?>')
                                        }
                                        $.ajax({
                                            url: _base_url_ + 'class/Main.php?action=place_order_process',
                                            method: 'POST',
                                            data: {
                                                ref: $ref,
                                                product_id: parseInt("<?= isset($id) ? $id : '' ?>"),
                                                valorUpsell: valorUpsell,
                                                qtdUpsell: qtdUpsell
                                            },
                                            dataType: 'json',
                                            timeout: 90000,
                                            error: (xhr, status) => {
                                                console.error(xhr)
                                                $("#overlay").stop(true, true).hide();
                                                alert(status === 'timeout'
                                                    ? 'O gateway demorou para responder. Tente novamente.'
                                                    : 'Não foi possível finalizar a compra. Tente novamente.');
                                            },
                                            success: function(resp) {
                                                console.log(resp)
                                                if (resp.status == 'success') {
                                                    location.replace(resp.redirect)
                                                } else if (resp.status == 'busy' && resp.retryable) {
                                                    if (queueAttempt >= 120) {
                                                        $("#overlay").stop(true, true).hide();
                                                        alert('O movimento está muito alto. Aguarde alguns instantes e tente novamente.');
                                                        return;
                                                    }
                                                    window.setTimeout(function() {
                                                        place_order($ref, queueAttempt + 1);
                                                    }, Number(resp.retry_after_ms || 600));
                                                } else if (resp.status == 'pay2m') {
                                                    $("#overlay").stop(true, true).hide();
                                                    alert(resp.error || 'Atualize seu cadastro para continuar.');
                                                    location.replace(resp.redirect)
                                                } else {
                                                    $("#overlay").stop(true, true).hide();
                                                    alert(resp.error || 'Não foi possível gerar o PIX. Tente novamente.');
                                                }
                                            },
                                            complete: function(xhr) {
                                                if (!xhr.responseJSON || !['success', 'busy'].includes(xhr.responseJSON.status)) {
                                                    $("#overlay").stop(true, true).hide();
                                                }
                                            }
                                        })
                                    }
                                })

                                function formatCurrency(total) {
                                    total = Number(total);
                                    if (!Number.isFinite(total)) {
                                        total = 0;
                                    }
                                    var decimalSeparator = ',';
                                    var thousandsSeparator = '.';
                                    var formattedTotal = total.toFixed(2); // Define 2 casas decimais
                                    // Substitui o ponto pelo separador decimal desejado
                                    formattedTotal = formattedTotal.replace('.', decimalSeparator);
                                    // Formata o separador de milhar
                                    var parts = formattedTotal.split(decimalSeparator);
                                    parts[0] = parts[0].replace(/\\B(?=(\\d{3})+(?!\\d))/g, thousandsSeparator);
                                    // Retorna o valor formatado
                                    return parts.join(decimalSeparator);
                                }

                                function calculatePrice(qty) {
                                    let price = '<?= $price ?>'
                                    let enable_sale = parseInt(<?= $enable_sale ?>);
                                    let sale_qty = parseInt(<?= $sale_qty ?>);
                                    let sale_price = <?= $sale_price ?>;
                                    let available = parseInt(<?= $available ?>);
                                    let total = price * qty;
                                    var max = parseInt(<?= isset($max_purchase) ? $max_purchase : '' ?>);
                                    var min = parseInt(<?= isset($min_purchase) ? $min_purchase : '' ?>);
                                    if (qty > available) {
                                        //calculatePrice(available);   
                                        //alert(\'Há apenas : \' + available + \' cotas disponíveis no momento.\');
                                        $('.aviso-content').html('Restam apenas ' + available + ' cotas disponíveis no momento.');
                                        $('#aviso_sorteio').click();
                                        $(".qty").val(available);
                                        //total = price * available;
                                        //$(\'#total\').html(\'R$ \'+formatCurrency(total)+\'\');
                                        calculatePrice(available);
                                        return;
                                    }
                                    if (qty < min) {
                                        // calculatePrice(min);   
                                        //alert(\'A quantidade mínima de cotas é de: \' + min + \'\');
                                        $('.aviso-content').html('A quantidade mínima de cotas é de: ' + min + '');
                                        //$(\'#aviso_sorteio\').click();
                                        $(".qty").val(min);
                                        total = price * min;
                                        calculatePrice(min);
                                        //$(\'#total\').html(\'R$ \'+formatCurrency(total)+\'\');
                                        return;
                                    }

                                    if (qty > max) {
                                        //alert(\'A quantidade máxima de cotas é de: \' + max + \'\');
                                        $('.aviso-content').html('A quantidade máxima de cotas é de: ' + max + '');
                                        //$(\'#aviso_sorteio\').click();
                                        $(".qty").val(max);
                                        total = price * max;
                                        calculatePrice(max);
                                        //$(\'#total\').html(\'R$ \'+formatCurrency(total)+\'\');
                                        return;
                                    }
                                    // Desconto acumulativo
                                    var qtd_desconto = parseInt(<?= $max_discount ?>);
                                    let dropeDescontos = [];
                                    for (i = 0; i < qtd_desconto; i++) {
                                        dropeDescontos[i] = {
                                            qtd: parseInt($(`#discount_qty_${i}`).text()),
                                            vlr: parseFloat($(`#discount_amount_${i}`).text())
                                        };
                                    }
                                    //console.log(dropeDescontos);
                                    var drope_desconto_qty = null;
                                    var drope_desconto = null;
                                    for (i = 0; i < dropeDescontos.length; i++) {
                                        if (qty >= dropeDescontos[i].qtd) {
                                            drope_desconto_qty = dropeDescontos[i].qtd;
                                            drope_desconto = dropeDescontos[i].vlr;
                                        }
                                    }
                                    var drope_desconto_aplicado = total;
                                    var desconto_acumulativo = false;
                                    var quantidade_de_numeros = drope_desconto_qty;
                                    var valor_do_desconto = drope_desconto;


                                    <?php
                                    if ($enable_cumulative_discount == 1) {
                                    ?>
                                        desconto_acumulativo = true;
                                    <?php
                                    }
                                    ?>
                                    if (desconto_acumulativo && qty >= quantidade_de_numeros) {
                                        var multiplicador_do_desconto = Math.floor(qty / quantidade_de_numeros);
                                        drope_desconto_aplicado = total - (valor_do_desconto * multiplicador_do_desconto);
                                    }
                                    // Aplicar desconto normal quando desconto acumulativo estiver desativado' .
                                    if (!desconto_acumulativo && qty >= drope_desconto_qty) {
                                        drope_desconto_aplicado = total - valor_do_desconto;
                                    }
                                    console.log(drope_desconto_qty)
                                    if (parseInt(qty) >= parseInt(drope_desconto_qty)) {
                                        $('#total').html('R$ ' + formatCurrency(drope_desconto_aplicado));
                                        $('.total').html('R$ ' + formatCurrency(drope_desconto_aplicado));
                                        $('.qtd').html(qty)
                                        $('.preco').html(formatCurrency(price))
                                    } else {
                                        console.log(valor_do_desconto)
                                        if (enable_sale == 1 && qty >= sale_qty) {
                                            total_sale = qty * sale_price;
                                            $('#total').html('De <strike>R$ ' + formatCurrency(total) + '</strike> por R$ ' + formatCurrency(total_sale));
                                            $('.total').html('De <strike>R$ ' + formatCurrency(total) + '</strike> por R$ ' + formatCurrency(total_sale));
                                            $('.qtd').html(qty)
                                            $('.preco').html(formatCurrency(price))
                                        } else {
                                            var desc = total - valor_do_desconto
                                            $('#total').html('R$ ' + formatCurrency(total));
                                            $('.total').html('R$ ' + formatCurrency(total));
                                            $('.qtd').html(qty)
                                            $('.preco').html(formatCurrency(price))
                                        }
                                    }
                                    //Fim desconto acumulativo
                                }

                                function qtyRaffle(qty, opt) {
                                    qty = parseInt(qty);
                                    let value = parseInt($(".qty").val());
                                    let qtyTotal = (value + qty);
                                    if (opt === true) {
                                        qtyTotal = (qtyTotal - value);
                                    }
                                    $(".qty").val(qtyTotal);
                                    calculatePrice(qtyTotal);
                                }

                                function add_cart() {
                                    let upsell = false
                                    if ($('#upsell').is(':checked')) {
                                        upsell = true;
                                    }
                                    let qty = $('.qty').val();

                                    $('#qty_cotas').text(qty);
                                    $.ajax({
                                        url: _base_url_ + "class/Main.php?action=add_to_card",
                                        method: "POST",
                                        data: {
                                            product_id: "<?= isset($id) ? $id : '' ?>",
                                            qty: qty,
                                            upsell: upsell
                                        },
                                        dataType: "json",
                                        error: err => {
                                            console.log(err)
                                            alert("NÃ£o foi possÃ­vel preparar a compra. Tente novamente.", 'error');

                                        },
                                        success: function(resp) {
                                            if (typeof resp == 'object' && resp.status == 'success') {
                                                //location.reload();
                                            } else if (!!resp.msg) {
                                                alert(resp.msg, 'error');
                                            } else if (!!resp.error) {
                                                alert(resp.error, 'error');
                                            } else {
                                                alert("NÃ£o foi possÃ­vel preparar a compra. Tente novamente.", 'error');
                                            }
                                        }
                                    })
                                }
                                $(document).ready(function() {
                                    $('.qty').on('keyup', function() {
                                        var value = parseInt($(this).val());
                                        var min = parseInt(<?= isset($min_purchase) ? $min_purchase : '' ?>);
                                        var max = parseInt(<?= isset($max_purchase) ? $max_purchase : '' ?>);
                                        if (value < min) {
                                            calculatePrice(min);
                                            //alert(\'A quantidade mínima de cotas é de: \' + min + \'\');
                                            $('.aviso-content').html('A quantidade mínima de cotas é de: ' + min + '');
                                            $('#aviso_sorteio').click();
                                            $(".qty").val(min);
                                        }
                                        if (value > max) {
                                            calculatePrice(max);
                                            //alert(\'A quantidade máxima de cotas é de: \' + max + \'\');
                                            $('.aviso-content').html('A quantidade máxima de cotas é de: ' + max + '');
                                            $('#aviso_sorteio').click();
                                            $(".qty").val(max);
                                        }
                                    });
                                });
                                $(document).ready(function() {
                                    $('#consultMyNumbers').submit(function(e) {
                                        e.preventDefault()
                                        var tipo = "<?= $search_type ?>"

                                        $.ajax({
                                            url: _base_url_ + "class/Main.php?action=" + tipo,
                                            method: 'POST',
                                            type: 'POST',
                                            data: new FormData($(this)[0]),
                                            dataType: 'json',
                                            cache: false,
                                            processData: false,
                                            contentType: false,
                                            error: err => {
                                                console.log(err)
                                                alert('An error occurred')
                                            },
                                            success: function(resp) {
                                                if (resp.status == 'success') {
                                                    location.href = (resp.redirect)
                                                } else {
                                                    alert('Nenhum registro de compra foi encontrado')
                                                    console.log(resp)
                                                }
                                            }
                                        })
                                    })
                                })
                            </script>
                            <script>
                                function copyPix() {
                                    var copyText = document.getElementById("affiliate_url");

                                    copyText.select();
                                    copyText.setSelectionRange(0, 99999);

                                    document.execCommand("copy");
                                    navigator.clipboard.writeText(copyText.value);

                                    alert("Link copiado com sucesso");
                                };
                                $(document).ready(function() {

                                    var cotas_array = '<?php echo isset($cotas_premiadas_premios) ? $cotas_premiadas_premios : ''; ?>';
                                    var product_id = parseInt("<?php echo isset($id) ? $id : ''; ?>");
                                    var cotas_premiadas = "<?php echo isset($cotas_premiadas) ? $cotas_premiadas : ''; ?>";
                                    var $quantidade_auto_cota = "<?php echo isset($quantidade_auto_cota) ? $quantidade_auto_cota : ''; ?>";
                                    if ($('#cotas-container').length) $.ajax({
                                        url: _base_url_ + "class/Main.php?action=load_cotas",

                                        method: 'POST',
                                        data: {
                                            product_id: product_id,
                                            cotas_premiadas: cotas_premiadas,
                                            cotas_array: cotas_array,
                                            quantidade_auto_cota: $quantidade_auto_cota
                                        },
                                        success: function(response) {
                                            var cotas = response.split('<div class="hr"></div>');
                                            var cotas_premiadas = cotas.slice(0, 3).join('<div class="hr"></div>');
                                            $('#cotas-container').html(cotas_premiadas);
                                            $('.cotas_modal').html(response);

                                        },
                                        error: function() {
                                            $('#cotas-container').html('<p>Erro ao carregar as cotas.</p>');
                                        }
                                    });
                                });
                                $(document).ready(function() {

                                    var cotas_array = '<?php echo isset($cotas_premiadas_premios_roleta) ? $cotas_premiadas_premios_roleta : ''; ?>';
                                    var product_id = parseInt("<?php echo isset($id) ? $id : ''; ?>");
                                    var cotas_premiadas = "<?php echo isset($cotas_premiadas_roleta) ? $cotas_premiadas_roleta : ''; ?>";
                                    var $quantidade_auto_cota = "<?php echo isset($quantidade_auto_cota) ? $quantidade_auto_cota : ''; ?>";
                                    if ($('#cotas-container_roleta').length) $.ajax({
                                        url: _base_url_ + "class/Main.php?action=load_cotas_roleta",
                                        method: 'POST',
                                        data: {
                                            product_id: product_id,
                                            cotas_premiadas: cotas_premiadas,
                                            cotas_array: cotas_array,
                                            quantidade_auto_cota: $quantidade_auto_cota
                                        },
                                        success: function(response) {
                                            console.log(response)
                                            var cotas = response.split('<div class="hr"></div>');
                                            var cotas_premiadas = cotas.slice(0, 3).join('<div class="hr"></div>');
                                            $('#cotas-container_roleta').html(cotas_premiadas);
                                        },
                                        error: function() {
                                            $('#cotas-container_roleta').html('<p>Erro ao carregar os bilhetes.</p>');
                                        }
                                    });
                                    $('.btn_mais_roleta').click(function() {
                                        $('#cotas-container_roleta').toggleClass("mais");

                                        if ($('#cotas-container_roleta').hasClass("mais")) {
                                            $(this).html(`
                                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-0 iconify iconify--mdi" sx="[object Object]" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                                    <path fill="currentColor" d="M7.41 8.58L12 13.17l4.59-4.59L18 10l-6 6l-6-6z"></path>
                                                </svg>
                                                Mostrar mais<span class="MuiTouchRipple-root css-w0pj6f"></span>
                                            `);
                                        } else {
                                            $(this).html(`
                                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-0 iconify iconify--mdi" sx="[object Object]" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                                    <path fill="currentColor" d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6l-6 6z"></path>
                                                </svg>
                                                
                                                Mostrar menos<span class="MuiTouchRipple-root css-w0pj6f"></span>
                                            `);
                                        }
                                    });
                                });
                                $(document).ready(function() {

                                    var cotas_array = '<?php echo isset($cotas_premiadas_premios_box) ? $cotas_premiadas_premios_box : ''; ?>';
                                    var product_id = parseInt("<?php echo isset($id) ? $id : ''; ?>");
                                    var cotas_premiadas = "<?php echo isset($cotas_premiadas_box) ? $cotas_premiadas_box : ''; ?>";
                                    var $quantidade_auto_cota = "<?php echo isset($quantidade_auto_cota) ? $quantidade_auto_cota : ''; ?>";
                                    if ($('#cotas-container_box').length) $.ajax({
                                        url: _base_url_ + "class/Main.php?action=load_cotas_box",
                                        method: 'POST',
                                        data: {
                                            product_id: product_id,
                                            cotas_premiadas: cotas_premiadas,
                                            cotas_array: cotas_array,
                                            quantidade_auto_cota: $quantidade_auto_cota
                                        },
                                        success: function(response) {
                                            var cotas = response.split('<div class="hr"></div>');
                                            var cotas_premiadas = cotas.slice(0, 3).join('<div class="hr"></div>');
                                            $('#cotas-container_box').html(cotas_premiadas);

                                        },
                                        error: function() {
                                            $('#cotas-container_box').html('<p>Erro ao carregar os bilhetes.</p>');
                                        }
                                    });
                                    $('.btn_mais_box').click(function() {
                                        $('#cotas-container_box').toggleClass("mais");

                                        if ($('#cotas-container_box').hasClass("mais")) {
                                            $(this).html(`
                                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-0 iconify iconify--mdi" sx="[object Object]" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                                    <path fill="currentColor" d="M7.41 8.58L12 13.17l4.59-4.59L18 10l-6 6l-6-6z"></path>
                                                </svg>
                                                Mostrar mais<span class="MuiTouchRipple-root css-w0pj6f"></span>
                                            `);
                                        } else {
                                            $(this).html(`
                                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-0 iconify iconify--mdi" sx="[object Object]" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                                    <path fill="currentColor" d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6l-6 6z"></path>
                                                </svg>
                                                
                                                Mostrar menos<span class="MuiTouchRipple-root css-w0pj6f"></span>
                                            `);
                                        }
                                    });
                                });
                                $(document).ready(function() {
                                    // Salva o elemento do header
                                    var header = $('.header-app-header.campanha .header-app-header-container');

                                    // Define a altura em que o scroll vai ser detectado
                                    var scrollTrigger = 50; // Altere para o valor que preferir (em pixels)

                                    // Adiciona um ouvinte de evento para o scroll
                                    $(window).on('scroll', function() {
                                        // Verifica a posição do scroll
                                        if ($(this).scrollTop() > scrollTrigger) {
                                            // Se o scroll estiver abaixo do valor de scrollTrigger, altera a cor de fundo
                                            header.css('background-color', '#00307a');
                                        } else {
                                            // Se o scroll estiver acima do valor de scrollTrigger, restaura a cor transparente
                                            header.css('background-color', 'transparent');
                                        }
                                    });
                                });
                                $(document).ready(function() {
                                    // Salva o elemento do header
                                    var header = $('.header-app-header.campanha .header-app-header-container');

                                    // Define a altura em que o scroll vai ser detectado
                                    var scrollTrigger = 50; // Altere para o valor que preferir (em pixels)

                                    // Adiciona um ouvinte de evento para o scroll
                                    $(window).on('scroll', function() {
                                        // Verifica a posição do scroll
                                        if ($(this).scrollTop() > scrollTrigger) {
                                            // Se o scroll estiver abaixo do valor de scrollTrigger, altera a cor de fundo
                                            header.css('background-color', '#00307a');
                                        } else {
                                            // Se o scroll estiver acima do valor de scrollTrigger, restaura a cor transparente
                                            header.css('background-color', 'transparent');
                                        }
                                    });
                                });
                            </script>
                            <?php if ($quantidade_auto_cota == 1) {
                            ?>
                                <script>
                                    $(document).ready(function() {
                                        var raffle = parseInt("<?php echo isset($id) ? $id : ''; ?>");
                                        $.ajax({
                                            url: _base_url_ + "class/Main.php?action=search_raffle_smallest_and_largest_number",
                                            method: 'POST',
                                            data: {
                                                raffle: raffle
                                            },
                                            success: function(response) {

                                                var data = JSON.parse(response);
                                                console.log(data)

                                                if (data.status == 'success') {
                                                    $('#major-cota').html(data.major.cota);
                                                    $('#major-winner').html(data.major.name);
                                                    $('#major-date').html(data.major.date);

                                                    $('#minor-cota').html(data.minor.cota);
                                                    $('#minor-winner').html(data.minor.name);
                                                    $('#minor-date').html(data.minor.date);

                                                }

                                            },
                                            error: function() {}
                                        });
                                    })
                                </script>
                            <?php
                            } ?>
                            <?php if ($quantidade_auto_cota_diario == 1) {
                            ?>
                                <script>
                                    $(document).ready(function() {
                                        var raffle = parseInt("<?php echo isset($id) ? $id : ''; ?>");
                                        $.ajax({
                                            url: _base_url_ + "class/Main.php?action=search_raffle_smallest_and_largest_number_today",
                                            method: 'POST',
                                            data: {
                                                raffle: raffle
                                            },
                                            success: function(response) {
                                                var data = JSON.parse(response);
                                                console.log(data)

                                                if (data.status == 'success') {
                                                    $('#major-cota_today').html(data.major.cota);
                                                    $('#major-winner_today').html(data.major.name);
                                                    $('#major-date_today').html(data.major.date);

                                                    $('#minor-cota_today').html(data.minor.cota);
                                                    $('#minor-winner_today').html(data.minor.name);
                                                    $('#minor-date_today').html(data.minor.date);

                                                }

                                            },
                                            error: function() {}
                                        });
                                    })
                                </script>
                            <?php
                            }  if ($enable_ranking_definido == 1) { ?>
                                <script>
                                    // Defina a data de fim - em formato de string (ex: '2025-02-02T12:00:00')
                                    var ranking_fim = '<?= $ranking_fim ?>';

                                    // Função para atualizar o contador
                                    function atualizarContadorRanking() {
                                        var contadorRanking = document.getElementById("contadorsegranking");
                                        if (!contadorRanking) {
                                            return;
                                        }
                                        // Data atual
                                        var dataAtual = new Date();

                                        // Data de fim
                                        var dataFim = new Date(ranking_fim);

                                        // Calculando a diferença em milissegundos
                                        var diferença = dataFim - dataAtual;

                                        if (diferença > 0) {
                                            // Calculando os dias, horas, minutos e segundos restantes
                                            var dias = Math.floor(diferença / (1000 * 60 * 60 * 24));
                                            var horas = Math.floor((diferença % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                            var minutos = Math.floor((diferença % (1000 * 60 * 60)) / (1000 * 60));
                                            var segundos = Math.floor((diferença % (1000 * 60)) / 1000);

                                            // Exibindo o contador no formato: "X dias, Y horas, Z minutos, W segundos"
                                            contadorRanking.innerHTML =
                                                "<i class='bi bi-alarm me-2'></i>" + dias + " dias, " + horas + ":" + minutos + ":" + segundos;
                                        } else {
                                            // Caso o tempo tenha expirado
                                            contadorRanking.innerHTML = "🔒 O ranking diário está fechado!";
                                        }
                                    }

                                    // Atualizar o contador a cada segundo
                                    setInterval(atualizarContadorRanking, 1000);

                                    // Chamar a função uma vez ao carregar a página
                                    atualizarContadorRanking();
                                </script>
                                <script>
 
  // Oculta o loader apenas depois de 2 segundos ao carregar a nova página
  window.addEventListener("load", function () {
    setTimeout(function () {
      document.getElementById("loadingSystem").style.display = "none";
    }, 2000); // 2000 milissegundos = 2 segundos
  });

  // Mostra o loader ao sair da página
  window.addEventListener("beforeunload", function () {
    document.getElementById("loadingSystem").style.display = "block";
  });
</script>
<?php if ($quantidade_auto_cota == 1) {
?>
    <script>
        $(document).ready(function() {
            var raffle = parseInt("<?php echo isset($id) ? $id : ''; ?>");
            $.ajax({
                url: _base_url_ + "class/Main.php?action=search_raffle_smallest_and_largest_number",
                method: 'POST',
                data: {
                    raffle: raffle
                },
                success: function(response) {

                    var data = JSON.parse(response);
                    console.log(data)

                    if (data.status == 'success') {
                        $('#major-cota').html(data.major.cota);
                        $('#major-winner').html(data.major.name);
                        $('#major-date').html(data.major.date);

                        $('#minor-cota').html(data.minor.cota);
                        $('#minor-winner').html(data.minor.name);
                        $('#minor-date').html(data.minor.date);

                    }

                },
                error: function() {}
            });
        })
    </script>
<?php
} ?>
<?php if ($quantidade_auto_cota_diario == 1) {
?>
    <script>
        $(document).ready(function() {
            var raffle = parseInt("<?php echo isset($id) ? $id : ''; ?>");
            $.ajax({
                url: _base_url_ + "class/Main.php?action=search_raffle_smallest_and_largest_number_today",
                method: 'POST',
                data: {
                    raffle: raffle,
                    data_ini: '<?= $cota_diaria_ini ?>',
                    data_fim: '<?= $cota_diaria_fim ?>'
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    console.log(data)
                    if (data.status == 'success') {
                        $('#major-cota_today').html(data.major.cota);
                        $('#major-winner_today').html(data.major.name);
                        $('#major-date_today').html(data.major.date);

                        $('#minor-cota_today').html(data.minor.cota);
                        $('#minor-winner_today').html(data.minor.name);
                        $('#minor-date_today').html(data.minor.date);

                    }

                },
                error: function() {}
            });
        })
    </script>
    <script>
        // Defina a data de fim - em formato de string (ex: '2025-02-02T12:00:00')
        var cota_diaria_fim = '<?= $cota_diaria_fim ?>';

        // Função para atualizar o contador
        function atualizarContador() {
            // Data atual
            var dataAtual = new Date();

            // Data de fim
            var dataFim = new Date(cota_diaria_fim);

            // Calculando a diferença em milissegundos
            var diferença = dataFim - dataAtual;

            if (diferença > 0) {
                // Calculando os dias, horas, minutos e segundos restantes
                var dias = Math.floor(diferença / (1000 * 60 * 60 * 24));
                var horas = Math.floor((diferença % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutos = Math.floor((diferença % (1000 * 60 * 60)) / (1000 * 60));
                var segundos = Math.floor((diferença % (1000 * 60)) / 1000);

                // Exibindo o contador no formato: "X dias, Y horas, Z minutos, W segundos"
                document.getElementById("contadorseg").innerHTML =
                    "<i class='bi bi-alarm me-2'></i>" + dias + " dias, " + horas + ":" + minutos + ":" + segundos;
            } else {
                // Caso o tempo tenha expirado
                document.getElementById("contadorseg").innerHTML = "❌ Sorteio finalizado!";
            }
        }

        // Atualizar o contador a cada segundo
        setInterval(atualizarContador, 1000);

        // Chamar a função uma vez ao carregar a página
        atualizarContador();
    </script>
<?php
}
if ($enable_ranking_definido == 1) { ?>
    <script>
        // Defina a data de fim - em formato de string (ex: '2025-02-02T12:00:00')
        var ranking_fim = '<?= $ranking_fim ?>';

        // Função para atualizar o contador
        function atualizarContadorRanking() {
            var contadorRanking = document.getElementById("contadorsegranking");
            if (!contadorRanking) {
                return;
            }
            // Data atual
            var dataAtual = new Date();

            // Data de fim
            var dataFim = new Date(ranking_fim);

            // Calculando a diferença em milissegundos
            var diferença = dataFim - dataAtual;

            if (diferença > 0) {
                // Calculando os dias, horas, minutos e segundos restantes
                var dias = Math.floor(diferença / (1000 * 60 * 60 * 24));
                var horas = Math.floor((diferença % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutos = Math.floor((diferença % (1000 * 60 * 60)) / (1000 * 60));
                var segundos = Math.floor((diferença % (1000 * 60)) / 1000);

                // Exibindo o contador no formato: "X dias, Y horas, Z minutos, W segundos"
                contadorRanking.innerHTML =
                    "<i class='bi bi-alarm me-2'></i>" + dias + " dias, " + horas + ":" + minutos + ":" + segundos;
            } else {
                // Caso o tempo tenha expirado
                contadorRanking.innerHTML = "❌ sorteio finalizado!";
            }
        }

        // Atualizar o contador a cada segundo
        setInterval(atualizarContadorRanking, 1000);

        // Chamar a função uma vez ao carregar a página
        atualizarContadorRanking();
    </script>
<?php }
?>


                                
                            <?php } ?>
