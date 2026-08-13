<?php


echo '<div class="container app-main">' . "\r\n" . '   <div class="app-title mb-2">' . "\r\n" . '      <h1>🏆 Ganhadores</h1>' . "\r\n" . '      <div class="app-title-desc">sortudos</div>' . "\r\n" . '   </div>' . "\r\n" . '   ';
	$sql = 'SELECT name AS product_name, draw_number, draw_winner, image_path, slug, date_of_draw FROM product_list WHERE draw_number <> \'\' ORDER BY date_of_draw DESC LIMIT 5';
	$products = $conn->query($sql);
echo '   <div class="app-content">' . "\r\n" . '      ';

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
							   	<div  href="">
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
					
					?>
				</div>
			</div>
		</div>
	<?php
	
