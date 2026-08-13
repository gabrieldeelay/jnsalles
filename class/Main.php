<?php
require_once "../settings.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;

class Main extends DBConnection
{
    private $settings = null;

    public function __construct()
    {
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function capture_err()
    {
        if (!$this->conn->error) {
            return false;
        } else {
            $resp["status"] = "failed";
            $resp["error"] = $this->conn->error;
            return json_encode($resp);
            exit();
        }
    }

    private function campaign_upload_error($code)
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'A imagem excede o limite permitido pelo servidor.',
            UPLOAD_ERR_FORM_SIZE => 'A imagem excede o limite permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'O envio da imagem foi interrompido. Tente novamente.',
            UPLOAD_ERR_NO_TMP_DIR => 'O servidor não conseguiu preparar a imagem.',
            UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu gravar a imagem.',
            UPLOAD_ERR_EXTENSION => 'O envio da imagem foi bloqueado pelo servidor.',
        ];
        return $messages[$code] ?? 'Não foi possível receber a imagem.';
    }

    private function campaign_blob_credentials()
    {
        $token = trim((string) getenv('BLOB_READ_WRITE_TOKEN'));
        $parts = explode('_', $token);
        $storeId = isset($parts[3]) ? trim($parts[3]) : '';

        if ($token === '' || $storeId === '') {
            return false;
        }

        return ['token' => $token, 'store_id' => $storeId];
    }

    private function campaign_blob_request($path, $method, $body, $headers = [])
    {
        $credentials = $this->campaign_blob_credentials();
        if (!$credentials || !function_exists('curl_init')) {
            return ['ok' => false, 'message' => 'O armazenamento de imagens não está disponível.'];
        }

        $requestId = $credentials['store_id'] . ':' . time() . ':' . bin2hex(random_bytes(5));
        $requestHeaders = array_merge([
            'Authorization: Bearer ' . $credentials['token'],
            'x-api-version: 12',
            'x-api-blob-request-id: ' . $requestId,
            'x-api-blob-request-attempt: 0',
            'x-vercel-blob-store-id: ' . $credentials['store_id'],
            'Expect:',
        ], $headers);

        $curl = curl_init('https://vercel.com/api/blob' . $path);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $requestHeaders,
        ]);
        $rawResponse = curl_exec($curl);
        $curlError = curl_error($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $response = is_string($rawResponse) ? json_decode($rawResponse, true) : null;
        if ($curlError !== '' || $statusCode < 200 || $statusCode >= 300 || !is_array($response)) {
            $apiMessage = is_array($response) ? ($response['error']['message'] ?? '') : '';
            error_log('[campaign-blob] request failed status=' . $statusCode . ' curl=' . ($curlError !== '' ? $curlError : 'none') . ' api=' . $apiMessage);
            return ['ok' => false, 'message' => 'Não foi possível gravar a imagem no armazenamento permanente.'];
        }

        return ['ok' => true, 'data' => $response];
    }

    private function delete_campaign_blob($url)
    {
        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));
        if (!preg_match('/^[a-z0-9-]+\.public\.blob\.vercel-storage\.com$/', $host)) {
            return;
        }

        $this->campaign_blob_request(
            '/delete',
            'POST',
            json_encode(['urls' => [(string) $url]]),
            ['Content-Type: application/json']
        );
    }

    private function save_campaign_main_image($file, $productId)
    {
        $error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => $this->campaign_upload_error($error)];
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['ok' => false, 'message' => 'O arquivo de imagem recebido é inválido.'];
        }

        if ((int) ($file['size'] ?? 0) > 4 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'A imagem continua muito grande. Escolha um arquivo de até 4 MB.'];
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        $accepted = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!$imageInfo || !in_array($imageInfo['mime'] ?? '', $accepted, true)) {
            return ['ok' => false, 'message' => 'Formato inválido. Use JPG, PNG, GIF ou WebP.'];
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        if ($width < 1 || $height < 1 || ($width * $height) > 40000000) {
            return ['ok' => false, 'message' => 'A resolução da imagem é inválida ou muito alta.'];
        }

        $contents = @file_get_contents($file['tmp_name']);
        if (!is_string($contents) || $contents === '') {
            return ['ok' => false, 'message' => 'A imagem está corrompida ou não pôde ser aberta.'];
        }

        $imageBytes = $contents;
        $outputMime = (string) $imageInfo['mime'];
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        $hasImageEditor = function_exists('imagecreatefromstring')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagecopyresampled')
            && function_exists('imagecrop')
            && function_exists('imagejpeg');

        if ($hasImageEditor) {
            $source = @imagecreatefromstring($contents);
            if (!$source) {
                return ['ok' => false, 'message' => 'A imagem está corrompida ou não pôde ser aberta.'];
            }

            $targetSize = 600;
            $scale = max($targetSize / $width, $targetSize / $height);
            $resizedWidth = max($targetSize, (int) ceil($width * $scale));
            $resizedHeight = max($targetSize, (int) ceil($height * $scale));
            $resized = imagecreatetruecolor($resizedWidth, $resizedHeight);
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $white);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $resizedWidth, $resizedHeight, $width, $height);

            $cropped = imagecrop($resized, [
                'x' => max(0, (int) floor(($resizedWidth - $targetSize) / 2)),
                'y' => max(0, (int) floor(($resizedHeight - $targetSize) / 2)),
                'width' => $targetSize,
                'height' => $targetSize,
            ]);
            imagedestroy($source);
            imagedestroy($resized);

            if (!$cropped) {
                return ['ok' => false, 'message' => 'Não foi possível ajustar a imagem para o formato da campanha.'];
            }

            ob_start();
            $saved = imagejpeg($cropped, null, 88);
            $imageBytes = ob_get_clean();
            imagedestroy($cropped);

            if (!$saved || !is_string($imageBytes) || $imageBytes === '') {
                return ['ok' => false, 'message' => 'Não foi possível salvar a nova imagem da campanha.'];
            }

            $outputMime = 'image/jpeg';
        }

        $extension = $extensions[$outputMime] ?? 'jpg';
        $pathname = 'campanhas/' . (int) $productId . '/campanha-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $blobResult = $this->campaign_blob_request(
            '/?pathname=' . rawurlencode($pathname),
            'PUT',
            $imageBytes,
            [
                'Content-Type: application/octet-stream',
                'Content-Length: ' . strlen($imageBytes),
                'x-vercel-blob-access: public',
                'x-content-type: ' . $outputMime,
                'x-add-random-suffix: 0',
                'x-cache-control-max-age: 31536000',
            ]
        );

        $blobUrl = $blobResult['data']['url'] ?? '';
        if (empty($blobResult['ok']) || !filter_var($blobUrl, FILTER_VALIDATE_URL)) {
            return ['ok' => false, 'message' => $blobResult['message'] ?? 'Não foi possível enviar a imagem.'];
        }

        return ['ok' => true, 'path' => $blobUrl];
    }

    public function save_product()
    {
        if (empty($this->settings->userdata('firstname')) || $this->settings->userdata('type') != 1) {
            http_response_code(403);
            return json_encode(['status' => 'failed', 'msg' => 'Não autorizado.']);
        }

        $id = $_POST["id"];
        $name = $this->conn->real_escape_string(
            filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS)
        );
        $description = $this->conn->real_escape_string(
            filter_var($_POST["description"], FILTER_SANITIZE_SPECIAL_CHARS)
        );
        $type_of_draw = $this->conn->real_escape_string($_POST["type_of_draw"]);
        $qty_numbers = $this->conn->real_escape_string($_POST["qty_numbers"]);

        if ($type_of_draw == 3) {
            $qty_numbers = 25;
        }

        if ($type_of_draw == 4) {
            $qty_numbers = 50;
        }
        $price = $this->conn->real_escape_string($_POST["price"]);
        $price = str_replace(".", "", $price);
        $price = str_replace(",", ".", $price);
        $price = (float) $price;
        $limit_orders = $this->conn->real_escape_string($_POST["limit_orders"]);
        $min_purchase = $this->conn->real_escape_string($_POST["min_purchase"]);
        $max_purchase = $this->conn->real_escape_string($_POST["max_purchase"]);
        $status = $this->conn->real_escape_string($_POST["status"]);
        $pending_numbers = $this->conn->real_escape_string("0");
        $paid_numbers = $this->conn->real_escape_string("0");

        $discount_qty = json_encode($_POST["discount_qty"]);

        $discount_amount = isset($_POST["discount_amount"]) ? array_map(function ($value) {
            return number_format((float) str_replace(",", ".", $value), 2, ".", "");
        }, $_POST["discount_amount"]) : [];
        $discount_amount = json_encode($discount_amount);


        $roleta_qty = json_encode($_POST["roleta_qty"]);
        $roleta_amount = json_encode($_POST["roleta_amount"]);

        $box_qty = json_encode($_POST["box_qty"]);
        $box_amount = json_encode($_POST["box_amount"]);

        $draw_name_list = filter_var(
            $_POST["draw_name"],
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY
        );
        $draw_name_json_str = json_encode($draw_name_list);
        $draw_name_json_escaped = $this->conn->real_escape_string(
            $draw_name_json_str
        );
        $draw_name = $draw_name_json_escaped;
        $draw_number = json_encode($_POST["draw_number"]);

        if ($draw_name == '[""]') {
            $draw_name = "";
        }

        if ($draw_number == '[""]') {
            $draw_number = "";
        }
        $enable_discount = isset($_POST["enable_discount"]) ? 1 : 0;
        $enable_double = isset($_POST["enable_double"]) ? 1 : 0;
        $enable_upsell = isset($_POST["enable_upsell"]) ? 1 : 0;
        $double_ini = $this->conn->real_escape_string($_POST["double_ini"]);
        $double_fim = $this->conn->real_escape_string($_POST["double_fim"]);
        $qtd_upsell = $this->conn->real_escape_string($_POST["qtd_upsell"]);
        $desconto_upsell = $this->conn->real_escape_string($_POST["desconto_upsell"]);
        $enable_discount = $this->conn->real_escape_string($enable_discount);
        $enable_cumulative_discount = isset(
            $_POST["enable_cumulative_discount"]
        )
            ? 1
            : 0;
        $enable_cumulative_discount = $this->conn->real_escape_string(
            $enable_cumulative_discount
        );
        $ranking_qty = $this->conn->real_escape_string($_POST["ranking_qty"])
            ? $this->conn->real_escape_string($_POST["ranking_qty"])
            : 0;
        $enable_ranking = isset($_POST["enable_ranking"]) ? 1 : 0;
        $enable_ranking = $this->conn->real_escape_string($enable_ranking);
        $ranking_message = $this->conn->real_escape_string(
            $_POST["ranking_message"]
        );
        $enable_ranking_show = isset($_POST["enable_ranking_show"]) ? 1 : 0;
        $enable_ranking_show = $this->conn->real_escape_string(
            $enable_ranking_show
        );
        
        $enable_ranking_definido = isset($_POST["enable_ranking_definido"]) ? 1 : 0;
        $ranking_ini = $this->conn->real_escape_string($_POST['ranking_ini']);
        $ranking_fim = $this->conn->real_escape_string($_POST['ranking_fim']);
        
        $ranking_type = $this->conn->real_escape_string($_POST["ranking_type"]);
        $enable_progress_bar = isset($_POST["enable_progress_bar"]) ? 1 : 0;
        $enable_progress_bar = $this->conn->real_escape_string(
            $enable_progress_bar
        );
        $enable_progress_bar_fake_value = $this->conn->real_escape_string($_POST["enable_progress_bar_fake_value"]);
        $enable_progress_bar_fake_value = str_replace(".", "", $enable_progress_bar_fake_value);
        $enable_progress_bar_fake_value = str_replace(",", ".", $enable_progress_bar_fake_value);
        $enable_progress_bar_fake_value = (float) $enable_progress_bar_fake_value;


        $enable_progress_bar_fake = isset($_POST["enable_progress_bar_fake"]) ? 1 : 0;
        $enable_progress_bar_fake = $this->conn->real_escape_string($enable_progress_bar_fake);
        $status_display = $this->conn->real_escape_string(
            $_POST["status_display"]
        );
        $subtitle = $this->conn->real_escape_string($_POST["subtitle"]);
        $cotas_premiadas = $this->conn->real_escape_string($_POST["cotas_premiadas"]);
        $cotas_premiadas_descricao = $this->conn->real_escape_string($_POST["cotas_premiadas_descricao"]);
        $cotas_premiadas_roleta = $this->conn->real_escape_string($_POST["cotas_premiadas_roleta"]);
        $cotas_premiadas_descricao_roleta = $this->conn->real_escape_string($_POST["cotas_premiadas_descricao_roleta"]);
        $cotas_premiadas_box = $this->conn->real_escape_string($_POST["cotas_premiadas_box"]);
        $cotas_premiadas_descricao_box = $this->conn->real_escape_string($_POST["cotas_premiadas_descricao_box"]);



        $date_of_draw = isset($_POST["date_of_draw"]) ? $this->conn->real_escape_string($_POST["date_of_draw"]) : NULL;

        $limit_order_remove = $this->conn->real_escape_string($_POST["limit_order_remove"]);

        $qty_select_1 = $this->conn->real_escape_string($_POST["qty_select_1"]);
        $qty_select_2 = $this->conn->real_escape_string($_POST["qty_select_2"]);
        $qty_select_3 = $this->conn->real_escape_string($_POST["qty_select_3"]);
        $qty_select_4 = $this->conn->real_escape_string($_POST["qty_select_4"]);
        $qty_select_5 = $this->conn->real_escape_string($_POST["qty_select_5"]);
        $qty_select_6 = $this->conn->real_escape_string($_POST["qty_select_6"]);
        $enable_sale = isset($_POST["enable_sale"]) ? 1 : 0;
        $enable_sale = $this->conn->real_escape_string($enable_sale);
        $sale_price = $this->conn->real_escape_string(0);
        $sale_qty = 0;
        $sale_price = str_replace(".", "", $sale_price);
        $sale_price = str_replace(",", ".", $sale_price);
        $sale_price = (float) $sale_price;
        $private_draw = isset($_POST["private_draw"]) ? 1 : 0;
        $private_draw = $this->conn->real_escape_string($private_draw);
        $featured_draw = isset($_POST["featured_draw"]) ? 1 : 0;
        $featured_draw = $this->conn->real_escape_string($featured_draw);
        $status_auto_cota = isset($_POST["status_auto_cota"]) == 1 ? 1 : 0;
        $status_auto_cota_roleta = isset($_POST["status_auto_cota_roleta"]) == 1 ? 1 : 0;
        $status_auto_cota_box = isset($_POST["status_auto_cota_box"]) == 1 ? 1 : 0;
        $quantidade_auto_cota = isset($_POST["quantidade_auto_cota"]) ? 1 : 0;
        $quantidade_auto_cota_diario = isset($_POST["quantidade_auto_cota_diario"]) ? 1 : 0;
        $cota_diaria_ini = $this->conn->real_escape_string($_POST["cota_diaria_ini"]);
        $cota_diaria_fim = $this->conn->real_escape_string($_POST["cota_diaria_fim"]);
        $probabilidade = $this->conn->real_escape_string($_POST["probabilidade"]);
        $tipo_auto_cota = $this->conn->real_escape_string($_POST["tipo_auto_cota"]);
        $tipo_auto_cota_roleta = $this->conn->real_escape_string($_POST["tipo_auto_cota_roleta"]);
        $tipo_auto_cota_box = $this->conn->real_escape_string($_POST["tipo_auto_cota_box"]);
        $cotas_premiadas_premios = $this->conn->real_escape_string($_POST["cotas_premiadas_premios"]);
        $cotas_premiadas_premios_roleta = $this->conn->real_escape_string($_POST["cotas_premiadas_premios_roleta"]);
        $cotas_premiadas_premios_box = $this->conn->real_escape_string($_POST["cotas_premiadas_premios_box"]);
        
        $habilitar_cota_sorte = isset($_POST["habilitar_cota_sorte"]) ? 1 : 0;
        $cota_sorte_ini = $this->conn->real_escape_string($_POST["cota_sorte_ini"]);
        $cota_sorte_fim = $this->conn->real_escape_string($_POST["cota_sorte_fim"]);
        $cota_sorte = $this->conn->real_escape_string($_POST["cota_sorte"]);
        $quantidade_compra_sorte = $this->conn->real_escape_string($_POST["quantidade_compra_sorte"]);

        $roleta = isset($_POST["roleta"]) ? 1 : 0;
        $box = isset($_POST["box"]) ? 1 : 0;


        $valor_base_auto = intval($_POST["valor_base_auto"]);
        $slug = slugify($name);
        $check_slug = $this->conn->query(
            'SELECT * FROM `product_list` where `slug` LIKE \'' . $slug . '%\''
        )->num_rows;

        if (0 < $check_slug) {
            $check_slug += 1;
            $slug = $slug . "-" . strval($check_slug);
        }

        $dealer_active = $this->settings->info("dealer_active");

        if ($dealer_active == 1) {
            $dealer_limit_quantity_numbers = $this->settings->info(
                "dealer_limit_quantity_numbers"
            )
                ? $this->settings->info("dealer_limit_quantity_numbers")
                : 10000000;

            if ($dealer_limit_quantity_numbers < $qty_numbers) {
                $resp["status"] = "failed";
                $resp["msg"] =
                    "A quantidade de números da campanha deve ser de até " .
                    $dealer_limit_quantity_numbers .
                    " números.";
                return json_encode($resp);
            }

            $dealer_limit_raffle_quantity = $this->settings->info(
                "dealer_limit_raffle_quantity"
            );

            if (empty($id)) {
                $raffles = $this->conn->query(
                    "SELECT * FROM `product_list` WHERE status <> 3"
                )->num_rows;
            } else {
                $raffles = $this->conn->query(
                    "SELECT * FROM `product_list` WHERE status <> 3 AND id <> " .
                        $id
                )->num_rows;
            }

            if ($dealer_limit_raffle_quantity <= $raffles) {
                $resp["status"] = "failed";
                $resp["msg"] =
                    "Voce atingiu o limite de campanhas ativas. Seu limite é de " .
                    $dealer_limit_raffle_quantity .
                    " campanhas ativas.";
                return json_encode($resp);
            }
        }

        $sql = "";

        if (empty($id)) {
            $sql =
               'INSERT INTO `product_list` (`name`,`description`,`price`,`status`,`type_of_draw`,`qty_numbers`,`limit_orders`,`min_purchase`,`max_purchase`,`slug`,`pending_numbers`,`paid_numbers`,`ranking_qty`,`enable_ranking`,`enable_ranking_definido`,`ranking_ini`,`ranking_fim`,`enable_progress_bar`,`enable_progress_bar_fake`,`enable_progress_bar_fake_value`,`draw_number`,`status_display`, `subtitle`, `cotas_premiadas`, `cotas_premiadas_descricao`, `cotas_premiadas_roleta`, `cotas_premiadas_descricao_roleta`,`cotas_premiadas_box`, `cotas_premiadas_descricao_box`,`date_of_draw`, `limit_order_remove`,`discount_qty`,`discount_amount`,`roleta_qty`,`roleta_amount`,`box_qty`,`box_amount`,`enable_discount`,`enable_double`,`double_ini`,`double_fim`,`enable_cumulative_discount`,`enable_sale`,`sale_qty`,`sale_price`,`ranking_message`,`enable_ranking_show`,`ranking_type`,`draw_winner`,`private_draw`,`featured_draw`,`qty_select_1`,`qty_select_2`,`qty_select_3`,`qty_select_4`,`qty_select_5`,`qty_select_6`,`status_auto_cota`,`status_auto_cota_roleta`,`status_auto_cota_box`,`valor_base_auto`, `tipo_auto_cota`, `tipo_auto_cota_roleta`,`tipo_auto_cota_box`, `quantidade_auto_cota`, `quantidade_auto_cota_diario`,`cota_diaria_ini`,`cota_diaria_fim`,`probabilidade`, `cotas_premiadas_premios`, `cotas_premiadas_premios_roleta`,`cotas_premiadas_premios_box`,`roleta`, `box`, `enable_upsell`, `qtd_upsell`, `desconto_upsell`, `habilitar_cota_sorte`, `cota_sorte_ini`, `cota_sorte_fim`, `cota_sorte`, `quantidade_compra_sorte`) VALUES (\'' .
                $name .
                '\',\'' .
                $description .
                '\',\'' .
                $price .
                '\',\'' .
                $status .
                '\',\'' .
                $type_of_draw .
                '\',\'' .
                $qty_numbers .
                '\',\'' .
                $limit_orders .
                '\',\'' .
                $min_purchase .
                '\',\'' .
                $max_purchase .
                '\',\'' .
                $slug .
                '\',\'' .
                $pending_numbers .
                '\',\'' .
                $paid_numbers .
                '\',\'' .
                $ranking_qty .
                '\',\'' .
                $enable_ranking .
                '\',\'' .
                $enable_ranking_definido .
                '\',\'' .
                $ranking_ini .
                '\',\'' .
                $ranking_fim .
                '\',\'' .
                $enable_progress_bar .
                '\',\'' .
                $enable_progress_bar_fake .
                '\',\'' .
                $enable_progress_bar_fake_value .
                '\',\'' .
                $draw_number .
                '\',\'' .
                $status_display .
                '\',\'' .
                $subtitle .
                '\',\'' .
                $cotas_premiadas .
                '\',\'' .
                $cotas_premiadas_descricao .
                '\',\'' .
                $cotas_premiadas_roleta .
                '\',\'' .
                $cotas_premiadas_descricao_roleta .
                '\',\'' .
                $cotas_premiadas_box .
                '\',\'' .
                $cotas_premiadas_descricao_box .
                '\',\'' .
                $date_of_draw .
                '\',\'' .
                $limit_order_remove .
                '\',\'' .
                $discount_qty .
                '\',\'' .
                $discount_amount .
                '\',\'' .
                $roleta_qty .
                '\',\'' .
                $roleta_amount .
                '\',\'' .
                $box_qty .
                '\',\'' .
                $box_amount .
                '\',\'' .
                $enable_discount .
                '\',\'' .
                $enable_double .
                '\',\'' .
                $double_ini .
                '\',\'' .
                $double_fim .
                '\',\'' .
                $enable_cumulative_discount .
                '\',\'' .
                $enable_sale .
                '\',\'' .
                $sale_qty .
                '\',\'' .
                $sale_price .
                '\',\'' .
                $ranking_message .
                '\',\'' .
                $enable_ranking_show .
                '\',\'' .
                $ranking_type .
                '\',\'' .
                $draw_name .
                '\',\'' .
                $private_draw .
                '\',\'' .
                $featured_draw .
                '\',\'' .
                $qty_select_1 .
                '\',\'' .
                $qty_select_2 .
                '\',\'' .
                $qty_select_3 .
                '\',\'' .
                $qty_select_4 .
                '\',\'' .
                $qty_select_5 .
                '\',\'' .
                $qty_select_6 .
                '\',\'' .
                $status_auto_cota .
                '\',\'' .
                $status_auto_cota_roleta .
                '\',\'' .
                $status_auto_cota_box .
                '\',\'' .
                $valor_base_auto .
                '\',\'' .
                $tipo_auto_cota .
                '\',\'' .
                $tipo_auto_cota_roleta .
                '\',\'' .
                $tipo_auto_cota_box .
                '\',\'' .
                $quantidade_auto_cota .
                '\',\'' .
                $quantidade_auto_cota_diario .
                '\',\'' .
                $cota_diaria_ini .
                '\',\'' .
                $cota_diaria_fim .
                '\',\'' .
                $probabilidade .
                '\',\'' .
                $cotas_premiadas_premios .
                '\',\'' .
                $cotas_premiadas_premios_roleta .
                '\',\'' .
                $cotas_premiadas_premios_box .
                '\',\'' .
                $roleta .
                '\',\'' .
                $box .
                '\',\'' .
                $enable_upsell .
                '\',\'' .
                $qtd_upsell .
                '\',\'' .
                $desconto_upsell .
                '\',\'' .
                $habilitar_cota_sorte .
                '\',\'' .
                $cota_sorte_ini .
                '\',\'' .
                $cota_sorte_fim .
                '\',\'' .
                $cota_sorte .
                '\',\'' .
                $quantidade_compra_sorte .
                '\') ';
        } else {
            $sql =
                "UPDATE `product_list`" .
                "\r\n\t\t\t" .
                'SET `name` = \'' .
                $name .
                '\', `description` = \'' .
                $description .
                '\', `price` = \'' .
                $price .
                '\', `status` = \'' .
                $status .
                '\', `type_of_draw` = \'' .
                $type_of_draw .
                '\', `qty_numbers` = \'' .
                $qty_numbers .
                '\', `limit_orders` = \'' .
                $limit_orders .
                '\', `min_purchase` = \'' .
                $min_purchase .
                '\', `max_purchase` = \'' .
                $max_purchase .
                '\', `slug` = \'' .
                $slug .
                '\', `ranking_qty` = \'' .
                $ranking_qty .
                '\', `enable_ranking` = \'' .
                $enable_ranking .
                '\', `enable_ranking_definido` = \'' .
                $enable_ranking_definido .
                '\', `ranking_ini` = \'' .
                $ranking_ini .
                '\', `ranking_fim` = \'' .
                $ranking_fim .
                '\', `enable_progress_bar` = \'' .
                $enable_progress_bar .
                '\', `enable_progress_bar_fake` = \'' .
                $enable_progress_bar_fake .
                '\', `enable_progress_bar_fake_value` = \'' .
                $enable_progress_bar_fake_value .
                '\', `draw_number` = \'' .
                $draw_number .
                '\', `status_display` = \'' .
                $status_display .
                '\', `subtitle` = \'' .
                $subtitle .
                '\', `cotas_premiadas` = \'' .
                $cotas_premiadas .
                '\', `cotas_premiadas_descricao` = \'' .
                $cotas_premiadas_descricao .
                '\', `cotas_premiadas_roleta` = \'' .
                $cotas_premiadas_roleta .
                '\', `cotas_premiadas_descricao_roleta` = \'' .
                $cotas_premiadas_descricao_roleta .
                '\', `cotas_premiadas_box` = \'' .
                $cotas_premiadas_box .
                '\', `cotas_premiadas_descricao_box` = \'' .
                $cotas_premiadas_descricao_box .
                '\', `date_of_draw` = \'' .
                $date_of_draw .
                '\', `limit_order_remove` = \'' .
                $limit_order_remove .
                '\', `discount_qty` = \'' .
                $discount_qty .
                '\', `discount_amount` = \'' .
                $discount_amount .
                '\', `roleta_qty` = \'' .
                $roleta_qty .
                '\', `roleta_amount` = \'' .
                $roleta_amount .
                '\', `box_qty` = \'' .
                $box_qty .
                '\', `box_amount` = \'' .
                $box_amount .
                '\', `enable_discount` = \'' .
                $enable_discount .
                '\', `enable_double` = \'' .
                $enable_double .
                '\', `double_ini` = \'' .
                $double_ini .
                '\', `double_fim` = \'' .
                $double_fim .
                '\', `enable_cumulative_discount` = \'' .
                $enable_cumulative_discount .
                '\', `enable_sale` = \'' .
                $enable_sale .
                '\', `sale_qty` = \'' .
                $sale_qty .
                '\', `sale_price` = \'' .
                $sale_price .
                '\', `ranking_message` = \'' .
                $ranking_message .
                '\', `enable_ranking_show` = \'' .
                $enable_ranking_show .
                '\', `ranking_type` = \'' .
                $ranking_type .
                '\', `draw_winner` = \'' .
                $draw_name .
                '\', `private_draw` = \'' .
                $private_draw .
                '\', `featured_draw` = \'' .
                $featured_draw .
                '\', `qty_select_1` = \'' .
                $qty_select_1 .
                '\', `qty_select_2` = \'' .
                $qty_select_2 .
                '\', `qty_select_3` = \'' .
                $qty_select_3 .
                '\', `qty_select_4` = \'' .
                $qty_select_4 .
                '\', `qty_select_5` = \'' .
                $qty_select_5 .
                '\', `qty_select_6` = \'' .
                $qty_select_6 .
                '\', `status_auto_cota` = \'' .
                $status_auto_cota .
                '\', `status_auto_cota_roleta` = \'' .
                $status_auto_cota_roleta .
                '\', `status_auto_cota_box` = \'' .
                $status_auto_cota_box .
                '\',`valor_base_auto` = \'' .
                $valor_base_auto .
                '\',`tipo_auto_cota` = \'' .
                $tipo_auto_cota .
                '\',`tipo_auto_cota_roleta` = \'' .
                $tipo_auto_cota_roleta .
                '\',`tipo_auto_cota_box` = \'' .
                $tipo_auto_cota_box .
                '\',`quantidade_auto_cota` = \'' .
                $quantidade_auto_cota .
                '\',`quantidade_auto_cota_diario` = \'' .
                $quantidade_auto_cota_diario .
                '\',`cota_diaria_ini` = \'' .
                $cota_diaria_ini .
                '\',`cota_diaria_fim` = \'' .
                $cota_diaria_fim .
                '\',`probabilidade` = \'' .
                $probabilidade .
                '\',`cotas_premiadas_premios` = \'' .
                $cotas_premiadas_premios .
                '\',`cotas_premiadas_premios_roleta` = \'' .
                $cotas_premiadas_premios_roleta .
                '\',`cotas_premiadas_premios_box` = \'' .
                $cotas_premiadas_premios_box .
                '\',`roleta` = \'' .
                $roleta .
                '\',`box` = \'' .
                $box .
                '\',`enable_upsell` = \'' .
                $enable_upsell .
                '\',`qtd_upsell` = \'' .
                $qtd_upsell .
                '\',`desconto_upsell` = \'' .
                $desconto_upsell .
                '\',`habilitar_cota_sorte` = \'' .
                $habilitar_cota_sorte .
                '\',`cota_sorte_ini` = \'' .
                $cota_sorte_ini .
                '\',`cota_sorte_fim` = \'' .
                $cota_sorte_fim .
                '\',`cota_sorte` = \'' .
                $cota_sorte .
                '\',`quantidade_compra_sorte` = \'' .
                $quantidade_compra_sorte .
                '\' WHERE `id` = ' .
                $id .
                ";";
        }

        $save = $this->conn->query($sql);

        if ($save) {
            $pid = !empty($id) ? $id : $this->conn->insert_id;
            $resp["pid"] = $pid;
            $resp["status"] = "success";

            if (empty($id)) {
                $resp["msg"] = "Product has been addedd successfully";
                $user_name = $this->settings->userdata("firstname");
                $insert = $this->conn->query(
                    'INSERT INTO `logs` (`origin`, `description`) VALUES (\'PRODUCT\', \'Produto ' .
                        $name .
                        " adicionado pelo usuário " .
                        $user_name .
                        '\')'
                );
            } else {
                $resp["msg"] = " Product has been updated successfully.";
                $user_name = $this->settings->userdata("firstname");
                $insert = $this->conn->query(
                    'INSERT INTO `logs` (`origin`, `description`) VALUES (\'PRODUCT\', \'Produto ' .
                        $name .
                        " atualizado pelo usuário " .
                        $user_name .
                        '\')'
                );
            }

            if (isset($_FILES['img']) && (int) ($_FILES['img']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $oldImage = '';
                $oldImageQuery = $this->conn->prepare('SELECT image_path FROM product_list WHERE id = ? LIMIT 1');
                $oldImageQuery->bind_param('i', $pid);
                $oldImageQuery->execute();
                $oldImageResult = $oldImageQuery->get_result();
                if ($oldImageResult && ($oldImageRow = $oldImageResult->fetch_assoc())) {
                    $oldImage = preg_replace('/\?.*$/', '', (string) $oldImageRow['image_path']);
                }
                $oldImageQuery->close();

                $imageResult = $this->save_campaign_main_image($_FILES['img'], $pid);
                if (empty($imageResult['ok'])) {
                    error_log('[campaign] main image upload failed product=' . (int) $pid . ' reason=' . ($imageResult['message'] ?? 'unknown'));
                    return json_encode([
                        'status' => 'failed',
                        'msg' => 'Os dados foram salvos, mas a imagem não foi alterada. ' . ($imageResult['message'] ?? 'Tente novamente.'),
                    ]);
                }

                $newImage = $imageResult['path'];
                $imageUpdate = $this->conn->prepare('UPDATE product_list SET image_path = ? WHERE id = ?');
                $imageUpdate->bind_param('si', $newImage, $pid);
                $imageSaved = $imageUpdate->execute();
                $imageUpdate->close();

                if (!$imageSaved) {
                    $this->delete_campaign_blob($newImage);
                    error_log('[campaign] main image database update failed product=' . (int) $pid);
                    return json_encode(['status' => 'failed', 'msg' => 'Os dados foram salvos, mas não foi possível associar a nova imagem.']);
                }

                if ($oldImage !== '' && $oldImage !== $newImage) {
                    $this->delete_campaign_blob($oldImage);
                }
            }

            $on_gallery = isset($_POST["on-gallery"])
                ? array_values(array_filter((array) $_POST["on-gallery"]))
                : [];

            $existingGallery = [];
            $existingGalleryQuery = $this->conn->prepare('SELECT image_gallery FROM product_list WHERE id = ? LIMIT 1');
            $existingGalleryQuery->bind_param('i', $pid);
            $existingGalleryQuery->execute();
            $existingGalleryResult = $existingGalleryQuery->get_result();
            if ($existingGalleryResult && ($existingGalleryRow = $existingGalleryResult->fetch_assoc())) {
                $decodedGallery = json_decode((string) $existingGalleryRow['image_gallery'], true);
                $existingGallery = is_array($decodedGallery) ? $decodedGallery : [];
            }
            $existingGalleryQuery->close();

            $galleryNames = isset($_FILES["image_gallery"]["name"])
                ? (array) $_FILES["image_gallery"]["name"]
                : [];
            $newGallery = [];

            if (array_filter($galleryNames)) {
                foreach ((array) $_FILES["image_gallery"]["tmp_name"] as $index => $tmpName) {
                    if (empty($galleryNames[$index])) {
                        continue;
                    }

                    $galleryFile = [
                        'name' => $galleryNames[$index],
                        'type' => $_FILES["image_gallery"]["type"][$index] ?? '',
                        'tmp_name' => $tmpName,
                        'error' => $_FILES["image_gallery"]["error"][$index] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $_FILES["image_gallery"]["size"][$index] ?? 0,
                    ];
                    $galleryResult = $this->save_campaign_main_image($galleryFile, $pid);

                    if (empty($galleryResult['ok'])) {
                        foreach ($newGallery as $newGalleryImage) {
                            $this->delete_campaign_blob($newGalleryImage);
                        }
                        error_log('[campaign] gallery upload failed product=' . (int) $pid . ' reason=' . ($galleryResult['message'] ?? 'unknown'));
                        return json_encode([
                            'status' => 'failed',
                            'msg' => 'Os dados foram salvos, mas uma imagem da galeria não foi alterada. ' . ($galleryResult['message'] ?? 'Tente novamente.'),
                        ]);
                    }

                    $newGallery[] = $galleryResult['path'];
                }
            }

            $finalGallery = array_values(array_merge($on_gallery, $newGallery));
            $galleryJson = json_encode($finalGallery, JSON_UNESCAPED_SLASHES);
            $galleryUpdate = $this->conn->prepare('UPDATE product_list SET image_gallery = ? WHERE id = ?');
            $galleryUpdate->bind_param('si', $galleryJson, $pid);
            $gallerySaved = $galleryUpdate->execute();
            $galleryUpdate->close();

            if (!$gallerySaved) {
                foreach ($newGallery as $newGalleryImage) {
                    $this->delete_campaign_blob($newGalleryImage);
                }
                return json_encode([
                    'status' => 'failed',
                    'msg' => 'Os dados foram salvos, mas não foi possível atualizar a galeria.',
                ]);
            }

            foreach ($existingGallery as $oldGalleryImage) {
                if (!in_array($oldGalleryImage, $finalGallery, true)) {
                    $this->delete_campaign_blob($oldGalleryImage);
                }
            }
        } else {
            $resp["status"] = "failed";
            $resp["err"] = $this->conn->error . ("[" . $sql . "]");
        }
        if ($resp["status"] == "success" && isset($resp["msg"])) {
            return json_encode($resp);
        }
    }

    public function delete_product()
    {
        if (!$this->settings->userdata("firstname")) {
            $resp["status"] = "failed";
            $resp["msg"] = "Não autorizado.";
            return json_encode($resp);
        }

        extract($_POST);
        $del = $this->conn->query(
            'DELETE FROM `product_list` where id = \'' . $id . '\''
        );

        if ($del) {
            $resp["status"] = "success";
        } else {
            $resp["status"] = "failed";
            $resp["error"] = $this->conn->error;
        }

        return json_encode($resp);
    }

    public function add_to_card()
    {
        extract($_POST);
        $customer_id = $this->settings->userdata("id");
        $delete = $this->conn->query(
            'DELETE FROM `cart_list` WHERE customer_id = \'' .
                $customer_id .
                '\''
        );

        if ($delete) {
            $check = $this->conn->query(
                'SELECT id FROM `cart_list` WHERE customer_id = \'' .
                    $customer_id .
                    '\' AND product_id = \'' .
                    $product_id .
                    '\''
            )->num_rows;

            if (0 < $check) {
                $update = $this->conn->query(
                    'UPDATE `cart_list` SET quantity = \'' .
                        $qty .
                        '\' WHERE customer_id = \'' .
                        $customer_id .
                        '\' AND product_id = \'' .
                        $product_id .
                        '\''
                );

                if ($update) {
                    $resp["status"] = "success";
                } else {
                    $resp["status"] = "failed";
                    $resp["error"] = $this->conn->error;
                }
            } else {
                $insert = $this->conn->query(
                    'INSERT INTO `cart_list` (`customer_id`, `product_id`, `quantity`) VALUES (\'' .
                        $customer_id .
                        '\', \'' .
                        $product_id .
                        '\', \'' .
                        $qty .
                        '\')'
                );

                if ($insert) {
                    $resp["status"] = "success";
                } else {
                    $resp["status"] = "failed";
                    $resp["error"] = $this->conn->error;
                }
            }
        } else {
            $resp["status"] = "failed";
            $resp["error"] = $this->conn->error;
        }

        if ($resp["status"] == "success") {
        }

        return json_encode($resp);
    }

    public function create_order()
    {
        if (!$this->settings->userdata("firstname")) {
            $resp["status"] = "failed";
            $resp["msg"] = "Não autorizado.";
            return json_encode($resp);
        }

        extract($_POST);
        $pref = date("Ymdhis.u");
        $code = "M-" . uniqidReal();
        $order_token = md5($pref . $code);
        $dateCreated = date("Y-m-d H:i:s");
        $payment_method = "Manual";
        $customer_query = $this->conn
            ->query(
                'SELECT id FROM customer_list WHERE phone = \'' .
                    $customer .
                    '\''
            )
            ->fetch_assoc();
        $customer_id = $customer_query["id"];

        if (!$customer_id) {
            $resp["status"] = "failed";
            $resp["msg"] = "Usuário não localizado.";
            return json_encode($resp);
        }

        $product_info = $this->conn
            ->query(
                'SELECT name, limit_order_remove, paid_numbers, pending_numbers, qty_numbers FROM product_list WHERE id = \'' .
                    $raffle .
                    '\''
            )
            ->fetch_assoc();
        $product_name = $product_info["name"];
        $order_expiration = $product_info["limit_order_remove"];
        $paid_numbers = $product_info["paid_numbers"];
        $pending_numbers = $product_info["pending_numbers"];
        $qty_numbers = $product_info["qty_numbers"];

        if ($qty_numbers < $quantidade + $paid_numbers + $pending_numbers) {
            $resp["status"] = "failed";
            $resp["msg"] = "A quantidade ultrapassa o limite disponível";
            return json_encode($resp);
        }

        $insert = $this->conn->query(
            "INSERT INTO `order_list` " .
                "\r\n\t\t" .
                "(`code`, `customer_id`, `product_name`, `quantity`, `status`, `total_amount`, `order_token`, `order_numbers`, `product_id`, `payment_method`, `order_expiration`, `date_created`, `date_updated`) " .
                "\r\n\t\t" .
                "VALUES " .
                "\r\n\t\t" .
                '(\'' .
                $code .
                '\', \'' .
                $customer_id .
                '\', \'' .
                $product_name .
                '\', \'' .
                $quantidade .
                '\', \'' .
                $status .
                '\', \'' .
                $price .
                '\', \'' .
                $order_token .
                '\', \'' .
                $order_numbers .
                '\', \'' .
                $raffle .
                '\', \'' .
                $payment_method .
                '\', \'' .
                $order_expiration .
                '\', \'' .
                $dateCreated .
                '\', \'' .
                $dateCreated .
                '\') '
        );

        if ($insert) {
            $oid = $this->conn->insert_id;
            $insert = $this->conn->query(
                "INSERT INTO `order_items` " .
                    "\r\n\t\t\t" .
                    "(`order_id`, `product_id`, `quantity`, `price`) " .
                    "\r\n\t\t\t" .
                    "VALUES " .
                    "\r\n\t\t\t" .
                    '(\'' .
                    $oid .
                    '\', \'' .
                    $raffle .
                    '\', \'' .
                    $quantidade .
                    '\', \'' .
                    $price .
                    '\') '
            );

            if ($status == 1) {
                $this->conn->query(
                    'UPDATE `product_list` SET `pending_numbers` = `pending_numbers` + \'' .
                        $quantidade .
                        '\' WHERE `id` = \'' .
                        $raffle .
                        '\''
                );
                order_email(
                    $this->settings->info("email_order"),
                    "[" .
                        $this->settings->info("name") .
                        "] - Confirmação de pedido",
                    $oid
                );
            } elseif ($status == 2) {
                $this->conn->query(
                    'UPDATE `product_list` SET `paid_numbers` = `paid_numbers` + \'' .
                        $quantidade .
                        '\' WHERE `id` = \'' .
                        $raffle .
                        '\''
                );
                order_email(
                    $this->settings->info("email_order"),
                    "[" .
                        $this->settings->info("name") .
                        "] - Confirmação de pedido",
                    $oid
                );
                order_email(
                    $this->settings->info("email_purchase"),
                    "[" .
                        $this->settings->info("name") .
                        "] - Pagamento aprovado",
                    $oid
                );
            }

            $this->correct_stock($raffle);
            $resp["status"] = "success";
            $resp["msg"] = "Pedido criado com sucesso!";
            $user_name = $this->settings->userdata("firstname");
            $insert = $this->conn->query(
                'INSERT INTO `logs` (`origin`, `description`) VALUES (\'ORDER\', \'Pedido manual ' .
                    $oid .
                    " criado pelo usuário " .
                    $user_name .
                    '\')'
            );
        } else {
            $resp["status"] = "failed";
            $resp["msg"] = "Erro ao criar pedido manual";
        }

        return json_encode($resp);
    }

    public function create_payment_affiliate()
    {
        if (!$this->settings->userdata("firstname")) {
            $resp["status"] = "failed";
            $resp["msg"] = "Não autorizado.";
            return json_encode($resp);
        }

        extract($_POST);
        $get_aff = $this->conn->query(
            'SELECT referral_code FROM referral r INNER JOIN customer_list c ON c.id = r.customer_id WHERE c.phone = \'' .
                $referral_id .
                '\''
        );

        if (0 < $get_aff->num_rows) {
            $row = $get_aff->fetch_assoc();
            $referral_code = $row["referral_code"];
        }

        $insert = $this->conn->query(
            "INSERT INTO referral_transactions (total_amount, referral_id) VALUES (" .
                $price .
                ", " .
                $referral_code .
                ")"
        );

        if ($insert) {
            $update = $this->conn->query(
                "UPDATE referral SET amount_paid = amount_paid + " .
                    $price .
                    ' WHERE referral_code = \'' .
                    $referral_code .
                    '\''
            );
            $update = $this->conn->query(
                "UPDATE referral SET amount_pending = amount_pending - " .
                    $price .
                    ' WHERE referral_code = \'' .
                    $referral_code .
                    '\''
            );
            $resp["status"] = "success";
            $resp["msg"] = "Pagamento cadastrado com sucesso!";
        } else {
            $resp["status"] = "failed";
            $resp["msg"] = "Erro ao criar pedido manual";
        }

        return json_encode($resp);
    }

    public function create_affiliate()
    {
        if (!$this->settings->userdata("firstname")) {
            $resp["status"] = "failed";
            $resp["msg"] = "Não autorizado.";
            return json_encode($resp);
        }

        extract($_POST);
        $percentage = str_replace(",", ".", $percentage);
        $user_id = $this->conn->query(
            'SELECT id FROM customer_list WHERE phone = \'' . $customer . '\''
        );

        if (0 < $user_id->num_rows) {
            if (0 < $user_id->num_rows) {
                $row = $user_id->fetch_assoc();
                $user_id = $row["id"];
                $user_verify = $this->conn->query(
                    "SELECT id FROM referral WHERE customer_id = " . $user_id
                );

                if (0 < $user_verify->num_rows) {
                    $update = $this->conn->query(
                        "UPDATE referral SET percentage = " .
                            $percentage .
                            ", status = " .
                            $status .
                            ' WHERE customer_id = \'' .
                            $user_id .
                            '\''
                    );

                    if ($update) {
                        $resp["status"] = "success";
                        $resp["msg"] = "Afiliado atualizado com sucesso";
                    } else {
                        $resp["status"] = "failed";
                        $resp["msg"] = "Erro ao atualizar afiliado";
                    }
                } elseif ($user_id) {
                    $insert = $this->conn->query(
                        "INSERT INTO referral" .
                            "\r\n\t\t\t\t\t\t\t" .
                            "(status, referral_code, percentage, amount_paid, amount_pending, customer_id)" .
                            "\r\n\t\t\t\t\t\t\t" .
                            "VALUES (" .
                            $status .
                            ", " .
                            $user_id .
                            ", " .
                            $percentage .
                            ", 0, 0, " .
                            $user_id .
                            ")"
                    );

                    if ($insert) {
                        $update = $this->conn->query(
                            "UPDATE customer_list SET is_affiliate = " .
                                $status .
                                ' WHERE id = \'' .
                                $user_id .
                                '\''
                        );
                        $resp["status"] = "success";
                        $resp["msg"] = "Afiliado cadastrado com sucesso";
                    } else {
                        $resp["status"] = "failed";
                        $resp["msg"] = "Erro ao criar afiliado";
                    }
                } else {
                    $resp["status"] = "failed";
                    $resp["msg"] = "Usuário não encontrado";
                }
            }
        }

        return json_encode($resp);
    }

    public function delete_affiliate()
    {
        if (!$this->settings->userdata("firstname")) {
            $resp["status"] = "failed";
            $resp["msg"] = "Não autorizado.";
            return json_encode($resp);
        }

        extract($_POST);
        $customer = $this->conn->query(
            'SELECT customer_id FROM referral WHERE id = \'' . $id . '\''
        );

        if (0 < $customer->num_rows) {
            $row = $customer->fetch_assoc();
            $customer_id = $row["customer_id"];
            $update = $this->conn->query(
                'UPDATE customer_list SET is_affiliate = 0 WHERE id = \'' .
                    $customer_id .
                    '\''
            );
        }

        $delete = $this->conn->query(
            'DELETE FROM referral WHERE id = \'' . $id . '\''
        );
        $deleteTransations = $this->conn->query(
            'DELETE FROM referral_transactions WHERE referral_id = \'' .
                $id .
                '\''
        );
        if ($delete && $deleteTransations) {
            $resp["status"] = "success";
            $resp["msg"] = "Afiliado excluído com sucesso";
        } else {
            $resp["status"] = "failed";
            $resp["msg"] = "Erro ao excluir afiliado";
        }

        return json_encode($resp);
    }

    public function deactive_license()
    {
        //  $license = $this->settings->info("license");

        //if (!empty($license)) {
        //  $update = $this->conn->query(
        //       'UPDATE system_info SET meta_value = \'\' WHERE meta_field = \'license\''
        //  );
        //$firstname = $this->settings->userdata("firstname");
        //     $insert = $this->conn->query(
        //       'INSERT INTO `logs` (`origin`, `description`) VALUES (\'LICENSE\', \'Licença desativada pelo usuário ' .
        //           $firstname .
        //         '\')'
        // );

        //  if ($update) {
        //    $url =
        //      "https://license.dropestore.com/wp-json/licensor/license/remove_domain";
        //         $domain = BASE_URL . "admin/";
        //       $curl = curl_init();
        //     curl_setopt($curl, CURLOPT_URL, $url);
        //   curl_setopt($curl, CURLOPT_POST, 1);
        // curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        //             curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        //           curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //         curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        //       curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        //     curl_setopt(
        //       $curl,
        //     CURLOPT_POSTFIELDS,
        //   "api_key=225A632C-7B598C64-74403549-BDF93958&license_code=" .
        //     $license .
        //   "&domain=" .
        // $domain
        // );
        //  curl_setopt($curl, CURLOPT_HTTPHEADER, [
        //    "Content-Type: application/x-www-form-urlencoded",
        //    ]);
        //  $response = json_decode(curl_exec($curl));
        //          curl_close($curl);
        //         $resp["status"] = "success";
        //       $resp["msg"] = "Licença removida.";
        //  }
        //  } else {
        //    $resp["status"] = "failed";
        //  $resp["msg"] = "Nenhuma licença encontrada.";
        //}

        //  return json_encode($resp);
    }

    public function place_order()
    {

        $lockFile = $_SERVER["DOCUMENT_ROOT"] . "/pedido.lock";
        $lock = fopen($lockFile, "w");

        if (flock($lock, LOCK_EX)) {

            $customer_id = $this->settings->userdata("id");
            $customer_fname = $this->settings->userdata("firstname");
            $customer_lname = $this->settings->userdata("lastname");
            $customer_phone = $this->settings->userdata("phone");
            $customer_email = $this->settings->userdata("email");
            $customer_cpf = $this->settings->userdata("cpf");
            $customer_name = $customer_fname . " " . $customer_lname;

            $dateCreated = date("Y-m-d H:i:s");
            $product_id = $_POST["product_id"];
            $valorUpsell = $_POST["valorUpsell"];
            $qtdUpsell = $_POST["qtdUpsell"];
            $numbers = isset($_POST["numbers"]) ? $_POST["numbers"] : "";
            $pref = date("Ymdhis.u");
            $code = uniqidReal();
            $ref = $_POST["ref"];
            $order_token = md5($pref . $code);

            $expirationCleanup = payment_expire_pending_orders((int) $product_id, 2);
            if (empty($expirationCleanup['ok'])) {
                error_log('[payments] expired order cleanup failed product=' . (int) $product_id);
            }

            if ($this->settings->info("pay2m") == 1) {
                // Se CPF for obrigatório e estiver vazio, bloqueia
                if ($this->settings->info("habilitar_cpf") == 1 && empty($customer_cpf)) {
                    $resp["status"] = "pay2m";
                    $resp["error"] =
                        "Seu cadastro precisa ser atualizado, por favor, adicione um CPF válido.";
                    $resp["redirect"] = BASE_URL . "user/atualizar-cadastro";
                    flock($lock, LOCK_UN);
                    fclose($lock);
                    return json_encode($resp);
                    exit();
                }

                // Se CPF não for obrigatório, preenche com CPF fake para evitar erro na API
                if (empty($customer_cpf)) {
                    $customer_cpf = "00000000000";
                }
            }

            $multiple = $this->settings->info("enable_multiple_order");

            if ($multiple == 1) {
                $multiple_order = $this->conn->prepare(
                    "SELECT id FROM `order_list` WHERE status = 1 AND customer_id = ?"
                );
                $multiple_order->bind_param("i", $customer_id);
                $multiple_order->execute();
                $customer_order = $multiple_order->get_result();

                if (0 < $customer_order->num_rows) {
                    $resp["status"] = "failed";
                    $resp["error"] =
                        "Faça o pagamento do pedido anterior para criar um novo pedido.";
                    flock($lock, LOCK_UN);
                    fclose($lock);
                    return json_encode($resp);
                    exit();
                }
            }

            $cart_total = $this->conn
                ->query(
                    'SELECT SUM(c.quantity * p.price) FROM `cart_list` c inner join product_list p on c.product_id = p.id where customer_id = \'' .
                        $customer_id .
                        '\' '
                )
                ->fetch_array()[0];


            $stmt_plist = $this->conn->prepare(
                "SELECT name, qty_numbers, limit_order_remove, type_of_draw FROM `product_list` WHERE id = ?"
            );
            $stmt_plist->bind_param("i", $product_id);
            $stmt_plist->execute();
            $product_list = $stmt_plist->get_result();

            if (0 < $product_list->num_rows) {
                $product = $product_list->fetch_assoc();
                $product_name = $product["name"];
                $qty_numbers = $product["qty_numbers"];
                $type_of_draw = $product["type_of_draw"];
                $order_expiration = $product["limit_order_remove"];
            }

            $quantity = $this->conn
                ->query(
                    'SELECT SUM(c.quantity) FROM `cart_list` c inner join product_list p on c.product_id = p.id where customer_id = \'' .
                        $customer_id .
                        '\' '
                )
                ->fetch_array()[0];

            if (!$quantity) {
                $resp["status"] = "failed";
                $resp["error"] = "Erro ao criar pedido.";
                flock($lock, LOCK_UN);
                fclose($lock);
                return json_encode($resp);
                exit();
            }

            $limitOrder = 0;
            $customerOrders = 0;
            $limitOrdersQuery = $this->conn->query(
                'SELECT limit_orders FROM product_list WHERE id = \'' .
                    $product_id .
                    '\''
            );
            if ($limitOrdersQuery && 0 < $limitOrdersQuery->num_rows) {
                $limitOrder = $limitOrdersQuery->fetch_assoc();
                $limitOrder = $limitOrder["limit_orders"];
            }

            $customerOrdersQuery = $this->conn->query(
                'SELECT id FROM order_list WHERE customer_id = \'' .
                    $customer_id .
                    '\' AND product_id = \'' .
                    $product_id .
                    '\''
            );
            if ($customerOrdersQuery && 0 < $customerOrdersQuery->num_rows) {
                $customerOrders = $customerOrdersQuery->num_rows;
            }

            if ($limitOrder != 0) {
                if ($limitOrder <= $customerOrders) {
                    $resp["status"] = "failed";
                    $resp["error"] =
                        "Você atingiu o limite de pedido(s) para essa campanha.";
                    flock($lock, LOCK_UN);
                    fclose($lock);
                    return json_encode($resp);
                    exit();
                }
            }

            $query =
                'SELECT discount_qty, enable_discount, enable_double, double_ini, double_fim, discount_amount,discount_roleta,roleta_qty, roleta_amount, discount_box, box_qty, box_amount, enable_cumulative_discount, enable_sale, sale_qty, roleta, box, sale_price, status, qty_numbers, probabilidade, pending_numbers, paid_numbers, date_of_draw, habilitar_cota_sorte, cota_sorte_ini, cota_sorte_fim, cota_sorte, quantidade_compra_sorte FROM product_list WHERE id = \'' .
                $product_id .
                '\'';
            $result = $this->conn->query($query);
            if ($result && 0 < $result->num_rows) {
                $row = $result->fetch_assoc();
                $pending_numbers = $row["pending_numbers"];
                $discount_qty = $row["discount_qty"];
                $enable_discount = $row["enable_discount"];
                $enable_double = $row["enable_double"];
                $double_ini = $row["double_ini"];
                $double_fim = $row["double_fim"];
                $probabilidade = $row["probabilidade"];
                $enable_cumulative_discount = $row["enable_cumulative_discount"];
                $discount_amount = $row["discount_amount"];
                $roleta_amount = $row["roleta_amount"];
                $roleta_qty = $row["roleta_qty"];
                $roleta_enable = $row["roleta"];
                $box_enable = $row["box"];
                $box_qty = $row["box_qty"];
                $box_amount = $row["box_amount"];
                $discount_box = $row["discount_box"];
                $discount_roleta = $row["discount_roleta"];
                $enable_sale = $row["enable_sale"];
                $sale_qty = $row["sale_qty"];
                $sale_price = $row["sale_price"];
                $status = $row["status"];
                $paid_n = $row["paid_numbers"];
                $pending_n = $row["pending_numbers"];
                $date_of_draw = $row["date_of_draw"];
                $habilitar_cota_sorte = $row["habilitar_cota_sorte"];
                $cota_sorte_ini = $row["cota_sorte_ini"];
                $cota_sorte_fim = $row["cota_sorte_fim"];
                $cota_sorte = $row["cota_sorte"];
                $quantidade_compra_sorte = $row["quantidade_compra_sorte"];
            }

            $totalSales = $paid_n + $pending_n;

            if (1 < $status) {
                $resp["status"] = "failed";
                $resp["error"] = "Campanha pausada ou finalizada.";
                return json_encode($resp);
                exit();
            }

            if ($qty_numbers <= $totalSales) {
                $this->conn->query(
                    'UPDATE product_list SET status = \'2\', status_display = \'6\' WHERE id = \'' .
                        $product_id .
                        '\''
                );
                $resp["status"] = "failed";
                $resp["error"] = "Camnpanha pausada ou finalizada.";
                flock($lock, LOCK_UN);
                fclose($lock);
                return json_encode($resp);
                exit();
            }


            if ($date_of_draw) {
                $expirationTime = date("Y-m-d H:i:s", strtotime($date_of_draw));
                $currentDateTime = date("Y-m-d H:i:s");

                if ($expirationTime < $currentDateTime) {
                    $resp["status"] = "failed";
                    $resp["error"] =
                        "Compra não permitida. A campanha foi pausada ou finalizada.";
                    flock($lock, LOCK_UN);
                    fclose($lock);
                    return json_encode($resp);
                    exit();
                }
            }

            $total_pending_numbers = $pending_n + $quantity;
            $total_paid_numbers = $paid_n + $quantity;
            $total_amount = 0 < $cart_total ? $cart_total : 0;
            $pay_status = 1;
            if ($total_amount == 0) {
                $pay_status = 2;
                $this->conn->query(
                    'UPDATE product_list SET paid_numbers = \'' .
                        $total_paid_numbers .
                        '\' WHERE id = \'' .
                        $product_id .
                        '\''
                );
            } else {
                $this->conn->query(
                    'UPDATE product_list SET pending_numbers = \'' .
                        $total_pending_numbers .
                        '\' WHERE id = \'' .
                        $product_id .
                        '\''
                );
            }

            $order_discount_amount = "";
            if ($enable_discount && $discount_amount) {
                $discount_qty = json_decode($discount_qty, true);
                $discount_amount = json_decode($discount_amount, true);
                $discount_roleta = json_decode($discount_roleta, true);
                $discount_box = json_decode($discount_box, true);
                $discounts = [];

                foreach ($discount_qty as $qty_index => $qty) {
                    foreach ($discount_amount as $amount_index => $amount) {
                        // Quando os índices de quantidade e valor coincidirem, vamos adicionar o desconto
                        if ($qty_index === $amount_index) {
                            // Adiciona os valores de quantidade, valor e roleta ao array $discounts
                            $discounts[$qty_index] = [
                                'qty' => $qty,
                                'amount' => $amount,
                                'roleta' => isset($discount_roleta[$qty_index]) ? $discount_roleta[$qty_index] : null 
 
                            ];
                        }
                    }
                }

                if ($enable_cumulative_discount == 1) {
                    $accumulative_discount = 0;
                    $remaining_quantity = $quantity;
                    usort($discounts, function ($a, $b) {
                        return $b["qty"] - $a["qty"];
                    });

                    foreach ($discounts as $discount) {
                        if ($discount["qty"] <= $remaining_quantity) {
                            $multiples = floor(
                                $remaining_quantity / $discount["qty"]
                            );
                            $discount_amount = $multiples * $discount["amount"];
                            $accumulative_discount += $discount_amount;
                            $remaining_quantity -= $multiples * $discount["qty"];
                        }
                    }

                    if (0 < $accumulative_discount) {
                        $total_amount -= $accumulative_discount;
                        $order_discount_amount = $accumulative_discount;
                    }
                } else {
                    usort($discounts, function ($a, $b) {
                        return $b["qty"] - $a["qty"];
                    });

                    foreach ($discounts as $discount) {
                        if ($discount["qty"] <= $quantity) {
                            $total_amount -= $discount["amount"];
                            $order_discount_amount = $discount["amount"];
                            $order_discount_roleta = $discount["roleta"];
                             $order_discount_box = $discount["box"];
                            break;
                        }
                    }
                }
            }
            if ($roleta_enable == 1) {
                if ($roleta_amount && $roleta_qty) {
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
                }
            }

            if ($box_enable == 1) {

                if ($box_amount && $box_qty) {
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
            }

            if (
                $enable_sale == 1 &&
                $enable_discount == 0 &&
                $sale_qty <= $quantity
            ) {
                $order_discount_amount = $total_amount - $quantity * $sale_price;
                $total_amount = $quantity * $sale_price;
            }

            $order_numbers = "";
            //$roleta = $order_discount_roleta ? $order_discount_roleta : 1;
            $qtdRoleta = 0;

            if ($roletas) {
                foreach ($roletas as $key => $roleta) {
                    $qtdRoleta = 0;
                    // Se for o último item ou o valor de $quantity for menor que o próximo valor de 'amount'
                    if ($key + 1 < count($roletas)) {
                        $nextRoleta = $roletas[$key + 1];
                        if ($quantity >= $roleta['amount'] && $quantity < $nextRoleta['amount']) {
                            $qtdRoleta = $roleta['qty'];
                            break;
                        }
                    } else {
                        // Para o último intervalo
                        if ($quantity > $roleta['amount']) {
                            $qtdRoleta = $roleta['qty'];
                            break;
                        }
                    }
                }
            } else {
                $qtdRoleta = 0;
            }
            $qtdBox = 0;
            if ($boxs) {
                foreach ($boxs as $key => $box) {
                    $qtdBox = 0;
                    // Verifica se o valor de $quantity está no intervalo da caixa atual
                    if ($key + 1 < count($boxs)) {
                        $nextBox = $boxs[$key + 1];
                        if ($quantity >= $box['amount'] && $quantity < $nextBox['amount']) {
                            $qtdBox = $box['qty'];
                            break;
                        }
                    } else {
                        // Para o último intervalo, se não houver mais caixas depois
                        if ($quantity > $box['amount']) {
                            $qtdBox = $box['qty'];
                            break;
                        }
                    }
                }
            } else {
                $qtdBox = 0;
            }


            $pixel_sell = $_SESSION['ads'] ? 1 : 0;

            $total_amount = floatval($total_amount) + floatval($valorUpsell);

            $quantity = $quantity + $qtdUpsell;

            $resp['quantity'] = $quantity;

            if ($enable_double == 1 && isset($double_ini) && isset($double_fim)) {
                $date = date('Y-m-d H:i:s');
                if (strtotime($double_ini) <= strtotime($date) && strtotime($double_fim) >= strtotime($date)) {
                    $quantity = $quantity * 2;
                }
            }



            $insert = $this->conn->query(
                'INSERT INTO `order_list` (`code`, `customer_id`, `product_name`, `quantity`, `status`, `total_amount`, `order_token`, `order_numbers`, `product_id`, `order_expiration`, `discount_amount`,`roleta`,`box`, `date_created`, `pixel_sell`) VALUES (\'' .
                    $code .
                    '\', \'' .
                    $customer_id .
                    '\', \'' .
                    $product_name .
                    '\', \'' .
                    $quantity .
                    '\', \'' .
                    $pay_status .
                    '\', \'' .
                    $total_amount .
                    '\', \'' .
                    $order_token .
                    '\', \'' .
                    $order_numbers .
                    '\', \'' .
                    $product_id .
                    '\', \'' .
                    $order_expiration .
                    '\', \'' .
                    $order_discount_amount .
                    '\', \'' .
                    $qtdRoleta .
                    '\', \'' .
                    $qtdBox .
                    '\', \'' .
                    $dateCreated .
                    '\', \'' .
                    $pixel_sell .
                    '\') '
            );

            if ($insert) {
                $oid = $this->conn->insert_id;
                $data = "";
                $sql_cart =
                    "SELECT c.*," .
                    "\r\n\t\t\t\t" .
                    "p.name AS product," .
                    "\r\n\t\t\t\t" .
                    "p.price," .
                    "\r\n\t\t\t\t" .
                    "p.image_path" .
                    "\r\n\t\t\t\t" .
                    "FROM `cart_list` c" .
                    "\r\n\t\t\t\t" .
                    "INNER JOIN product_list p ON c.product_id = p.id" .
                    "\r\n\t\t\t\t" .
                    'WHERE customer_id = \'' .
                    $customer_id .
                    '\'';
                $cart = $this->conn->query($sql_cart);
                $qty_numbers = $qty_numbers - 1;
                $total_numbers_generated = $quantity;
                $use_manual_numbers = false;

                if (1 < $type_of_draw) {
                    $use_manual_numbers = true;
                }

                if ($use_manual_numbers) {
                    $orders = $this->conn->query(
                        'SELECT order_numbers FROM order_list WHERE product_id = \'' .
                            $product_id .
                            '\' AND status <> 3'
                    );
                    $cotas_vendidas = [];
                    $all_lucky_numbers = [];

                    while ($row = $orders->fetch_assoc()) {
                        $cotas_vendidas[] = $row["order_numbers"];
                    }

                    $all_lucky_numbers = implode(",", $cotas_vendidas);
                    $all_lucky_numbers = explode(",", $all_lucky_numbers);
                    $cotas_vendidas = array_filter($cotas_vendidas);
                    $arrValues = array_filter(
                        explode(",", implode(",", $cotas_vendidas))
                    );
                    $result = $this->is_in_array($numbers, $arrValues);

                    if ($result) {
                        $resultNumber = implode(",", $result);
                        $resp["status"] = "failed";
                        $resp["error"] =
                            1 < count($result)
                            ? "Os números " .
                            $resultNumber .
                            " acabaram de ser reservados por outra pessoa. Por favor, escolha outros números"
                            : "O número " .
                            $resultNumber .
                            " acabou de ser reservado por outra pessoa. Por favor, escolha outro número";
                        $this->conn->query(
                            'DELETE FROM `order_list` where code = \'' .
                                $code .
                                '\''
                        );
                        $this->conn->query(
                            'UPDATE `product_list` SET `pending_numbers` = `pending_numbers` - \'' .
                                $total_numbers_generated .
                                '\' WHERE `id` = \'' .
                                $product_id .
                                '\''
                        );
                        return json_encode($resp);
                    }

                    $order_numbers = implode(",", $numbers) . ",";
                    $update = $this->conn->query(
                        'UPDATE `order_list` SET `order_numbers` = \'' .
                            $order_numbers .
                            '\' WHERE `code` = \'' .
                            $code .
                            '\''
                    );
                } else {
                    $orders = $this->conn->query(
                        "SELECT order_list.order_numbers, product_list.cotas_premiadas, product_list.status_auto_cota,product_list.cotas_premiadas_roleta, product_list.status_auto_cota_roleta,product_list.cotas_premiadas_box, product_list.status_auto_cota_box, product_list.paid_numbers, product_list.tipo_auto_cota,product_list.tipo_auto_cota_roleta,product_list.tipo_auto_cota_box, product_list.qty_numbers
                        FROM order_list
                        INNER JOIN product_list ON product_list.id = order_list.product_id
                        WHERE order_list.product_id = '$product_id' AND order_list.status <> 3"
                    );

                    $cotas_vendidas = [];
                    $cotas_premiadas = "";
                    $cotas_premiadas_roleta = "";
                    $cotas_premiadas_box = "";

                    $all_lucky_numbers = [];
                    $row = $orders->fetch_assoc();

                    $total_numbers = $row["qty_numbers"];
                    $total_paid_numbers = $row["paid_numbers"];
                    $status_auto_cota = $row["status_auto_cota"];
                    $tipo_auto_cota = $row["tipo_auto_cota"];
                    $status_auto_cota_roleta = $row["status_auto_cota_roleta"];
                    $tipo_auto_cota_roleta = $row["tipo_auto_cota_roleta"];
                    $status_auto_cota_box = $row["status_auto_cota_box"];
                    $tipo_auto_cota_box = $row["tipo_auto_cota_box"];


                    while ($row) {
                        $cotas_vendidas[] = $row["order_numbers"];
                        if (empty($cotas_premiadas) && !empty($row["cotas_premiadas"]) && $status_auto_cota == 1 && !empty($row["tipo_auto_cota"])) {
                            $cotas_premiadas = $row["tipo_auto_cota"];
                        }
                        if (empty($cotas_premiadas_roleta) && !empty($row["cotas_premiadas_roleta"]) && $status_auto_cota_roleta == 1 && !empty($row["tipo_auto_cota_roleta"])) {
                            $cotas_premiadas_roleta = $row["tipo_auto_cota_roleta"];
                        }
                        if (empty($cotas_premiadas_box) && !empty($row["cotas_premiadas_box"]) && $status_auto_cota_box == 1 && !empty($row["tipo_auto_cota_box"])) {
                            $cotas_premiadas_box = $row["tipo_auto_cota_box"];
                        }
                        $row = $orders->fetch_assoc();
                    }

                    if (!empty($cotas_premiadas)) {
                        $cotas_vendidas[] = $cotas_premiadas;
                    }
                    if (!empty($cotas_premiadas_roleta)) {
                        $cotas_vendidas[] = $cotas_premiadas_roleta;
                    }
                    if (!empty($cotas_premiadas_box)) {
                        $cotas_vendidas[] = $cotas_premiadas_box;
                    }

                    $all_lucky_numbers = [];
                    foreach ($cotas_vendidas as $cota) {
                        $numbers = explode(',', $cota); // Explodir os números
                        foreach ($numbers as $number) {
                            $all_lucky_numbers[] = trim($number); // Adicionar ao array final
                        }
                    }
                    // Filtrar os números (se necessário)
                    $all_lucky_numbers = array_filter($all_lucky_numbers);

                    if ($qty_numbers < $total_numbers_generated + count($all_lucky_numbers) - 1) {
                        $resp["status"] = "failed";
                        $resp["error"] = "[DP01] - Erro ao criar pedido, selecione uma quantidade menor.";
                        $this->conn->query("DELETE FROM `order_list` WHERE code = '$code'");
                        $this->conn->query("UPDATE `product_list` SET `pending_numbers` = `pending_numbers` - '$total_numbers_generated' WHERE `id` = '$product_id'");
                        flock($lock, LOCK_UN);
                        fclose($lock);
                        return $resp;
                    }

                    // Ensure $all_lucky_numbers contains integers
                    $sold_numbers_set = array_flip($all_lucky_numbers);
                    $numeris = [];
                    $globos = strlen((string) ($qty_numbers));

                    while (count($numeris) < $total_numbers_generated) {
                        $random_number = mt_rand(0, $qty_numbers);
                        $padded_number = str_pad($random_number, $globos, "0", STR_PAD_LEFT);
                         // Ajuste com base na probabilidade
                        $adjusted_number = $random_number;

                        // Probabilidade de 10%: Números mais concentrados no meio
                        if ($probabilidade <= 10) {
                            $middle = $qty_numbers / 2;
                            // Ajuste mais forte no centro
                            $adjusted_number = $middle + mt_rand(- ($middle / 4), $middle / 4);
                        }
                        // Probabilidade de 25%: Ainda concentrado, mas com mais variação
                        elseif ($probabilidade <= 25) {
                            $middle = $qty_numbers / 2;
                            $adjusted_number = $middle + mt_rand(- ($middle / 3), $middle / 3);
                        }
                        // Probabilidade de 50%: Números mais distribuídos, mas ainda próximos ao meio
                        elseif ($probabilidade <= 50) {
                            $middle = $qty_numbers / 2;
                            $adjusted_number = $middle + mt_rand(- ($middle / 2), $middle / 2);
                        }
                        // Probabilidade de 75%: Maior dispersão
                        elseif ($probabilidade <= 75) {
                            $adjusted_number = mt_rand(0, $qty_numbers); // Dispersão mais ampla
                        }
                        // Probabilidade de 100%: Totalmente disperso
                        else {
                            $adjusted_number = mt_rand(0, $qty_numbers); // Números aleatórios de todo o intervalo
                        }

                        // Garantir que o número gerado seja um inteiro
                        $adjusted_number = intval($adjusted_number);

                        // Preenche o número com zeros à esquerda para garantir o formato
                        $padded_number = str_pad($adjusted_number, $globos, "0", STR_PAD_LEFT);

                        // Verifica se o número já foi vendido

                        if (!isset($sold_numbers_set[$padded_number])) {
                            $numeris[] = $padded_number;
                            $sold_numbers_set[$padded_number] = true;
                        }
                    }
                         $agora = new DateTime();

                    if ($habilitar_cota_sorte && $agora >= new DateTime($cota_sorte_ini) && $agora <= new DateTime($cota_sorte_fim)) {
                        if ($quantidade_compra_sorte > 0) {
                            $quantidade_compra_sorte--;
                            $this->conn->query("UPDATE `product_list` SET `quantidade_compra_sorte` = '$quantidade_compra_sorte' WHERE `id` = '$product_id'");
                        } else if ($quantidade_compra_sorte == 0) {
                            $quantidade_compra_sorte--;
                            $this->conn->query("UPDATE `product_list` SET `quantidade_compra_sorte` = '$quantidade_compra_sorte', `cota_sorte` = NULL WHERE `id` = '$product_id'");
                            $numeris[] = trim($cota_sorte);
                        }
                    }
                    sort($numeris);

                    $order_numbers = implode(",", $numeris);
                    $update = $this->conn->query(
                        'UPDATE `order_list` SET `order_numbers` = \'' .
                            $order_numbers .
                            '\' WHERE `code` = \'' .
                            $code .
                            '\''
                    );
                }



                if ($total_amount > 0) {
                    $payment = payment_create_pix(
                        $oid,
                        $total_amount,
                        $customer_name,
                        $customer_email,
                        $customer_cpf,
                        $order_expiration,
                        $customer_phone
                    );
                    if (empty($payment['ok'])) {
                        $this->conn->query("UPDATE order_list SET status = 3 WHERE id = " . (int) $oid . " AND status = 1");
                        $this->correct_stock($product_id);
                        flock($lock, LOCK_UN);
                        fclose($lock);
                        return json_encode([
                            "status" => "failed",
                            "error" => $payment['message'] ?? "Não foi possível gerar o PIX. Tente novamente.",
                        ]);
                    }
                    $resp['gateway'] = $payment['provider'];
                }

                if (!empty($ref)) {
                    $referral = $this->conn->query(
                        'SELECT status FROM referral WHERE referral_code = \'' .
                            $ref .
                            '\''
                    );

                    if (0 < $referral->num_rows) {
                        $row = $referral->fetch_assoc();
                        $status_affiliate = $row["status"];

                        if ($status_affiliate == 1) {
                            $update = $this->conn->query(
                                "UPDATE order_list SET referral_id = " .
                                    $ref .
                                    " WHERE id = " .
                                    $oid
                            );
                        }
                    }
                }

                if ($this->settings->info("enable_dwapi") == 1) {
                    $queryPhone = $this->conn->query(
                        'SELECT phone FROM customer_list WHERE id = \'' .
                            $customer_id .
                            '\''
                    );
                    if ($queryPhone && 0 < $queryPhone->num_rows) {
                        $customerRow = $queryPhone->fetch_assoc();
                        $customerPhone = $customerRow["phone"];
                        $message = $this->settings->info(
                            "mensagem_novo_pedido_dwapi"
                        );
                        $queryPIX = $this->conn->query(
                            'SELECT pix_code FROM order_list WHERE id = \'' .
                                $oid .
                                '\''
                        );
                        if ($queryPIX && 0 < $queryPIX->num_rows) {
                            $pixRow = $queryPIX->fetch_assoc();
                            $pix_code = $pixRow["pix_code"];
                            $this->send_order_whatsapp(
                                $customerPhone,
                                $customer_name,
                                $product_name,
                                $order_numbers,
                                $total_amount,
                                $message,
                                $pix_code,
                                $order_token 
                            );
                        }
                    }
                }

                while ($row = $cart->fetch_assoc()) {
                    if (!empty($data)) {
                        $data .= ", ";
                    }

                    $data .=
                        '(\'' .
                        $oid .
                        '\', \'' .
                        $row["product_id"] .
                        '\', \'' .
                        $row["quantity"] .
                        '\', \'' .
                        $row["price"] .
                        '\')';
                }

                if (!empty($data)) {
                    $sql =
                        "INSERT INTO order_items (`order_id`, `product_id`, `quantity`, `price`) VALUES " .
                        $data;
                    $save = $this->conn->query($sql);

                    if ($save) {
                        $resp["status"] = "success";
                        $this->conn->query(
                            'DELETE FROM `cart_list` where customer_id = \'' .
                                $customer_id .
                                '\''
                        );
                    } else {
                        $resp["status"] = "failed";
                        $resp["error"] = $this->conn->error;
                        $this->conn->query(
                            'DELETE FROM `order_list` where id = \'' .
                                $oid .
                                '\''
                        );
                    }
                } else {
                    $resp["status"] = "success";
                }
            } else {
                $resp["status"] = "failed";
                $resp["error"] = $this->conn->error;
            }

            if ($resp["status"] == "success") {
                $resp["redirect"] = "/compra/" . $order_token . "";
            }

            if ($this->settings->info("enable_pixel") == 1) {
                $dados = [
                    "first_name" => $customer_fname,
                    "last_name" => $customer_lname,
                    "phone" => "55" . $customer_phone,
                    "id" => $oid,
                    "total_amount" => $total_amount,
                ];
                send_event_pixel("InitiateCheckout", $dados);
            }

            $this->correct_stock($product_id);

            if ($status == 1) {
                $query = $this->conn->query(
                    'SELECT SUM(quantity) as quantity FROM order_list WHERE product_id = \'' .
                        $product_id .
                        '\' AND status <> 3'
                );
                if ($query && 0 < $query->num_rows) {
                    $row = $query->fetch_assoc();
                    $quantidade = $row["quantity"];

                    if ($qty_numbers + 1 <= $quantidade) {
                        $this->conn->query(
                            'UPDATE product_list SET status = \'3\', status_display = \'6\' WHERE id = \'' .
                                $product_id .
                                '\''
                        );
                    }
                }
            }

            order_email(
                $this->settings->info("email_order"),
                "[" .
                    $this->settings->info("name") .
                    "] - Confirmação de pedido",
                $oid
            );
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        return json_encode($resp);
    }

    public function gerarNumeroRifa($numerosVendidos, $qtd_pedido, $qty_numbers)
    {
        $numerosGerados = [];

        while (count($numerosGerados) < $qtd_pedido) {
            $numero = "";

            do {
                $numero = str_pad(
                    mt_rand(0, $qty_numbers),
                    strlen($qty_numbers),
                    "0",
                    STR_PAD_LEFT
                );
            } while (
                in_array($numero, $numerosVendidos) ||
                in_array($numero, $numerosGerados)
            );

            $numerosGerados[] = $numero;
        }

        return $numerosGerados;
    }

    public function is_in_array($values, $array)
    {
        $numbers = false;

        foreach ((array) $values as $value) {
            if (in_array($value, $array)) {
                $numbers[] = $value;
            }
        }

        return $numbers;
    }

    public function correct_stock($id)
    {
        if (empty($id)) {
            $id = $_GET["id"];
        }

        $sql_pending = $this->conn->query(
            'SELECT p.pending_numbers, SUM(o.quantity) as quantity FROM product_list as p LEFT JOIN order_list as o ON p.id = o.product_id WHERE p.id = \'' .
                $id .
                '\' AND o.status = \'1\''
        );
        if ($sql_pending && 0 < $sql_pending->num_rows) {
            while ($row = $sql_pending->fetch_assoc()) {
                $pl_pending = $row["pending_numbers"];
                $ol_pending = $row["quantity"];
                if (empty($ol_pending) || $ol_pending == null) {
                    $ol_pending = 0;
                }

                if ($pl_pending != $ol_pending) {
                    $update = $this->conn->query(
                        'UPDATE product_list SET pending_numbers = \'' .
                            $ol_pending .
                            '\' WHERE id = \'' .
                            $id .
                            '\''
                    );

                    if ($update) {
                        $resp["status"] = "success";
                        continue;
                    }

                    $resp["status"] = "failed";
                    $resp["msg"] = $this->conn->error;
                }
            }
        }

        $sql_paid = $this->conn->query(
            'SELECT p.paid_numbers, SUM(o.quantity) as quantity FROM product_list as p LEFT JOIN order_list as o ON p.id = o.product_id WHERE p.id = \'' .
                $id .
                '\' AND o.status = \'2\''
        );
        if ($sql_paid && 0 < $sql_paid->num_rows) {
            $resp = [];
            while ($row = $sql_paid->fetch_assoc()) {
                $pl_paid = $row["paid_numbers"];
                $ol_paid = $row["quantity"];
                if (empty($ol_paid) || $ol_paid == null) {
                    $ol_paid = 0;
                }

                if ($pl_paid != $ol_paid) {
                    $update = $this->conn->query(
                        'UPDATE product_list SET paid_numbers = \'' .
                            $ol_paid .
                            '\' WHERE id = \'' .
                            $id .
                            '\''
                    );

                    if ($update) {
                        $resp["status"] = "success";
                        continue;
                    }

                    $resp["status"] = "failed";
                    $resp["msg"] = $this->conn->error;
                }
            }
        }

        return json_encode($resp);
    }

     public function send_order_whatsapp($phone, $name, $pname, $cotas, $total, $message, $pix, $order_token)
{
    $token_dwapi = $this->settings->info('token_dwapi');
    $cotas = rtrim($cotas, ',');
    $pix = ($pix != '' ? $pix : '');

    if (!empty($message)) {
        // Substitui os placeholders no template da mensagem
        $message = str_replace("[N]", "\n", $message);
        $message = str_replace('[CAMPANHA]', $pname, $message);
        $message = str_replace('[CLIENTE]', $name, $message);
        $message = str_replace('[COTAS]', $cotas, $message);
        $message = str_replace('[TOTAL]', $total, $message);
        $message = str_replace('[PIX]', $pix, $message);
        $link = "https://".$_SERVER['HTTP_HOST']."/compra/" . $order_token;
        $message = str_replace("[LINK]", $link, $message);
        
        // Define os dados da API dwapi
        $dwapi = [
            'number' => '55' . $phone,
            'body' => $message
        ];

        // Inicia a requisição cURL
        $curl_dwapi = curl_init();
        curl_setopt_array($curl_dwapi, [
            CURLOPT_URL => 'https://api.whatsjet.cloud/api/messages/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_TCP_FASTOPEN => 1,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($dwapi, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token_dwapi
            ]
        ]);

        // Executa a requisição e captura a resposta
        $retorno = curl_exec($curl_dwapi);

        // Fecha a conexão cURL
        curl_close($curl_dwapi);
    }
}
    public function update_order_status()
    {
        extract($_POST);
        $qry = $this->conn->query(
            "SELECT o.status, o.product_id, o.quantity, p.qty_numbers, p.type_of_draw, o.code, o.referral_id, o.total_amount" .
                "\r\n\t\t\t\t\t\t\t\t\t" .
                "FROM order_list o" .
                "\r\n\t\t\t\t\t\t\t\t\t" .
                "INNER JOIN product_list p ON o.product_id = p.id" .
                "\r\n\t\t\t\t\t\t\t\t\t" .
                'WHERE o.id = \'' .
                $id .
                '\''
        );

        if (0 < $qry->num_rows) {
            $row = $qry->fetch_assoc();
            $status_order = $row["status"];
            $product_id = $row["product_id"];
            $quantity = $row["quantity"];
            $qty_numbers = $row["qty_numbers"];
            $code = $row["code"];
            $ref = $row["referral_id"];
            $total_amount = $row["total_amount"];
        }

        $product_list = $this->conn->query(
            "\r\n\t\t\t" .
                "SELECT pending_numbers, paid_numbers" .
                "\r\n\t\t\t" .
                "FROM product_list" .
                "\r\n\t\t\t" .
                'WHERE id = \'' .
                $product_id .
                '\'' .
                "\r\n\t\t\t"
        );

        if (0 < $product_list->num_rows) {
            $row = $product_list->fetch_assoc();
            $pendingNumbers = $row["pending_numbers"];
            $updatePending = $pendingNumbers - $quantity;
            $paidNumbers = $row["paid_numbers"];
            $updatePaid = $paidNumbers + $quantity;
        }

        date_default_timezone_set("America/Sao_Paulo");
        $payment_date = date("Y-m-d H:i:s");

        if ($status_order == 3) {
            if ($qty_numbers < $pendingNumbers + $paidNumbers + $quantity) {
                $resp["failed"] = "failed";
                $resp["msg"] =
                    "Não é possível aprovar este pedido pois ultrapassa a quantidade disponível.";
                return json_encode($resp);
            }

            $orders = $this->conn->query(
                'SELECT order_numbers FROM order_list WHERE product_id = \'' .
                    $product_id .
                    '\' AND status <> 3'
            );
            $cotas_vendidas = [];
            $all_lucky_numbers = [];

            while ($row = $orders->fetch_assoc()) {
                $cotas_vendidas[] = $row["order_numbers"];
            }

            $all_lucky_numbers = implode(",", $cotas_vendidas);
            $all_lucky_numbers = explode(",", $all_lucky_numbers);
            $numeros_ja_vendidos = array_filter($all_lucky_numbers);
            $qty_numbers = $qty_numbers - 1;

            if ($qty_numbers < $quantity + count($numeros_ja_vendidos) - 1) {
                $resp["status"] = "failed";
                $resp["error"] =
                    "[DP01] - Erro ao criar pedido, selecione uma quantidade menor.";
                $this->conn->query(
                    'DELETE FROM `order_list` where code = \'' . $code . '\''
                );
                $this->conn->query(
                    'UPDATE `product_list` SET `pending_numbers` = `pending_numbers` - \'' .
                        $quantity .
                        '\' WHERE `id` = \'' .
                        $product_id .
                        '\''
                );
                return json_encode($resp);
            }
            $globos = strlen($qty_numbers);
            $numeris = range(0, $qty_numbers);
            $numeris = array_map(function ($item) use ($qty_numbers, $globos) {
                return str_pad(
                    $item,
                    max((int) $globos, strlen($qty_numbers)),
                    "0",
                    STR_PAD_LEFT
                );
            }, $numeris);
            $array_without_ja_vendidos = array_filter(
                array_diff($numeris, $numeros_ja_vendidos)
            );
            shuffle($array_without_ja_vendidos);
            $order_numbers = array_slice(
                $array_without_ja_vendidos,
                0,
                $quantity
            );
            $order_numbers = implode(",", $order_numbers) . ",";
            $update = $this->conn->query(
                'UPDATE `order_list` set `status` = \'' .
                    $status .
                    '\', `order_numbers` = \'' .
                    $order_numbers .
                    '\', `payment_method` = \'Manual\', `whatsapp_status` = \'\', `date_updated` = \'' .
                    $payment_date .
                    '\' where id = \'' .
                    $id .
                    '\''
            );
        } else {
            $update = $this->conn->query(
                'UPDATE `order_list` set `status` = \'' .
                    $status .
                    '\', `payment_method` = \'Manual\', `whatsapp_status` = \'\', `date_updated` = \'' .
                    $payment_date .
                    '\' where id = \'' .
                    $id .
                    '\''
            );
        }

        if ($update) {
            if ($ref) {
                $referral = $this->conn->query(
                    'SELECT * FROM referral WHERE referral_code = \'' .
                        $ref .
                        '\''
                );

                if (0 < $referral->num_rows) {
                    $row = $referral->fetch_assoc();
                    $status_affiliate = $row["status"];
                    $percentage_affiliate = $row["percentage"];
                    $amount_paid_affiliate = $row["amount_paid"];
                    $amount_pending_affiliate = $row["amount_pending"];
                }
            }

            $user_name = $this->settings->userdata("firstname");
            $insert = $this->conn->query(
                'INSERT INTO `logs` (`origin`, `description`) VALUES (\'ORDER\', \'Pedido ' .
                    $id .
                    " aprovado manualmente pelo usuário " .
                    $user_name .
                    '\')'
            );

            // Quando muda de pendente para pago
            if ($status_order == 1 && $status == "2") {
                $sql_pl =
                    'UPDATE product_list SET pending_numbers = \'' .
                    $updatePending .
                    '\', paid_numbers = \'' .
                    $updatePaid .
                    '\' WHERE id = \'' .
                    $product_id .
                    '\'';
                $this->conn->query($sql_pl);

                // Envio de mensagem WhatsApp
                if ($this->settings->info("enable_dwapi") == 1) {
                    $query2 = $this->conn->query("
                    SELECT o.id, c.firstname, p.name, c.lastname, c.phone, o.pix_code, o.total_amount, o.order_numbers
                    FROM order_list o
                    LEFT JOIN customer_list c ON o.customer_id = c.id
                    LEFT JOIN product_list p ON o.product_id = p.id
                    WHERE o.id = '$id' LIMIT 1
                ");

                    if ($query2 && $query2->num_rows > 0) {
                        $result = $query2->fetch_assoc();
                        $name = $result["firstname"] . " " . $result["lastname"];
                        $pname = $result["name"];
                        $phone = $result["phone"];
                        $cotas = $result["order_numbers"];
                        $total = $result["total_amount"];
                        $pix = $result["pix_code"];

                        $message = $this->settings->info("mensagem_pedido_pago_dwapi");
                        $this->send_order_whatsapp($phone, $name, $pname, $cotas, $total, $message, $pix, $order_token);

                        // Atualizar status do envio
                        $this->conn->query('UPDATE order_list SET dwapi_status = 1 WHERE id = \'' . $id . '\'');
                    }
                }

                if ($ref) {
                    if ($status_affiliate == 1) {
                        $value = $total_amount * $percentage_affiliate;
                        $value = $value / 100;
                        $aff_sql =
                            "UPDATE referral SET amount_pending = amount_pending + " .
                            $value .
                            " WHERE referral_code = " .
                            $ref;
                        $this->conn->query($aff_sql);
                    }
                }

                order_email(
                    $this->settings->info("email_purchase"),
                    "[" .
                        $this->settings->info("name") .
                        "] - Pagamento aprovado",
                    $id
                );
            }

            // Quando muda de cancelado para pago
            if ($status_order == 3 && $status == "2") {
                corrigir_duplicidade($id);
                $sql_pl =
                    'UPDATE product_list SET paid_numbers = \'' .
                    $updatePaid .
                    '\' WHERE id = \'' .
                    $product_id .
                    '\'';
                $this->conn->query($sql_pl);

                // Envio de mensagem WhatsApp
                if ($this->settings->info("enable_dwapi") == 1) {
                    $query2 = $this->conn->query("
                    SELECT o.id, c.firstname, p.name, c.lastname, c.phone, o.pix_code, o.total_amount, o.order_numbers
                    FROM order_list o
                    LEFT JOIN customer_list c ON o.customer_id = c.id
                    LEFT JOIN product_list p ON o.product_id = p.id
                    WHERE o.id = '$id' LIMIT 1
                ");

                    if ($query2 && $query2->num_rows > 0) {
                        $result = $query2->fetch_assoc();
                        $name = $result["firstname"] . " " . $result["lastname"];
                        $pname = $result["name"];
                        $phone = $result["phone"];
                        $cotas = $result["order_numbers"];
                        $total = $result["total_amount"];
                        $pix = $result["pix_code"];

                        $message = $this->settings->info("mensagem_pedido_pago_dwapi");
                        $this->send_order_whatsapp($phone, $name, $pname, $cotas, $total, $message, $pix, $order_token);

                        // Atualizar status do envio
                        $this->conn->query('UPDATE order_list SET dwapi_status = 1 WHERE id = \'' . $id . '\'');
                    }
                }

                if ($ref) {
                    if ($status_affiliate == 1) {
                        $value = $total_amount * $percentage_affiliate;
                        $value = $value / 100;
                        $aff_sql =
                            "UPDATE referral SET amount_pending = amount_pending + " .
                            $value .
                            " WHERE referral_code = " .
                            $ref;
                        $this->conn->query($aff_sql);
                    }
                }

                order_email(
                    $this->settings->info("email_purchase"),
                    "[" .
                        $this->settings->info("name") .
                        "] - Pagamento aprovado",
                    $id
                );
            }

            if ($status_order == "2" && $status == "3") {
                $sql_pl =
                    'UPDATE product_list SET paid_numbers = paid_numbers - \'' .
                    $quantity .
                    '\' WHERE id = \'' .
                    $product_id .
                    '\'';
                $this->conn->query($sql_pl);

                if ($ref) {
                    if ($status_affiliate == 1) {
                        $value = $total_amount * $percentage_affiliate;
                        $value = $value / 100;
                        $aff_sql =
                            "UPDATE referral SET amount_pending = amount_pending - " .
                            $value .
                            " WHERE referral_code = " .
                            $ref;
                        $this->conn->query($aff_sql);
                    }
                }
            }

            if ($status_order == "2" && $status == "1") {
                $sql_pl =
                    'UPDATE product_list SET paid_numbers = paid_numbers - \'' .
                    $quantity .
                    '\' WHERE id = \'' .
                    $product_id .
                    '\'';
                $this->conn->query($sql_pl);

                if ($ref) {
                    if ($status_affiliate == 1) {
                        $value = $total_amount * $percentage_affiliate;
                        $value = $value / 100;
                        $aff_sql =
                            "UPDATE referral SET amount_pending = amount_pending - " .
                            $value .
                            " WHERE referral_code = " .
                            $ref;
                        $this->conn->query($aff_sql);
                    }
                }

                order_email(
                    $this->settings->info("email_order"),
                    "[" .
                        $this->settings->info("name") .
                        "] - Confirmação de pedido",
                    $id
                );
            }

            if ($status_order == "1" && $status == "3") {
                $sql_pl =
                    'UPDATE product_list SET pending_numbers = pending_numbers - \'' .
                    $quantity .
                    '\' WHERE id = \'' .
                    $product_id .
                    '\'';
                $this->conn->query($sql_pl);
            }

            $resp["status"] = "success";
        } else {
            $resp["failed"] = "failed";
            $resp["msg"] = $this->conn->error;
        }

        revert_product($product_id);
        $query = $this->conn->query(
            'SELECT SUM(quantity) as quantity FROM order_list WHERE product_id = \'' .
                $product_id .
                '\' AND status <> 3'
        );
        if ($query && 0 < $query->num_rows) {
            $row = $query->fetch_assoc();
            $quantidade = $row["quantity"];

            if ($qty_numbers <= $quantidade) {
                $this->conn->query(
                    'UPDATE product_list SET status = \'3\', status_display = \'6\' WHERE id = \'' .
                        $product_id .
                        '\''
                );
            }
        }

        return json_encode($resp);
    }
    public function check_payment_status()
    {
        if (!$this->settings->userdata("firstname")) {
            $resp["status"] = "failed";
            $resp["msg"] = "Não autorizado.";
            return json_encode($resp);
        }

        $orderToken = trim((string) ($_POST['order_token'] ?? ''));
        $statement = $this->conn->prepare('SELECT id, status FROM order_list WHERE order_token = ? LIMIT 1');
        $statement->bind_param('s', $orderToken);
        $statement->execute();
        $result = $statement->get_result();
        $order = $result ? $result->fetch_assoc() : null;
        $statement->close();
        if (!$order) {
            return json_encode(['status' => 'failed', 'msg' => 'Pedido não encontrado.']);
        }
        if ((int) $order['status'] === 1) {
            $check = payment_check_order((int) $order['id']);
            return json_encode(['status' => $check['status'], 'msg' => $check['message'] ?? '']);
        }
        return json_encode(['status' => (int) $order['status']]);
    }

    public function export_raffle_contacts()
    {
        extract($_GET);
        $where = "";

        if ($raffle) {
            $where .= ' AND o.product_id = \'' . $raffle . '\'';
        }

        if ($status) {
            $where .= ' AND o.status = \'' . $status . '\'';
        }

        if (!empty($where)) {
            $where = " WHERE " . ltrim($where, " AND");
        }

        $qry = $this->conn->query(
            'SELECT o.*, CONCAT(c.firstname, \' \', c.lastname) as customer, p.type_of_draw, c.phone, c.cpf, o.id, o.order_numbers' .
                "\r\n\t\t\t" .
                "FROM `order_list` o" .
                "\r\n\t\t\t" .
                "INNER JOIN customer_list c ON o.customer_id = c.id" .
                "\r\n\t\t\t" .
                "INNER JOIN product_list p ON o.product_id = p.id" .
                "\r\n\t\t\t" .
                $where .
                "\r\n\t\t\t" .
                "ORDER BY ABS(UNIX_TIMESTAMP(o.date_created)) DESC"
        );

        if (0 < $qry->num_rows) {
            header("Content-Type: text/csv");
            header(
                'Content-Disposition: attachment; filename="contatos-' .
                    base64_encode($raffle) .
                    '.csv"'
            );
            header("Pragma: no-cache");
            header("Expires: 0");
            $file = fopen("php://output", "w");
            fwrite($file, "﻿");

            while ($row = $qry->fetch_assoc()) {
                fputcsv(
                    $file,
                    [
                        $row["id"],
                        $row["customer"],
                        $row["phone"],
                        $row["cpf"],
                        $row["order_numbers"],
                    ],
                    ";",
                    " "
                );
            }

            fclose($file);
            exit();
        } else {
            $resp["status"] = "failed";
            $resp["msg"] = $this->conn->error;
        }

        return json_encode($resp);
    }

    public function export_raffle_contacts2()
    {
        extract($_GET);
        require_once "../includes/simplexlsxgen/src/SimpleXLSXGen.php";
        $where = "";

        if ($raffle) {
            $where .= ' AND o.product_id = \'' . $raffle . '\'';
        }

        if ($status) {
            $where .= ' AND o.status = \'' . $status . '\'';
        }

        if (!empty($where)) {
            $where = " WHERE " . ltrim($where, " AND");
        }

        $qry = $this->conn->query(
            'SELECT o.*, CONCAT(c.firstname, \' \', c.lastname) as customer, p.type_of_draw, c.phone, c.cpf, o.id, o.order_numbers' .
                "\r\n\t\t\t" .
                "FROM `order_list` o" .
                "\r\n\t\t\t" .
                "INNER JOIN customer_list c ON o.customer_id = c.id" .
                "\r\n\t\t\t" .
                "INNER JOIN product_list p ON o.product_id = p.id" .
                "\r\n\t\t\t" .
                $where .
                "\r\n\t\t\t" .
                "ORDER BY ABS(UNIX_TIMESTAMP(o.date_created)) DESC"
        );
        $lista = [];
        $product = $this->conn
            ->query(
                'SELECT name FROM product_list WHERE id = \'' . $raffle . '\''
            )
            ->fetch_assoc();
        $lista[0] = [
            '<middle><center><style height="30" bgcolor="#800000" color="#FFFFFF">' .
                $product["name"] .
                "</style></center></middle>",
        ];

        if ($this->settings->info("enable_cpf") == 1) {
            $lista[1] = [
                '<middle><center><style height="30" bgcolor="#000000" color="#FFFFFF">PEDIDO</style></center></middle>',
                '<middle><center><style height="30" bgcolor="#000000" color="#FFFFFF">NOME</style></center></middle>',
                '<middle><center><style height="30" bgcolor="#000000" color="#FFFFFF">TELEFONE</style></center></middle>',
                '<middle><center><style height="30" bgcolor="#000000" color="#FFFFFF">CPF</style></center></middle>',
                '<middle><center><style height="30" bgcolor="#000000" color="#FFFFFF">COTA</style></center></middle>',
            ];
        } else {
            $lista[1] = [
                '<middle><center><style height="30" bgcolor="#000000" color="#FFFFFF">PEDIDO</style></center></middle>',
                '<middle><center><style height="30" bgcolor="#000000" color="#FFFFFF">NOME</style></center></middle>',
                '<middle><center><style height="30" bgcolor="#000000" color="#FFFFFF">TELEFONE</style></center></middle>',
                '<middle><center><style height="30" bgcolor="#000000" color="#FFFFFF">COTA</style></center></middle>',
            ];
        }

        if (0 < $qry->num_rows) {
            while ($row = $qry->fetch_assoc()) {
                $numbers = array_filter(
                    explode(",", $row["order_numbers"] ?? "")
                );

                if ($numbers) {
                    $y = 1;

                    foreach ($numbers as $number) {
                        $key_list = $row["id"] . "__" . $y;
                        $lista[$key_list]["id"] = $row["id"] ?? "";
                        $lista[$key_list]["nome"] = $row["customer"] ?? "";
                        $lista[$key_list]["phone"] =
                            "(" .
                            substr($row["phone"], 0, 2) .
                            ") " .
                            substr($row["phone"], 2, -4) .
                            " - " .
                            substr($row["phone"], -4) ??
                            "";

                        if ($this->settings->info("enable_cpf") == 1) {
                            $lista[$key_list]["cpf"] = $row["cpf"] ?? "";
                        }

                        $lista[$key_list]["cotas"] = $number;
                        ++$y;
                    }
                }
            }

            if ($this->settings->info("enable_cpf") == 1) {
                $xlsx = Shuchkin\SimpleXLSXGen::fromArray($lista)
                    ->setDefaultFont("Calibri")
                    ->setDefaultFontSize(10)
                    ->mergeCells("A1:E1");
            } else {
                $xlsx = Shuchkin\SimpleXLSXGen::fromArray($lista)
                    ->setDefaultFont("Calibri")
                    ->setDefaultFontSize(10)
                    ->mergeCells("A1:D1");
            }

            $xlsx->downloadAs("relatorio-pedidos.xlsx");
            exit();
        } else {
            $resp["status"] = "failed";
            $resp["msg"] = $this->conn->error;
        }

        return json_encode($resp);
    }

    public function export_customers()
    {
        extract($_GET);
        $where = "";

        if ($name) {
            $where =
                'WHERE CONCAT(firstname, \' \', lastname) LIKE \'%' .
                $name .
                '%\'';
        }

        if ($phone) {
            $where = 'WHERE phone LIKE \'%' . $phone . '%\'';
        }

        if ($email) {
            $where = 'WHERE email LIKE \'%' . $email . '%\'';
        }

        $qry = $this->conn->query(
            'SELECT *, concat(firstname,\' \', lastname) as `name`' .
                "\r\n\t\t" .
                "from `customer_list`" .
                "\r\n\t\t" .
                $where .
                "\r\n\t\t" .
                "order by `name` asc"
        );

        if (0 < $qry->num_rows) {
            header("Content-Type: text/csv");
            header('Content-Disposition: attachment; filename="clientes.csv"');
            header("Pragma: no-cache");
            header("Expires: 0");
            $file = fopen("php://output", "w");
            fwrite($file, "﻿");

            while ($row = $qry->fetch_assoc()) {
                fputcsv(
                    $file,
                    [
                        $row["id"],
                        $row["name"],
                        $row["phone"],
                        $row["email"],
                        $row["cpf"],
                    ],
                    ";",
                    " "
                );
            }

            fclose($file);
            exit();
        } else {
            $resp["status"] = "failed";
            $resp["msg"] = $this->conn->error;
        }

        return json_encode($resp);
    }

    public function delete_order()
    {
        if (!$this->settings->userdata("firstname")) {
            $resp["status"] = "failed";
            $resp["msg"] = "Não autorizado.";
            return json_encode($resp);
        }

        extract($_POST);
        $qry = $this->conn->query(
            'SELECT status, product_id, quantity FROM order_list WHERE id = \'' .
                $id .
                '\''
        );

        if (0 < $qry->num_rows) {
            $row = $qry->fetch_assoc();
            $status_order = $row["status"];
            $product_id = $row["product_id"];
            $quantity = $row["quantity"];
        }

        $product_list = $this->conn->query(
            "\r\n\t\t\t" .
                "SELECT pending_numbers, paid_numbers" .
                "\r\n\t\t\t" .
                "FROM product_list" .
                "\r\n\t\t\t" .
                'WHERE id = \'' .
                $product_id .
                '\'' .
                "\r\n\t\t"
        );

        if (0 < $product_list->num_rows) {
            $row = $product_list->fetch_assoc();
            $pendingNumbers = $row["pending_numbers"];
            $updatePending = $pendingNumbers - $quantity;
            $paidNumbers = $row["paid_numbers"];
            $updatePaid = $paidNumbers - $quantity;
        }

        if ($status_order == "1") {
            $sql_pl =
                'UPDATE product_list SET pending_numbers = \'' .
                $updatePending .
                '\' WHERE id = \'' .
                $product_id .
                '\'';
            $this->conn->query($sql_pl);
        }

        if ($status_order == "2") {
            $sql_pl =
                'UPDATE product_list SET paid_numbers = \'' .
                $updatePaid .
                '\' WHERE id = \'' .
                $product_id .
                '\'';
            $this->conn->query($sql_pl);
        }

        $delete = $this->conn->query(
            'DELETE FROM `order_list` where id = \'' . $id . '\''
        );
        revert_product($product_id);

        if ($delete) {
            $resp["status"] = "success";
            $user_name = $this->settings->userdata("firstname");
            $insert = $this->conn->query(
                'INSERT INTO `logs` (`origin`, `description`) VALUES (\'ORDER\', \'Pedido ' .
                    $id .
                    " deletado pelo usuário " .
                    $user_name .
                    '\')'
            );
        } else {
            $resp["status"] = "failed";
            $resp["error"] = $this->conn->error;
        }

        if ($resp["status"] == "success") {
        }

        return json_encode($resp);
    }

    public function correct_order()
    {
        extract($_POST);
        $qry = $this->conn
            ->query(
                "SELECT o.status, p.id as product, o.quantity, p.qty_numbers, o.code" .
                    "\r\n\t\t\t\t\t\t\t\t\t" .
                    "FROM order_list o " .
                    "\r\n\t\t\t\t\t\t\t\t\t" .
                    "INNER JOIN product_list p ON o.product_id = p.id" .
                    "\r\n\t\t\t\t\t\t\t\t\t" .
                    'WHERE o.id = \'' .
                    $id .
                    '\''
            )
            ->fetch_assoc();
        $product_id = $qry["product"];
        $qty_numbers = $qry["qty_numbers"] - 1;
        $total_numbers_generated = $qry["quantity"];
        $orders = $this->conn->query(
            'SELECT order_numbers FROM order_list WHERE product_id = \'' .
                $product_id .
                '\' AND status <> 3'
        );
        $cotas_vendidas = [];
        $all_lucky_numbers = [];

        while ($row = $orders->fetch_assoc()) {
            $cotas_vendidas[] = $row["order_numbers"];
        }

        $all_lucky_numbers = implode(",", $cotas_vendidas);
        $all_lucky_numbers = explode(",", $all_lucky_numbers);
        $numeros_ja_vendidos = array_filter($all_lucky_numbers);

        if (
            $qty_numbers <
            $total_numbers_generated + count($numeros_ja_vendidos) - 1
        ) {
            $resp["status"] = "failed";
            $resp["error"] =
                "[DP01] - Erro ao criar pedido, selecione uma quantidade menor.";
            $this->conn->query(
                'DELETE FROM `order_list` where code = \'' . $code . '\''
            );
            $this->conn->query(
                'UPDATE `product_list` SET `pending_numbers` = `pending_numbers` - \'' .
                    $total_numbers_generated .
                    '\' WHERE `id` = \'' .
                    $product_id .
                    '\''
            );
            return json_encode($resp);
        }
        $globos = strlen($qty_numbers);
        $numeris = range(0, $qty_numbers);
        $numeris = array_map(function ($item) use ($qty_numbers, $globos) {
            return str_pad(
                $item,
                max((int) $globos, strlen($qty_numbers)),
                "0",
                STR_PAD_LEFT
            );
        }, $numeris);
        $array_without_ja_vendidos = array_filter(
            array_diff($numeris, $numeros_ja_vendidos)
        );
        shuffle($array_without_ja_vendidos);
        $order_numbers = array_slice(
            $array_without_ja_vendidos,
            0,
            $total_numbers_generated
        );
        $order_numbers = implode(",", $order_numbers) . ",";
        $update = $this->conn->query(
            'UPDATE order_list SET order_numbers =  \'' .
                $order_numbers .
                '\' WHERE id = \'' .
                $id .
                '\''
        );

        if ($update) {
            $resp["status"] = "success";
        } else {
            $resp["status"] = "failed";
        }

        return json_encode($resp);
    }

    public function correct_quantity()
    {
        extract($_POST);
        $qry = $this->conn
            ->query(
                "SELECT o.status, p.id as product, o.quantity, p.qty_numbers, o.code, o.order_numbers" .
                    "\r\n\t\t\t\t\t\t\t\t\t" .
                    "FROM order_list o " .
                    "\r\n\t\t\t\t\t\t\t\t\t" .
                    "INNER JOIN product_list p ON o.product_id = p.id" .
                    "\r\n\t\t\t\t\t\t\t\t\t" .
                    'WHERE o.id = \'' .
                    $id .
                    '\''
            )
            ->fetch_assoc();
        $product_id = $qry["product"];
        $qty_numbers = $qry["qty_numbers"] - 1;
        $numbers = $qry["order_numbers"];
        $total_numbers_generated = $qtd;
        $orders = $this->conn->query(
            'SELECT order_numbers FROM order_list WHERE product_id = \'' .
                $product_id .
                '\' AND status <> 3'
        );
        $cotas_vendidas = [];
        $all_lucky_numbers = [];

        while ($row = $orders->fetch_assoc()) {
            $cotas_vendidas[] = $row["order_numbers"];
        }

        $all_lucky_numbers = implode(",", $cotas_vendidas);
        $all_lucky_numbers = explode(",", $all_lucky_numbers);
        $numeros_ja_vendidos = array_filter($all_lucky_numbers);

        if (
            $qty_numbers <
            $total_numbers_generated + count($numeros_ja_vendidos) - 1
        ) {
            $resp["status"] = "failed";
            $resp["error"] =
                "[DP01] - Erro ao criar pedido, selecione uma quantidade menor.";
            $this->conn->query(
                'DELETE FROM `order_list` where code = \'' . $code . '\''
            );
            $this->conn->query(
                'UPDATE `product_list` SET `pending_numbers` = `pending_numbers` - \'' .
                    $total_numbers_generated .
                    '\' WHERE `id` = \'' .
                    $product_id .
                    '\''
            );
            return json_encode($resp);
        }
        $globos = strlen($qty_numbers);
        $numeris = range(0, $qty_numbers);
        $numeris = array_map(function ($item) use ($qty_numbers, $globos) {
            return str_pad(
                $item,
                max((int) $globos, strlen($qty_numbers)),
                "0",
                STR_PAD_LEFT
            );
        }, $numeris);
        $array_without_ja_vendidos = array_filter(
            array_diff($numeris, $numeros_ja_vendidos)
        );
        shuffle($array_without_ja_vendidos);
        $order_numbers = array_slice(
            $array_without_ja_vendidos,
            0,
            $total_numbers_generated
        );
        $order_numbers = $numbers . implode(",", $order_numbers) . ",";
        $update = $this->conn->query(
            'UPDATE order_list SET order_numbers =  \'' .
                $order_numbers .
                '\' WHERE id = \'' .
                $id .
                '\''
        );

        if ($update) {
            $resp["status"] = "success";
        } else {
            $resp["status"] = "failed";
        }

        return json_encode($resp);
    }

    public function contact_send_email()
    {
        global $_settings;
        extract($_POST);
        $to = $_settings->info("email");
        $message = "";

        if (!$_settings->info("smtp_host")) {
            $message .= "Nome: " . $nome . "\n";
            $message .= "Email: " . $email . "\n";
            $message .= "Telefone: " . $telefone . "\n";
            $message .= "Campanha: " . $campanha . "\n";
            $message .= "Assunto: " . $assunto . "\n";
            $message .= "Mensagem: " . $mensagem . "\n";
            $mailSent = mail($to, $assunto, $message);

            if ($mailSent) {
                $resp["status"] = "success";
            } else {
                $resp["status"] = "failed";
            }
        } else {
            require_once "../includes/phpmailer/src/Exception.php";
            require_once "../includes/phpmailer/src/PHPMailer.php";
            require_once "../includes/phpmailer/src/SMTP.php";
            $message .= "Nome: " . $nome . "<br>";
            $message .= "Email: " . $email . "<br>";
            $message .= "Telefone: " . $telefone . "<br>";
            $message .= "Campanha: " . $campanha . "<br>";
            $message .= "Assunto: " . $assunto . "<hr>";
            $message .= "Mensagem: " . $mensagem;
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->SMTPOptions = [
                    "ssl" => [
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                        "allow_self_signed" => true,
                    ],
                ];
                $mail->SMTPAuth = true;
                $mail->Host = $_settings->info("smtp_host");
                $mail->Username = $_settings->info("smtp_user");
                $mail->Password = $_settings->info("smtp_pass");
                $mail->SMTPSecure =
                    PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = $_settings->info("smtp_port");
                $mail->CharSet = "UTF-8";
                $mail->setFrom(
                    $_settings->info("smtp_user"),
                    $_settings->info("name")
                );
                $mail->addAddress($to, $nome);
                $mail->isHTML(true);
                $mail->Subject = $assunto;
                $mail->Body = $message;
                $mail->send();
                $resp["status"] = "success";
            } catch (PHPMailer\PHPMailer\Exception $e) {
                echo "Não foi possível enviar a mensagem. Mailer Error: " .
                    $mail->ErrorInfo;
                $resp["status"] = "failed";
            }
        }

        return json_encode($resp);
    }

    public function recover_password()
    {
        if (!$this->settings->userdata("firstname")) {
            $resp["status"] = "failed";
            $resp["msg"] = "Não autorizado.";
            return json_encode($resp);
        }

        global $_settings;
        extract($_POST);
        $assunto = "Recuperação de senha";
        $message = "";
        $senha = $this->generate_password();
        $qry = $this->conn->query(
            'SELECT * FROM customer_list WHERE email = \'' . $email . '\''
        );

        if (0 < $qry->num_rows) {
            $update_pass = $this->conn->query(
                'UPDATE `customer_list` SET `password` = md5(\'' .
                    $senha .
                    '\') WHERE email = \'' .
                    $email .
                    '\''
            );

            if ($update_pass) {
                if (!$_settings->info("smtp_host")) {
                    $message .=
                        'Olá, vimos que você solicitou uma recuperação de senha, aqui estão os dados da sua nova senha:\\n\\n';
                    $message .= "Nova senha: " . $senha . "\n";
                    $mailSent = mail($email, $assunto, $message);

                    if ($mailSent) {
                        $resp["status"] = "success";
                    } else {
                        $resp["status"] = "failed";
                    }
                } else {
                    require_once "../includes/phpmailer/src/Exception.php";
                    require_once "../includes/phpmailer/src/PHPMailer.php";
                    require_once "../includes/phpmailer/src/SMTP.php";
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    $message .=
                        "Olá, vimos que você solicitou uma recuperação de senha, aqui estão os dados da sua nova senha:<br><br>";
                    $message .= "Nova senha: " . $senha . "<br><br>";
                    $message .= "Atenciosamente " . $_settings->info("name");

                    try {
                        $mail->isSMTP();
                        $mail->SMTPSecure = "ssl";
                        $mail->Mailer = "smtp";
                        $mail->SMTPDebug = 0;
                        $mail->SMTPAuth = true;
                        $mail->Host = $_settings->info("smtp_host");
                        $mail->Username = $_settings->info("smtp_user");
                        $mail->Password = $_settings->info("smtp_pass");
                        $mail->SMTPSecure =
                            PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port = $_settings->info("smtp_port");
                        $mail->CharSet = "UTF-8";
                        $mail->setFrom(
                            $_settings->info("smtp_user"),
                            $_settings->info("name")
                        );
                        $mail->addAddress($email, "Customer");
                        $mail->isHTML(true);
                        $mail->Subject = $assunto;
                        $mail->Body = $message;
                        $mail->send();
                        $resp["status"] = "success";
                    } catch (PHPMailer\PHPMailer\Exception $e) {
                        echo "Não foi possível enviar a mensagem. Mailer Error: " .
                            $mail->ErrorInfo;
                        $resp["status"] = "failed";
                    }
                }
            }
        }

        return json_encode($resp);
    }

    public function recover_password_admin()
    {
        if (!$this->settings->userdata("firstname")) {
            $resp["status"] = "failed";
            $resp["msg"] = "Não autorizado.";
            return json_encode($resp);
        }

        global $_settings;
        extract($_POST);
        $assunto = "Recuperação de senha";
        $message = "";
        $senha = $this->generate_password();
        $qry = $this->conn->query(
            'SELECT * FROM users WHERE username = \'' .
                $username .
                '\' AND email = \'' .
                $email .
                '\''
        );

        if (0 < $qry->num_rows) {
            $update_pass = $this->conn->query(
                'UPDATE `users` SET `password` = md5(\'' .
                    $senha .
                    '\') WHERE username = \'' .
                    $username .
                    '\''
            );

            if ($update_pass) {
                if (!$_settings->info("smtp_host")) {
                    $message .=
                        "Olá, vimos que você solicitou uma recuperação de senha, aqui estão os dados da sua nova senha:" .
                        "\n\n";
                    $message .= "Nova senha: " . $senha . "\n\n";
                    $message .= "Atenciosamente " . $_settings->info("name");
                    $mailSent = mail($email, $assunto, $message);

                    if ($mailSent) {
                        $resp["status"] = "success";
                    } else {
                        $resp["status"] = "failed";
                    }
                } else {
                    require_once "../includes/phpmailer/src/Exception.php";
                    require_once "../includes/phpmailer/src/PHPMailer.php";
                    require_once "../includes/phpmailer/src/SMTP.php";
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    $message .=
                        "Olá, vimos que você solicitou uma recuperação de senha, aqui estão os dados da sua nova senha:<br><br>";
                    $message .= "Nova senha: " . $senha . "<br><br>";
                    $message .= "Atenciosamente " . $_settings->info("name");

                    try {
                        $mail->SMTPAuth = true;
                        $mail->Host = $_settings->info("smtp_host");
                        $mail->Username = $_settings->info("smtp_user");
                        $mail->Password = $_settings->info("smtp_pass");
                        $mail->SMTPSecure =
                            PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port = $_settings->info("smtp_port");
                        $mail->CharSet = "UTF-8";
                        $mail->setFrom(
                            $_settings->info("smtp_user"),
                            $_settings->info("name")
                        );
                        $mail->addAddress($email, $_settings->info("name"));
                        $mail->isHTML(true);
                        $mail->Subject = $assunto;
                        $mail->Body = $message;
                        $mail->send();
                        $resp["status"] = "success";
                    } catch (PHPMailer\PHPMailer\Exception $e) {
                        echo "Não foi possível enviar a mensagem. Mailer Error: " .
                            $mail->ErrorInfo;
                        $resp["status"] = "failed";
                    }
                }
            }
        } else {
            echo "Usuário ou email inválido.";
            $resp["status"] = "failed";
            return json_encode($resp);
        }

        return json_encode($resp);
    }

    public function generate_password()
    {
        $alphabet =
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;

        for ($i = 0; $i < 8; ++$i) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        return implode($pass);
    }

    public function search_orders_by_phone()
    {
        $phone = $this->conn->real_escape_string($_POST["phone"]);
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $resp = [];
        $customerQuery = $this->conn->query(
            "\r\n\t\t\t" .
                "SELECT id" .
                "\r\n\t\t\t" .
                "FROM customer_list" .
                "\r\n\t\t\t" .
                'WHERE phone = \'' .
                $phone .
                '\'' .
                "\r\n\t\t\t"
        );
        if ($customerQuery && 0 < $customerQuery->num_rows) {
            $customerRow = $customerQuery->fetch_assoc();
            $customerId = $customerRow["id"];
            $orderQuery = $this->conn->query(
                "\r\n\t\t\t\t" .
                    "SELECT *" .
                    "\r\n\t\t\t\t" .
                    "FROM order_list" .
                    "\r\n\t\t\t\t" .
                    'WHERE customer_id = \'' .
                    $customerId .
                    '\'' .
                    "\r\n\t\t\t\t"
            );
            if ($orderQuery && 0 < $orderQuery->num_rows) {
                $_SESSION["phone"] = $phone;
                $resp["status"] = "success";
                $resp["redirect"] = "/meus-numeros";
            } else {
                $resp["status"] = "failed";
                $resp["error"] =
                    "Nenhum resultado encontrado na tabela order_list para o número de telefone fornecido.";
            }
        } else {
            $resp["status"] = "failed";
            $resp["error"] =
                "Nenhum resultado encontrado na tabela customer_list para o número de telefone fornecido.";
        }

        return json_encode($resp);
    }

    public function search_orders_by_cpf()
    {
        $cpf = $this->conn->real_escape_string($_POST["cpf"]);
        $resp = [];
        $cpfQuery = $this->conn->query(
            'SELECT `id` FROM customer_list WHERE cpf = \'' . $cpf . '\''
        );
        if ($cpfQuery && 0 < $cpfQuery->num_rows) {
            $cpfRow = $cpfQuery->fetch_assoc();
            $clientId = $cpfRow["id"];
            $orderQuery = $this->conn->query(
                'SELECT * FROM order_list WHERE customer_id = \'' .
                    $clientId .
                    '\''
            );
            if ($orderQuery && 0 < $orderQuery->num_rows) {
                $_SESSION["cpf"] = $cpf;
                $resp["status"] = "success";
                $resp["redirect"] = "/meus-numeros";
            } else {
                $resp["status"] = "failed";
                $resp["error"] =
                    "Nenhum resultado encontrado na tabela order_list para o CPF fornecido.";
            }
        } else {
            $resp["status"] = "failed";
            $resp["error"] =
                "Nenhum resultado encontrado na tabela customer_list para o CPF fornecido.";
        }

        return json_encode($resp);
    }

    public function load_numbers()
    {
        $status = $_POST["status"];
        $id = $_POST["id"];
        $resultado = [];

        if ($status == 1) {
            $firstnames = [];
            $stmt_plist = $this->conn->prepare(
                "SELECT qty_numbers, pending_numbers, paid_numbers FROM `product_list` WHERE id = ?"
            );
            $stmt_plist->bind_param("i", $id);
            $stmt_plist->execute();
            $product_list = $stmt_plist->get_result();

            if ($product_list->num_rows > 0) {
                $product = $product_list->fetch_assoc();
                $qty_numbers = $product["qty_numbers"];
                $pending_numbers = $product["pending_numbers"];
                $paid_numbers = $product["paid_numbers"];
            }

            $qty_numbers = $qty_numbers - 1;
            $total_numbers_generated = $qty_numbers;
            if ($pending_numbers || $paid_numbers) {
                $total_numbers_generated =
                    $qty_numbers - ($pending_numbers + $paid_numbers);
            }

            $all_lucky_numbers = [];
            $orders = $this->conn->query("SELECT o.*
	FROM `order_list` o 	
	WHERE o.product_id = '{$id}'");

            while ($row1 = $orders->fetch_assoc()) {
                $all_lucky_numbers[] = $row1["order_numbers"];
            }

            $all_lucky_numbers = implode(",", $all_lucky_numbers);
            $all_lucky_numbers = explode(",", $all_lucky_numbers);
            $used_numbers = array_flip($all_lucky_numbers);
            $numeros = [];
            for ($j = -1; $j < $total_numbers_generated; $j++) {
                $stmt_orders = $this->conn->prepare(
                    "SELECT order_numbers FROM order_list WHERE product_id = ?"
                );
                $stmt_orders->bind_param("i", $id);
                $stmt_orders->execute();
                $orders = $stmt_orders->get_result();
                if ($orders->num_rows > 0) {
                    $order = $orders->fetch_assoc();
                    $order_lucky_numbers = $order["order_numbers"];
                }

                $lucky_numbers = "";
 
                do {
                    $random_number = str_pad(
                        rand(0, $qty_numbers),
                        strlen($qty_numbers),
                        "0",
                        STR_PAD_LEFT
                    );
                    $random_number = sprintf(
                        "%0" . strlen($qty_numbers) . "d",
                        $random_number
                    );
                } while (isset($used_numbers[$random_number]));

                $used_numbers[$random_number] = true;
                $numeros[] = $random_number;
            }
        } elseif ($status == 2) {
            $result = $this->conn->query("
  SELECT ol.order_numbers, cl.firstname
  FROM order_list ol
  JOIN customer_list cl ON ol.customer_id = cl.id
  WHERE ol.product_id = '{$id}' AND ol.status = '1'
");

            $numeros = [];
            $firstnames = [];

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $order_numbers = $row["order_numbers"];
                    $order_numbers_array = explode(",", $order_numbers);
                    $order_numbers_array = array_filter($order_numbers_array);

                    foreach ($order_numbers_array as $numero) {
                        $numeros[] = $numero;
                        $firstnames[] = $row["firstname"];
                    }
                }
            }
        } elseif ($status == 3) {
            $result = $this->conn->query("
  SELECT ol.order_numbers, cl.firstname
  FROM order_list ol
  JOIN customer_list cl ON ol.customer_id = cl.id
  WHERE ol.product_id = '{$id}' AND ol.status = '2'
");

            $numeros = [];
            $firstnames = [];

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $order_numbers = $row["order_numbers"];
                    $order_numbers_array = explode(",", $order_numbers);
                    $order_numbers_array = array_filter($order_numbers_array);

                    foreach ($order_numbers_array as $numero) {
                        $numeros[] = $numero;
                        $firstnames[] = $row["firstname"];
                    }
                }
            }
        } elseif ($status == 4) {
            $numeros = [];
            $firstnames = [];
            $payment_status = [];

            $stmt_plist = $this->conn->prepare(
                "SELECT qty_numbers, pending_numbers, paid_numbers FROM `product_list` WHERE id = ?"
            );
            $stmt_plist->bind_param("i", $id);
            $stmt_plist->execute();
            $product_list = $stmt_plist->get_result();

            if ($product_list->num_rows > 0) {
                $product = $product_list->fetch_assoc();
                $qty_numbers = $product["qty_numbers"];
                $pending_numbers = $product["pending_numbers"];
                $paid_numbers = $product["paid_numbers"];
            }


            //            $qty_numbers = $qty_numbers - 1;
            $total_numbers_generated = 25;
            /*	if($pending_numbers || $paid_numbers){
    $total_numbers_generated = $qty_numbers - ($pending_numbers + $paid_numbers);
    } */

            $all_lucky_numbers = [];
            $orders = $this->conn->query("
  SELECT ol.order_numbers, ol.status, cl.firstname
  FROM order_list ol
  JOIN customer_list cl ON ol.customer_id = cl.id
  WHERE ol.product_id = '{$id}'
");

            while ($row1 = $orders->fetch_assoc()) {
                $order_numbers = $row1["order_numbers"];
                $order_numbers_array = explode(",", $order_numbers);
                $order_numbers_array = array_filter($order_numbers_array);

                foreach ($order_numbers_array as $numero) {
                    $numeros[] = $numero;
                    $firstnames[$numero] = $row1["firstname"];
                    $payment_status[$numero] = $row1["status"];
                }
            }

            $all_lucky_numbers = implode(",", $all_lucky_numbers);
            $all_lucky_numbers = explode(",", $all_lucky_numbers);
            $used_numbers = array_flip($all_lucky_numbers);
            $numeros = [];
            for ($j = 0; $j < $total_numbers_generated; $j++) {
                $stmt_orders = $this->conn->prepare(
                    "SELECT order_numbers FROM order_list WHERE product_id = ?"
                );
                $stmt_orders->bind_param("i", $id);
                $stmt_orders->execute();
                $orders = $stmt_orders->get_result();
                if ($orders->num_rows > 0) {
                    $order = $orders->fetch_assoc();
                    $order_lucky_numbers = $order["order_numbers"];
                }

                $lucky_numbers = "";

                do {
                    $random_number = str_pad(
                         rand(0, $qty_numbers - 1),
                        strlen($qty_numbers),
                        "0",
                        STR_PAD_LEFT
                    );
                    $random_number = sprintf(
                        "%0" . strlen($qty_numbers) . "d",
                        $random_number
                    );
                } while (isset($used_numbers[$random_number]));

                $used_numbers[$random_number] = true;
                $numeros[] = $random_number;
            }
        } elseif ($status == 5) {
            $numeros = [];
            $firstnames = [];
            $payment_status = [];

            $stmt_plist = $this->conn->prepare(
                "SELECT qty_numbers, pending_numbers, paid_numbers FROM `product_list` WHERE id = ?"
            );
            $stmt_plist->bind_param("i", $id);
            $stmt_plist->execute();
            $product_list = $stmt_plist->get_result();

            if ($product_list->num_rows > 0) {
                $product = $product_list->fetch_assoc();
                $qty_numbers = $product["qty_numbers"];
                $pending_numbers = $product["pending_numbers"];
                $paid_numbers = $product["paid_numbers"];
            }

            $total_numbers_generated = 50;

            $all_lucky_numbers = [];
            $orders = $this->conn->query("
  SELECT ol.order_numbers, ol.status, cl.firstname
  FROM order_list ol
  JOIN customer_list cl ON ol.customer_id = cl.id
  WHERE ol.product_id = '{$id}'
");

            while ($row1 = $orders->fetch_assoc()) {
                $order_numbers = $row1["order_numbers"];
                $order_numbers_array = explode(",", $order_numbers);
                $order_numbers_array = array_filter($order_numbers_array);

                foreach ($order_numbers_array as $numero) {
                    $numeros[] = $numero;
                    $firstnames[$numero] = $row1["firstname"];
                    $payment_status[$numero] = $row1["status"];
                }
            }

            $all_lucky_numbers = implode(",", $all_lucky_numbers);
            $all_lucky_numbers = explode(",", $all_lucky_numbers);
            $used_numbers = array_flip($all_lucky_numbers);
            $numeros = [];
            for ($j = 0; $j < $total_numbers_generated; $j++) {
                $stmt_orders = $this->conn->prepare(
                    "SELECT order_numbers FROM order_list WHERE product_id = ?"
                );
                $stmt_orders->bind_param("i", $id);
                $stmt_orders->execute();
                $orders = $stmt_orders->get_result();
                if ($orders->num_rows > 0) {
                    $order = $orders->fetch_assoc();
                    $order_lucky_numbers = $order["order_numbers"];
                }

                $lucky_numbers = "";

                do {
                    $random_number = str_pad(
                        rand(0, $qty_numbers - 1),
                        strlen($qty_numbers),
                        "0",
                        STR_PAD_LEFT
                    );
                    $random_number = sprintf(
                        "%0" . strlen($qty_numbers) . "d",
                        $random_number
                    );
                } while (isset($used_numbers[$random_number]));

                $used_numbers[$random_number] = true;
                $numeros[] = $random_number;
            }
        }

        if (!empty($numeros)) {
            $resultado["status"] = "success";
            $resultado["numeros"] = $numeros;
            $resultado["nomes"] = $firstnames;
            $resultado["payment_status"] = isset($payment_status)
                ? $payment_status
                : "";
        } else {
            $resultado["status"] = "error";
        }

        echo json_encode($resultado);
    }

    public function search_raffle_winner()
    {
        global $conn;
        $draw_number = trim($conn->real_escape_string($_POST["number"]));
        $raffle = $conn->real_escape_string($_POST["raffle"]);
        $sqlx =
            "\r\n\t\t" .
            "SELECT type_of_draw" .
            "\r\n\t\t" .
            "FROM product_list" .
            "\r\n\t\t" .
            'WHERE id = \'' .
            $raffle .
            '\'' .
            "\r\n\t\t" .
            "LIMIT 1" .
            "\r\n\t\t";
        $resultx = $conn->query($sqlx);
        $type_of_draw = "";
        if ($resultx && 0 < $resultx->num_rows) {
            $row = $resultx->fetch_assoc();
            $type_of_draw = $row["type_of_draw"];
        }

        $bichos = [];

        if ($type_of_draw == 3) {
            $bichos = [
                "00" => "Avestruz",
                "01" => "Águia",
                "02" => "Burro",
                "03" => "Borboleta",
                "04" => "Cachorro",
                "05" => "Cabra",
                "06" => "Carneiro",
                "07" => "Camelo",
                "08" => "Cobra",
                "09" => "Coelho",
                "10" => "Cavalo",
                "11" => "Elefante",
                "12" => "Galo",
                "13" => "Gato",
                "14" => "Jacaré",
                "15" => "Leão",
                "16" => "Macaco",
                "17" => "Porco",
                "18" => "Pavão",
                "19" => "Peru",
                "20" => "Touro",
                "21" => "Tigre",
                "22" => "Urso",
                "23" => "Veado",
                "24" => "Vaca",
            ];
        }

        if ($type_of_draw == 4) {
            $bichos = [
                "00" => "Avestruz M1",
                "01" => "Avestruz M2",
                "02" => "Águia M1",
                "03" => "Águia M2",
                "04" => "Burro M1",
                "05" => "Burro M2",
                "06" => "Borboleta M1",
                "07" => "Borboleta M2",
                "08" => "Cachorro M1",
                "09" => "Cachorro M2",
                "10" => "Cabra M1",
                "11" => "Cabra M2",
                "12" => "Carneiro M1",
                "13" => "Carneiro M2",
                "14" => "Camelo M1",
                "15" => "Camelo M2",
                "16" => "Cobra M1",
                "17" => "Cobra M2",
                "18" => "Coelho M1",
                "19" => "Coelho M2",
                "20" => "Cavalo M1",
                "21" => "Cavalo M2",
                "22" => "Elefante M1",
                "23" => "Elefante M2",
                "24" => "Galo M1",
                "25" => "Galo M2",
                "26" => "Gato M1",
                "27" => "Gato M2",
                "28" => "Jacaré M1",
                "29" => "Jacaré M2",
                "30" => "Leão M1",
                "31" => "Leão M2",
                "32" => "Macaco M1",
                "33" => "Macaco M2",
                "34" => "Porco M1",
                "35" => "Porco M2",
                "36" => "Pavão M1",
                "37" => "Pavão M2",
                "38" => "Peru M1",
                "39" => "Peru M2",
                "40" => "Touro M1",
                "41" => "Touro M2",
                "42" => "Tigre M1",
                "43" => "Tigre M2",
                "44" => "Urso M1",
                "45" => "Urso M2",
                "46" => "Veado M1",
                "47" => "Veado M2",
                "48" => "Vaca M1",
                "49" => "Vaca M2",
            ];
        }

        $draw_number_normalized = $draw_number;
        $bicho = "";

        foreach ($bichos as $key => $value) {
            $normalizedValue = $value;

            if (strcmp($draw_number_normalized, $normalizedValue) === 0) {
                $draw_number = $key;
                $bicho = $value;
                break;
            }
        }

        $sql =
            "\r\n\t\t" .
            "SELECT o.id, c.firstname, c.lastname, c.email, c.phone, o.date_created, o.date_updated, o.status, o.quantity, o.total_amount, o.product_name" .
            "\r\n\t\t" .
            "FROM order_list o" .
            "\r\n\t\t" .
            "INNER JOIN customer_list c ON o.customer_id = c.id" .
            "\r\n\t\t" .
            "INNER JOIN product_list p ON o.product_id = p.id" .
            "\r\n\t\t" .
            'WHERE (o.order_numbers LIKE CONCAT(\'%,\', \'' .
            $draw_number .
            '\', \',%\') ' .
            "\r\n\t\t" .
            'OR o.order_numbers LIKE CONCAT(\'' .
            $draw_number .
            '\', \',%\') ' .
            "\r\n\t\t" .
            'OR o.order_numbers LIKE CONCAT(\'%,\', \'' .
            $draw_number .
            '\')' .
            "\r\n\t\t" .
            'OR o.order_numbers = \'' .
            $draw_number .
            '\')' .
            "\r\n\t\t" .
            'AND o.product_id = \'' .
            $raffle .
            '\'' .
            "\r\n\t\t" .
            "AND o.status = 2" .
            "\r\n\t\t" .
            "LIMIT 1" .
            "\r\n\t";
        $result = $conn->query($sql);
        if ($result && 0 < $result->num_rows) {
            $row = $result->fetch_assoc();
            $pedidoId = $row["id"];
            $firstname = $row["firstname"];
            $lastname = $row["lastname"];
            $email = $row["email"];
            $phone = formatPhoneNumber($row["phone"]);
            $date =
                date("d/m/Y", strtotime($row["date_created"])) .
                " às " .
                date("H:i", strtotime($row["date_created"]));
            $quantity = $row["quantity"];
            $value = number_format(
                $row["total_amount"] ? $row["total_amount"] : 0,
                2,
                ",",
                "."
            );
            $fullname = "" . $firstname . " " . $lastname . "";
            $payment_status = $row["status"];
            $product_name = $row["product_name"];
            $payment_date = date("d/m/Y", strtotime($row["date_updated"])) .
                " às " .
                date("H:i", strtotime($row["date_updated"]));

            if ($payment_status == 1) {
                $payment_status = "Pendente";
            }

            if ($payment_status == 2) {
                $payment_status = "Pago";
            }

            if ($payment_status == 3) {
                $payment_status = "Cancelado";
            }

            if ($bicho) {
                $draw_number = $bicho;
            }

            $resultado["status"] = "success";
            $resultado["pedido"] = $pedidoId;
            $resultado["name"] = $fullname;
            $resultado["phone"] = $phone;
            $resultado["date"] = $date;
            $resultado["quantity"] = $quantity;
            $resultado["value"] = $value;
            $resultado["number"] = $draw_number;
            $resultado["payment_status"] = $payment_status;
            $resultado["product_name"] = $product_name;
            $resultado["type_of_draw"] = $type_of_draw;
            $resultado["payment_date"] = $payment_date;
            echo json_encode($resultado);
            exit();
        } else {
            $resultado["status"] = "failed";
            echo json_encode($resultado);
            exit();
        }
    }

    function save_raffle_winner()
    {
        if (
            !isset($_POST["id"], $_POST["draw_number"], $_POST["draw_winner"])
        ) {
            $resp["status"] = "failed";
            $resp["err"] = "Vencedor não encontrado.";
            echo json_encode($resp);
            return;
        }

        $id = $this->conn->real_escape_string($_POST["id"]);

        $number = $this->conn->real_escape_string($_POST["draw_number"]);
        $draw_number_formatado = preg_replace("/[^0-9]/", "", $number);
        $array_draw_number = [$draw_number_formatado];
        $draw_number = json_encode($array_draw_number);

        $winner = $this->conn->real_escape_string($_POST["draw_winner"]);
        $draw_winner_formatado = preg_replace("/[^0-9]/", "", $winner);
        $array_draw_winner = [$draw_winner_formatado];
        $draw_winner = json_encode($array_draw_winner);

        $sql = "UPDATE `product_list` SET `draw_number`='{$draw_number}', `draw_winner`='{$draw_winner}' WHERE `id` = {$id};";
        $save = $this->conn->query($sql);
        if ($save) {
            $resp["status"] = "success";
            $resp["msg"] = "Vencedor salvo com sucesso.";
        } else {
            $resp["status"] = "failed";
            $resp["err"] = $this->conn->error . "[{$sql}]";
        }

        return json_encode($resp);
    }
    function manage_draw()
    {
        $product_id = $this->conn->real_escape_string($_POST["product_id"]);
        $horainicial = $this->conn->real_escape_string($_POST["horainicial"]);
        $horafinal = $this->conn->real_escape_string($_POST["horafinal"]);
        $valorqt = $this->conn->real_escape_string($_POST["valorqt"]);

        if ($product_id == "") {
            $resp["status"] = "failed";
            $resp["error"] = "Erro ao criar sorteio.";
            return json_encode($resp);
            exit();
        }

        // Consulta SQL para buscar order_numbers onde product_id é igual a $product_id
        $query = "SELECT order_numbers FROM order_list WHERE product_id = '$product_id'";

        // Adiciona a condição para valorqt se estiver disponível
        if (!empty($valorqt)) {
            $query .= " AND quantity >= '$valorqt'";
        }

        // Adiciona as condições para horainicial e horafinal se estiverem disponíveis
        if (!empty($horainicial) && !empty($horafinal)) {
            $query .= " AND date_created BETWEEN '$horainicial' AND '$horafinal'";
        }

        // Executar a consulta
        $result = $this->conn->query($query);
        // Array para armazenar os order_numbers
        $order_numbers = [];

        // Fetch all rows from the result set
        while ($row = $result->fetch_assoc()) {
            // Adicionar cada order_number ao array
            $order_numbers[] = $row["order_numbers"];
        }

        // Agora $order_numbers é um array com todos os order_numbers onde product_id é igual a $product_id
        // Vamos verificar se o array está vazio
        if (empty($order_numbers)) {
            $resp["status"] = "failed";
            $resp["error"] = "Não há números vendidos para este sorteio.";
            return json_encode($resp);
            exit();
        }
        // vamos verificar entradas duplicadas

        $new_array = explode(",", $order_numbers[0]);
        $order_numbers = $new_array;

        // Verificar se o array está vazio
        if (empty($order_numbers)) {
            $resp["status"] = "failed";
            $resp["error"] = "Não há números vendidos para este sorteio.";
            return json_encode($resp);
            exit();
        }
        $result = array_rand($order_numbers, 1);

        $winner = $this->conn->real_escape_string($order_numbers[$result]);

        $sql = "SELECT * FROM `order_list` WHERE FIND_IN_SET('$winner', order_numbers) > 0 AND product_id = '$product_id'";
        $result = $this->conn->query($sql);

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        if (empty($rows)) {
            $resp["status"] = "failed";
            $resp["error"] = "Erro ao buscar vencedor.";
            return json_encode($resp);
            exit();
        }

        $res = $rows[0];
        $res["draw_cota"] = $winner;
        $customer_winner = "SELECT * FROM `customer_list` WHERE id = '{$res["customer_id"]}'";
        $customer = $this->conn->query($customer_winner)->fetch_assoc();
        $res["customer_name"] =
            $customer["firstname"] . " " . $customer["lastname"];
        $res["customer_phone"] = $customer["phone"];
        $res["customer_avatar"] = $customer["avatar"];

        return json_encode($res);
    }
    public function verify_orders_mp()
    {
        extract($_GET);
        $mercadopago_access_token = $this->settings->info(
            "mercadopago_access_token"
        );
        $orders = $this->conn->query(
            "SELECT o.id, o.id_mp" .
                "\r\n\t\t\t" .
                "FROM order_list o WHERE o.status = 3 " .
                "\r\n\t\t\t" .
                'AND o.date_created BETWEEN \'' .
                $start .
                ' 00:00:00\' AND \'' .
                $end .
                ' 23:59:59\'' .
                "\r\n\t\t\t" .
                "AND o.product_id = " .
                $product .
                "\r\n\t\t\t" .
                'AND payment_method = \'MercadoPago\''
        );

        if (0 < $orders->num_rows) {
            echo "Quantidade de pedidos: " . $orders->num_rows . "<hr>";

            while ($row = $orders->fetch_assoc()) {
                $order_id = $row["id"];
                $url =
                    "https://api.mercadopago.com/v1/payments/search?sort=date_created&criteria=desc&external_reference=" .
                    $order_id .
                    "&range=date_created&begin_date=NOW-5DAYS&end_date=NOW";
                $headers = [
                    "Accept: application/json",
                    "Authorization: Bearer " . $mercadopago_access_token,
                ];
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $resposta = curl_exec($ch);
                curl_close($ch);
                $payment_info = json_decode($resposta, true);
                $status = $payment_info["results"][0]["status"];
                echo "Pedido " .
                    $order_id .
                    " está com status: " .
                    $status .
                    " no Mercado Pago<br>";
            }

            echo "<hr>Fim da verificação de pedidos.";
        } else {
            echo "Nenhum pedido a ser verificado.";
        }
    }
   
    public function verify_duplicates()
    {
        extract($_GET);
        $id = 136;

        if ($id) {
            $time_start = microtime(true);
            $orders = $this->conn->query(
                'SELECT o.order_numbers, p.qty_numbers FROM order_list o INNER JOIN product_list p ON o.product_id = p.id WHERE o.status <> 3 AND o.product_id = \'' .
                    $id .
                    '\''
            );
            $cotas_vendidas = [];

            while ($row = $orders->fetch_assoc()) {
                $cotas_vendidas[] = $row["order_numbers"];
            }

            $cotas_vendidas = implode(",", $cotas_vendidas);
            $cotas_vendidas = explode(",", $cotas_vendidas);
            $cotas_vendidas = array_filter($cotas_vendidas);
            $duplicate_numbers = array_diff_assoc(
                $cotas_vendidas,
                array_unique($cotas_vendidas)
            );
            echo "Total: " . count($duplicate_numbers) . "<br>";

            if (empty($duplicate_numbers)) {
                echo "Números duplicados: nenhum<br><hr>";
            } else {
                echo "Números duplicados: " .
                    implode(",", $duplicate_numbers) .
                    "<br><hr>";
            }

            $time_end = microtime(true);
            $execution_time = ($time_end - $time_start) / 60;
            echo "Processo realizado com sucesso.<br>";
            echo "Tempo de duração: " . $execution_time;
        }
    }

    public function correct_duplicates()
    {
        extract($_GET);

        if ($id) {
            $time_start = microtime(true);

            if ($type == 1) {
                $orders = $this->conn->query(
                    'SELECT o.order_numbers, p.qty_numbers FROM order_list o INNER JOIN product_list p ON o.product_id = p.id WHERE o.status <> 3 AND o.product_id = \'' .
                        $id .
                        '\''
                );
                $cotas_vendidas = [];

                while ($row = $orders->fetch_assoc()) {
                    $cotas_vendidas[] = $row["order_numbers"];
                    $qty_numbers = $row["qty_numbers"];
                }
                $cotas_vendidas = implode(",", $cotas_vendidas);
                $cotas_vendidas = explode(",", $cotas_vendidas);
                $cotas_vendidas = array_filter($cotas_vendidas);
                $duplicate_numbers = array_unique(
                    array_diff_assoc(
                        $cotas_vendidas,
                        array_unique($cotas_vendidas)
                    )
                );
                echo "Total: " . count($duplicate_numbers) . "<br>";
                echo "Números duplicados: " .
                    implode(",", $duplicate_numbers) .
                    "<br><hr>";
                $qty_numbers = $qty_numbers - 1;
                $globos = strlen($qty_numbers);
                $numeris = range(0, $qty_numbers);
                $numeris = array_map(function ($item) use ($qty_numbers, $globos) {
                    return str_pad(
                        $item,
                        max((int) $globos, strlen($qty_numbers)),
                        "0",
                        STR_PAD_LEFT
                    );
                }, $numeris);
                $array_without_ja_vendidos = array_filter(
                    array_diff($numeris, $cotas_vendidas)
                );
                shuffle($array_without_ja_vendidos);
                $order_numbers = array_slice(
                    $array_without_ja_vendidos,
                    0,
                    count($duplicate_numbers)
                );

                if (0 < count($duplicate_numbers)) {
                    $count = 0;

                    foreach ($duplicate_numbers as $number) {
                        $find_query = $this->conn->query(
                            "SELECT * FROM order_list WHERE product_id=" .
                                $id .
                                ' AND status <> 3 AND order_numbers REGEXP \'' .
                                $number .
                                '\' ORDER BY id DESC LIMIT 1'
                        );

                        while ($row = $find_query->fetch_assoc()) {
                            $oid = $row["id"];
                            $new_number = $order_numbers[$count];
                            $update = $this->conn->query(
                                'UPDATE order_list SET order_numbers = REPLACE(order_numbers, \'' .
                                    $number .
                                    '\', \'' .
                                    $new_number .
                                    '\') WHERE id = \'' .
                                    $oid .
                                    '\''
                            );
                            ++$count;
                        }
                    }
                }
            } elseif ($type == 2) {
                $orders = $this->conn->query(
                    'SELECT o.order_numbers, p.qty_numbers FROM order_list o INNER JOIN product_list p ON o.product_id = p.id WHERE o.status <> 3 AND o.product_id = \'' .
                        $id .
                        '\''
                );
                $cotas_vendidas = [];

                while ($row = $orders->fetch_assoc()) {
                    $cotas_vendidas[] = $row["order_numbers"];
                    $qty_numbers = $row["qty_numbers"];
                }
                $cotas_vendidas = implode(",", $cotas_vendidas);
                $cotas_vendidas = explode(",", $cotas_vendidas);
                $cotas_vendidas = array_filter($cotas_vendidas);
                $duplicate_numbers = array_unique(
                    array_diff_assoc(
                        $cotas_vendidas,
                        array_unique($cotas_vendidas)
                    )
                );
                echo "Total: " . count($duplicate_numbers) . "<br>";
                echo "Números duplicados: " .
                    implode(",", $duplicate_numbers) .
                    "<br><hr>";
                $qty_numbers = $qty_numbers - 1;
                $globos = strlen($qty_numbers);
                $numeris = range(0, $qty_numbers);
                $numeris = array_map(function ($item) use ($qty_numbers, $globos) {
                    return str_pad(
                        $item,
                        max((int) $globos, strlen($qty_numbers)),
                        "0",
                        STR_PAD_LEFT
                    );
                }, $numeris);
                $array_without_ja_vendidos = array_filter(
                    array_diff($numeris, $cotas_vendidas)
                );
                shuffle($array_without_ja_vendidos);
                $order_numbers = array_slice(
                    $array_without_ja_vendidos,
                    0,
                    count($duplicate_numbers)
                );

                if (0 < count($duplicate_numbers)) {
                    $count = 0;

                    foreach ($duplicate_numbers as $number) {
                        $find_query = $this->conn->query(
                            "SELECT id, order_numbers FROM order_list WHERE product_id=" .
                                $id .
                                ' AND status <> 3 AND order_numbers REGEXP \'' .
                                $number .
                                '\' ORDER BY id DESC LIMIT 1'
                        );

                        while ($row = $find_query->fetch_assoc()) {
                            $oid = $row["id"];
                            $num_pedidos = $row["order_numbers"];
                            $num_pedidos = explode(",", $num_pedidos);
                            $new_duplicate_numbers = array_unique(
                                array_diff_assoc(
                                    $num_pedidos,
                                    array_unique($num_pedidos)
                                )
                            );

                            foreach ($new_duplicate_numbers as $index => $new_number) {
                                $num_pedidos[$index] = $order_numbers[$count];
                            }

                            $novos_numeros = implode(",", $num_pedidos);
                            $update = $this->conn->query(
                                'UPDATE order_list SET order_numbers = \'' .
                                    $novos_numeros .
                                    '\' WHERE id = \'' .
                                    $oid .
                                    '\''
                            );
                            ++$count;
                        }
                    }
                }
            } elseif ($type == 3) {
                $orders = $this->conn->query(
                    'SELECT o.order_numbers, p.qty_numbers FROM order_list o INNER JOIN product_list p ON o.product_id = p.id WHERE o.product_id = \'' .
                        $id .
                        '\''
                );
                $cotas_vendidas = [];

                while ($row = $orders->fetch_assoc()) {
                    $cotas_vendidas[] = $row["order_numbers"];
                    $qty_numbers = $row["qty_numbers"];
                }
                $cotas_vendidas = implode(",", $cotas_vendidas);
                $cotas_vendidas = explode(",", $cotas_vendidas);
                $cotas_vendidas = array_filter($cotas_vendidas);
                $duplicate_numbers = array_filter(
                    array_unique(
                        array_diff_assoc($cotas_vendidas, $cotas_vendidas)
                    )
                );
                echo "Total: " . count($duplicate_numbers) . "<br>";
                echo "Números duplicados: " .
                    implode(",", $duplicate_numbers) .
                    "<br><hr>";
                $numeros_ja_vendidos = array_filter($cotas_vendidas);
                $qty_numbers = $qty_numbers - 1;
                $globos = strlen($qty_numbers);
                $numeris = range(0, $qty_numbers);
                $numeris = array_map(function ($item) use ($qty_numbers, $globos) {
                    return str_pad(
                        $item,
                        max((int) $globos, strlen($qty_numbers)),
                        "0",
                        STR_PAD_LEFT
                    );
                }, $numeris);
                $array_without_ja_vendidos = array_filter(
                    array_diff($numeris, $numeros_ja_vendidos)
                );
                shuffle($array_without_ja_vendidos);
                $find_query = $this->conn->query(
                    "SELECT id FROM order_list WHERE product_id=" .
                        $id .
                        ' AND order_numbers REGEXP \'Array\''
                );
                $orders = [];

                while ($row = $find_query->fetch_assoc()) {
                    $orders[] = $row["id"];
                }

                for ($i = 0; $i < count($orders); ++$i) {
                    $new_number = array_slice(
                        $array_without_ja_vendidos,
                        $i,
                        1
                    );
                    $oid = $orders[$i];
                    $update = $this->conn->query(
                        'UPDATE order_list SET order_numbers = REPLACE(order_numbers, \'Array\', \'' .
                            $new_number[0] .
                            '\') WHERE id = \'' .
                            $oid .
                            '\''
                    );
                }
            }

            $time_end = microtime(true);
            $execution_time = ($time_end - $time_start) / 60;
            echo "Processo realizado com sucesso.<br>";
            echo "Tempo de duração: " . $execution_time;
        }
    }

    public function correct_array()
    {
        extract($_POST);

        if ($pid) {
            $orders = $this->conn->query(
                'SELECT o.order_numbers, p.qty_numbers FROM order_list o INNER JOIN product_list p ON o.product_id = p.id WHERE o.product_id = \'' .
                    $pid .
                    '\''
            );
            $cotas_vendidas = [];

            while ($row = $orders->fetch_assoc()) {
                $cotas_vendidas[] = $row["order_numbers"];
                $qty_numbers = $row["qty_numbers"];
            }
            $cotas_vendidas = implode(",", $cotas_vendidas);
            $cotas_vendidas = explode(",", $cotas_vendidas);
            $cotas_vendidas = array_filter($cotas_vendidas);
            $numeros_ja_vendidos = array_filter($cotas_vendidas);
            $qty_numbers = $qty_numbers - 1;
            $globos = strlen($qty_numbers);
            $numeris = range(0, $qty_numbers);
            $numeris = array_map(function ($item) use ($qty_numbers, $globos) {
                return str_pad(
                    $item,
                    max((int) $globos, strlen($qty_numbers)),
                    "0",
                    STR_PAD_LEFT
                );
            }, $numeris);
            $array_without_ja_vendidos = array_filter(
                array_diff($numeris, $numeros_ja_vendidos)
            );
            shuffle($array_without_ja_vendidos);
            $find_query = $this->conn->query(
                'SELECT order_numbers FROM order_list WHERE product_id = \'' .
                    $pid .
                    '\' AND id = \'' .
                    $oid .
                    '\' AND order_numbers REGEXP \'Array\''
            );
            $numbers = [];

            while ($row = $find_query->fetch_assoc()) {
                $numbers[] = $row["order_numbers"];
            }

            $numbers = implode(",", $numbers);
            $numbers = explode(",", $numbers);
            $numbers = array_filter($numbers);
            $count = 0;

            foreach ($numbers as $number) {
                if ($number == "Array") {
                    $new_number = array_slice(
                        $array_without_ja_vendidos,
                        $count,
                        1
                    );
                    $update = $this->conn->query(
                        'UPDATE order_list SET order_numbers = REPLACE(order_numbers, \'Array\', \'' .
                            $new_number[0] .
                            '\') WHERE id = \'' .
                            $oid .
                            '\''
                    );
                }

                ++$count;
            }

            $resp["status"] = "success";
            return json_encode($resp);
        }
    }

    public function search_raffle_smallest_and_largest_number()
    {

        $product_id = $this->conn->real_escape_string($_POST["raffle"]);
        $start_date = isset($_POST["start_date"]) ? $_POST["start_date"] : null;
        $end_date = isset($_POST["end_date"]) ? $_POST["end_date"] : null;


        // $resp["error"] = $start_date;
        // return json_encode($resp);
        $where = ' product_id = ' . $product_id;
        $where .= ' AND status = "2"';

        // Verificando se start_date foi enviado e se é válido
        if ($start_date) {
            $start_date = (new DateTime($start_date))->format('Y-m-d H:i:s');
            $where .= ' AND date_created >= "' . $start_date . '"';
        }

        // Verificando se end_date foi enviado e se é válido
        if ($end_date) {
            $end_date = (new DateTime($end_date))->format('Y-m-d H:i:s');
            $where .= ' AND date_created <= "' . $end_date . '"';
        }

        if (empty($product_id)) {
            $resp["status"] = "failed";
            $resp["error"] = "Selecione um sorteio.";
            return json_encode($resp);
            exit();
        }

        $sql = "
        SELECT 
            order_numbers
        FROM 
            order_list
        WHERE   
            $where";

        $result = $this->conn->query($sql);
        if ($result->num_rows == 0) {
            $resp["status"] = "failed";
            $resp["error"] = "Nenhum número encontrado.";
            return json_encode($resp);
            exit();
        }

        $order_numbers = [];

        while ($row = $result->fetch_assoc()) {
            $order_numbers = array_merge($order_numbers, explode(",", $row["order_numbers"]));
        }

        $order_numbers = array_filter($order_numbers);

        if (empty($order_numbers)) {
            $resp["status"] = "failed";
            $resp["error"] = "Nenhum número encontrado.";
            return json_encode($resp);
            exit();
        }

        $major_cota = max($order_numbers);
        $minor_cota = min($order_numbers);

        $resp = [];

        foreach (['major' => $major_cota, 'minor' => $minor_cota] as $key => $cota) {
            $where = ' product_id = ' . $product_id;
            $where .= ' AND order_numbers LIKE "%' . $cota . '%"';
            $where .= ' AND status = "2"';


            $sql = "
        SELECT 
            o.date_created, 
            o.status, 
            c.firstname, 
            c.lastname, 
            c.phone, 
            o.date_updated 
        FROM 
            order_list AS o
        INNER JOIN 
            customer_list AS c 
        ON 
            o.customer_id = c.id 
        WHERE 
            $where
        LIMIT 1"; // Usar LIMIT 1 para garantir que estamos obtendo apenas um resultado

            $result = $this->conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Construa os dados para maior e menor cota
                $resp[$key] = [
                    "cota" => $key == "major" ? $major_cota : $minor_cota,
                    "name" => $row["firstname"] . " " . $row["lastname"],
                    "phone" => $row["phone"],
                    "date" => date("d/m/Y", strtotime($row["date_created"])) . " às " . date("H:i", strtotime($row["date_created"])),
                    "date_updated" => date("d/m/Y", strtotime($row["date_updated"])) . " às " . date("H:i", strtotime($row["date_updated"])),
                    "payment_status" => $row["status"] == 1 ? "Pendente" : "Pago"
                ];
            } else {
                $resp[$key] = [
                    "status" => "failed",
                    "error" => "Nenhum dado encontrado para o número $cota."
                ];
            }
        }

        $resp['status'] = 'success';

        return json_encode($resp);
    }
    public function search_raffle_smallest_and_largest_number_today()
    {
        $product_id = $this->conn->real_escape_string($_POST["raffle"]);
        $start_date = isset($_POST["data_ini"]) ? $_POST["data_ini"] : null;
        $end_date = isset($_POST["data_fim"]) ? $_POST["data_fim"] : null;

        $current_date = date('Y-m-d');
        // Verificando se start_date foi enviado e se é válido


        $where = ' product_id = ' . $product_id;
        if (empty($product_id)) {
            $resp["status"] = "failed";
            $resp["error"] = "Selecione um sorteio.";
            return json_encode($resp);
            exit();
        }


        if ($start_date && $end_date) {
            $where .= ' AND date_created >= "' . $start_date . '"';
            $where .= ' AND date_created <= "' . $end_date . '"';
        } else {
            $where .= ' AND DATE(o.date_created) = "' . $current_date . '"';
        }

        // Filtrar pelo dia atual


        $where .= ' AND status = "2"';

        $sql = "
        SELECT 
            order_numbers
        FROM 
            order_list AS o
        WHERE 
            $where";

        $result = $this->conn->query($sql);
        if ($result->num_rows == 0) {
            $resp["status"] = "failed";
            $resp["error"] = "Nenhum número encontrado.";
            return json_encode($resp);
            exit();
        }

        $order_numbers = [];

        while ($row = $result->fetch_assoc()) {
            $order_numbers = array_merge($order_numbers, explode(",", $row["order_numbers"]));
        }

        $order_numbers = array_filter($order_numbers);

        if (empty($order_numbers)) {
            $resp["status"] = "failed";
            $resp["error"] = "Nenhum número encontrado.";
            return json_encode($resp);
            exit();
        }

        $major_cota = max($order_numbers);
        $minor_cota = min($order_numbers);

        $resp = [];

        foreach (['major' => $major_cota, 'minor' => $minor_cota] as $key => $cota) {

            $where = ' product_id = ' . $product_id;
            $where .= ' AND order_numbers LIKE "%' . $cota . '%"';
            $where .= ' AND status = "2"';


            $sql = "
            SELECT 
                o.date_created, 
                o.status, 
                c.firstname, 
                c.lastname, 
                c.phone, 
                o.date_updated 
            FROM 
                order_list AS o
            INNER JOIN 
                customer_list AS c 
            ON 
                o.customer_id = c.id 
            WHERE 
                $where
            LIMIT 1"; // Usar LIMIT 1 para garantir que estamos obtendo apenas um resultado

            $result = $this->conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                $resp[$key] = [
                    "cota" => $key == "major" ? $major_cota : $minor_cota,
                    "name" => $row["firstname"] . " " . $row["lastname"],
                    "phone" => $row["phone"],
                    "date" => date("d/m/Y", strtotime($row["date_created"])) . " às " . date("H:i", strtotime($row["date_created"])),
                    "date_updated" => date("d/m/Y", strtotime($row["date_updated"])) . " às " . date("H:i", strtotime($row["date_updated"])),
                    "payment_status" => $row["status"] == 1 ? "Pendente" : "Pago"
                ];
            } else {
                $resp[$key] = [
                    "status" => "failed",
                    "error" => "Nenhum dado encontrado para o número $cota."
                ];
            }
        }

        $resp['status'] = 'success';

        return json_encode($resp);
    }

    public function load_cotas()
    {
        global $_settings;
        $this->settings = $_settings;
      
        $conn = $_settings->conn;

        $id = $_POST['product_id']; // Assumindo que o ID do produto é passado via GET
        $prod = $conn->query("SELECT roleta, box FROM product_list WHERE id = $id ");
        $produto = $prod->fetch_assoc();

        $cotas_premiadas = $_POST['cotas_premiadas']; // Exemplo de cotas premiadas
        $cotas_vendidas = [];
        $cotas_array = $_POST['cotas_array'];
        $quantidade_auto_cota = $_POST['quantidade_auto_cota'];
        $deserialized = [];
        $pairs = explode(',', $cotas_array);

        foreach ($pairs as $pair) {
            // Split the pair by the first colon to get the key
            $first_split = explode(':', $pair, 2);
            $key = $first_split[0];
            $rest = $first_split[1];

            // Split the rest by the last colon to separate value and status
            $last_colon_pos = strrpos($rest, ':');
            $value = substr($rest, 0, $last_colon_pos);
            $tipo = substr($rest, $last_colon_pos + 1);

            // Create the deserialized array with value=value
            $deserialized[$key] = "$value";
            // Add the tipo as another key-value pair
            $deserialized[$key . '_tipo'] = $tipo;
        }

        $cotas_array = $deserialized;

        $cotas_premiadas_array = explode(',', $cotas_premiadas);
        foreach ($cotas_premiadas_array as $num) {
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
        $all_lucky_numbers = array_column($cotas_vendidas, 'cota');
        $cotas_premiadas_all = $cotas_premiadas_array;
        $cotas_premiadas_sold = array_intersect($all_lucky_numbers, $cotas_premiadas_all);

        $cotas_premiadas_available = array_diff($cotas_premiadas_all, $cotas_premiadas_sold);
if ($min_cotas_purchased > 0) {
            $cotas_premiadas_available = $cotas_premiadas_all;
            $cotas_premiadas_sold = [];
        }
        ob_start();

        if ($cotas_premiadas_sold) {
            foreach ($cotas_premiadas_sold as $cota) {
                $prize = $cotas_array[$cota];
                $tipo = ucfirst($cotas_array[$cota . '_tipo']);

                $winner = $cotas_vendidas[array_search($cota, $all_lucky_numbers)]['winner'];
                $customer = $conn->query("SELECT * FROM customer_list WHERE id = $winner")->fetch_assoc();
                $lastname = $customer['lastname'];
                $lastname_masked = substr($lastname, 0, 3) . '***';
                $customer_name = $customer['firstname'] . ' ' . $lastname_masked;



                $minor = $tipo == 'Menor' ? true : false;
                $major = $tipo == 'Maior' ? true : false;
                if ($cota != '' && !$minor && !$major) {
                    $length = strlen($cota);
                    echo '<div class=" sc-3f9a15f1-7 reservada p-1" style="background: linear-gradient(90deg, #000, #414141) !important; border: 1px solid #414141; border-radius: 10px; margin-bottom: 6px; ">';
 
                    echo '   <div style="justify-content:space-between ;display:flex; align-items:center ; ">';
                    echo '        <div style="display:flex; align-items:center ;   justify-content: space-between; width: 100%; padding: 0 4px 0 0;">';
                    echo '            <span  class="wd-' . $length . ' new_gradient_anime --md ' . $tipo . ' btn btn-sm btn-light text-dark" style="min-width:100px !important; border-radius: 6px; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 256 256" class="w-3 lg:w-4 h-3 lg:h-4">
                <path d="M232,108a12,12,0,0,0,12-12V64a20,20,0,0,0-20-20H32A20,20,0,0,0,12,64V96a12,12,0,0,0,12,12,20,20,0,0,1,0,40,12,12,0,0,0-12,12v32a20,20,0,0,0,20,20H224a20,20,0,0,0,20-20V160a12,12,0,0,0-12-12,20,20,0,0,1,0-40ZM36,170.34a44,44,0,0,0,0-84.68V68H88V188H36Zm184,0V188H112V68H220V85.66a44,44,0,0,0,0,84.68Z"></path>
            </svg>&nbsp;' . $cota . '</span>';
                    echo '          <span class="prize" style=" font-weight:500; margin-right:8px;color:white ">' . $prize . '</span>';
                    echo '         <span style="text-wrap:nowrap; font-size:14px;font-weight:600; color:white">' . $customer_name . ' 🏆</span>';
                    echo '       </div>';
                    echo '    </div>';

                    echo '   </div>';
                }
            }
        }
        if ($cotas_premiadas_available) {
            $count = 0;
            foreach ($cotas_premiadas_available as $cota) {
                $prize = $cotas_array[$cota];
                $prize = explode('=', $prize)[0];

                $count++;

                $tipo = ucfirst($cotas_array[$cota . '_tipo']);
                $minor = $tipo == 'Menor' ? true : false;
                $major = $tipo == 'Maior' ? true : false;
                if ($cota != '' && !$minor && !$major) {
                    $length = strlen($cota);
                    echo '<div class=" sc-3f9a15f1-7 bg-dark disponivel p-1" style="background-color: #ffffff !important; border: 1px solid #cdd0d5; border-radius: 10px;margin-bottom: 6px;">';

                    echo '   <div style="justify-content:space-between ;display:flex; align-items:center ; ">';
                    echo '        <div style="display:flex; align-items:center  ;   justify-content: space-between; width: 100%; padding: 0 4px 0 0;">';
                    echo '      <span class="wd-' . $length . ' new_gradient_anime --md ' . $tipo . ' btn btn-sm btn-light text-dark" style="min-width:100px !important; background-color: #6c757d !important; color: #ffffff !important; border-radius: 6px; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px;">' . $cota . '</span>';
                    echo '          <span class="prize" style="font-family: Montserrat, \'Public Sans\', sans-serif; margin-right:8px; color:#000000 !important;">' . $prize . '</span>';
                    echo '         <div style="text-wrap:nowrap; font-size:14px;font-weight:600; color:#fff"><span style="color: #1ebc1e;">● </span><span style="color: #414141;">Disponível</span></div>';
                    echo '       </div>';
                    echo '    </div>';

                    echo '   </div>';
                }
            }
        }
        return ob_get_clean();
    }
     public function load_cotas_roleta()
    {
        global $_settings;
        $this->settings = $_settings;

        $theme = $_settings->info('theme');
        $bgTheme = "";
        $textTheme = "";
        if ($theme == 1) {
            $bgTheme = "bg-white";
            $btnTheme = "btn-warning";
            $textTheme = "text-dark";
        } else if ($theme == 2) {
            $bgTheme = "bg-dark";
            $btnTheme = "btn-warning";
            $textTheme = "text-light";
        } else if ($theme == 3) {
            $bgTheme = "bg-secondary";
            $btnTheme = "btn-warning";
            $textTheme = "text-light";
        } else if ($theme == 4) {
            $bgTheme = "bg-primary";
            $btnTheme = "btn-warning";
            $textTheme = "text-light";
        } else if ($theme == 5) {
            $bgTheme = "bg-dark";
            $btnTheme = "btn-warning";
            $textTheme = "text-light";
        }

        $conn = $_settings->conn;

        $id = $_POST['product_id']; // Assumindo que o ID do produto é passado via GET
        $prod = $conn->query("SELECT roleta, box FROM product_list WHERE id = $id ");
        $produto = $prod->fetch_assoc();

        $cotas_premiadas = $_POST['cotas_premiadas']; // Exemplo de cotas premiadas
        $cotas_vendidas = [];
        $cotas_array = $_POST['cotas_array'];
        $quantidade_auto_cota = $_POST['quantidade_auto_cota'];
        $deserialized = [];
        $pairs = explode(',', $cotas_array);

        foreach ($pairs as $pair) {
            // Split the pair by the first colon to get the key
            $first_split = explode(':', $pair, 2);
            $key = $first_split[0];
            $rest = $first_split[1];

            // Split the rest by the last colon to separate value and status
            $last_colon_pos = strrpos($rest, ':');
            $value = substr($rest, 0, $last_colon_pos);
            $tipo = substr($rest, $last_colon_pos + 1);

            // Create the deserialized array with value=value
            $deserialized[$key] = "$value";
            // Add the tipo as another key-value pair
            $deserialized[$key . '_tipo'] = $tipo;
        }

        $cotas_array = $deserialized;

        $cotas_premiadas_array = explode(',', $cotas_premiadas);
        foreach ($cotas_premiadas_array as $num) {
            if (empty($num)) {
                continue;
            }

            $stmt = $conn->prepare('SELECT customer_id FROM order_list WHERE FIND_IN_SET(?, order_numbers) AND product_id = ? AND status = 2');
            $stmt->bind_param('si', $num, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows > 0) {
                $cotas_vendidas[] = ['cota' => $num, 'winner' => $row['customer_id']];
            }
        }
        $all_lucky_numbers = array_column($cotas_vendidas, 'cota');
        $cotas_premiadas_all = $cotas_premiadas_array;
        $cotas_premiadas_sold = array_intersect($all_lucky_numbers, $cotas_premiadas_all);

        $cotas_premiadas_available = array_diff($cotas_premiadas_all, $cotas_premiadas_sold);
        if ($min_cotas_purchased > 0) {
            $cotas_premiadas_available = $cotas_premiadas_all;
            $cotas_premiadas_sold = [];
        }
        ob_start();

        if ($cotas_premiadas_sold) {
            foreach ($cotas_premiadas_sold as $cota) {
                $prize = $cotas_array[$cota];
                $tipo = ucfirst($cotas_array[$cota . '_tipo']);

                $winner = $cotas_vendidas[array_search($cota, $all_lucky_numbers)]['winner'];
                $customer = $conn->query("SELECT * FROM customer_list WHERE id = $winner")->fetch_assoc();
                $lastname = $customer['lastname'];
                $lastname_masked = substr($lastname, 0, 3) . '***';
                $customer_name = $customer['firstname'] . ' ' . $lastname_masked;




                $minor = $tipo == 'Menor' ? true : false;
                $major = $tipo == 'Maior' ? true : false;
                if ($cota != '' && !$minor && !$major) {

                    if ($produto['roleta'] || $produto['box']) {
                        echo '    <div class="app-titulos-premiados--item bg-dark app-titulos-premiados--selected" style="background: linear-gradient(90deg, #122f40, #255775) !important; border: 1px solid #22516d; border-radius: 10px; margin-bottom: 6px; ">';
                        echo '        <div class="wd-6 new_gradient_anime --md Premiada btn btn-sm btn-light text-dark"  style="min-width:100px !important; background-color: #ffffff !important; color: #000000 !important; border-radius: 6px; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px;">' . $prize . '</div>';
                         echo '         <span style="text-wrap:nowrap; font-size:14px;font-weight:600; color:white">' . $customer_name . ' 🏆</span>';
                        echo '        </div>';
                        echo '    </div>';
                    } else {


                        echo '    <div class=" sc-3f9a15f1-7 reservada p-1" style="style="background: linear-gradient(90deg, #000, #414141) !important; border: 1px solid #000000; border-radius: 10px; margin-bottom: 6px; ">';
                        echo '        <div class="wd-6 new_gradient_anime --md Premiada btn btn-sm btn-light text-dark" style="min-width:75px !important; border-radius: 6px; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px">' . $prize . '</div>';
                        echo '        <div class="app-titulos-premiados--ganhador"><span>' . $customer_name . '';
                        echo '        <i class="bi bi-trophy-fill text-warning"></i></span></div>';
                        echo '    </div>';
                    }
                }
            }
        }
        if ($cotas_premiadas_available) {
            $count = 0;
            foreach ($cotas_premiadas_available as $cota) {
                $prize = $cotas_array[$cota];
                $prize = explode('=', $prize)[0];

                $count++;

                $tipo = ucfirst($cotas_array[$cota . '_tipo']);
                $minor = $tipo == 'Menor' ? true : false;
                $major = $tipo == 'Maior' ? true : false;
                if ($cota != '' && !$minor && !$major) {

                    if ($produto['roleta'] || $produto['box']) {
                        
                        echo '    <div class="app-titulos-premiados--item bg-dark app-titulos-premiados--selected" style="background-color: #ffffff !important; border: 1px solid #ced4da; border-radius: 10px;margin-bottom: 6px;">';
                        echo '        <div class="wd-6 new_gradient_anime --md Premiada btn btn-sm btn-light text-dark" style="min-width:100px !important; background-color: #6c757d !important; color: #ffffff !important; border-radius: 6px; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px;">' . $prize . '</div>';
                        echo '        <div style="text-wrap:nowrap; font-size:14px;font-weight:600; color:#fff"><span style="color: #1ebc1e;">● </span><span style="color: #414141;">Disponível</span></div>';
                        echo '    </div>';
                    } else {

                        echo '    <div class="app-titulos-premiados--item bg-dark p-2">';
                        echo '        <div class="wd-6 new_gradient_anime --md Premiada btn btn-sm btn-light text-dark" style="min-width:75px !important; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px">' . $prize . '</div>';
                        echo '        <div style="text-wrap:nowrap; font-size:14px; font-weight:600; color:#fff"><span style="color: #1ebc1e;">● </span><span style="color: #414141;">Disponível</span></div>';
                        echo '    </div>';
                    }
                }
            }
        }
        return ob_get_clean();
    }

    public function load_cotas_box()
    {
        global $_settings;
        $this->settings = $_settings;

        $theme = $_settings->info('theme');
        $bgTheme = "";
        $textTheme = "";
        if ($theme == 1) {
            $bgTheme = "bg-white";
            $btnTheme = "btn-warning";
            $textTheme = "text-dark";
        } else if ($theme == 2) {
            $bgTheme = "bg-dark";
            $btnTheme = "btn-warning";
            $textTheme = "text-light";
        } else if ($theme == 3) {
            $bgTheme = "bg-secondary";
            $btnTheme = "btn-warning";
            $textTheme = "text-light";
        } else if ($theme == 4) {
            $bgTheme = "bg-primary";
            $btnTheme = "btn-warning";
            $textTheme = "text-light";
        } else if ($theme == 5) {
            $bgTheme = "bg-dark";
            $btnTheme = "btn-warning";
            $textTheme = "text-light";
        }

        $conn = $_settings->conn;

        $id = $_POST['product_id']; // Assumindo que o ID do produto é passado via GET
        $prod = $conn->query("SELECT roleta, box FROM product_list WHERE id = $id ");
        $produto = $prod->fetch_assoc();

        $cotas_premiadas = $_POST['cotas_premiadas']; // Exemplo de cotas premiadas
        $cotas_vendidas = [];
        $cotas_array = $_POST['cotas_array'];
        $quantidade_auto_cota = $_POST['quantidade_auto_cota'];
        $deserialized = [];
        $pairs = explode(',', $cotas_array);

        foreach ($pairs as $pair) {
            // Split the pair by the first colon to get the key
            $first_split = explode(':', $pair, 2);
            $key = $first_split[0];
            $rest = $first_split[1];

            // Split the rest by the last colon to separate value and status
            $last_colon_pos = strrpos($rest, ':');
            $value = substr($rest, 0, $last_colon_pos);
            $tipo = substr($rest, $last_colon_pos + 1);

            // Create the deserialized array with value=value
            $deserialized[$key] = "$value";
            // Add the tipo as another key-value pair
            $deserialized[$key . '_tipo'] = $tipo;
        }

        $cotas_array = $deserialized;

        $cotas_premiadas_array = explode(',', $cotas_premiadas);
        foreach ($cotas_premiadas_array as $num) {
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
        $all_lucky_numbers = array_column($cotas_vendidas, 'cota');
        $cotas_premiadas_all = $cotas_premiadas_array;
        $cotas_premiadas_sold = array_intersect($all_lucky_numbers, $cotas_premiadas_all);

        $cotas_premiadas_available = array_diff($cotas_premiadas_all, $cotas_premiadas_sold);
        if ($min_cotas_purchased > 0) {
            $cotas_premiadas_available = $cotas_premiadas_all;
            $cotas_premiadas_sold = [];
        }
        ob_start();

        if ($cotas_premiadas_sold) {
            foreach ($cotas_premiadas_sold as $cota) {
                $prize = $cotas_array[$cota];
                $tipo = ucfirst($cotas_array[$cota . '_tipo']);

                $winner = $cotas_vendidas[array_search($cota, $all_lucky_numbers)]['winner'];
                $customer = $conn->query("SELECT * FROM customer_list WHERE id = $winner")->fetch_assoc();
                $lastname = $customer['lastname'];
                $lastname_masked = substr($lastname, 0, 3) . '***';
                $customer_name = $customer['firstname'] . ' ' . $lastname_masked;

                

                $minor = $tipo == 'Menor' ? true : false;
                $major = $tipo == 'Maior' ? true : false;
                if ($cota != '' && !$minor && !$major) {

                    if ($produto['roleta'] || $produto['box']) {
                        echo '    <div class="app-titulos-premiados--item bg-dark app-titulos-premiados--selected" style="background: linear-gradient(90deg, #1d8c57, #42bf71) !important; border: 1px solid #35ac68; border-radius: 10px; margin-bottom: 6px; ">';
                        echo '        <div class="wd-6 new_gradient_anime --md Premiada btn btn-sm btn-light text-dark" style="min-width:100px !important; border-radius: 6px; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px"><img src="/uploads/caixa-premiada-aberta.png" style="width: 25%;">' . $prize . '</div>';
                        echo '         <span style="text-wrap:nowrap; font-size:14px;font-weight:600; color:white">' . $customer_name . ' 🏆</span>';
                        echo '        </div>';
                        echo '    </div>';
                    } else {


                        echo '    <div class="app-titulos-premiados--item bg-success app-titulos-premiados--selected">';
                        echo '        <div class="wd-6 new_gradient_anime --md Premiada btn btn-sm btn-light text-dark" style="min-width:100px !important; border-radius: 6px; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px">' . $prize . '</div>';
                        echo '        <div class="app-titulos-premiados--ganhador"><span>' . $customer_name . '';
                        echo '        <i class="bi bi-trophy-fill text-warning"></i></span></div>';
                        echo '    </div>';
                    }
                }
            }
        }
        if ($cotas_premiadas_available) {
            $count = 0;
            foreach ($cotas_premiadas_available as $cota) {
                $prize = $cotas_array[$cota];
                $prize = explode('=', $prize)[0];

                $count++;

                $tipo = ucfirst($cotas_array[$cota . '_tipo']);
                $minor = $tipo == 'Menor' ? true : false;
                $major = $tipo == 'Maior' ? true : false;
                if ($cota != '' && !$minor && !$major) {

                    if ($produto['roleta'] || $produto['box']) {

                        echo '    <div class="app-titulos-premiados--item bg-dark p-2" style="background-color: #ffffff !important; border: 1px solid #ced4da; border-radius: 10px;margin-bottom: 6px;">';
                        echo '        <div class="wd-6 new_gradient_anime --md Premiada btn btn-sm btn-light text-dark" style="min-width:100px !important; background-color: #6c757d !important; color: #ffffff !important; border-radius: 6px; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px;"><img src="/uploads/caixa-premiada.png" style="width: 20%;"> ' . $prize . '</div>';
                        echo '        <div style="text-wrap:nowrap; font-size:14px;font-weight:600; color:#fff"><span style="color: #1ebc1e;">● </span><span style="color: #414141;">Disponível</span></div>';
                        echo '    </div>';
                    } else {

                        echo '    <div class="app-titulos-premiados--item bg-dark p-2">';
                        echo '        <div class="wd-6 new_gradient_anime --md Premiada btn btn-sm btn-light text-dark" style="min-width:100px !important; border-radius: 6px; font-family: Montserrat, "Public Sans", sans-serif; margin-right:4px; font-size:14px">' . $prize . '</div>';
                        echo '        <div style="text-wrap:nowrap; font-size:14px;font-weight:600; color:#fff"><span style="color: #1ebc1e;">● </span><span style="color: #414141;">Disponível</span></div>';
                        echo '    </div>';
                    }
                }
            }
        }
        return ob_get_clean();
    }
    public function att_roleta()
    {
        
        $resp = [];

        extract($_POST);

        $roletaserach = $this->conn->query("SELECT roleta, roleta_aberta FROM `order_list` where order_token = '$order_token'");
        $roletaserach = $roletaserach->fetch_assoc();


        $roletaserach['roleta'] -= 1;
        $roletaserach['roleta_aberta'] += 1;

        $qry = $this->conn->query('UPDATE order_list SET roleta = \'' . $roletaserach['roleta'] . '\', roleta_aberta = \'' . $roletaserach['roleta_aberta'] . '\' WHERE order_token = \'' . $order_token . '\'');

        if ($qry) {
            $resp['upRoleta'] = true;
        } else {
            $resp['upRoleta'] = false;
        }

        return json_encode($resp);
    }
    public function att_box()
    {
        $resp = [];

        extract($_POST);

        $boxserach = $this->conn->query("SELECT box, box_aberta FROM `order_list` where order_token = '$order_token'");
        $boxserach = $boxserach->fetch_assoc();


        $boxserach['box'] -= 1;
        $boxserach['box_aberta'] += 1;

        $qry = $this->conn->query('UPDATE order_list SET box = \'' . $boxserach['box'] . '\', box_aberta = \'' . $boxserach['box_aberta'] . '\' WHERE order_token = \'' . $order_token . '\'');

        if ($qry) {
            $resp['upBox'] = true;
        } else {
            $resp['upBox'] = false;
        }

        return json_encode($resp);
    }
     public function buscar_hora_premiada()
    {
        // Obtendo os parâmetros do POST
        $product_id = $this->conn->real_escape_string($_POST["raffle"]);
        $start_date = isset($_POST["start_date"]) ? $_POST["start_date"] : null;
        $end_date = isset($_POST["end_date"]) ? $_POST["end_date"] : null;
        $valorMinimo = $this->conn->real_escape_string($_POST["valor_minimo"]);

        // Verificando se o product_id foi enviado
        if (empty($product_id)) {
            $resp["status"] = "failed";
            $resp["error"] = "Selecione um sorteio.";
            return json_encode($resp);
            exit();
        }

        // Inicializando o filtro WHERE com o product_id e status
        $where = "o.product_id = '$product_id' AND o.status = '2'";

        // Verificando se start_date foi enviado e se é válido
        if ($start_date) {
            $start_date = (new DateTime($start_date))->format('Y-m-d H:i:s');
            $where .= " AND o.date_created >= '$start_date'";
        }

        // Verificando se end_date foi enviado e se é válido
        if ($end_date) {
            $end_date = (new DateTime($end_date))->format('Y-m-d H:i:s');
            $where .= " AND o.date_created <= '$end_date'";
        }

        // Verificando se valorMinimo foi enviado
        if ($valorMinimo) {
            $valorMinimo = str_replace(",", ".", $valorMinimo);
            $where .= " AND o.total_amount >= '$valorMinimo'";
        }

        // Consulta SQL para buscar um registro aleatório de order_list com os filtros aplicados
        $sql = "
        SELECT 
            o.order_numbers, 
            o.date_created, 
            o.status, 
            c.firstname, 
            c.lastname, 
            c.phone, 
            o.total_amount, 
            o.date_updated 
        FROM 
            order_list AS o
        INNER JOIN 
            customer_list AS c 
        ON 
            o.customer_id = c.id 
        WHERE 
            $where
        ORDER BY RAND() 
        LIMIT 1"; // LIMIT 1 garante que apenas um resultado seja retornado

        // Executando a consulta
        $result = $this->conn->query($sql);

        // Verificando se há algum resultado
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Separando os números por vírgula e pegando um número aleatório
            $order_numbers = explode(",", $row["order_numbers"]);
            $random_number = $order_numbers[array_rand($order_numbers)];

            // Montando a resposta
            $resp = [
                "status" => "success",
                "cota" => $random_number, // Agora, "cota" é um número aleatório extraído da lista
                "name" => $row["firstname"] . " " . $row["lastname"],
                "phone" => $row["phone"],
                "total_amount" => $row["total_amount"],
                "date" => date("d/m/Y", strtotime($row["date_created"])) . " às " . date("H:i", strtotime($row["date_created"])),
                "date_updated" => date("d/m/Y", strtotime($row["date_updated"])) . " às " . date("H:i", strtotime($row["date_updated"])),
                "payment_status" => $row["status"] == 1 ? "Pendente" : "Pago"
            ];
        } else {
            $resp["status"] = "failed";
            $resp["error"] = "Nenhum número encontrado.";
        }

        // Retornando a resposta
        return json_encode($resp);
    }

    public function generate_pdf()
    {
        $id = $_GET['id'];




        // Cria uma instância do Dompdf
        $dompdf = new Dompdf();

        // Consulta para buscar os dados do pedido específico
        $qry = "SELECT o.*, c.firstname, c.lastname, c.phone FROM order_list o
    INNER JOIN customer_list c ON o.customer_id = c.id
    WHERE o.id = '$id'";
        $result = $this->conn->query($qry);
        $row = $result->fetch_assoc();



        if ($row) {
            // Início do conteúdo HTML
            $html = '<h2>Comprovante de compra</h2>';

            $html .= '<span>' . $row['id'] . '</span>';
            $html .= '<span>' . $row['date_created'] . '</span>';
            $html .= '<span>' . $row['product_name'] . '</span>';
            $html .= '<span>' . $row['firstname'] . $row['lastname'] . '</span>';
            $html .= '<span>' . $row['phone'] . '</span>';
            $html .= '<span>' . $row['quantity'] . '</span>';
            $html .= '<span>' . 'R$' . $row['total_amount'] . '</span>';
            $html .= '<span>' . $row['referral_id'] . '</span>';
            $html .= '<span>' . ($row['status'] == 2 ? 'Pago' : 'Pendente') . '</span>';


            $order_numbers_without_spaces = preg_replace('/\s+/', '', $row['order_numbers']);

            $html .= '<div style="width: 100%; word-wrap: break-word; word-break: break-all; overflow-wrap: break-word; overflow: hidden; margin-top: 36px; display: flex; padding: 12px; text-align: left;">' . $order_numbers_without_spaces . '</div>';


            // Carrega o HTML no Dompdf
            $dompdf->loadHtml($html);

            // Define o tamanho do papel e a orientação
            $dompdf->setPaper('A4', 'portrait');

            // Renderiza o PDF
            $dompdf->render();

            // Envia o PDF para o navegador
            $dompdf->stream("relatorio_pedidos_$id.pdf", ["Attachment" => false]);
        } else {
            echo "Pedido não encontrado.";
        }
    }

    public function view_numbers()
    {
        $id = $_POST['id'];
        $qry = "SELECT order_numbers FROM order_list WHERE id = '$id'";
        $result = $this->conn->query($qry);
        $row = $result->fetch_assoc();
        $order_numbers = $row['order_numbers'];
        $resp["status"] = "success";
        $resp["order_numbers"] = $order_numbers;
        return json_encode($resp);
    }
}

require_once "../settings.php";
$Main = new Main();
$action = !isset($_GET["action"]) ? "none" : strtolower($_GET["action"]);
$sysset = new System();

switch ($action) {
    case "save_product_sys":
        echo $Main->save_product();
        break;
    case "delete_product_sys":
        echo $Main->delete_product();
        break;
    case "generate_pdf":
        echo $Main->generate_pdf();
         break;
    case "buscar_hora_premiada":
        echo $Main->buscar_hora_premiada();
        break;
    case "search_raffle_smallest_and_largest_number":
        echo $Main->search_raffle_smallest_and_largest_number();
        break;
    case "search_raffle_smallest_and_largest_number_today":
        echo $Main->search_raffle_smallest_and_largest_number_today();
        break;
    case "add_to_card":
        echo $Main->add_to_card();
        break;
    case "view_numbers":
        echo $Main->view_numbers();
        break;
    case "place_order_process":
        echo $Main->place_order();
        break;
    case "correct_duplicates":
        echo $Main->correct_duplicates();
        break;
    case "verify_duplicates":
        echo $Main->verify_duplicates();
        break;
    case "verify_orders_mp":
        echo $Main->verify_orders_mp();
        break;
    case "correct_array":
        echo $Main->correct_array();
        break;
    case "delete_order":
        echo $Main->delete_order();
        break;
    case "correct_order":
        echo $Main->correct_order();
        break;
    case "correct_quantity":
        echo $Main->correct_quantity();
        break;
    case "update_order_status_sys":
        echo $Main->update_order_status();
        break;
    case "check_order":
        echo $Main->check_payment_status();
        break;
    case "check_payment_status":
        echo $Main->check_payment_status();
        break;
    case "update_whatsapp_status":
        echo $Main->update_whatsapp_status();
        break;
    case "export_raffle_contacts":
        echo $Main->export_raffle_contacts();
        break;
    case "export_raffle_contacts2":
        echo $Main->export_raffle_contacts2();
        break;
    case "export_customers":
        echo $Main->export_customers();
        break;
    case "search_orders_by_phone":
        echo $Main->search_orders_by_phone();
        break;
    case "search_orders_by_cpf":
        echo $Main->search_orders_by_cpf();
        break;
    case "contact_send_email":
        echo $Main->contact_send_email();
        break;
    case "recover_password":
        echo $Main->recover_password();
        break;
    case "recover_password_admin":
        echo $Main->recover_password_admin();
        break;
    case "generate_password":
        echo $Main->generate_password();
        break;
    case "load_numbers":
        echo $Main->load_numbers();
        break;
    case "search_raffle_winner":
        echo $Main->search_raffle_winner();
        break;
    case "save_raffle_winner":
        echo $Main->save_raffle_winner();
        break;
    case "create_order":
        echo $Main->create_order();
        break;
    case "create_payment_affiliate":
        echo $Main->create_payment_affiliate();
        break;
    case "create_affiliate":
        echo $Main->create_affiliate();
        break;
    case "delete_affiliate":
        echo $Main->delete_affiliate();
        break;
    case "deactive_license":
        echo $Main->deactive_license();
        break;
    case "manage_draw":
        echo $Main->manage_draw();
        break;
    case "search_raffle_smallest_and_largest_number":
        echo $Main->search_raffle_smallest_and_largest_number();
        break;
    case "search_raffle_smallest_and_largest_number_today":
        echo $Main->search_raffle_smallest_and_largest_number_today();
        break;
    case "att_roleta":
        echo $Main->att_roleta();
        break;
    case "att_box":
        echo $Main->att_box();
        break;
    case "load_cotas":
        echo $Main->load_cotas();
        break;
    case "load_cotas_roleta":
        echo $Main->load_cotas_roleta();
        break;
    case "load_cotas_box":
        echo $Main->load_cotas_box();
        break;
    default:
        break;
}
