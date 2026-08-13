<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script src="https://kit.fontawesome.com/aa593cd0c8.js" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>

		const BASE_URL = '<?php echo BASE_URL; ?>';

		function showProductLinkPopup(slug) {
			const productLink = `${BASE_URL}campanha/${slug}`;

			Swal.fire({
				title: 'Link do Sorteio',
				html: `
				<div style="display: flex; flex-direction: column; align-items: center;">
					<input type="text" id="product-link" class="swal2-input" value="${productLink}" readonly style="width: 100%; max-width: 300px;">
					<button class="swal2-confirm swal2-styled" onclick="copyToClipboard()" style="width: 100%; max-width: 300px; margin-top: 10px;">Copiar Link</button>
					</div>
			`,
				showConfirmButton: false,
				customClass: {
					popup: 'swal2-popup-custom',
				},
				didOpen: () => {
					document.getElementById('product-link').focus();
				}
			});
		}

		function copyToClipboard() {
			const input = document.getElementById('product-link');
			input.select();
			input.setSelectionRange(0, 99999); /* For mobile devices */
			document.execCommand('copy');

			Swal.fire({
				icon: 'success',
				title: 'Link copiado!',
				text: 'O link do produto foi copiado para a área de transferência.',
				confirmButtonText: 'OK'
			});
		}

	</script>

	<style>
		.text-green-700 {
			--text-opacity: 1;
			color: #046c4e;
			color: rgba(4, 108, 78, var(--text-opacity));
		}

		.px-2 {
			padding-left: .5rem;
			padding-right: .5rem;
		}

		.py-1 {
			padding-top: .25rem;
			padding-bottom: .25rem;
		}

		.leading-tight {
			line-height: 1.25;
		}

		.font-semibold {
			font-weight: 600;
		}

		.rounded-full {
			border-radius: 9999px;
		}

		.bg-green-100 {
			--bg-opacity: 1;
			background-color: #def7ec;
			background-color: rgba(222, 247, 236, var(--bg-opacity));
		}

		@media (min-width: 768px) {
			.swal2-popup-custom {
				width: 400px !important;
			}
		}

		.space-x-4>:not(template)~:not(template) {
			--space-x-reverse: 0;
			margin-left: 4px;
			margin-right: 0;
		}

		.wrapper {
			display: inline-flex;
			list-style: none;
			height: 120px;
			width: 100%;
			padding-top: 40px;
			font-family: "Poppins", sans-serif;
			justify-content: center;
		}

		.wrapper .icon {
			position: relative;
			background: #fff;
			border-radius: 50%;
			margin: 10px;
			width: 50px;
			height: 50px;
			font-size: 18px;
			display: flex;
			justify-content: center;
			align-items: center;
			flex-direction: column;
			box-shadow: 0 10px 10px rgba(0, 0, 0, 0.1);
			cursor: pointer;
			transition: all 0.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
		}

		.wrapper .tooltip {
			position: absolute;
			top: 0;
			font-size: 14px;
			background: #fff;
			color: #fff;
			padding: 5px 8px;
			border-radius: 5px;
			box-shadow: 0 10px 10px rgba(0, 0, 0, 0.1);
			opacity: 0;
			pointer-events: none;
			transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
		}

		.wrapper .tooltip::before {
			position: absolute;
			content: "";
			height: 8px;
			width: 8px;
			background: #fff;
			bottom: -3px;
			left: 50%;
			transform: translate(-50%) rotate(45deg);
			transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
		}

		.wrapper .icon:hover .tooltip {
			top: -45px;
			opacity: 1;
			visibility: visible;
			pointer-events: auto;
			white-space: nowrap;
		}

		.wrapper .icon:hover span,
		.wrapper .icon:hover .tooltip {
			text-shadow: 0px -1px 0px rgba(0, 0, 0, 0.1);
		}

		.wrapper .facebook:hover,
		.wrapper .facebook:hover .tooltip,
		.wrapper .facebook:hover .tooltip::before {
			background: #1877f2;
			color: #fff;
		}

		.wrapper .twitter:hover,
		.wrapper .twitter:hover .tooltip,
		.wrapper .twitter:hover .tooltip::before {
			background: #20b998;
			color: #fff;
		}

		.wrapper .instagram:hover,
		.wrapper .instagram:hover .tooltip,
		.wrapper .instagram:hover .tooltip::before {
			background: #e4405f;
			color: #fff;
		}
	</style>




	<script>
		document.addEventListener("DOMContentLoaded", function () {
			// Seleciona todos os links com id 'duplicate-undefined'
			//  var links = document.querySelectorAll('<a href="#undefined" id="duplicate-undefined">Duplicar</a>');

			// Itera sobre os links e oculta aqueles com o texto 'Duplicar'
			// links.forEach(function(link) {
			//   if (link.textContent.trim() === 'Duplicar') {
			//     link.style.display = 'none';
			//}
			// });
		});
	</script>
</head>


<?php




$status = (isset($_GET['status']) ? $_GET['status'] : '1');
echo '</script><style>' . "\r\n" . '#myProgress{width:100%;background-color:#ddd}#myBar{height:12px;background-color:#4caf50;text-align:center;line-height:30px;color:#fff}.alert{position:relative;padding:.75rem 1.25rem;margin-bottom:1rem;border:1px solid transparent;border-radius:.25rem}.alert-danger{color:#721c24;background-color:#f8d7da;border-color:#f5c6cb}' . "\r\n" . '</style>' . "\r\n" . '<main class="h-full pb-16 overflow-y-auto">' . "\r\n\t" . '<div class="container grid px-6 mx-auto">' . "\r\n\t\t" . '<h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Sorteios' . "\r\n\t\t" . '<a href="./?page=products/manage_product" id="create_new">' . "\r\n\t\t\t" . '<button class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">' . "\r\n\t\t\t\t" . 'Criar novo' . "\r\n\t\t\t" . '</button>' . "\r\n\t\t" . '</a>' . "\r\n\t\t" . '</h2>' . "\r\n\t\t" . '<form action="" id="filter-form" style="margin-bottom:10px" method="GET">' . "\r\n\t\t" . '<div class="flex filtro-busca">' . "\r\n\t\t\t" . '<select name="status" id="status" class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">' . "\r\n\t\t\t\t" . '<option value="">Todos os status</option>' . "\r\n\t\t\t\t" . '<option value="1" ';

if ($status == '1') {
	echo 'selected';
}

echo '>Ativas</option>' . "\r\n\t\t\t\t" . '<option value="2" ';

if ($status == '2') {
	echo 'selected';
}

echo '>Pausadas</option>' . "\r\n\t\t\t\t" . '<option value="3" ';

if ($status == '3') {
	echo 'selected';
}

echo '>Finalizadas</option>' . "\r\n\t\t\t" . '</select>' . "\r\n\t\t\t" . '<button class="px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple filtrar"> Filtrar</button>' . "\r\n\t\t" . '</div>' . "\r\n\t" . '</form>' . "\t\r\n\t" . '<div class="w-full overflow-hidden rounded-lg shadow-xs">' . "\r\n\t\t" . '<div class="w-full overflow-x-auto">' . "\r\n\t\t\t" .



	'<table class="w-full whitespace-no-wrap">' . "\r\n\t\t\t\t" . '<thead>' . "\r\n\t\t\t\t\t" . '<tr' . "\r\n\t\t\t\t\t" . 'class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"' . "\r\n\t\t\t\t\t" . '>' . "\r\n\t\t\t\t\t" . '<th class="px-4 py-3">Sorteio</th>' . "\r\n\t\t\t\t\t" . '<th class="px-4 py-3">Tipo</th>' . "\r\n\t\t\t\t\t" . '<th class="px-4 py-3">Valor</th>' . "\r\n\t\t\t\t\t" . '<th class="px-4 py-3">Qtd. Números</th>' . "\r\n\t\t\t\t\t" . '<th class="px-4 py-3">Status</th>' . "\r\n\t\t\t\t\t" . '<th class="px-4 py-3">Data</th>' . "\r\n\t\t\t\t\t" . '<th class="px-4 py-3">Ação</th>' . "\r\n\t\t\t\t" . '</tr>' . "\r\n\t\t\t" . '</thead>' . "\r\n\t\t\t" . '<tbody' . "\r\n\t\t\t" . 'class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"' . "\r\n\t\t\t" . '>' . "\r\n\t\t\t";
$perPage = 10;
$page = (isset($_GET['pg']) ? $_GET['pg'] : 1);
$offset = $perPage * ($page - 1);
$i = 1;
$where = '';

if ($status) {
	$where .= ' AND status = \'' . $status . '\'';
}

if (!empty($where)) {
	$where = ' WHERE ' . ltrim($where, ' AND');
}

$qry = $conn->query('SELECT * from `product_list` ' . $where . ' ORDER BY id DESC LIMIT ' . $perPage . ' OFFSET ' . $offset);
$totalResults = $conn->query('SELECT id FROM product_list ' . $where)->num_rows;
$totalPages = ceil($totalResults / $perPage);


while ($row = $qry->fetch_assoc()) {
	$qry2 = $conn->query('SELECT SUM(quantity) FROM order_list WHERE product_id = ' . $row['id'] . ' AND status <> 3');
	$row2 = $qry2->fetch_assoc();
	$quantityy = $row2['SUM(quantity)'];
	$percent = ($row2['SUM(quantity)'] * 100) / $row['qty_numbers'];
	$percent = number_format($percent, 2, '.', '');
	echo "\t\t\t\t" . '<tr class="text-gray-700 dark:text-gray-400">' . "\r\n\t\t\t\t\t" . '<td class="px-4 py-3">' . "\r\n\t\t\t\t\t\t" . '<div class="flex items-center text-sm">' . "\r\n\t\t\t\t\t\t\t" . '<!-- Avatar with inset shadow -->' . "\r\n\t\t\t\t\t\t\t" . '<div' . "\r\n\t\t\t\t\t\t\t" . 'class="relative hidden w-8 h-8 mr-3 rounded-full md:block"' . "\r\n\t\t\t\t\t\t\t" . '>' . "\r\n\t\t\t\t\t\t\t" . '<img' . "\r\n\t\t\t\t\t\t\t" . 'class="object-cover w-full h-full rounded-full"' . "\r\n\t\t\t\t\t\t\t" . 'src="';
	echo validate_image($row['image_path']);
	echo '"' . "\r\n\t\t\t\t\t\t\t" . 'alt=""' . "\r\n\t\t\t\t\t\t\t" . 'loading="lazy"' . "\r\n\t\t\t\t\t\t\t" . '/>' . "\r\n\t\t\t\t\t\t\t" . '<div' . "\r\n\t\t\t\t\t\t\t" . 'class="absolute inset-0 rounded-full shadow-inner"' . "\r\n\t\t\t\t\t\t\t" . 'aria-hidden="true"' . "\r\n\t\t\t\t\t\t\t" . '></div>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t\t" . '<div>' . "\r\n\t\t\t\t\t\t\t" . '<p class="font-semibold">';
	echo $row['name'];
	echo '</p>' . "\r\n\t\t\t\t\t\t\t" . '<p class="text-xs text-gray-600 dark:text-gray-400">' . "\r\n\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t" . '</p>' . "\r\n\t\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t\t" . '</div>' . "\r\n\t\t\t\t" . '</td>' . "\r\n\t\t\t\t" . '<td class="px-4 py-3 text-sm">' . "\r\n\t\t\t\t\t";

	if ($row['type_of_draw'] == 1) {
		echo "\t\t\t\t\t\t" . 'Automático' . "\r\n\t\t\t\t\t";
	}

	echo "\r\n\t\t\t\t\t";

	if ($row['type_of_draw'] == 2) {
		echo "\t\t\t\t\t\t" . 'Números' . "\r\n\t\t\t\t\t";
	}

	echo "\r\n\t\t\t\t\t";

	if ($row['type_of_draw'] == 3) {
		echo "\t\t\t\t\t\t" . 'Fazendinha' . "\r\n\t\t\t\t\t";
	}

	echo "\t\t\t\t\t";

	if ($row['type_of_draw'] == 4) {
		echo "\t\t\t\t\t\t" . 'Fazendinha metade' . "\r\n\t\t\t\t\t";
	}

	echo "\t\t\t\t" . '</td>' . "\r\n\r\n\t\t\t\t" . '<td class="px-4 py-3 text-sm">' . "\r\n\t\t\t\t\t" . '<div border-radius: 8px; padding: 5px; display: inline-block;">R$ ';
	echo format_num($row['price'], 2);
	echo '</div>' . "\t\t\t\t" . '</td>' . "\r\n\r\n\t\t\t\t" . '<td class="px-4 py-3 text-sm">' . "\r\n\t\t\t\t\t" . '<div id="myProgress" style=" height: 12px; border-radius: 12px; overflow: hidden; background-color: #f3f3f3;">' . "\r\n" . '                    <div id="myBar" style="width:';
	echo $percent;
	echo '%; height: 100%; border-radius: 12px; background-color: #6C2BD9;"></div>' . "\r\n" . '                    </div>' . "\r\n\t\t\t\t\t";
	echo $percent;
	echo '% de ';
	echo $row['qty_numbers'];
	echo ' vendidos' . "\r\n\t\t\t\t" . '</td>' . "\r\n\r\n\t\t\t\t" . '<td class="px-4 py-3 text-xs">' . "\r\n\t\t\t\t\t";



	if ($row['status'] == 1) {
		echo "\t\t\t\t\t\t" . '<span' . "\r\n\t\t\t\t\t\t" . 'class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full  dark:text-green-700"' . "\r\n\t\t\t\t\t\t" . 'style=" border-color: #2F855A;"' . "\r\n\t\t\t\t\t\t" . '>' . "\r\n\t\t\t\t\t\t" . '● Ativo' . "\r\n\t\t\t\t\t" . '</span>' . "\r\n\t\t\t\t";
	}

	echo "\r\n\t\t\t\t";

	if ($row['status'] == 2) {
		echo "\t\t\t\t\t" . '<span class="px-2 py-1 font-semibold leading-tight text-red-700 border border-red-700 rounded-full"' . "\r\n\t\t\t\t\t\t" . 'style="background-color: transparent; border-color: #C53030;"' . "\r\n\t\t\t\t\t\t" . '>' . "\r\n\t\t\t\t\t\t" . 'Pausado' . "\r\n\t\t\t\t\t" . '</span>' . "\t\t\t" . '  ' . "\r\n\r\n\t\t\t\t";
	}




	echo "\r\n\t\t\t\t";

	if ($row['status'] == 3) {
		echo "\t\t\t\t\t" . '<span' . "\r\n\t\t\t\t\t" . 'class="px-2 py-1 font-semibold leading-tight text-orange-700 bg-orange-100 rounded-full dark:text-white dark:bg-orange-600"' . "\r\n\t\t\t\t\t" . '>' . "\r\n\t\t\t\t\t" . 'Finalizado' . "\r\n\t\t\t\t" . '</span>' . "\r\n\t\t\t";
	}

	echo "\t\t" . '</td>' . "\r\n\t\t" . '<td class="px-4 py-3 text-sm">' . "\r\n\t\t\t";
	echo date('d/m/Y', strtotime($row['date_created'])) . "<br>às " . date('H:i', strtotime($row['date_created']));


	echo "\t\t" . '</td>' . "\r\n\t\t" . '<td class="px-4 py-3">' . "\r\n\t\t\t" . '<div class="flex items-center space-x-4 text-sm">' . "\r\n\t\t\t" . '' . "\r\n\t\t\t" . '<a href="./report.php?id=';
	echo $row['id'];



	echo '" target="_blank">' . "\r\n\t\t\t\t\t" . '<button title="Relatório de vendas" ' . "\r\n\t\t\t\t\t" . 'class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"' . "\r\n\t\t\t\t\t" . 'aria-label="Relatório de vendas"' . "\r\n\t\t\t\t\t" . '>' . "\r\n\t\t\t\t\t" . '<i class="fa-duotone  fa-chart-simple" style="color: #5827AC;font-size:22px"></i>' . "\r\n\t\t\t\t" . '</button>' . "\r\n\t\t\t" . '</a>' . "\r\n\t\t\t" . '' . "\r\n\t\t\t" .


		'<a id="view-draw" ';


	echo '" target="_blank">' . "\r\n\t\t\t\t" .


		'<button onclick="showProductLinkPopup(\'' . $row['slug'] . '\')" title="Compartilhar" ' . "\r\n\t\t\t\t" . 'class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"' . "\r\n\t\t\t\t" . 'aria-label="View">' . "\r\n\t\t\t\t" . '<i class="fa-duotone  fa-share-nodes" style="color: #5827AC;font-size:22px"></i>' . "\r\n\t\t\t" . '</button>'





		. "\r\n\t\t" . '</a>' . "\r\n\t\t" .







		'<a href="./?page=products/manage_product&id=';
	echo $row['id'];
	echo '">' . "\r\n\t\t\t" . '<button title="Editar" ' . "\r\n\t\t\t" . 'class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"' . "\r\n\t\t\t" . 'aria-label="Edit">' . "\r\n\t\t\t\t" . '    <i class="fa-duotone  fa-pen-to-square" style="color: #5827AC;font-size:22px"></i> 

		
' . "\r\n\t\t\t" .


		'</button>' . "\r\n\t\t" . '</a>	
' . "\r\n\t\t" .



		'' . "\r\n\t\t" . "\r\n\t\t\t" .




















		'<a
		  						class="duplicate"
		  data-id="';
	echo $row['id'];


	echo '" href="#" ';
	echo '>' . "\r\n\t\t\t" .


		'<button title="Duplicar" ' . "\r\n\t\t\t" . 'class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg  focus:outline-none focus:shadow-outline-gray"' . "\r\n\t\t\t" . 'aria-label="Corrigir duplicidades">' . "\r\n\t\t\t\t" . '<i class="fa-duotone fa-circle-plus" style="font-size:22px"></i>' . "\r\n\t\t\t" . '</button>'



		. "\r\n\t\t" . '</a>' . "\r\n\t\t" .
		'<a class="stock" data-id="';
	echo $row['id'];









	echo '">' . "\r\n\t\t\t	" . "\r\n\t\t\t\t" . '' . "\r\n\t\t\t\t\t" . '' . "\r\n\t\t\t\t\t" . '' . "\r\n\t\t\t\t" . '</svg>' . "\r\n\t\t\t" . '</button>' . "\r\n\t\t" . '</a>' . "\r\n\t\t" . '' . "\r\n\t\t";



	if ($_settings->userdata('type') == '1') {
		echo "\t\t" . '<a class="delete_sorteio" href="javascript:void(0)" @click="openModal" data-id="';
		echo $row['id'];
		echo '">' . "\r\n\t\t\t" . '<button title="Deletar" ' . "\r\n\t\t\t" . 'class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-red-600 rounded-lg dark:text-red-600 focus:outline-none focus:shadow-outline-gray" aria-label="Delete">' . "\r\n\t\t\t" . '<i class="fa-solid fa-trash-can" style="font-size:20px"></i>
		
' . "\r\n\t\t" . '</button>' . "\r\n\t\t" . '</a>' . "\r\n";
	}

	echo "\r\n" . '</div>' . "\r\n" . '</td>' . "\r\n" . '</tr>' . "\r\n";
}

echo "\r\n" . '</tbody>' . "\r\n" . '</table>';
?>
</div>

<div
	class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
	<span class="flex items-center col-span-3"></span>
	<span class="col-span-2"></span>

	<!-- Pagination -->
	<span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end">
		<nav aria-label="Table navigation">
			<ul class="inline-flex items-center">
				<?php
				$totalPages = ceil($totalResults / $perPage);

				if ($page > 1) {
					echo "\t\t\t\t\t" . '<a href=\'./?page=products&status=';
					echo $status;
					echo '&pg=';
					echo $page - 1;
					echo '\'><li>' . "\r\n\t\t\t\t\t\t" . '<button' . "\r\n\t\t\t\t\t\t" . 'class="px-3 py-1 rounded-md rounded-l-lg focus:outline-none focus:shadow-outline-purple"' . "\r\n\t\t\t\t\t\t" . 'aria-label="Previous"' . "\r\n\t\t\t\t\t\t" . '>' . "\r\n\t\t\t\t\t\t" . '<svg' . "\r\n\t\t\t\t\t\t" . 'class="w-4 h-4 fill-current"' . "\r\n\t\t\t\t\t\t" . 'aria-hidden="true"' . "\r\n\t\t\t\t\t\t" . 'viewBox="0 0 20 20"' . "\r\n\t\t\t\t\t\t" . '>' . "\r\n\t\t\t\t\t\t" . '<path' . "\r\n\t\t\t\t\t\t" . 'd="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"' . "\r\n\t\t\t\t\t\t" . 'clip-rule="evenodd"' . "\r\n\t\t\t\t\t\t" . 'fill-rule="evenodd"' . "\r\n\t\t\t\t\t\t" . '></path>' . "\r\n\t\t\t\t\t" . '</svg>' . "\r\n\t\t\t\t" . '</button>' . "\r\n\t\t\t" . '</li></a>' . "\r\n\t\t";
				}

				echo "\r\n\t\t";

				if (3 < $page) {
					echo "\t\t\t" . '<a href="./?page=products&status=';
					echo $status;
					echo '&pg=1"><li><button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">1</button></li></a>' . "\r\n\t\t\t" . '<li class="dots">...</li>' . "\r\n\t\t";
				}

				echo "\r\n\t\t";

				if (0 < ($page - 2)) {
					echo "\t\t\t" . '<a href="./?page=products&status=';
					echo $status;
					echo '&pg=';
					echo $page - 2;
					echo '"><li><button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">';
					echo $page - 2;
					echo '</button></li></a>' . "\r\n\t\t";
				}

				echo "\r\n\t\t";

				if (0 < ($page - 1)) {
					echo "\t\t\t" . '<a href="./?page=products&status=';
					echo $status;
					echo '&pg=';
					echo $page - 1;
					echo '"><li><button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">';
					echo $page - 1;
					echo '</button></li></a>' . "\r\n\t\t";
				}

				echo "\r\n\t\t" . '<a href="./?page=products&status=';
				echo $status;
				echo '&pg=';
				echo $page;
				echo '">' . "\r\n\t\t\t" . '<li>' . "\r\n\t\t\t\t" . '<button' . "\t" . 'class="px-3 py-1 text-white transition-colors duration-150 bg-purple-600 border border-r-0 border-purple-600 rounded-md focus:outline-none focus:shadow-outline-purple">';
				echo $page;
				echo '</button>' . "\r\n\t\t\t" . '</li>' . "\r\n\t\t" . '</a>' . "\r\n\t\t";

				if (($page + 1) < ($totalPages + 1)) {
					echo "\t\t\t" . '<a href="./?page=products&status=';
					echo $status;
					echo '&pg=';
					echo $page + 1;
					echo '"><li><button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">';
					echo $page + 1;
					echo '</button></li></a>' . "\t\r\n\t\t";
				}

				echo "\r\n\t\t";

				if (($page + 2) < ($totalPages + 1)) {
					echo "\t\t\t" . '<a href="./?page=products&status=';
					echo $status;
					echo '&pg=';
					echo $page + 2;
					echo '"><li><button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">';
					echo $page + 2;
					echo '</button></li></a>' . "\r\n\t\t";
				}

				echo "\r\n\t\t";

				if ($page < ($totalPages - 2)) {
					echo "\t\t\t" . '<li class="dots">...</li>' . "\r\n\t\t\t" . '<a href="./?page=products&status=';
					echo $status;
					echo '&pg=';
					echo $totalPages;
					echo '"><li><button class="px-3 py-1 rounded-md focus:outline-none focus:shadow-outline-purple">';
					echo $totalPages;
					echo '</button></li></a>' . "\r\n\t\t";
				}

				echo "\r\n\r\n\t\t";

				if ($page < $totalPages) {
					echo "\t\t\t" . '<a href="./?page=products&status=';
					echo $status;
					echo '&pg=';
					echo $page + 1;
					echo '"><li>' . "\r\n\t\t\t\t" . '<button' . "\r\n\t\t\t\t" . 'class="px-3 py-1 rounded-md rounded-r-lg focus:outline-none focus:shadow-outline-purple"' . "\r\n\t\t\t\t" . 'aria-label="Next"' . "\r\n\t\t\t\t" . '>' . "\r\n\t\t\t\t" . '<svg' . "\r\n\t\t\t\t" . 'class="w-4 h-4 fill-current"' . "\r\n\t\t\t\t" . 'aria-hidden="true"' . "\r\n\t\t\t\t" . 'viewBox="0 0 20 20"' . "\r\n\t\t\t\t" . '>' . "\r\n\t\t\t\t" . '<path' . "\r\n\t\t\t\t" . 'd="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"' . "\r\n\t\t\t\t" . 'clip-rule="evenodd"' . "\r\n\t\t\t\t" . 'fill-rule="evenodd"' . "\r\n\t\t\t\t" . '></path>' . "\r\n\t\t\t" . '</svg>' . "\r\n\t\t" . '</button>' . "\r\n\t" . '</li>' . "\r\n" . '</a>' . "\r\n";


				} ?>
			</ul>
		</nav>
	</span>
	<!-- End pagination -->
	<?php

	echo '</div>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n" . '</main>' . "\r\n\r\n" . '<!-- Modal Delete -->' . "\r\n" . '<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center" style="display: none;">' . "\r\n\t" . '<!-- Modal -->' . "\r\n\t" . '<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform translate-y-1/2" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0  transform translate-y-1/2" @click.away="closeModal" @keydown.escape="closeModal" class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl" role="dialog" id="modal" style="display: none;">' . "\r\n\t\t" . '<!-- Remove header if you don\'t want a close icon. Use modal body to place modal tile. -->' . "\r\n\t\t" . '<header class="flex justify-end">' . "\r\n\t\t\t" . '<button class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover: hover:text-gray-700" aria-label="close" @click="closeModal">' . "\r\n\t\t\t\t" . '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" role="img" aria-hidden="true">' . "\r\n\t\t\t\t\t" . '<path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path>' . "\r\n\t\t\t\t" . '</svg>' . "\r\n\t\t\t" . '</button>' . "\r\n\t\t" . '</header>' . "\r\n\t\t" . '<div class="mt-4 mb-6">' . "\r\n\t\t\t" . '<p class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">' . "\r\n\t\t\t\t" . 'Deseja excluir?' . "\r\n\t\t\t" . '</p>' . "\r\n\t\t\t" . '<p class="text-sm text-gray-700 dark:text-gray-400">' . "\r\n\t\t\t\t" . 'Você realmente deseja excluir esse Sorteio?' . "\r\n\t\t\t" . '</p>' . "\r\n\t\t" . '</div>' . "\r\n\t\t" . '<footer class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">' . "\r\n\t\t\t" . '<button @click="closeModal" class="w-full px-5 py-3 text-sm font-medium leading-5 text-white text-gray-700 transition-colors duration-150 border border-gray-300 rounded-lg dark:text-gray-400 sm:px-4 sm:py-2 sm:w-auto active:bg-transparent hover:border-gray-500 focus:border-gray-500 active:text-gray-500 focus:outline-none focus:shadow-outline-gray">' . "\r\n\t\t\t\t" . 'Não' . "\r\n\t\t\t" . '</button>' . "\r\n\t\t\t" . '<button class="delete_data w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">' . "\r\n\t\t\t\t" . 'Sim' . "\r\n\t\t\t" . '</button>' . "\r\n\t\t" . '</footer>' . "\r\n\t" . '</div>' . "\r\n" . '</div>' . "\r\n" . '<!-- End Modal Delete -->' . "\r\n\r\n" . '<script>' . "\r\n\t" . '$(document).ready(function(){' . "\r\n\t\t" . '$(\'.delete_sorteio\').click(function(){' . "\r\n\t\t\t" . 'var id = $(this).attr(\'data-id\');' . "\r\n\t\t\t" . '$(\'.delete_data\').attr(\'data-id\', id);' . "\t\r\n\t\t" . '})' . "\r\n\t\t" . '$(\'.delete_data\').click(function(){' . "\r\n\t\t\t" . 'var id = $(this).attr(\'data-id\');' . "\r\n\t\t\t" . 'delete_product(id)' . "\t\r\n\t\t" . '})' . "\r\n\r\n\t" . '})' . "\r\n\r\n\t" . 'function delete_product($id){' . "\r\n\t\t" . '$.ajax({' . "\r\n\t\t\t" . 'url:_base_url_+"class/Main.php?action=delete_product_sys",' . "\r\n\t\t\t" . 'method:"POST",' . "\r\n\t\t\t" . 'data:{id: $id},' . "\r\n\t\t\t" . 'dataType:"json",' . "\r\n\t\t\t" . 'error:err=>{' . "\r\n\t\t\t\t" . 'console.log(err)' . "\r\n\t\t\t\t" . 'alert("[AP01] - An error occured.");' . "\r\n\t\t\t" . '},' . "\r\n\t\t\t" . 'success:function(resp){' . "\r\n\t\t\t\t" . 'if(typeof resp== \'object\' && resp.status == \'success\'){' . "\r\n\t\t\t\t\t" . 'location.reload();' . "\r\n\t\t\t\t" . '}else{' . "\r\n\t\t\t\t\t" . 'alert("[AP02] - An error occured.");' . "\r\n\t\t\t\t" . '}' . "\r\n\t\t\t" . '}' . "\r\n\t\t" . '})' . "\r\n\t" . '}' . "\r\n\r\n\t" . '$(function(){' . "\r\n\t\t" . '$(\'#filter-form\').submit(function(e){' . "\r\n\t\t\t" . 'e.preventDefault()' . "\r\n\t\t\t" . 'location.href = \'./?page=products&\'+$(this).serialize()' . "\r\n\t\t" . '})' . "\r\n\r\n\r\n\t" . '})' . "\r\n" . '</script>';

	?>

	<script>
		$(document).ready(function () {


			$('.duplicate').each(function () {
				$(this).click(function () {
					var id = $(this).attr('data-id');
					duplicateRaffle(id);
				})
			})
		})


		function duplicateRaffle(id) {
			var now = new Date();
			var date = now.getFullYear() + '-' + (now.getMonth() + 1) + '-' + now.getDate();
			var time = now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();
			var dateTime = date + ' ' + time;

			$.ajax({
				url: '/class/Mister.php?action=duplicate_product',
				type: 'POST',
				data: { id: id, dateTime: dateTime },
				success: function (response) {
					var res = JSON.parse(response);

					if (res.status === 'success') {
						Swal.fire({
							icon: 'success',
							title: 'Duplicado!',
							text: 'Rifa duplicada com sucesso!',
							confirmButtonText: 'OK'
						});

						location.reload();
					} else {
						alert('Erro ao duplicar rifa!');
					}
				},
				error: function () {
					alert('Erro ao duplicar rifa!');
				}
			});
		}
	</script>

</html>