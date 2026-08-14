<?php
// Decodded

require_once 'sess_auth.php';

echo '<!DOCTYPE html>' . "\r\n" . '<html :class="{ \'theme-dark\': dark }" x-data="data()" lang="en" class="theme-dark">' . "\r\n\r\n" . '<head>' . "\r\n" . '  <meta charset="UTF-8" />' . "\r\n" . '  <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">' . "\r\n" . '  <title>' . "\r\n" . '    ';
$pageTitle = isset($_GET['page']) ? $_GET['page'] : '';
$siteName = $_settings->info('name');

switch ($pageTitle) {
    case 'products':
        echo 'Campanhas - ' . $siteName;
        break;
    case 'products/manage_product':
        echo 'Nova campanha - ' . $siteName;
        break;
    case 'orders':
        echo 'Pedidos - ' . $siteName;
        break;
    case 'report':
        echo 'Relatórios - ' . $siteName;
        break;
    case 'license':
        echo 'Licença - ' . $siteName;
        break;
    case 'orders/view_order':
        echo 'Visualizar pedido - ' . $siteName;
        break;
    case 'ranking':
        echo 'Ranking de compradores - ' . $siteName;
        break;
    case 'customers':
        echo 'Clientes - ' . $siteName;
        break;
    case 'customers/manage_customer':
        echo 'Editar usuário - ' . $siteName;
        break;
    case 'user/list':
        echo 'Usuários - ' . $siteName;
        break;
    case 'gateway':
        echo 'Gateway de pagamento - ' . $siteName;
        break;
    case 'system_info':
        echo 'Configuração - ' . $siteName;
        break;
    default:
        echo $siteName;
        break;
}

echo '  </title>' . "\r\n\r\n" . '  ';

if ($_settings->info('favicon')) {
    echo '    <link rel="shortcut icon" href="';
    echo validate_image($_settings->info('favicon'));
    echo '" />' . "\r\n" . '    <link rel="apple-touch-icon" sizes="180x180" href="';
    echo validate_image($_settings->info('favicon'));
    echo '">' . "\r\n" . '    <link rel="icon" type="image/png" sizes="32x32" href="';
    echo validate_image($_settings->info('favicon'));
    echo '">' . "\r\n" . '    <link rel="icon" type="image/png" sizes="16x16" href="';
    echo validate_image($_settings->info('favicon'));
    echo '">' . "\r\n\r\n" . '  ';
}

echo '<script>' . 'var _base_url_ =\'' . BASE_URL . '\';</script>';

echo '<script src="' . BASE_URL . 'admin/assets/js/focus-trap.js"></script>';

echo "\r\n" . '  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />' . "\r\n" . '  <link rel="stylesheet" href="';
echo BASE_URL;
echo 'admin/assets/css/tailwind.output.css" />' . "\r\n" . '  <link rel="stylesheet" href="';
echo BASE_URL;
echo 'admin/assets/css/style.css" />' . "\r\n" . '  <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>' . "\r\n" . '  <script src="';
echo BASE_URL;
echo 'admin/assets/js/init-alpine.js"></script>' . "\r\n" . '  <script src="';
echo BASE_URL;
echo 'libs/jquery/jquery.min.js"></script>' . "\r\n" . '  <script src="';
echo BASE_URL;

echo 'assets/js/admin.js"></script>' . "\r\n" . '  <script>' . "\r\n" . '    var _base_url2_ = \'';
echo BASE_URL;
echo '\';' . "\r\n" . '  </script>' . "\r\n" .

'	<link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.2/css/all.css">
'
. "\r\n" .

'</head>' . "\r\n\r\n" . '<body>' . "\r\n" . '  <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ \'overflow-hidden\': isSideMenuOpen}">' . "\r\n" . '    <!-- Desktop sidebar -->' . "\r\n" . '    <aside class="z-20 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block flex-shrink-0 relative">' . "\r\n" . '      <div class="py-4 text-gray-500 dark:text-gray-400">' . "\r\n" . '        <a style="display:inline-flex" class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="';
echo BASE_URL;
echo '" target="_blank">' . "\r\n" . '        ';
echo '<div style="display: flex; justify-content: center; align-items: center; height: 100px; overflow: hidden;width:145px; height: 55px;">';
echo '<img src="/logo.png" style="max-width: 50%;  margin: 0; padding: 0; display: block;">';
echo '</div>';
echo '        </a>' . "\r\n\r\n" . '        <ul class="">' . "\r\n" . '          ';

echo "\r\n" .
    '            <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a target="_parent" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./">' .
    "\r\n" .
    '<i class="fa-duotone w-6 fa-house text-lg"></i>' .
    "\r\n" .
    '                <span class="ml-4">Dashboard</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '     
	       <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a target="_parent" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=products">' .
    "\r\n" .
    '<i class="fa-duotone w-6 fa-table-list text-lg"></i>' .
    "\r\n" .
    '                <span class="ml-4">Campanhas</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '     
		   
		 
		 <li class="relative px-6 py-3 hidden">' .
    "\r\n" .
    '              <a target="_parent" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=products/maior">' .
    "\r\n" .
    '                <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"' .
    "\r\n" .
    '                  stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">' .
    "\r\n" .
    '                  <path' .
    "\r\n" .
    '                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">' .
    "\r\n" .
    '                  </path>' .
    "\r\n" .
    '                </svg>' .
    "\r\n" .
    '                <span class="ml-4">Maior e Menor</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    ' 
		 	        

		 
		 
		 
		   <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a target="_parent" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=orders">' .
    "\r\n" .
   '<i class="fa-duotone w-6 fa-cart-shopping text-lg"></i>' .
    "\r\n" .
    '                <span class="ml-4">Pedidos</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '            <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a target="_parent" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=report">' .
    "\r\n" .
		'<i class="fa-duotone w-6 fa-chart-simple text-lg"></i>' .
    "\r\n" .
    '                <span class="ml-4">Relatórios</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '            <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a target="_parent" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=ranking">' .
    "\r\n\r\n" .
   '<i class="fa-duotone w-6 fa-trophy text-lg"></i>' .
    "\r\n" .
    '                <span class="ml-4">Ranking</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '            <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a target="_parent" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=customers">' .
    "\r\n" .
   '<i class="fa-duotone w-6 fa-folder-user text-lg"></i>' .
    "\r\n" .
    '                <span class="ml-4">Clientes</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '            <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a target="_parent" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=affiliates">' .
    "\r\n" .
   '<i class="fa-duotone w-6 fa-users text-lg"></i>' .
    "\r\n" .
    '                <span class="ml-4">Afiliados</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    //novos links uniia

    '        
	
	
	
	<li class="relative px-6 py-3 hidden">' .
    "\r\n" .
    '              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=user/list">' .
    "\r\n\r\n" .
    '                <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"' .
    "\r\n" .
    '                  stroke="currentColor">' .
    "\r\n" .
    '                  <path' .
    "\r\n" .
    '                    d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />' .
    "\r\n" .
    '                  <path' .
    "\r\n" .
    '                    d="M8.256 14a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z" />' .
    "\r\n" .
    '                </svg>' .
    "\r\n" .
    '                <span class="ml-4">Usuários</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '            <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=gateway">' .
    "\r\n\r\n" .
   '<i class="fa-brands fa-pix text-lg"></i>' .
    "\r\n\r\n" .
    '                <span class="ml-4">Gateway de pagamento</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '            <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=system_info">' .
    "\r\n" .
   '<i class="fa-duotone w-6 fa-gear text-lg "></i>' .
    "\r\n" .
    '                <span class="ml-4">Configuração</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '            <li class="relative px-6 py-3">' .
    "\r\n" .
    '              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="./?page=admin_accounts">' .
    "\r\n" .
    '<i class="fa-duotone w-6 fa-user-shield text-lg"></i>' .
    "\r\n" .
    '                <span class="ml-4">Administradores</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '            <li class="relative px-6 py-3 hidden">' .
    "\r\n" .
    '              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                               href="./?page=cotas">' .
    "\r\n" .
    '                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trophy" viewBox="0 0 16 16">
  <path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5q0 .807-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33 33 0 0 1 2.5.5m.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935m10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935M3.504 1q.01.775.056 1.469c.13 2.028.457 3.546.87 4.667C5.294 9.48 6.484 10 7 10a.5.5 0 0 1 .5.5v2.61a1 1 0 0 1-.757.97l-1.426.356a.5.5 0 0 0-.179.085L4.5 15h7l-.638-.479a.5.5 0 0 0-.18-.085l-1.425-.356a1 1 0 0 1-.757-.97V10.5A.5.5 0 0 1 9 10c.516 0 1.706-.52 2.57-2.864.413-1.12.74-2.64.87-4.667q.045-.694.056-1.469z"/>
</svg>' .
    "\r\n" .
    '                <span class="ml-4">Cotas Premiadas</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '          ';

echo '          ' . "\r\n" . '          ';

echo "\r\n" . '        </ul>' . "\r\n" . '        <div  class="px-6 mt-2 ">' . "\r\n" . '          <a href="';
echo '#';
echo '" target="_blank">' .
    "\r\n" .
    '            <button' .
    "\r\n" .
    '              class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">' .
    "\r\n" .
    '              Suporte' .
    "\r\n" .
    '              <span class="ml-2" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"' .
    "\r\n" .
    '                  fill="currentColor" class="bi bi-wrench-adjustable-circle" viewBox="0 0 16 16">' .
    "\r\n" .
    '                  <path d="M12.496 8a4.491 4.491 0 0 1-1.703 3.526L9.497 8.5l2.959-1.11c.027.2.04.403.04.61Z" />' .
    "\r\n" .
    '                  <path' .
    "\r\n" .
    '                    d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0Zm-1 0a7 7 0 1 0-13.202 3.249l1.988-1.657a4.5 4.5 0 0 1 7.537-4.623L7.497 6.5l1 2.5 1.333 3.11c-.56.251-1.18.39-1.833.39a4.49 4.49 0 0 1-1.592-.29L4.747 14.2A7 7 0 0 0 15 8Zm-8.295.139a.25.25 0 0 0-.288-.376l-1.5.5.159.474.808-.27-.595.894a.25.25 0 0 0 .287.376l.808-.27-.595.894a.25.25 0 0 0 .287.376l1.5-.5-.159-.474-.808.27.596-.894a.25.25 0 0 0-.288-.376l-.808.27.596-.894Z" />' .
    "\r\n" .
    '                </svg></span>' .
    "\r\n" .
    '            </button>' .
    "\r\n" .
    '          </a>' .
    "\r\n" .
    '          <br>' .
    "\r\n" .
    '       <span style="position: absolute;z-index:99;bottom:4px; left:5%; right:25%; font-size:12px">   Versão ';
define('VERSION', 'Premium');
echo  VERSION;
echo '<br/>';
echo 'Atualizado em: 15/05/2025';
echo '      </span>  </div>' .
    "\r\n" .
    '      </div>' .
    "\r\n" .
    '    </aside>' .
    "\r\n" .
    '    <!-- Mobile sidebar -->' .
    "\r\n" .
    '    <!-- Backdrop -->' .
    "\r\n" .
    '    <div x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"' .
    "\r\n" .
    '      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"' .
    "\r\n" .
    '      x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"' .
    "\r\n" .
    '      x-transition:leave-end="opacity-0"' .
    "\r\n" .
    '      class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"></div>' .
    "\r\n" .
    '    <aside class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden"' .
    "\r\n" .
    '      x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"' .
    "\r\n" .
    '      x-transition:enter-start="opacity-0 transform -translate-x-20" x-transition:enter-end="opacity-100"' .
    "\r\n" .
    '      x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"' .
    "\r\n" .
    '      x-transition:leave-end="opacity-0 transform -translate-x-20" @click.away="closeSideMenu"' .
    "\r\n" .
    '      @keydown.escape="closeSideMenu">' .
    "\r\n" .
    '      <div class="py-4 text-gray-500 dark:text-gray-400">' .
    "\r\n" .
    '        <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="#">' .
    "\r\n" .
    '          ';
echo '<div style="display: flex; justify-content: center; align-items: center; height: 100px; overflow: hidden;">';
echo '<img src="/logo.png" style="max-width: 50%; height: auto; margin: 0; padding: 0; display: block;">';
echo '</div>';
echo '        </a>' .
    "\r\n" .
    '        <ul class="mt-6">' .
    "\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '              href="./">' .
    "\r\n" .
               '<i class="fa-duotone w-6 fa-house text-lg"></i>' .
    "\r\n" .
    '              <span class="ml-4">Dashboard</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    '          </li>' .
    "\r\n" .
    '        </ul>' .
    "\r\n" .
    '        <ul>' .
    "\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"' .
    "\r\n" .
    '              href="./?page=products">' .
    "\r\n" .
   '<i class="fa-duotone w-6 fa-table-list text-lg"></i>' .
    "\r\n" .
    '              <span class="ml-4">Campanhas</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    '          </li>' .
    "\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '              href="./?page=orders">' .
    "\r\n" .
'<i class="fa-duotone w-6 fa-cart-shopping text-lg"></i>' .
    "\r\n" .
    '              <span class="ml-4">Pedidos</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    '          </li>' .
    "\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '              href="./?page=report">' .
    "\r\n" .
 '<i class="fa-duotone w-6 fa-chart-simple text-lg"></i>' .
    "\r\n" .
    '              <span class="ml-4">Relatórios</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    '          </li>' .
    '<li class="relative px-6 py-3 hidden">' .
    "\r\n" .
    '              <a target="_parent" class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=products/maior">' .
    "\r\n" .
    '                <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"' .
    "\r\n" .
    '                  stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">' .
    "\r\n" .
    '                  <path' .
    "\r\n" .
    '                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">' .
    "\r\n" .
    '                  </path>' .
    "\r\n" .
    '                </svg>' .
    "\r\n" .
    '                <span class="ml-4">Maior e Menor</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
    '     
' .
    "\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '              href="./?page=ranking">' .
    "\r\n" .
  '<i class="fa-duotone w-6 fa-trophy text-lg"></i>' .
    "\r\n" .
    '              <span class="ml-4">Ranking</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    '          </li>' .
    "\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '              href="./?page=customers">' .
    "\r\n" .
  '<i class="fa-duotone w-6 fa-folder-user text-lg"></i>' .
    "\r\n" .
    '              <span class="ml-4">Clientes</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    '          </li>' .
    "\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '              href="./?page=affiliates">' .
    "\r\n" .
  '<i class="fa-duotone w-6 fa-users text-lg"></i>' .
    "\r\n" .
    '              <span class="ml-4">Afiliados</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    '          </li>' .
    "\r\n" .
    '          <li class="relative px-6 py-3 hidden">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '              href="./?page=user/list">' .
    "\r\n" .
    '              <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"' .
    "\r\n" .
    '                stroke="currentColor">' .
    "\r\n" .
    '                <path' .
    "\r\n" .
    '                  d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />' .
    "\r\n" .
    '                <path' .
    "\r\n" .
    '                  d="M8.256 14a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z" />' .
    "\r\n" .
    '              </svg>' .
    "\r\n" .
    '              <span class="ml-4">Usuários</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    ' <li class="relative px-6 py-3 hidden">' .
    "\r\n" .
    '              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                href="./?page=cotas">' .
    "\r\n" .
    '                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trophy" viewBox="0 0 16 16">
  <path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5q0 .807-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.777-2.833 3.011v2.173l1.425.356c.194.048.377.135.537.255L13.3 15.1a.5.5 0 0 1-.3.9H3a.5.5 0 0 1-.3-.9l1.838-1.379c.16-.12.343-.207.537-.255L6.5 13.11v-2.173c-.955-.234-2.043-1.146-2.833-3.012a3 3 0 1 1-1.132-5.89A33 33 0 0 1 2.5.5m.099 2.54a2 2 0 0 0 .72 3.935c-.333-1.05-.588-2.346-.72-3.935m10.083 3.935a2 2 0 0 0 .72-3.935c-.133 1.59-.388 2.885-.72 3.935M3.504 1q.01.775.056 1.469c.13 2.028.457 3.546.87 4.667C5.294 9.48 6.484 10 7 10a.5.5 0 0 1 .5.5v2.61a1 1 0 0 1-.757.97l-1.426.356a.5.5 0 0 0-.179.085L4.5 15h7l-.638-.479a.5.5 0 0 0-.18-.085l-1.425-.356a1 1 0 0 1-.757-.97V10.5A.5.5 0 0 1 9 10c.516 0 1.706-.52 2.57-2.864.413-1.12.74-2.64.87-4.667q.045-.694.056-1.469z"/>
</svg>' .
    "\r\n" .
    '                <span class="ml-4">Cotas Premiadas</span>' .
    "\r\n" .
    '              </a>' .
    "\r\n" .
    '            </li>' .
    '          </li>' .
    "\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '              href="./?page=gateway">' .
    "\r\n" .
  '<i class="fa-brands fa-pix text-lg"></i>' .
    "\r\n" .
    '              <span class="ml-4">Gateway de pagamento</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n\r\n" .
    '          </li>' .
    "\r\n\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '              href="./?page=system_info">' .
    "\r\n" .
   '<i class="fa-duotone w-6 fa-gear text-lg mr-4"></i>' .
    "\r\n" .
    '              <span>Configuração</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n\r\n" .
    '          </li>' .
    "\r\n\r\n" .
    '          <li class="relative px-6 py-3">' .
    "\r\n" .
    '            <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="./?page=admin_accounts">' .
    "\r\n" .
    '<i class="fa-duotone w-6 fa-user-shield text-lg mr-4"></i>' .
    "\r\n" .
    '              <span>Administradores</span>' .
    "\r\n" .
    '            </a>' .
    "\r\n" .
    '          </li>' .
    "\r\n\r\n" .
    '          ';

echo "\r\n" . '        </ul>' . "\r\n" . '        <div class="px-6 my-6">' . "\r\n" . '          <a href="';
echo '#';
echo '" target="_blank">' .
    "\r\n" .
    '            <button' .
    "\r\n" .
    '              class="flex items-center justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">' .
    "\r\n" .
    '              Suporte' .
    "\r\n" .
    '              <span class="ml-2" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"' .
    "\r\n" .
    '                  fill="currentColor" class="bi bi-wrench-adjustable-circle" viewBox="0 0 16 16">' .
    "\r\n" .
    '                  <path d="M12.496 8a4.491 4.491 0 0 1-1.703 3.526L9.497 8.5l2.959-1.11c.027.2.04.403.04.61Z" />' .
    "\r\n" .
    '                  <path' .
    "\r\n" .
    '                    d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0Zm-1 0a7 7 0 1 0-13.202 3.249l1.988-1.657a4.5 4.5 0 0 1 7.537-4.623L7.497 6.5l1 2.5 1.333 3.11c-.56.251-1.18.39-1.833.39a4.49 4.49 0 0 1-1.592-.29L4.747 14.2A7 7 0 0 0 15 8Zm-8.295.139a.25.25 0 0 0-.288-.376l-1.5.5.159.474.808-.27-.595.894a.25.25 0 0 0 .287.376l.808-.27-.595.894a.25.25 0 0 0 .287.376l1.5-.5-.159-.474-.808.27.596-.894a.25.25 0 0 0-.288-.376l-.808.27.596-.894Z" />' .
    "\r\n" .
    '                </svg></span>' .
    "\r\n" .
    '            </button>' .
    "\r\n" .
    '          </a>' .
    "\r\n" .
    '        </div>' .
    "\r\n" .
    '      </div>' .
    "\r\n" .
    '    </aside>' .
    "\r\n" .
    '    <div class="flex flex-col flex-1">' .
    "\r\n" .
    '      <header class="z-10 py-4 bg-white shadow-md dark:bg-gray-800">' .
    "\r\n" .
    '        <div' .
    "\r\n" .
    '          class="container flex items-center justify-between h-full px-6 mx-auto text-purple-600 dark:text-purple-300">' .
    "\r\n" .
    '          <!-- Mobile hamburger -->' .
    "\r\n" .
    '          <button class="p-1 -ml-1 mr-5 rounded-md md:hidden focus:outline-none focus:shadow-outline-purple"' .
    "\r\n" .
    '            @click="toggleSideMenu" aria-label="Menu">' .
    "\r\n" .
    '            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">' .
    "\r\n" .
    '              <path fill-rule="evenodd"' .
    "\r\n" .
    '                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"' .
    "\r\n" .
    '                clip-rule="evenodd"></path>' .
    "\r\n" .
    '            </svg>' .
    "\r\n" .
    '          </button>' .
    "\r\n" .
    '          <!-- Search input -->' .
    "\r\n" .
    '       
    
 <!-- Right navbar links -->
<ul class="navbar-nav ml-auto">
    <li class="nav-item">
    
        <a class="nav-link btn-custom" href="';
		echo BASE_URL;
		echo '" target="_blank">
            Ver site <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </a>
    </li>
</ul>

<style>
    .btn-custom {
        background-color: #6c2bd9; /* Cor verde */
        color: white; 
        padding: 1px 8px;
        border-radius: 5px; /* Bordas arredondadas de 5px */
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px; /* Espaço entre ícone e texto */
    }

    .btn-custom:hover {
        background-color: #7538e0; /* Cor verde mais escura no hover */
    }
</style>
    
<!-- /Right navbar links -->
    
    <div class="  flex justify-center flex-1 lg:mr-32">' .
    "\r\n" .
    '  <div style="gap:16px; align-items:baseline" class="p_message flex flex-wrap items-baseline ">
      <p style="color:#fff ;font-size:12px" class="text-sm leading-6  flex ">
        <strong class="font-semibold">' .
    $user_name .
    ' </strong><svg viewBox="0 0 2 2" style="margin-inline:8px"  class="mx-2 inline h-1 w-1 fill-current" aria-hidden
    
      </p>
      
    </div>' .
    "\r\n" .
    '          </div>' .
    "\r\n" .
    '          <ul class="flex items-center flex-shrink-0 space-x-6">' .
    "\r\n" .
    '            <!-- Theme toggler -->' .
    "\r\n" .
    '            <li class="flex">' .
    "\r\n" .
    '              <button class="rounded-md focus:outline-none focus:shadow-outline-purple" @click="toggleTheme"' .
    "\r\n" .
    '                aria-label="Toggle color mode">' .
    "\r\n" .
    '                <template x-if="!dark">' .
    "\r\n" .
    '                  <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">' .
    "\r\n" .
    '                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>' .
    "\r\n" .
    '                  </svg>' .
    "\r\n" .
    '                </template>' .
    "\r\n" .
    '                <template x-if="dark">' .
    "\r\n" .
    '                  <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">' .
    "\r\n" .
    '                    <path fill-rule="evenodd"' .
    "\r\n" .
    '                      d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"' .
    "\r\n" .
    '                      clip-rule="evenodd"></path>' .
    "\r\n" .
    '                  </svg>' .
    "\r\n" .
    '                </template>' .
    "\r\n" .
    '              </button>' .
    "\r\n" .
    '            </li>' .
    "\r\n" .
	
    '          </ul>' .
    "\r\n" .
    '          </template>' .
    "\r\n" .
    '          </li>' .
   
    "\r\n" .
    '          <!-- Profile menu -->' .
    "\r\n" .
    '          <li class="relative">' .
    "\r\n" .
    '            <button class="align-middle rounded-full focus:shadow-outline-purple focus:outline-none"' .
    "\r\n" .
    '              @click="toggleProfileMenu" @keydown.escape="closeProfileMenu" aria-label="Account" aria-haspopup="true">' .
    "\r\n" .
    '              <img class="object-cover w-8 h-8 rounded-full"' .
    "\r\n" .
    '                src="';
echo validate_image($_settings->userdata('avatar'));
echo '" alt="" aria-hidden="true" />' . "\r\n" . '            </button>' . "\r\n" . '            <template x-if="isProfileMenuOpen">' . "\r\n" . '              <ul x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"' . "\r\n" . '                x-transition:leave-end="opacity-0" @click.away="closeProfileMenu" @keydown.escape="closeProfileMenu"' . "\r\n" . '                class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700"' . "\r\n" . '                aria-label="submenu">' . "\r\n\r\n" . '                <li class="flex">' . "\r\n" . '                  <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"' . "\r\n" . '                    href="';
echo BASE_URL . 'admin/?page=user/manage_user&id=' . $_settings->userdata('id') . '';
echo '">' .
    "\r\n" .
  '<i class="fa-duotone fa-user text-lg mr-3 w-6"></i>' .
    "\r\n" .
    '                    <span>Minha conta</span>' .
    "\r\n" .
    '                  </a>' .
    "\r\n" .
    '                </li>' .
    "\r\n" .
    '                <li class="flex">' .
    "\r\n" .
    '                  <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                    href="./?page=user/list">' .
    "\r\n" .
   '<i class="fa-duotone w-6 fa-users text-lg mr-3 w-6"></i>' .
    "\r\n" .
    '                    <span>Usuários</span>' .
    "\r\n" .
    '                  </a>' .
    "\r\n" .
    '                </li>' .
    "\r\n" .
	'                <li class="flex">' .
    "\r\n" .
    '                  <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                    href="./?page=system_info">' .
    "\r\n" .
   '<i class="fa-duotone  fa-gear text-lg mr-3 w-6"></i>' .
    "\r\n" .
    '                    <span>Configuração</span>' .
    "\r\n" .
    '                  </a>' .
    "\r\n" .
    '                </li>' .
    "\r\n" .
    '                <li class="flex">' .
    "\r\n" .
    '                  <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"' .
    "\r\n" .
    '                    href="';
echo BASE_URL . 'class/Auth.php?action=logout';
echo '">' . "\r\n" . 
'<i class="fa-duotone fa-left-from-bracket text-lg mr-3 w-6"></i>'
. "\r\n" . '         
									              <span>Log out</span>' . "\r\n" . '                  </a>' . "\r\n" . '                </li>' . "\r\n" . '              </ul>' . "\r\n" . '            </template>' . "\r\n" . '          </li>' . "\r\n" . '          </ul>' . "\r\n" . '        </div>' . "\r\n" . '      </header>';

?>
