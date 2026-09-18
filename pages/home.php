<style>
.bg-azul-personalizado {
  background-color: #198754;
}
.home-shell{padding-top:16px;padding-bottom:28px}.home-hero{position:relative;overflow:hidden;margin-bottom:18px;padding:18px 20px;border-radius:14px;background:linear-gradient(135deg,#3b2330 0%,#632945 100%);color:#fff;box-shadow:0 3px 12px rgba(52,36,44,.1)}.home-hero:after{content:"";position:absolute;width:130px;height:130px;right:-65px;top:-72px;border-radius:50%;background:rgba(255,255,255,.055)}.home-hero__eyebrow{display:inline-flex;margin-bottom:6px;color:#f4dce7;font-size:.62rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.home-hero h1{max-width:620px;margin:0 0 6px;font-size:1.32rem;font-weight:820;letter-spacing:-.03em}.home-hero p{max-width:700px;margin:0;color:rgba(255,255,255,.8);font-size:.73rem;line-height:1.5}.home-trust{display:flex;gap:7px;margin-top:12px}.home-trust span{flex:1;padding:7px;border:1px solid rgba(255,255,255,.12);border-radius:8px;background:rgba(255,255,255,.045);font-size:.61rem;text-align:center}.home-campaign-grid{display:grid;gap:14px}.home-info-card{margin:16px 0;padding:16px 18px;border:1px solid rgba(52,36,44,.1);border-radius:14px;background:#fff;box-shadow:0 1px 4px rgba(52,36,44,.04)}.home-info-card h2{margin:0 0 6px;color:#34242c;font-size:.98rem;font-weight:800}.home-info-card p{margin:0;color:#806976;font-size:.7rem;line-height:1.6}.home-info-actions{display:flex;gap:8px;margin-top:12px}.home-info-actions a{flex:1;padding:9px 10px;border-radius:9px;background:#b42c63;color:#fff!important;font-size:.67rem;font-weight:750;text-align:center}.home-featured{height:360px!important}.home-shell .SorteioTpl_sorteioTpl__home{overflow:hidden;border:1px solid rgba(52,36,44,.1)!important;border-radius:14px!important;box-shadow:0 1px 5px rgba(52,36,44,.05)!important}.home-shell .SorteioTpl_info__t1BZr{padding:14px!important}.home-shell .app-title{margin:12px 0}.home-campaign-card{min-height:280px!important;padding:0!important;display:flex!important;flex-direction:column!important;align-items:stretch!important}.home-campaign-card .SorteioTpl_imagemContainer__2-pl4{width:100%!important;height:210px!important;margin:0!important;border-radius:13px 13px 0 0}.home-campaign-card .SorteioTpl_imagem__2GXxI{width:100%!important;height:100%!important;object-fit:cover!important;border-radius:13px 13px 0 0!important}.home-campaign-card .SorteioTpl_info__t1BZr{position:relative;flex:1;min-height:78px;background:#fff}.home-campaign-card .SorteioTpl_title__3RLtu{font-size:1rem!important;font-weight:750!important}.home-campaign-card:after{display:none}.home-campaign-grid>.col-12{margin:0!important}@media(min-width:760px){.home-shell{max-width:900px!important}.home-campaign-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.home-campaign-grid>.col-12:only-child{grid-column:1/-1;max-width:none;width:100%}.home-campaign-grid>.col-12:only-child .home-campaign-card{min-height:340px!important}.home-campaign-grid>.col-12:only-child .SorteioTpl_imagemContainer__2-pl4{height:260px!important}}@media(max-width:520px){.home-hero{padding:15px}.home-hero h1{font-size:1.16rem}.home-trust{display:grid;grid-template-columns:1fr 1fr}.home-trust span:last-child{grid-column:1/-1}.home-featured{height:320px!important}.home-campaign-card{min-height:260px!important}.home-campaign-card .SorteioTpl_imagemContainer__2-pl4{height:185px!important}.home-info-actions{flex-direction:column}}
</style>
<style>
.home-legal-card{display:grid;grid-template-columns:94px minmax(0,1fr);overflow:hidden;margin:16px 0;border:1px solid var(--theme-border,#e2e8f0);border-radius:14px;background:linear-gradient(135deg,var(--theme-surface,#fff),var(--theme-soft,#f8fafc));box-shadow:0 1px 5px rgba(52,36,44,.05)}
.home-legal-card__brand{display:flex;align-items:center;justify-content:center;padding:16px;background:linear-gradient(145deg,var(--theme-header,#472536),var(--theme-secondary,#6f2445))}
.home-legal-card__brand img{width:74px;height:74px;object-fit:contain;filter:drop-shadow(0 5px 9px rgba(0,0,0,.18))}
.home-legal-card__content{padding:16px 18px}
.home-legal-card__eyebrow{display:inline-flex;margin-bottom:4px;color:var(--theme-secondary,#6f2445);font-size:.58rem;font-weight:850;letter-spacing:.1em;text-transform:uppercase}
.home-legal-card h2{margin:0 0 8px;color:var(--theme-text,#34242c);font-size:.96rem;font-weight:820}
.home-legal-card p{margin:0 0 7px;color:var(--theme-muted,#806976);font-size:.68rem;line-height:1.62;text-align:left}
.home-legal-card p:last-child{margin-bottom:0}
@media(max-width:520px){.home-legal-card{grid-template-columns:1fr}.home-legal-card__brand{min-height:82px;padding:10px}.home-legal-card__brand img{width:68px;height:68px}.home-legal-card__content{padding:15px}.home-legal-card p{font-size:.7rem;line-height:1.58}}
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

<?php
$siteDescription = trim((string) $_settings->info('site_description'));
if ($siteDescription === '') {
	$siteDescription = jnsalles_default_site_description();
}
?>

<div class="container app-main home-shell">
	<section class="home-hero">
		<span class="home-hero__eyebrow">Sua sorte começa aqui</span>
		<h1>Campanhas transparentes, participação simples.</h1>
		<p><?= htmlspecialchars($siteDescription, ENT_QUOTES, 'UTF-8') ?></p>
		<div class="home-trust"><span>✓ Escolha sua campanha</span><span>✓ Confirme o PIX</span><span>✓ Acompanhe suas cotas</span></div>
	</section>
	<div class="row">
		<div class="col-12">
			<div class="app-title">
				<h1>⚡ Campanhas</h1>
				<div class="app-title-desc">Escolha sua sorte</div>
			</div>
		</div>
	</div>
	
	<?php
	$featuredProductId = 0;
	$qry = $conn->query("SELECT * FROM `product_list` WHERE delete_flag = '0' AND featured_draw = '1' AND private_draw = '0' ORDER BY CASE status WHEN 1 THEN 0 WHEN 2 THEN 1 ELSE 2 END, id DESC LIMIT 1");
	while ($row = $qry->fetch_assoc()) { ?>
		<?php $featuredProductId = (int) $row['id']; ?>
		<div class="col-12 mb-2 home-featured">
			<a href="/campanha/<?php echo $row['slug']; ?>" class="h-100 SorteioTpl_sorteioTpl__home SorteioTpl_destaque__3vnWR pointer custom-highlight-card">
			    
			    <div style="bottom: 68px !important;" class="custom-badge-display">
				
			<?php
$status = (int) $row['status'] === 3 ? 4 : (int) $row['status_display'];
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
        echo '<span class="badge bg-dark font-xsss">Finalizada</span>';
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

	<div class="home-campaign-grid">
	<?php
$excludeFeaturedProduct = $featuredProductId > 0 ? ' AND id <> ' . $featuredProductId : '';
$qry = $conn->query("SELECT * FROM `product_list` WHERE delete_flag = '0' AND private_draw = '0'" . $excludeFeaturedProduct . " ORDER BY id DESC LIMIT 10");

if ($qry->num_rows > 0) {
	while ($row = $qry->fetch_assoc()) {
?>
		<div class="col-12 mb-2">
			<a href="/campanha/<?php echo $row['slug']; ?>">
				<div class="SorteioTpl_sorteioTpl__home pointer home-campaign-card">
					<div class="SorteioTpl_imagemContainer__2-pl4 col-auto">
						<div style="display:block;overflow:hidden;position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;margin:0">
							<img alt="<?php echo $row['name']; ?>" src="<?php echo validate_image($row['image_path']); ?>" class="SorteioTpl_imagem__2GXxI" style="position:absolute;top:0;left:0;bottom:0;right:0;margin:auto;display:block;min-width:100%;min-height:100%" />
						</div>
					</div>

					<div class="SorteioTpl_info__t1BZr">
						<h1 class="SorteioTpl_title__3RLtu"><?php echo $row['name']; ?></h1>
						<p class="SorteioTpl_descricao__1b7iL" style="margin-bottom:1px"><?php echo isset($row['subtitle']) ? $row['subtitle'] : ''; ?></p>

						<?php
						$cardDisplayStatus = (int) $row['status'] === 3 ? 4 : (int) $row['status_display'];
						if ($cardDisplayStatus == 1) {
							echo '<span class="badge bg-success blink bg-opacity-75 font-xsss">Adquira já!</span>';
						} elseif ($cardDisplayStatus == 2) {
							echo '<span class="badge bg-dark blink font-xsss mobile badge-status-1">Corre que está acabando!</span>';
						} elseif ($cardDisplayStatus == 3) {
							echo '<span class="badge bg-dark font-xsss mobile badge-status-3">Aguarde a campanha!</span>';
						} elseif ($cardDisplayStatus == 4) {
							echo '<span class="badge bg-dark font-xsss">Finalizada</span>';
						} elseif ($cardDisplayStatus == 5) {
							echo '<span class="badge bg-dark font-xsss">Em breve!</span>';
						} elseif ($cardDisplayStatus == 6) {
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
	</div>

	<section class="home-info-card">
		<h2>Como funciona e quais são as regras?</h2>
		<p>Escolha uma campanha, selecione a quantidade de cotas e finalize o pagamento. A participação entra no sorteio somente depois da confirmação. Cada campanha informa seu prêmio, suas cotas premiadas e o critério de apuração.</p>
		<div class="home-info-actions"><a href="/termos-de-uso">Ler regulamento completo</a><a href="/campanhas">Ver todas as campanhas</a></div>
	</section>

	<section class="home-legal-card" aria-labelledby="home-legal-title">
		<div class="home-legal-card__brand">
			<img src="<?= htmlspecialchars($logo ?: BASE_URL . 'assets/img/logo.png', ENT_QUOTES, 'UTF-8') ?>" alt="NXP Salles">
		</div>
		<div class="home-legal-card__content">
			<span class="home-legal-card__eyebrow">Informação importante</span>
			<h2 id="home-legal-title">Termo informativo da ação promocional</h2>
			<p>Rifa promocional realizada por NXP Salles, inscrita no CNPJ 64.895.043/0001-65. Ao adquirir um número ou cota desta rifa, o participante declara estar ciente e de acordo com as regras da ação promocional. A participação é permitida apenas para maiores de 18 (dezoito) anos.</p>
			<p>O pagamento da cota garante a participação no sorteio correspondente à campanha divulgada pela organização. Não haverá reembolso após a confirmação do pagamento, salvo nos casos previstos em lei ou em eventual cancelamento da ação pela organização.</p>
			<p>O sorteio, a apuração do ganhador e a entrega do prêmio seguirão os critérios informados na divulgação oficial da campanha. Ao participar, o cliente autoriza a divulgação do resultado e do nome do ganhador nos canais da organização, sem ônus adicional. Em caso de dúvidas, o participante deve entrar em contato diretamente com a organização responsável.</p>
		</div>
	</section>

	<?php

	$sql = 'SELECT name AS product_name, qty_numbers, draw_number, draw_winner, image_path, slug, date_of_draw FROM product_list WHERE delete_flag = 0 AND private_draw = 0 AND draw_number <> \'\' ORDER BY date_of_draw DESC LIMIT 5';
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
                                            <p class="mb-0">Número da sorte <b><?php echo htmlspecialchars((string) $winner['number'], ENT_QUOTES, 'UTF-8'); ?></b></p>
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
	$faqItems = [];
	for ($faqIndex = 1; $faqIndex <= 4; $faqIndex++) {
		$faqQuestion = trim((string) $_settings->info('question' . $faqIndex));
		$faqAnswer = trim((string) $_settings->info('answer' . $faqIndex));
		if ($faqQuestion !== '' && $faqAnswer !== '') {
			$faqItems[] = ['question' => $faqQuestion, 'answer' => $faqAnswer];
		}
	}
	if (count($faqItems) === 0) {
		$faqItems = [
			['question' => 'Como faço para participar?', 'answer' => 'Escolha uma campanha, selecione a quantidade de cotas, informe seus dados e finalize o pagamento por PIX.'],
			['question' => 'Quando minha participação é confirmada?', 'answer' => 'A participação é confirmada automaticamente depois que o pagamento é identificado pelo sistema.'],
			['question' => 'Onde vejo minhas cotas?', 'answer' => 'Abra o menu do site e acesse “Meus títulos”. Informe os mesmos dados usados na compra para consultar seus pedidos e cotas.'],
			['question' => 'Como funcionam as cotas premiadas?', 'answer' => 'Cada campanha informa quais números possuem prêmios extras. Se uma dessas cotas for atribuída ao seu pedido confirmado, ela aparecerá identificada na campanha.'],
		];
	}
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
			<?php foreach ($faqItems as $faqIndex => $faqItem): ?>
				<div class="mb-2">
					<div class="pergunta-item d-flex flex-column p-2 bg-card box-shadow-08 rounded-10 font-weight-500 font-xs">
						<div class="pergunta-item--pergunta collapsed" data-bs-toggle="collapse" data-bs-target="#pergunta-faq-<?= (int) $faqIndex ?>" aria-expanded="false" aria-controls="pergunta-faq-<?= (int) $faqIndex ?>">
							<i class="bi bi-arrow-right me-2 incrivel-primariaLink"></i>
							<span><?= htmlspecialchars($faqItem['question'], ENT_QUOTES, 'UTF-8') ?></span>
						</div>
						<div class="d-block">
							<div class="pergunta-item--resp mt-1 collapse" id="pergunta-faq-<?= (int) $faqIndex ?>" data-bs-parent="#perguntas-box">
								<p class="mb-0"><?= nl2br(htmlspecialchars($faqItem['answer'], ENT_QUOTES, 'UTF-8')) ?></p>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

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
