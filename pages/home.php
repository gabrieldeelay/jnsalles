<style>
.bg-azul-personalizado {
  background-color: #198754;
}
.home-shell{padding-top:16px;padding-bottom:28px}
.home-hero{position:relative;overflow:hidden;margin-bottom:18px;padding:18px 20px;border-radius:14px;background:linear-gradient(135deg,#3b2330 0%,#632945 100%);color:#fff;box-shadow:0 3px 12px rgba(52,36,44,.1)}
.home-hero:after{content:"";position:absolute;width:130px;height:130px;right:-65px;top:-72px;border-radius:50%;background:rgba(255,255,255,.055)}
.home-hero__eyebrow{display:inline-flex;margin-bottom:6px;color:#f4dce7;font-size:.62rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
.home-hero h1{max-width:620px;margin:0 0 6px;font-size:1.32rem;font-weight:820;letter-spacing:-.03em}
.home-hero p{max-width:700px;margin:0;color:rgba(255,255,255,.8);font-size:.73rem;line-height:1.5}
.home-trust{display:flex;gap:7px;margin-top:12px}
.home-trust span{flex:1;padding:7px;border:1px solid rgba(255,255,255,.12);border-radius:8px;background:rgba(255,255,255,.045);font-size:.61rem;text-align:center}
.home-section-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin:20px 0 11px}
.home-section-heading__eyebrow{display:block;margin-bottom:2px;color:var(--theme-primary,#b42c63);font-size:.58rem;font-weight:850;letter-spacing:.13em;text-transform:uppercase}
.home-section-heading h2{margin:0;color:var(--theme-text,#34242c);font-size:1.16rem;font-weight:850;letter-spacing:-.025em}
.home-section-heading p{max-width:310px;margin:0;color:var(--theme-muted,#806976);font-size:.66rem;line-height:1.45;text-align:right}
.home-active-card{display:block;overflow:hidden;border:1px solid rgba(52,36,44,.1);border-radius:18px;background:var(--theme-surface,#fff);box-shadow:0 10px 28px rgba(52,36,44,.1);color:inherit!important;transition:transform .2s ease,box-shadow .2s ease}
.home-active-card:hover{transform:translateY(-2px);box-shadow:0 14px 34px rgba(52,36,44,.14)}
.home-active-card__media{position:relative;width:100%;aspect-ratio:16/9;min-height:280px;max-height:520px;overflow:hidden;background:linear-gradient(145deg,#f8f8f8,#ececef)}
.home-active-card__media img{display:block;width:100%;height:100%;object-fit:contain;object-position:center}
.home-active-card__media:after{content:"";position:absolute;inset:auto 0 0;height:26%;background:linear-gradient(180deg,transparent,rgba(0,0,0,.1));pointer-events:none}
.home-active-card__badge{position:absolute;z-index:2;top:14px;left:14px;display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border-radius:999px;background:#15803d;color:#fff;font-size:.61rem;font-weight:850;letter-spacing:.08em;text-transform:uppercase;box-shadow:0 5px 16px rgba(21,128,61,.25)}
.home-active-card__badge:before{content:"";width:7px;height:7px;border-radius:50%;background:#b7f7ca;box-shadow:0 0 0 4px rgba(183,247,202,.16);animation:homePulse 1.7s ease-in-out infinite}
@keyframes homePulse{50%{transform:scale(.75);opacity:.7}}
.home-active-card__body{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:18px 20px 20px}
.home-active-card__copy{min-width:0}
.home-active-card__copy h3{margin:0 0 4px;color:var(--theme-text,#242027);font-size:1.15rem;font-weight:850;line-height:1.25;letter-spacing:-.02em}
.home-active-card__copy p{margin:0;color:var(--theme-muted,#776b73);font-size:.72rem;line-height:1.45}
.home-active-card__date{display:inline-flex;align-items:center;gap:5px;margin-top:8px;color:var(--theme-muted,#776b73);font-size:.63rem;font-weight:700}
.home-active-card__cta{flex:0 0 auto;display:inline-flex;align-items:center;gap:8px;padding:11px 15px;border-radius:11px;background:var(--theme-primary,#b42c63);color:#fff;font-size:.68rem;font-weight:850;white-space:nowrap;box-shadow:0 6px 16px rgba(180,44,99,.22)}
.home-finished-section{margin-top:22px}
.home-finished-list{display:flex;flex-direction:column;gap:10px}
.home-finished-card{display:grid;grid-template-columns:148px minmax(0,1fr);overflow:hidden;border:1px solid rgba(52,36,44,.09);border-radius:13px;background:rgba(255,255,255,.82);color:inherit!important;box-shadow:0 2px 8px rgba(52,36,44,.045);transition:border-color .2s ease,background .2s ease}
.home-finished-card:hover{border-color:rgba(52,36,44,.17);background:#fff}
.home-finished-card__media{position:relative;min-height:108px;overflow:hidden;background:#e9e9eb}
.home-finished-card__media img{display:block;width:100%;height:100%;object-fit:cover;filter:grayscale(1) contrast(.88);opacity:.72}
.home-finished-card__status{position:absolute;left:9px;bottom:9px;padding:5px 8px;border:1px solid rgba(255,255,255,.32);border-radius:6px;background:rgba(16,16,18,.82);color:#fff;font-size:.52rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase;backdrop-filter:blur(5px)}
.home-finished-card__body{display:grid;grid-template-columns:minmax(0,1fr) minmax(210px,.75fr);align-items:center;gap:16px;padding:13px 15px}
.home-finished-card__title{min-width:0}
.home-finished-card__title h3{display:-webkit-box;overflow:hidden;margin:0 0 4px;color:#39343a;font-size:.78rem;font-weight:800;line-height:1.35;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.home-finished-card__title p{margin:0;color:#928991;font-size:.6rem;line-height:1.4}
.home-finished-card__results{display:flex;flex-direction:column;gap:5px}
.home-winner-row{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:9px;padding:7px 9px;border-radius:9px;background:#f4f2f3}
.home-winner-row__person small,.home-winner-row__number small{display:block;margin-bottom:1px;color:#9a9298;font-size:.48rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.home-winner-row__person strong{display:block;overflow:hidden;color:#373137;font-size:.65rem;font-weight:850;text-overflow:ellipsis;white-space:nowrap}
.home-winner-row__number{text-align:right}
.home-winner-row__number strong{display:block;color:#151316;font-size:.68rem;font-weight:900;white-space:nowrap}
.home-info-card{margin:16px 0;padding:16px 18px;border:1px solid rgba(52,36,44,.1);border-radius:14px;background:#fff;box-shadow:0 1px 4px rgba(52,36,44,.04)}
.home-info-card h2{margin:0 0 6px;color:#34242c;font-size:.98rem;font-weight:800}
.home-info-card p{margin:0;color:#806976;font-size:.7rem;line-height:1.6}
.home-info-actions{display:flex;gap:8px;margin-top:12px}
.home-info-actions a{flex:1;padding:9px 10px;border-radius:9px;background:#b42c63;color:#fff!important;font-size:.67rem;font-weight:750;text-align:center}
.home-shell .app-title{margin:12px 0}
@media(min-width:760px){.home-shell{max-width:900px!important}}
@media(max-width:700px){.home-section-heading{align-items:flex-start;flex-direction:column;gap:3px}.home-section-heading p{text-align:left}.home-active-card__media{min-height:0;aspect-ratio:4/3}.home-active-card__body{align-items:flex-start;flex-direction:column;padding:15px}.home-active-card__cta{width:100%;justify-content:center}.home-finished-card{grid-template-columns:112px minmax(0,1fr)}.home-finished-card__body{grid-template-columns:1fr;gap:9px;padding:11px}.home-finished-card__media{min-height:142px}}
@media(max-width:520px){.home-hero{padding:15px}.home-hero h1{font-size:1.16rem}.home-trust{display:grid;grid-template-columns:1fr 1fr}.home-trust span:last-child{grid-column:1/-1}.home-active-card__media{aspect-ratio:1.08/1}.home-active-card__copy h3{font-size:1rem}.home-finished-card{grid-template-columns:96px minmax(0,1fr)}.home-finished-card__media{min-height:152px}.home-finished-card__title h3{-webkit-line-clamp:3}.home-winner-row{grid-template-columns:1fr}.home-winner-row__number{text-align:left}.home-info-actions{flex-direction:column}}
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
	<?php
	$decodeDrawList = static function ($value) {
		$value = trim((string) $value);
		if ($value === '') {
			return [];
		}

		$decoded = json_decode($value, true);
		if (is_array($decoded)) {
			return array_values($decoded);
		}

		return [$value];
	};

	$activeCampaign = null;
	$activeQuery = $conn->query("SELECT * FROM `product_list` WHERE delete_flag = '0' AND private_draw = '0' AND status <> '3' ORDER BY featured_draw DESC, CASE status WHEN 1 THEN 0 WHEN 2 THEN 1 ELSE 2 END, id DESC LIMIT 1");
	if ($activeQuery && $activeQuery->num_rows > 0) {
		$activeCampaign = $activeQuery->fetch_assoc();
	}
	$activeDrawTimestamp = $activeCampaign && !empty($activeCampaign['date_of_draw'])
		? strtotime((string) $activeCampaign['date_of_draw'])
		: false;
	?>

	<?php if ($activeCampaign): ?>
		<section aria-labelledby="active-campaign-title">
			<div class="home-section-heading">
				<div>
					<span class="home-section-heading__eyebrow">Em destaque</span>
					<h2 id="active-campaign-title">Campanha em andamento</h2>
				</div>
				<p>Participe da campanha atual e acompanhe suas cotas em tempo real.</p>
			</div>

			<a href="/campanha/<?= htmlspecialchars((string) $activeCampaign['slug'], ENT_QUOTES, 'UTF-8') ?>" class="home-active-card">
				<div class="home-active-card__media">
					<span class="home-active-card__badge">Em andamento</span>
					<img
						src="<?= htmlspecialchars(validate_image($activeCampaign['image_path']), ENT_QUOTES, 'UTF-8') ?>"
						alt="<?= htmlspecialchars((string) $activeCampaign['name'], ENT_QUOTES, 'UTF-8') ?>"
						decoding="async"
						fetchpriority="high"
					>
				</div>
				<div class="home-active-card__body">
					<div class="home-active-card__copy">
						<h3><?= htmlspecialchars((string) $activeCampaign['name'], ENT_QUOTES, 'UTF-8') ?></h3>
						<?php if (!empty($activeCampaign['subtitle'])): ?>
							<p><?= htmlspecialchars((string) $activeCampaign['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
						<?php endif; ?>
						<?php if ($activeDrawTimestamp && (int) date('Y', $activeDrawTimestamp) > 1970): ?>
							<span class="home-active-card__date"><i class="bi bi-calendar2-check"></i> Sorteio em <?= date('d/m/Y', $activeDrawTimestamp) ?></span>
						<?php endif; ?>
					</div>
					<span class="home-active-card__cta">Participar agora <i class="bi bi-arrow-right"></i></span>
				</div>
			</a>
		</section>
	<?php endif; ?>

	<?php
	$finishedCampaigns = $conn->query("SELECT id, name, subtitle, slug, image_path, draw_number, draw_winner, date_of_draw FROM `product_list` WHERE delete_flag = '0' AND private_draw = '0' AND status = '3' AND TRIM(COALESCE(draw_number, '')) NOT IN ('', '[]', 'null') AND TRIM(COALESCE(draw_winner, '')) NOT IN ('', '[]', 'null') ORDER BY date_of_draw DESC, id DESC LIMIT 10");
	?>

	<?php if ($finishedCampaigns && $finishedCampaigns->num_rows > 0): ?>
		<section class="home-finished-section" aria-labelledby="finished-campaigns-title">
			<div class="home-section-heading">
				<div>
					<span class="home-section-heading__eyebrow">Resultados anteriores</span>
					<h2 id="finished-campaigns-title">Campanhas finalizadas</h2>
				</div>
				<p>Consulte os ganhadores e as cotas premiadas das campanhas encerradas.</p>
			</div>

			<div class="home-finished-list">
				<?php while ($finishedCampaign = $finishedCampaigns->fetch_assoc()): ?>
					<?php
					$winnerPhones = $decodeDrawList($finishedCampaign['draw_winner']);
					$winningNumbers = $decodeDrawList($finishedCampaign['draw_number']);
					$winnerRows = [];
					$winnerTotal = max(count($winnerPhones), count($winningNumbers));
					$finishedDrawTimestamp = !empty($finishedCampaign['date_of_draw'])
						? strtotime((string) $finishedCampaign['date_of_draw'])
						: false;

					for ($winnerIndex = 0; $winnerIndex < $winnerTotal; $winnerIndex++) {
						$winnerPhone = isset($winnerPhones[$winnerIndex]) ? trim((string) $winnerPhones[$winnerIndex]) : '';
						$winningNumber = isset($winningNumbers[$winnerIndex]) ? trim((string) $winningNumbers[$winnerIndex]) : '';
						$winnerName = 'Ganhador confirmado';

						if ($winnerPhone !== '') {
							$safeWinnerPhone = $conn->real_escape_string($winnerPhone);
							$winnerQuery = $conn->query("SELECT TRIM(CONCAT(COALESCE(firstname, ''), ' ', COALESCE(lastname, ''))) AS name FROM customer_list WHERE phone = '{$safeWinnerPhone}' LIMIT 1");
							if ($winnerQuery && ($winnerCustomer = $winnerQuery->fetch_assoc()) && trim((string) $winnerCustomer['name']) !== '') {
								$winnerName = trim((string) $winnerCustomer['name']);
							} elseif (!preg_match('/^\\d+$/', $winnerPhone)) {
								$winnerName = $winnerPhone;
							}
						}

						$winnerRows[] = [
							'name' => $winnerName,
							'number' => $winningNumber !== '' ? $winningNumber : '—',
						];
					}
					?>

					<a href="/campanha/<?= htmlspecialchars((string) $finishedCampaign['slug'], ENT_QUOTES, 'UTF-8') ?>" class="home-finished-card">
						<div class="home-finished-card__media">
							<img src="<?= htmlspecialchars(validate_image($finishedCampaign['image_path']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) $finishedCampaign['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
							<span class="home-finished-card__status">Finalizada</span>
						</div>
						<div class="home-finished-card__body">
							<div class="home-finished-card__title">
								<h3><?= htmlspecialchars((string) $finishedCampaign['name'], ENT_QUOTES, 'UTF-8') ?></h3>
								<?php if ($finishedDrawTimestamp && (int) date('Y', $finishedDrawTimestamp) > 1970): ?>
									<p>Encerrada em <?= date('d/m/Y', $finishedDrawTimestamp) ?></p>
								<?php else: ?>
									<p>Resultado confirmado</p>
								<?php endif; ?>
							</div>
							<div class="home-finished-card__results">
								<?php foreach ($winnerRows as $winnerRow): ?>
									<div class="home-winner-row">
										<div class="home-winner-row__person">
											<small>Ganhador</small>
											<strong><?= htmlspecialchars($winnerRow['name'], ENT_QUOTES, 'UTF-8') ?></strong>
										</div>
										<div class="home-winner-row__number">
											<small>Cota premiada</small>
											<strong>#<?= htmlspecialchars($winnerRow['number'], ENT_QUOTES, 'UTF-8') ?></strong>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</a>
				<?php endwhile; ?>
			</div>
		</section>
	<?php endif; ?>

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
