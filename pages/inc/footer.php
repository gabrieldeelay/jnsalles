<?php
// Decodded

$enable_footer = $_settings->info('enable_footer');
$enable_password = $_settings->info('enable_password');
$enable_email = $_settings->info('enable_email');
$enable_cpf = $_settings->info('enable_cpf') == 1 || payment_requires_customer_document();
$enable_two_phone = $_settings->info('enable_two_phone');
$text_footer = $_settings->info('text_footer');
$whatsapp_footer = $_settings->info('whatsapp_footer');
$instagram_footer = $_settings->info('instagram_footer');
$facebook_footer = $_settings->info('facebook_footer');
$twitter_footer = $_settings->info('twitter_footer');
$youtube_footer = $_settings->info('youtube_footer');
$favicon = $_settings->info('favicon');
$site_name = trim((string) $_settings->info('name'));
$site_name = $site_name !== '' ? htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') : 'Site';

if ($enable_footer == '1') { ?>
	<style>
		.container-fluid.rodape {
			background: var(--incrivel-primaria);
			text-align: center;
			color: #6e6e6e;
		}

		.container-fluid.rodape .col-md-12.col-12.font-xs a {
			color: #eee;
			font-weight: bold;
		}

		.app-title-footer {
			font-weight: 100;
			font-size: 18px;
		}

		li.spacing-icon {
			padding: 10px;
		}

		.spacing-icon a {
			
		}

		ul.social a.whatsapp1:hover {
			color: #00e676;
		}

		ul.social a.instagram1:hover {
			color: #bc3090;
		}

		ul.social a.facebook1:hover {
			color: #3c5a99;
		}

		ul.social a.twitter1:hover {
			color: #00acee;
		}

		ul.social a.youtube1:hover {
			color: #e22c29;
		}

		.text-center.links-rodape a {
			color: #eee;
		}
	</style>
	<div class="container-fluid rodape mt-3">
		<div class="row justify-content-center align-items-center" style="padding:15px;background-color: #dee2e7;">
			<div class="col-md-12 col-12">
				<ul class="list-unstyled d-flex flex-wrap justify-content-center social" style="margin-bottom:0px;">

					<?php
					if ($whatsapp_footer) { ?>
						<li class="spacing-icon">
							<a class="whatsapp1" target="_blank" href="<?= $whatsapp_footer ?>" title="WhatsApp">
								<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
									<path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"></path>
								</svg></a>
						</li>
					<?php
					}

					if ($instagram_footer) { ?>

						<li class="spacing-icon">
							<a class="instagram1" target="_blank" href="<?= $instagram_footer ?>" title="instagram">
								<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
									<path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"></path>
								</svg></a>
						</li>

					<?php
					}

					if ($facebook_footer) { ?>
						<li class="spacing-icon">
							<a class="facebook1" target="_blank" href="<?= $facebook_footer ?>" title="Facebook">
								<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
									<path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"></path>
								</svg></a>
						</li>

					<?php
					}

					if ($twitter_footer) { ?>

						<li class="spacing-icon">
							<a class="twitter1" target="_blank" href="<?= $twitter_footer ?>" title="Twitter">
								<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-twitter" viewBox="0 0 16 16">
									<path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"></path>
								</svg></a>
						</li>

					<?php
					}

					if ($youtube_footer) { ?>
						<li class="spacing-icon">
							<a class="youtube1" target="_blank" href="<?= $youtube_footer ?>" title="Youtube">
								<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-play-btn-fill" viewBox="0 0 16 16">
									<path d="M0 12V4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm6.79-6.907A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814l-3.5-2.5z"></path>
								</svg></a>
						</li>

					<?php
					}
					?>
				</ul>
			</div>
			<?php
			if ($enable_footer) {
			?>
				<div class="">
					<div class="py-3 d-flex justify-content-center align-items-center gap-3 imgs-footer">
						<div>
							<img src="<?= BASE_URL ?>assets/img/footer1.png" alt="" width="0px" style="filter: contrast(0.5) !important;">
						</div>
						<div>
							<img src="<?= BASE_URL ?>assets/img/footer2.png" alt="" width="0px" style="filter: contrast(0.5) !important;">
						</div>
					</div>

				</div>


				<div class="col-md-12 col-12" style="font-size:11px;">
					<hr>
					<?php
					if ($text_footer) {
						echo blockHTML($text_footer);
					} else { ?>
						<span style="color:var(--incrivel-bgLink);">© Copyright <?= date('Y') ?> - <?= $site_name ?> Todos os direitos reservados.</span><br>
					<?php } ?>
						<span style="color:var(--incrivel-bgLink);">Desenvolvido por</span> <a href="/contato.php?site=<?= BASE_URL ?>"
                       target="_blank"
                       class="font-weight-600 font-xs badge"
                       rel="noreferrer"
                       style="background-color:#0d6efd;">
                        <?= $site_name ?>
                    </a>
				<?php
				}
				?>
				</div>
		</div>
	<?php
}

if (!$user_id) { ?>
		<form class="modal fade" id="loginModal2">
		    
			<div class="modal-dialog modal-sm modal-fullscreen-md-down modal-dialog-centered">
				<div class="modal-content rounded-0">
					<div class="modal-header">
						<h5 class="modal-title  text-white">Login</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
					</div>
					<div class="modal-body app-form">
						<p class="text-muted font-xs">Por favor, entre com seus dados ou faça um cadastro.</p> <span id="aviso-login"></span>
						<div class="mb-2">
							<div class="form-floating font-weight-500">
								<input onkeyup="formatarTEL(this);" maxlength="15" name="phone" id="phone" required="" class="form-control text-black" placeholder="(00) 0000-0000" value=""> <label for="username">Telefone</label>
							</div>
						</div>
						<?php
						if ($enable_password == '1') { ?>
							<div class="mb-2">
								<div class="form-floating font-weight-500">
									<input type="password" name="password" id="password" class="form-control text-black" placeholder="Senha" required=""><label for="password">Senha</label>
								</div>
							</div>
							<div class="btn btn-link btn-sm text-decoration-none mb-4 text-cardLink opacity-75"><a href="/recuperar-senha">Esqueci minha senha</a></div>
						<?php
						}
						?>
						<div class="d-flex justify-content-center align-items-center flex-column">
							<button type="submit" class="btn btn-wide-in btn-warning font-weight-500 rounded-pill mb-2">Continuar</button>
							<div class="btn btn-link btn-sm text-decoration-none"><a href="<?= BASE_URL ?>cadastrar">Criar conta</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>

		<?php
		if (isset($slug)) { ?>
			<span id="openCadastro" data-bs-toggle="modal" data-bs-target="#cadastroModalLegacy" style="display:none;"></span>
			<form class="modal fade" id="cadastroModalLegacy">
				<div class="modal-dialog modal-sm modal-fullscreen-md-down modal-dialog-centered">
					<div class="modal-content rounded-0">
						<div class="modal-header">
							<h5 class="modal-title  text-white">Cadastro</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
						</div>
						<div class="modal-body app-form">
							<p class="text-muted font-xs">Por favor, entre com seus dados para finalizar o cadastro.</p>
							<span id="aviso-login"></span>
							<div class="mb-2">
								<label for="firstname2" class="form-label text-white">Nome</label>
								<input type="text" name="firstname2" class="form-control text-black" id="firstname2" placeholder="Nome" required="">
							</div>
							<div class="mb-2">
								<label for="lastname2" class="form-label text-white">Sobrenome</label>
								<input type="text" name="lastname2" class="form-control text-black" id="lastname2" placeholder="Sobrenome" required="">
							</div>
							<?php
							if ($enable_cpf == 1) { ?>
								<div class="mb-2">
									<label for="cpf" class="form-label text-white">CPF</label>
									<input id="cpf" name="cpf" type="text" class="form-control text-black" oninput="mascara(this)" maxlength="14" pattern=".{14,}" placeholder="000.000.000-00" onkeydown="javascript: fMasc( this, mCPF );" required>
								</div>
							<?php
							} ?>

							<div class="mb-2">
								<label for="phone" class="form-label text-white">Telefone</label>
								<input onkeyup="formatarTEL(this);" maxlength="15" name="phone" id="phone" required="" class="phone form-control text-black" placeholder="(00) 0000-0000" value="">
							</div>

							<?php
							if ($enable_two_phone == 1) {  ?>
								<div class="mb-2">
									<label for="phone_confirm" class="form-label text-white">Confirme seu telefone</label>
									<input onkeyup="formatarTEL(this);" maxlength="15" name="phone_confirm" id="phone_confirm" required="" class="phone_confirm form-control text-black" placeholder="(00) 0000-0000" value="">
								</div>
							<?php
							}
							if ($enable_email == 1) { ?>
								<div class="mb-2">
									<label for="email2" class="form-label text-white">E-mail</label>
									<input type="email" name="email2" class="form-control text-black" id="email2" placeholder="exemplo@exemplo.com" required>
								</div>
							<?php
							}

							if ($enable_password == '1') { ?>
								<div class="mb-2">
									<div class="form-floating font-weight-500">
										<input type="password" name="password" id="password" class="form-control text-black" placeholder="Senha" required=""><label for="password">Senha</label>
									</div>
								</div>
								<div class="btn btn-link btn-sm text-decoration-none mb-4 text-cardLink opacity-75"><a href="/recuperar-senha">Esqueci minha senha</a>
								</div>
							<?php
							}

							if (trim((string) $_settings->info('terms')) !== '' || function_exists('jnsalles_default_terms')) { ?>
								<div class="alert alert-primary mt-3 font-xss">Ao se cadastrar você concorda com nossos <a style="color:var(--incrivel-primaria);" href="<?= BASE_URL ?>termos-de-uso" target="_blank">termos</a>
								</div>
							<?php
							}
							?>
							<div class="d-flex justify-content-center align-items-center flex-column">
								<button type="submit" class="btn btn-wide-in btn-warning font-weight-500 rounded-pill mb-2">Continuar</button>
							</div>
						</div>
					</div>
				</div>
			</form>
	<?php
		}
	}
	?>
	<div class="modal fade" id="newCheckoutModal">
		<div class="modal-dialog modal-lg modal-fullscreen-md-down modal-dialog-centered">
			<div class="modal-content bg-dark">
				<div class="modal-header">
					<div class="modal-title  text-white">
						<div class="d-flex align-items-center gap-2">
							<button class="btn btn-lg btn-outline-secondary">
								<i class="bi bi-cart text-white"></i>
							</button>
							<div class="text-start text-white">
								<span>Compra referente a participação no sorteio</span>
								<div><?php echo isset($name) ? $name : ''; ?></div>
							</div>
						</div>
					</div>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body app-form">
					<div class="mb-3 steps-header d-flex justify-content-between align-items-center px-3">
						<div id="step1-header" class="step-header d-flex align-items-center w-100">
							<i class="active btn btn-secondary rounded-circle fs-3 p-3 bi bi-search"></i>
							<div class="step1-progress"></div>
						</div>
						<div id="step2-header" class="step-header d-flex align-items-center w-100">
							<i class="btn btn-secondary rounded-circle fs-3 p-3 bi bi bi-person-add"></i>
							<div class="step2-progress"></div>
						</div>
						<div id="step3-header" class="step-header d-flex align-items-center w-100">
							<i class="btn btn-secondary rounded-circle fs-3 p-3 bi bi-cart"></i>
							<div class="step3-progress"></div>
						</div>
						<div id="step4-header" class="step-header d-flex align-items-center">
							<i class="btn btn-secondary rounded-circle fs-3 p-3 bi bi-cash-coin"></i>
						</div>
					</div>
					<!-- Etapa 1 -->
					<div class="step" id="step1">
						<form id="loginModal">
							<div class="card mb-3" style="background-color: #c4c4c4;">
								<div class="card-body text-center">
									<div class="mb-2 d-flex justify-content-center align-items-center gap-2">
										<i class="bi bi-ticket-perforated fs-3 "></i>
										<div class="fs-5 fw-bold"><span class="qtd"><?= isset($min_purchase) ? $min_purchase : '' ?></span> x <span class="preco">R$ <?= $price ?></span> =</div>
									</div>
									<span class="total btn btn-success btn-lg" style="opacity: 1; font-size:1.1rem !important">R$
										<?php
										if (isset($price)) {
											$price_total = $price * $min_purchase;
											echo format_num($price_total, 2);
										}
										?>

									</span>
								</div>
							</div>
							<hr>
							<p class="text-muted font-xs">Por favor, entre com seus dados ou faça um cadastro.</p>
							<span id="aviso-login"></span>
							<div class="mb-3">
								<label for="phone" class="form-label text-white">Telefone</label>
								<input onkeyup="formatarTEL(this);" maxlength="15" name="phone" id="phone" required="" class="form-control text-black" placeholder="(00) 0000-0000" value="">
							</div>
							<?php
							if ($enable_password == '1') { ?>
								<div class="mb-2">
									<div class="form-floating font-weight-500">
										<input type="password" name="password" id="password" class="form-control text-black" placeholder="Senha" required=""><label for="password">Senha</label>
									</div>
								</div>
								<div class="btn btn-link btn-sm text-decoration-none mb-4 text-cardLink opacity-75"><a href="/recuperar-senha">Esqueci minha senha</a></div>
							<?php
							}
							?>
							<button type="submit" id="next1" class="btn btn-success font-weight-500 rounded w-100 mb-2">Prosseguir<i class="ms-2 bi bi-arrow-right"></i></button>
						</form>
					</div>

					<!-- Etapa 2 -->
					<div class="step" id="step2" style="display:none;">
						<form id="cadastroModal2">
							<div class="modal-header">
								<h5 class="modal-title  text-white">Cadastro</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
							</div>
							<div class="modal-body app-form">
								<p class="text-muted font-xs">Por favor, entre com seus dados para finalizar o cadastro.</p>
								<span id="aviso-login"></span>
								<div class="mb-2">
									<label for="firstname" class="form-label text-white">Nome</label>
									<input type="text" name="firstname" class="form-control text-black" id="firstname" placeholder="Nome" required="">
								</div>
								<div class="mb-2">
									<label for="lastname" class="form-label text-white">Sobrenome</label>
									<input type="text" name="lastname" class="form-control text-black" id="lastname" placeholder="Sobrenome" required="">
								</div>
								<?php
								if ($enable_cpf == 1) { ?>
									<div class="mb-2">
										<label for="cpf" class="form-label text-white">CPF</label>
										<input id="cpf" name="cpf" type="text" class="form-control text-black" oninput="mascara(this)" maxlength="14" pattern=".{14,}" placeholder="000.000.000-00" onkeydown="javascript: fMasc( this, mCPF );" required>
									</div>
								<?php
								} ?>

								<div class="mb-2">
									<label for="phone" class="form-label text-white">Telefone</label>
									<input onkeyup="formatarTEL(this);" maxlength="15" name="phone" id="phone" required="" class="phonec form-control text-black" placeholder="(00) 0000-0000" value="">
								</div>

								<?php
								if ($enable_two_phone == 1) {  ?>
									<div class="mb-2">
										<label for="phone_confirm" class="form-label text-white">Confirme seu telefone</label>
										<input onkeyup="formatarTEL(this);" maxlength="15" name="phone_confirm" id="phone_confirm" required="" class="phone_confirmc form-control text-black" placeholder="(00) 0000-0000" value="">
									</div>
								<?php
								}
								if ($enable_email == 1) { ?>
									<div class="mb-2">
										<label for="email" class="form-label text-white">E-mail</label>
										<input type="email" name="email" class="form-control text-black" id="email" placeholder="exemplo@exemplo.com" required>
									</div>
								<?php
								}

								if ($enable_password == '1') { ?>
									<div class="mb-2">
										<div class="form-floating font-weight-500">
											<input type="password" name="password" id="password" class="form-control text-black" placeholder="Senha" required=""><label for="password">Senha</label>
										</div>
									</div>
									<div class="btn btn-link btn-sm text-decoration-none mb-4 text-cardLink opacity-75"><a href="/recuperar-senha">Esqueci minha senha</a>
									</div>
								<?php
								}

								if (trim((string) $_settings->info('terms')) !== '' || function_exists('jnsalles_default_terms')) { ?>
									<div class="alert alert-primary mt-3 font-xss">Ao se cadastrar você concorda com nossos <a style="color:var(--incrivel-primaria);" href="<?= BASE_URL ?>termos-de-uso" target="_blank">termos</a>
									</div>
								<?php
								}
								?>
								<div class="d-flex justify-content-center align-items-center gap-3">
									<button type="button" id="prev1" class="btn btn-secondary font-weight-500 rounded-pill mb-2"><i class="me-2 bi bi-arrow-left"></i>Voltar</button>
									<button type="submit" id="next2" class="btn btn-success font-weight-500 rounded-pill mb-2">Prosseguir<i class="ms-2 bi bi-arrow-right"></i></button>
								</div>
							</div>
						</form>
					</div>

					<!-- Etapa 3 -->
					<div class="step" id="step3" style="display:none;">
						<div class="card mb-3 border-0 checkout-summary-card">
							<div class="card-body text-center row align-items-center justify-content-between">
								<?php if ($enable_upsell == 1) { ?>
									<div class="col-md-6 mb-3">
										<div class="card" style="position: relative;">
											<div class="card-body text-start">
												<h5>Oferta Imperdível</h5>
												<p class="font-xsss">Aumente suas chances de ganhar!</p>
											</div>
											<div class="card-footer">
												<div class="form-check d-flex align-items-end gap-2">
													<input class="form-check-input" type="checkbox" value="1" id="upsell">
													<label class="form-check-label" for="upsell">
														+<?= $qtd_upsell ?> Bilhetes por apenas
														<span>R$
															<?php
															if (isset($price)) {
																$price_total_upsell = $qtd_upsell * ($price - (($price * $desconto_upsell) / 100));
																echo format_num($price_total_upsell, 2);
															}
															?>
														</span>
													</label>
												</div>
											</div>
											<div class="" style="position: absolute;top:-6px; right: 10px;">
												<div style="position: relative;">
													<svg width="65" height="45" viewBox="0 0 77 53" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M61.8734 0H71.7112V7.47695H61.8734V0Z" fill="#EABE3B"></path>
														<path d="M71.7112 0C74.6324 0 77 2.35807 77 5.26762V7.47695H66.4224V5.26762C66.4224 2.35807 68.79 0 71.7112 0Z" fill="#3D1E0B"></path>
														<path d="M-3.8147e-05 5.26533V50.7312C-3.8147e-05 52.4096 1.76902 53.5057 3.28076 52.7632L30.9975 39.149C32.3933 38.4637 34.0291 38.4637 35.4248 39.149L63.1416 52.7632C64.6533 53.5057 66.4224 52.4096 66.4224 50.7312V0H5.28646C2.36636 0 -3.8147e-05 2.35693 -3.8147e-05 5.26533Z" fill="#EABE3B"></path>
													</svg>
													<span class="fw-bold" style="position: absolute;top:33%; left:47%;transform: translate(-50%,-50%);"><?= intval($desconto_upsell) ?>%</span>
												</div>
											</div>
										</div>
									</div>
								<?php } ?>
								<div class="<?= $enable_upsell == 1 ? 'col-md-6' : 'col-12' ?>">
									<div class="mb-2 d-flex justify-content-center align-items-center gap-2 flex-wrap">
										<i class="bi bi-ticket-perforated fs-3 "></i>
										<div class="fs-5 fw-bold"><span class="qtd"><?= isset($min_purchase) ? $min_purchase : '' ?></span> x <span class="preco">R$ <?= $price ?></span> =</div>
										<span class="total fs-5 fw-bold">R$
											<?php
											if (isset($price)) {
												$price_total = $price * $min_purchase;
												echo format_num($price_total, 2);
											}
											?>
										</span>
									</div>
									<hr>
									<div class="area_do_upsell mb-2 d-flex justify-content-end align-items-center gap-2">

									</div>
									<div class="total-geral mb-2 d-flex justify-content-end align-items-center gap-2"></div>
								</div>

							</div>
						</div>
						<div class="d-flex justify-content-center align-items-center gap-3">
							<button type="button" id="submitFormNew" class="btn w-100 btn-success font-weight-500 rounded-pill mb-2">FINALIZAR<i class="ms-2 bi bi-arrow-right"></i></button>
						</div>
						<div class="text-center text-secondary small checkout-session-note">
							<span>Você esta realizando a compra como <?= $_settings->userdata('firstname') ? $_settings->userdata('firstname') : '' ?> <?= $_settings->userdata('lastname') ? $_settings->userdata('lastname') : '' ?></span>
						</div>
					</div>
					<style>
						.checkout-summary-card { background: linear-gradient(135deg, #f8fafc, #eef2f4); color: #172029; }
						.checkout-summary-card .card-body { min-height: 0; padding: 1.25rem 1rem; }
						.checkout-summary-card hr { margin: .75rem auto; max-width: 360px; opacity: .18; }
						.checkout-session-note { margin-top: .25rem; opacity: .8; }
					</style>

					<!-- Etapa 4 -->
					<div class="step" id="step4" style="display:none;">

					</div>

				</div>
			</div>
		</div>
	</div>
	<script>
		function fMasc(objeto, mascara) {
			obj = objeto
			masc = mascara
			setTimeout("fMascEx()", 1)
		}

		function fMascEx() {
			obj.value = masc(obj.value)
		}

		$(document).ready(function() {
			$('#form-cadastrar, #cadastroModalLegacy').submit(function(e) {
				e.preventDefault();
				var phoneValue = $('.phone').val();
				var phoneConfirmValue = $('.phone_confirm').val();
				if ($('.phone')) {
					if (phoneValue.length < 15 || phoneValue.length > 15) {
						alert('Telefone inválido. Por favor corrija.');
						return;
					}
				}
				if (phoneConfirmValue) {
					if (phoneConfirmValue != phoneValue) {
						alert('Telefone inválido. Por favor corrija');
						return;
					}
				}
				$.ajax({
					url: _base_url_ + "class/Customer.php?action=registration",
					method: 'POST',
					type: 'POST',
					data: new FormData($(this)[0]),
					dataType: 'json',
					cache: false,
					processData: false,
					contentType: false,
					error: err => {
						console.log(err)
						$('#overlay').stop(true, true).hide();
						alert('Não foi possível concluir o cadastro. Tente novamente.')
					},
					success: function(resp) {
						if (resp.status == 'success') {
							//alert(\'Cadastrado com sucessso.\');                
							$('.btn-close').click();
							$('#overlay').fadeIn(300);
							setTimeout(function() {
								$('#add_to_cart').click();
							}, 1000);
							setTimeout(function() {
								$('#place_order').click();
								//$("#overlay").fadeOut(300);                
							}, 2000);
						} else if (resp.status == 'phone_already') {
							alert(resp.msg);
						} else if (resp.status == 'cpf_already') {
							alert(resp.msg);
						} else if (resp.status == 'cpf_invalid') {
							alert(resp.msg);
						} else if (resp.status == 'email_already') {
							alert(resp.msg);
						} else {
							alert('An error occurred')
							console.log(resp)
						}
					}
				})
			})
		})
		$(document).ready(function() {
			$('#loginModal2').submit(function(e) {
				e.preventDefault()
				$.ajax({
					url: _base_url_ + "class/Auth.php?action=login_customer",
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

							<?php

							if (isset($slug)) {

							?>
								$('.btn-close').click();
								$('#overlay').fadeIn(300);
								setTimeout(function() {
									$('#add_to_cart').click();
								}, 1000);
								setTimeout(function() {
									$('#place_order').click();
									$("#overlay").fadeOut(300);
								}, 2000);
							<?php
							} else {
							?>
								location.reload();
							<?php
							}

							?>
						} else if (!!resp.msg) {
							<?php
							if (!isset($slug)) {

							?>
								var phone = $('#loginModal #phone').val();
								$('#aviso-login').html('<div style="color:red;font-size:14px;margin-bottom:10px;">Telefone ou senha incorretos!</div>');
							<?php
							} else {
							?>
								var phone = $('#loginModal #phone').val();
								$('#cadastroModalLegacy #phone').val(phone);
								$('#openCadastro').click();
							<?php
							}

							?>
						} else {
							alert('An error occurred')
							console.log(resp)
						}
					}
				})
			})
		})

		function mCPF(cpf) {
			cpf = cpf.replace(/\D/g, "")
			cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2")
			cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2")
			cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2")
			return cpf
		}

		function mascara(i) {
			let valor = i.value.replace(/\D/g, '');
			if (isNaN(valor[valor.length - 1])) {
				i.value = valor.slice(0, -1);
				return;
			}
			i.setAttribute("maxlength", "14");
			i.value = valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');

		}

		function formatarTEL(e) {
			v = e.value, console.log("v:" + v), console.log("v.length:" + v.length), v = v.replace(/\D/g, ""), v = v.replace(/^(\d{2})(\d)/g, "($1) $2"), console.log("v:" + v), v = v.replace(/(\d)(\d{4})$/, "$1-$2"), e.value = v
		}
		<?php
		echo 'function formatarCPF(r) { var e = (r = r.replace(/\\D/g, "")).replace(/(\\d{3})(\\d{3})(\\d{3})(\\d{2})/, "$1.$2.$3-$4"); document.getElementById("cpf").value = e }';
		?>
	</script>
	<script>
		$(document).ready(function() {
			// Navegação entre as etapas
			$('#next1').click(function() {
				validateStep1()
			});

			$('#next2').click(function() {
				validateStep2()
			});

			$('#next3').click(function() {
				if (validateStep3()) {
					$('#step3').hide();
					$('#step4').show();
					$('.step3-progress').addClass('active');
					$('#step4-header i').addClass('active');
				}
			});

			$('#prev1').click(function() {
				$('#step2').hide();
				$('#step1').show();
				$('#step2-header').removeClass('active');
			});

			$('#prev2').click(function() {
				$('#step3').hide();
				$('#step2').show();
				$('#step3-header').removeClass('active');
			});

			$('#prev3').click(function() {
				$('#step4').hide();
				$('#step3').show();
				$('#step4-header').removeClass('active');
			});
			<?php if ($user_id): ?>
				$('#step1').hide();
				$('#step3').show();
				$('.step1-progress').addClass('active');
				$('.step2-progress').addClass('active');
				$('#step2-header i').addClass('active');
				$('#step1-header i').removeClass('bi-search');
				$('#step1-header i').addClass('bi-check');
				$('#step2-header i').removeClass('bi-person-add');
				$('#step2-header i').addClass('bi-check');
				$('#step3-header i').addClass('active');
			<?php endif; ?>

			// Validação de cada etapa
			function validateStep1() {

				$('#overlay').fadeIn(300);
				$('#loginModal').off('submit.checkout').on('submit.checkout', function(e) {
					e.preventDefault()
					$.ajax({
						url: _base_url_ + "class/Auth.php?action=login_customer",
						method: 'POST',
						type: 'POST',
						data: new FormData($(this)[0]),
						dataType: 'json',
						cache: false,
						processData: false,
						contentType: false,
						error: err => {
							console.log(err)
							$('#overlay').stop(true, true).hide();
							alert('Não foi possível entrar. Tente novamente.')
						},
						success: function(resp) {
							console.log(resp)
							if (resp.status == 'success') {
								<?php
								if (isset($slug)) {
								?>
									//$('.btn-close').click();
									$('#overlay').fadeOut(300);
									// setTimeout(function() {
									//     $('#add_to_cart').click();
									// }, 1000);
									// setTimeout(function() {
									//     $('#place_order').click();
									//     //$("#overlay").fadeOut(300);                    
									// }, 2000);
									$('#step1').hide();
									$('#step3').show();
									$('.step1-progress').addClass('active');
									$('.step2-progress').addClass('active');
									$('#step2-header i').addClass('active');
									$('#step1-header i').removeClass('bi-search');
									$('#step1-header i').addClass('bi-check');
									$('#step2-header i').removeClass('bi-person-add');
									$('#step2-header i').addClass('bi-check');
									$('#step3-header i').addClass('active');
									return true
								<?php

								} else { ?>
									location.reload();
								<?php
								}
								?>
							} else if (!!resp.msg) {
								<?php
								if (!isset($slug)) {
								?>
									var phone = $('#loginModal #phone').val();
									$('#aviso-login').html('<div style="color:red;font-size:14px;margin-bottom:10px;">Telefone ou senha incorretos!</div>');
								<?php
								} else { ?>
									$('#overlay').fadeOut(300);
									var phone = $('#loginModal #phone').val();
									$('#cadastroModal2 #phone').val(phone);
									//$('#openCadastro').click();
									$('#step1').hide();
									$('#step2').show();
									$('.step1-progress').addClass('active');
									$('#step2-header i').addClass('active');
									$('#step1-header i').removeClass('bi-search');
									$('#step1-header i').addClass('bi-check');
								<?php
								}
								?>
								return false
							} else {
								alert('An error occurred')
								console.log(resp)
								return false
							}
						}
					})
				})

			}

			function validateStep2() {
				$('#overlay').fadeIn(300);
				$('#cadastroModal2').off('submit.checkout').on('submit.checkout', function(e) {
					e.preventDefault();
					var phoneValue = $('.phonec').val();
					var phoneConfirmValue = $('.phone_confirmc').val();
					if ($('.phonec')) {
						if (phoneValue.length < 15 || phoneValue.length > 15) {
							$('#overlay').fadeOut(300);
							alert('Telefone inválido. Por favor corrija.');
							return;
						}
					}
					if (phoneConfirmValue) {
						if (phoneConfirmValue != phoneValue) {
							$('#overlay').fadeOut(300);
							alert('Telefone inválido. Por favor corrija');
							return;
						}
					}
					$.ajax({
						url: _base_url_ + "class/Customer.php?action=registration",
						method: 'POST',
						type: 'POST',
						data: new FormData($(this)[0]),
						dataType: 'json',
						cache: false,
						processData: false,
						contentType: false,
						error: err => {
							console.log(err)
							$('#overlay').stop(true, true).hide();
							alert('Não foi possível concluir o cadastro. Tente novamente.')
						},
						success: function(resp) {
							console.log(resp)
							if (resp.status == 'success') {
								//alert(\'Cadastrado com sucessso.\');                
								$('#overlay').fadeOut(300);
								// setTimeout(function() {
								//     $('#add_to_cart').click();
								// }, 1000);
								// setTimeout(function() {
								//     $('#place_order').click();
								//     //$("#overlay").fadeOut(300);                
								// }, 2000);
								$('#step2').hide();
								$('#step3').show();
								$('.step2-progress').addClass('active');
								$('#step3-header i').addClass('active');
								$('#step2-header i').removeClass('bi-person-add');
								$('#step2-header i').addClass('bi-check');
								return true
							} else if (resp.status == 'phone_already') {
								alert(resp.msg);
								$('#overlay').fadeOut(300);
								return false
							} else if (resp.status == 'cpf_already') {
								alert(resp.msg);
								$('#overlay').fadeOut(300);
								return false
							} else if (resp.status == 'cpf_invalid') {
								alert(resp.msg);
								$('#overlay').fadeOut(300);
								return false
							} else if (resp.status == 'email_already') {
								alert(resp.msg);
								$('#overlay').fadeOut(300);
								return false
							} else {
								alert('An error occurred')
								console.log(resp)
								$('#overlay').fadeOut(300);
								return false
							}
						}
					})
				})
			}

			function validateStep3() {
				// let telefone = $('#telefone').val();
				// let dataNascimento = $('#dataNascimento').val();
				// if (telefone === "" || dataNascimento === "") {
				//     alert("Por favor, preencha todos os campos da Etapa 3.");
				//     return false;
				// }
				return true;
			}

			// Submissão do formulário
			$('#submitFormNew').click(function(event) {
				event.preventDefault();
				$("#overlay").fadeIn(300);
				$('#add_to_cart').click();
				setTimeout(function() {
					$('#place_order').click();
					//$("#overlay").fadeOut(300);                    
				}, 2000);
			});
		});
	</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous" data-nscript="afterInteractive"></script>
	<script>
		$(window).on('load', function() {
			// Remove o efeito blur após o carregamento da página
			$('.app-main img, h1, h2, h3, h4, h5, h6, p, .badge, .app-title-desc').css('filter', 'blur(0px)');
			$('.imgs-footer img').css('filter', 'contrast(0.5)');
		});
	</script>
	<script>
		$(document).ready(function() {

			// Salva o elemento da div .gemeoss
			var gemeoss = $('.gemeoss');

			// Define a altura original da div para restaurar depois
			var originalHeight = gemeoss.outerHeight();

			// Define o ponto de scroll para ativar a mudança
			var scrollTrigger = 50; // Alterar conforme necessário (em pixels)

			// Adiciona um ouvinte de evento para o scroll
			$(window).on('scroll', function() {
				if ($(this).scrollTop() > scrollTrigger) {
					// Quando o scroll passar de scrollTrigger, diminui a altura da div
					gemeoss.css('height', '0');
				} else {
					// Caso contrário, restaura a altura original
					gemeoss.css('height', originalHeight);
				}
			});
		});
	</script>
	<script>
		$('#upsell').change(function() {
			if ($(this).is(':checked')) {
				// Ação quando o checkbox for marcado
				var price_upsell = '<?= $price_total_upsell ?>'
				var qtd_upsell = '<?= $qtd_upsell ?>'

				var total = $('#total').html().replace(/\s+/g, '').replace('R$', '').replace(',', '.');
				console.log(price_upsell)
				console.log(total)
				var valorTotal = formatCurrency(parseFloat(total) + parseFloat(price_upsell))
				$('.area_do_upsell').html(`
                            <div class="mb-2 d-flex justify-content-center align-items-center gap-2">
                                    <i class="bi bi-ticket-perforated fs-3 "></i>
                                    <div class="fs-5 fw-bold"><span class="qtd">${qtd_upsell}</span> =</div>
                                    <span class="total_upsell fs-5 fw-bold">R$ ${price_upsell}</span>
                            </div>
                            
                        `)
				$('.total-geral').html(`
                        <hr>
                            <div class="text-success fs-4 fw-bold">Total = R$ ${valorTotal}</div>
                        `)
			} else {
				$('.area_do_upsell').html('')
				$('.total-geral').html('')
				// Ação quando o checkbox for desmarcado
				console.log('Checkbox desmarcado');
			}
		});
	</script>

	</body>

	</html>
