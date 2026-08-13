<style>
.bg-azul-personalizado {
  background-color: #198754;
}
</style>
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
<div id="loadingSystem" style="display: none;"></div>
<!-- Estilos básicos -->

<div class="container app-main">
	<div class="row">
		<div class="col-12">
			<div class="app-title">
				<h1>⚡ Campanhas</h1>
				<div class="app-title-desc">Escolha sua sorte</div>
			</div>
		</div>
	</div>
	
	<?php
	$qry = $conn->query('SELECT * FROM `product_list` WHERE status_display <> \'4\' AND featured_draw = \'1\' ORDER BY RAND() LIMIT 1');
	while ($row = $qry->fetch_assoc()) { ?>
		<div class="col-12 mb-2" style="height: 450px;">
			<a href="/campanha/<?php echo $row['slug']; ?>" class="h-100 SorteioTpl_sorteioTpl__home SorteioTpl_destaque__3vnWR pointer custom-highlight-card">
			    
			    <div style="bottom: 68px !important;" class="custom-badge-display">
				
			<?php
$status = (int) $row['status_display'];
switch ($status) {
    case 1:
        echo '<span class="badge bg-azul-personalizado blink font-xsss text-white">Adquira já!</span>';
        break;
    case 2:
        echo '<span class="badge bg-dark blink font-xsss mobile badge-status-1">Corre que está acabando!</span>';
        break;
    case 3:
        echo '<span class="badge bg-dark font-xsss mobile badge-status-3">Aguarde a campanha!</span>';
        break;
    case 4:
        echo '<span class="badge bg-dark font-xsss">Concluído</span>';
        break;
    case 5:
        echo '<span class="badge bg-dark font-xsss">Em breve!</span>';
        break;
    case 6:
        echo '<span class="badge bg-dark font-xsss">Aguarde o sorteio!</span>';
        break;
}

if (!empty($row['date_of_draw'])) {
    $dataHora = date('d/m/Y', strtotime($row['date_of_draw'])) . ' às ' . date('H:i', strtotime($row['date_of_draw']));
    echo '<div class="SorteioTpl_dtSorteio__2mfSc custom-calendar-display mt-1">';
    echo '<i class="bi bi-calendar2-check me-1"></i>' . $dataHora;
    echo '</div>';
}
?>

				</div>
				<div class="SorteioTpl_imagemContainer__2-pl4 col-auto">
					<div id="carouselSorteio640d0a84b1fef407920230311" class="carousel slide carousel-dark carousel-fade" data-bs-ride="carousel">
						<div class="carousel-inner">
							<div class="carousel-item active" style="width:100%;height:420px">
								<div style="display:block;overflow:hidden;position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;margin:0">
									<img alt="<?php echo $row['name']; ?>" src="<?php echo validate_image($row['image_path']); ?>" decoding="async" data-nimg="fill" class="SorteioTpl_imagem__2GXxI" style="object-fit:cover;position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;padding:0;border:none;margin:auto;display:block;width:0;height:0;min-width:100%;max-width:100%;min-height:100%;max-height:100%">
									<noscript>
										<img alt="<?php echo $row['name']; ?>" src="<?php echo validate_image($row['image_path']); ?>" decoding="async" data-nimg="fill" style="object-fit:cover;position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;padding:0;border:none;margin:auto;display:block;width:0;height:0;min-width:100%;max-width:100%;min-height:100%;max-height:100%" class="SorteioTpl_imagem__2GXxI" loading="lazy" />
									</noscript>
								</div>
							</div>
						</div>
					</div>
				</div>

			<div class="SorteioTpl_info__t1BZr custom-content-wrapper" style="background: linear-gradient(45deg, #fff, #ffffff) !important;">
    <h2 class="SorteioTpl_title__3RLtu" style="font-weight: bold; color: #000000;"><?php echo $row['name']; ?></h2>
<h6 class="SorteioTpl_descricao__1b7iL" style="margin-bottom:1px; color: #5d5d5d;"><?php echo (isset($row['subtitle']) ? $row['subtitle'] : ''); ?></h6>
</div>

			</a>
	

		</div>
	<?php } ?>

	<?php
$qry = $conn->query('SELECT * FROM `product_list` WHERE featured_draw = \'0\' AND private_draw = \'0\' ORDER BY id DESC LIMIT 10');

if ($qry->num_rows > 0) {
	while ($row = $qry->fetch_assoc()) {
?>
		<div class="col-12 mb-2">
			<a href="/campanha/<?php echo $row['slug']; ?>">
				<div class="SorteioTpl_sorteioTpl__home pointer">
					<div class="SorteioTpl_imagemContainer__2-pl4 col-auto">
						<div style="display:block;overflow:hidden;position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;margin:0">
							<img alt="<?php echo $row['name']; ?>" src="<?php echo validate_image($row['image_path']); ?>" class="SorteioTpl_imagem__2GXxI" style="position:absolute;top:0;left:0;bottom:0;right:0;margin:auto;display:block;min-width:100%;min-height:100%" />
						</div>
					</div>

					<div class="SorteioTpl_info__t1BZr">
						<h1 class="SorteioTpl_title__3RLtu"><?php echo $row['name']; ?></h1>
						<p class="SorteioTpl_descricao__1b7iL" style="margin-bottom:1px"><?php echo isset($row['subtitle']) ? $row['subtitle'] : ''; ?></p>

						<?php
						if ($row['status_display'] == 1) {
							echo '<span class="badge bg-success blink bg-opacity-75 font-xsss">Adquira já!</span>';
						} elseif ($row['status_display'] == 2) {
							echo '<span class="badge bg-dark blink font-xsss mobile badge-status-1">Corre que está acabando!</span>';
						} elseif ($row['status_display'] == 3) {
							echo '<span class="badge bg-dark font-xsss mobile badge-status-3">Aguarde a campanha!</span>';
						} elseif ($row['status_display'] == 4) {
							echo '<span class="badge bg-dark font-xsss">Concluído</span>';
						} elseif ($row['status_display'] == 5) {
							echo '<span class="badge bg-dark font-xsss">Em breve!</span>';
						} elseif ($row['status_display'] == 6) {
							echo '<span class="badge bg-dark font-xsss">Aguarde o sorteio!</span>';
						}
						?>

						<?php if (!empty($row['date_of_draw'])) { ?>
                    	<div class="SorteioTpl_dtSorteio__2mfSc mt-1">
	                	<i class="bi bi-calendar2-check"></i>
	                	<?php echo date('d/m/Y', strtotime($row['date_of_draw'])) . ' às ' . date('H:i', strtotime($row['date_of_draw'])); ?>
                    	</div>
                       <?php } ?>

					</div>
				</div>
			</a>
		</div>
<?php
	}
}
?>

	<?php

	$sql = 'SELECT name AS product_name, draw_number, draw_winner, image_path, slug, date_of_draw FROM product_list WHERE draw_number <> \'\' ORDER BY date_of_draw DESC LIMIT 5';
	$products = $conn->query($sql);

	if (0 < $products->num_rows) {
	?>
		<div class="app-ganhadores mb-2">
			<div class="col-12">
				<div class="app-title">
					<h1>🎉 Ganhadores</h1>
					<div class="app-title-desc">sortudos</div>
				</div>
			</div>

			<div class="col-12">
				<div class="row">
					<?php
					while ($row = $products->fetch_assoc()) {
						$product_name = $row['product_name'];
						$draw_number = $row['draw_number'];
						$draw_name = $row['draw_winner'];
						$draw_number_arr = json_decode(json_encode($draw_number));
						$draw_winner_arr = json_decode(json_encode($draw_name));
						$draw_number = $draw_number_arr[0];
						$draw_name = $draw_winner_arr[0];
						$date_of_draw = strtotime($row['date_of_draw']);
						$date_of_draw = date('d/m/y', $date_of_draw);
						$image_path = validate_image($row['image_path']);

						if (!empty($draw_number_arr)) {
							$draw_number_arr = (isset($draw_number_arr) ? $draw_number_arr : '');

							if ($draw_number_arr) {
								$draw_winner_arr = json_decode($draw_winner_arr, true);
								$draw_number_arr = json_decode($draw_number_arr, true);
								$winners = [];

								foreach ($draw_winner_arr as $qty_index => $name) {
									foreach ($draw_number_arr as $amount_index => $number) {
									   $query = $conn->query("SELECT CONCAT(firstname, ' ', lastname) as name, avatar, phone FROM customer_list WHERE phone = '$name'");
                                       $rowCustomer = $query->fetch_assoc();

                                       $ddd = substr($rowCustomer['phone'], 0, 2);
                                       $masked_phone = "($ddd)****-****";

                                       $winners[$qty_index] = [
                                    	'name' => $rowCustomer['name'],
                                    	'number' => $number,
                                    	'product' => $product_name,
                                    	'date' => $date_of_draw,
                                    	'image' => ($rowCustomer['avatar'] ? validate_image($rowCustomer['avatar']) : BASE_URL . 'assets/img/avatar.png'),
                                      	'phone' => $masked_phone
                                         ];

										}
									}
								}
							}

							foreach ($winners as $winner) {
					?>
								
									
									<div href="">
									<div class="ganhadorItem_ganhadorContainer__1Sbxm mb-2"style="cursor: pointer;">
										<div class="ganhadorItem_ganhadorFoto__324kH box-shadow-08">
											<div style="display:block;overflow:hidden;position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;margin:0">
												<img alt="<?php echo $winner['product']; ?> ganhador do prêmio <?php echo $winner['product']; ?>" src="<?php echo $winner['image']; ?>" decoding="async" data-nimg="fill" style="position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;padding:0;border:none;margin:auto;display:block;width:0;height:0;min-width:100%;max-width:100%;min-height:100%;max-height:100%;object-fit: cover;">
												<noscript>
													<img alt="<?php echo $draw_name; ?> ganhador do prêmio <?php echo $winner['product']; ?>" src="<?php echo $winner['image']; ?>" decoding="async" data-nimg="fill" style="position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;padding:0;border:none;margin:auto;display:block;width:0;height:0;min-width:100%;max-width:100%;min-height:100%;max-height:100%;object-fit: cover;" loading="lazy" />
												</noscript>
											</div>
										</div>
										<div class="undefined w-100">
											<p class="mb-0"><b><?php echo $winner['name']; ?></b></p>
											<div class="ganhadorItem_ganhadorDescricao__Z4kO2">
                                            	<p class="mb-0">Prêmio:<b><?php echo $winner['product']; ?></b></p>
                                            	<p class="mb-0">Número da sorte <b><?php echo $winner['number']; ?></b></p>
                                            	<p class="mb-0">Data da premiação <b><?php echo $winner['date']; ?></b></p>
                                            	<p class="mb-0">Telefone <b><?php echo $winner['phone']; ?></b></p>
                                           </div>

										</div>
									</div>
								</div>
						
        	<?php
	       	}
					}
	       	}
	       	?>	
        
	<?php
	
	// Perguntas frequentes
	?>
	<style>
		.pergunta-item{
			cursor: pointer;
		}
	</style>
	<div class="app-perguntas">
		<div class="app-title">
			<h1>🤷 Perguntas frequentes</h1>
		</div>
		<div id="perguntas-box">
			<?php if (!!$_settings->info('question1') && !!$_settings->info('answer1')): ?>
				<div class="mb-2">
					<div class="pergunta-item d-flex flex-column p-2 bg-card box-shadow-08 rounded-10 font-weight-500 font-xs">
						<div class="pergunta-item--pergunta collapsed" data-bs-toggle="collapse" data-bs-target="#pergunta-63c30d4b6bd40368220230114" aria-expanded="false" aria-controls="pergunta-63c30d4b6bd40368220230114">
							<i class="bi bi-arrow-right me-2 incrivel-primariaLink"></i>
							<span><?php echo $_settings->info('question1'); ?></span>
						</div>
						<div class="d-block">
							<div class="pergunta-item--resp mt-1 collapse" id="pergunta-63c30d4b6bd40368220230114" data-bs-parent="#perguntas-box">
								<p class="mb-0"><?php echo $_settings->info('answer1'); ?></p>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!!$_settings->info('question2') && !!$_settings->info('answer2')): ?>
				<div class="mb-2">
					<div class="pergunta-item d-flex flex-column p-2 bg-card box-shadow-08 rounded-10 font-weight-500 font-xs">
						<div class="pergunta-item--pergunta collapsed" data-bs-toggle="collapse" data-bs-target="#pergunta-1" aria-expanded="false" aria-controls="pergunta-1">
							<i class="bi bi-arrow-right me-2 incrivel-primariaLink"></i>
							<span><?php echo $_settings->info('question2'); ?></span>
						</div>
						<div class="d-block">
							<div class="pergunta-item--resp mt-1 collapse" id="pergunta-1" data-bs-parent="#perguntas-box">
								<p class="mb-0"><?php echo $_settings->info('answer2'); ?></p>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!!$_settings->info('question3') && !!$_settings->info('answer3')): ?>
				<div class="mb-2">
					<div class="pergunta-item d-flex flex-column p-2 bg-card box-shadow-08 rounded-10 font-weight-500 font-xs">
						<div class="pergunta-item--pergunta collapsed" data-bs-toggle="collapse" data-bs-target="#pergunta-2" aria-expanded="false" aria-controls="pergunta-2">
							<i class="bi bi-arrow-right me-2 incrivel-primariaLink"></i>
							<span><?php echo $_settings->info('question3'); ?></span>
						</div>
						<div class="d-block">
							<div class="pergunta-item--resp mt-1 collapse" id="pergunta-2" data-bs-parent="#perguntas-box">
								<p class="mb-0"><?php echo $_settings->info('answer3'); ?></p>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!!$_settings->info('question4') && !!$_settings->info('answer4')): ?>
				<div class="mb-2">
					<div class="pergunta-item d-flex flex-column p-2 bg-card box-shadow-08 rounded-10 font-weight-500 font-xs">
						<div class="pergunta-item--pergunta collapsed" data-bs-toggle="collapse" data-bs-target="#pergunta-3" aria-expanded="false" aria-controls="pergunta-3">
							<i class="bi bi-arrow-right me-2 incrivel-primariaLink"></i>
							<span><?php echo $_settings->info('question4'); ?></span>
						</div>
						<div class="d-block">
							<div class="pergunta-item--resp mt-1 collapse" id="pergunta-3" data-bs-parent="#perguntas-box">
								<p class="mb-0"><?php echo $_settings->info('answer4'); ?></p>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($enable_password == 1): ?>
				<div class="mb-2">
					<div class="pergunta-item d-flex flex-column p-2 bg-card box-shadow-08 rounded-10 font-weight-500 font-xs">
						<div class="pergunta-item--pergunta collapsed" data-bs-toggle="collapse" data-bs-target="#pergunta-4" aria-expanded="false" aria-controls="pergunta-4">
							<i class="bi bi-arrow-right me-2 incrivel-primariaLink"></i>
							<span>Esqueci minha senha, como faço?</span>
						</div>
						<div class="d-block">
							<div class="pergunta-item--resp mt-1 collapse" id="pergunta-4" data-bs-parent="#perguntas-box">
								<p class="mb-0">Você consegue recuperar sua senha indo no menu do site, depois em "Entrar" e logo a baixo tem "Esqueci minha senha".</p>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	</div>
<!-- Fim perguntas frequentes -->
<?php
