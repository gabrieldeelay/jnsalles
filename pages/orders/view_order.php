<?php

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';

// Obtém o domínio
$host = $_SERVER['HTTP_HOST'];

// Obtém o caminho da URL (caminho e parâmetros)
$requestUri = $_SERVER['REQUEST_URI'];

// Combina tudo para formar a URL completa
$currentUrl = $protocol . $host . $requestUri;

$currentUrl;


function leowp_format_luck_numbers($client_lucky_numbers, $raffle_total_numbers, $class, $opt, $type_of_draw)
{
    $bichos = array();
    if ($type_of_draw == 3) {
        $bichos = array(
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
            "24" => "Vaca"
        );
    }
    if ($type_of_draw == 4) {
        $bichos = array(
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
            "49" => "Vaca M2"
        );
    }

    if ($client_lucky_numbers) {
        foreach ($client_lucky_numbers as $client_lucky_number) {
            if (!empty($client_lucky_number)) {
                $size = strlen($client_lucky_number);
                if ($type_of_draw == 3 || $type_of_draw == 4) {
                    $bicho_name = $bichos[$client_lucky_number];
                    echo '<span style="border-radius: 5px !important; display: inline-block; padding: 5px; border-radius:2px; margin: 4px;"  class=" ' . $class . ' me-0 alert-success">' . $bicho_name . '</span>';
                } else {
                    $output = ($type_of_draw == 3 || $type_of_draw == 4) ? $bichos[$client_lucky_number] : $client_lucky_number;
                    if ($opt == true) {
                        echo '<span style="border-radius: 5px !important; display: inline-block; padding: 5px; border-radius:2px; margin: 4px;" class=" ' . $class . ' me-0 wd-' . $size . '">' . $output . '</span>';
                    } else {
                        echo '' . $output . '<span class="comma-hide">,</span>';
                    }
                }
            }
        }
    } else {
        echo '...';
    }
};



$orderitem = $conn->query("SELECT * FROM `order_list` where order_token = '{$_GET['id']}'");

$orderitem = $orderitem->fetch_assoc();

$product = $conn->query("SELECT cotas_premiadas,cotas_premiadas_roleta,cotas_premiadas_box, type_of_draw, cotas_premiadas_premios,cotas_premiadas_premios_roleta,cotas_premiadas_premios_box, roleta, box FROM `product_list` where id = '{$orderitem['product_id']}'");
$product = $product->fetch_assoc();
$type_of_draw = $product['type_of_draw'];
$cotas_p = $product['cotas_premiadas'];
$cotas_premiadas_premios = $product['cotas_premiadas_premios'];

$cotas_p_roleta = $product['cotas_premiadas_roleta'];
$cotas_premiadas_premios_roleta = $product['cotas_premiadas_premios_roleta'];
$cotas_p_box = $product['cotas_premiadas_box'];
$cotas_premiadas_premios_box = $product['cotas_premiadas_premios_box'];
$tipo_roleta = $product['roleta'];
$tipo_box = $product['box'];

$deserialized = [];
$pairs = explode(',', $cotas_premiadas_premios);
foreach ($pairs as $pair) {
    [$key, $value] = explode(':', $pair, 2);
    $deserialized[$key] = $value;
}
$cotas_array = $deserialized;
$cotas_premiadas = explode(',', $cotas_p);
$cotas_premiadas_roleta = explode(',', $cotas_p_roleta);
$cotas_premiadas_box = explode(',', $cotas_p_box);
$my_numbers = 0;
$my_numbers = $orderitem['order_numbers'];
$my_numbers = explode(',', $my_numbers);

// Inicialize a página atual com base no parâmetro da URL ou padrão para 1
$current_page = 1;
if (isset($_GET['p']) && $_GET['pg'] > 0) {
    $current_page = intval($_GET['pg']);
}

// Defina o limite de itens por página
$limit = 100;

// Calcule o offset para array_slice
$offset = ($current_page - 1) * $limit;

// Faça o slicing do array com base no offset e limite
$sliced_numbers = array_slice($my_numbers, $offset, $limit);





$pagstar = $_settings->info('pagstar') == 1 ? true : false;
$pay2m = $_settings->info('pay2m') == 1 ? true : false;




$whatsapp =  $_settings->info('phone');

$enable_hide_numbers = $_settings->info('enable_hide_numbers');
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT *  from `order_list` where order_token = '{$_GET['id']}'");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
        $customer_id = $customer_id;
    } else {
        echo "<script>alert('Você não tem permissão para acessar essa página.'); 
   location.replace('/');</script>";
        exit;
    }
} else {
    echo "<script>alert('Você não tem permissão para acessar essa página.'); 
   location.replace('/');</script>";
    exit;
}
?>

<style>
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
</style>
<div id="loadingSystem" style="display: none;"></div>
<div class="app-main container">
    <div class="compra-status">
        <?php if ($status == '1') { ?>
            <div class="app-alerta-msg mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="MuiBox-root css-cmn6gx iconify iconify--eos-icons text-warning" sx="[object Object]" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" style="font-size: 64px;">
                    <g>
                        <path fill="currentColor" d="M7 3H17V7.2L12 12L7 7.2V3Z">
                            <animate id="iconifyReact0" fill="freeze" attributeName="opacity" begin="0;iconifyReact1.end" dur="2s" from="1" to="0"></animate>
                        </path>
                        <path fill="currentColor" d="M17 21H7V16.8L12 12L17 16.8V21Z">
                            <animate fill="freeze" attributeName="opacity" begin="0;iconifyReact1.end" dur="2s" from="0" to="1"></animate>
                        </path>
                        <path fill="currentColor" d="M6 2V8H6.01L6 8.01L10 12L6 16L6.01 16.01H6V22H18V16.01H17.99L18 16L14 12L18 8.01L17.99 8H18V2H6ZM16 16.5V20H8V16.5L12 12.5L16 16.5ZM12 11.5L8 7.5V4H16V7.5L12 11.5Z"></path>
                        <animateTransform id="iconifyReact1" attributeName="transform" attributeType="XML" begin="iconifyReact0.end" dur="0.5s" from="0 12 12" to="180 12 12" type="rotate"></animateTransform>
                    </g>
                </svg>
                <div class="app-alerta-msg--txt">
                    <h3 class="app-alerta-msg--titulo">Aguardando Pagamento!</h3>
                    <p>Finalize o pagamento</p>
                </div>
            </div>
        <?php } ?>

        <?php if ($status == '2') { ?>
            <div class="app-alerta-msg mb-2">
                <i class="app-alerta-msg--icone bi bi-check-circle text-success"></i>
                <div class="app-alerta-msg--txt">
                    <h3 class="app-alerta-msg--titulo">Compra Aprovada!</h3>
                    <p>Agora é só torcer!</p>
                </div>
            </div>
        <?php } ?>

        <?php if ($status == '3') { ?>
            <div class="app-alerta-msg mb-2">
                <i style="color:red" class="app-alerta-msg--icone bi bi-x-circle"></i>
                <div class="app-alerta-msg--txt">
                    <h3 class="app-alerta-msg--titulo">Pedido cancelado!</h3>
                    <p>O prazo para pagamento do seu pedido expirou.</p>
                </div>
            </div>
        <?php } ?>

        <hr class="my-2">
    </div>
    <?php if ($status == '1') { ?>
        <div class="compra-pagamento">
            <div class="pagamentoQrCode text-center">
                <div class="pagamento-rapido">
                    <div class="app-card card rounded-top rounded-0 shadow-none border-bottom">
                        <div class="card-body">
                            <div class="pagamento-rapido--progress">
                                <div class="d-flex justify-content-center align-items-center mb-1 font-llg">
                                    <div><small>Você tem</small></div>
                                    <div class="mx-1"><b class="font-md" id="tempo-restante"></b></div>
                                    <div><small>para pagar</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="app-card card rounded-bottom rounded-0 rounded-bottom b-1 border-dark mb-2">
                    <div class="card-body">
                        <div id="pix-generation-state" class="alert alert-info text-center mb-2 <?= trim((string) $pix_code) !== '' ? 'd-none' : '' ?>" role="status" aria-live="polite">
                            <div class="spinner-border spinner-border-sm me-2" aria-hidden="true"></div>
                            <strong>Pedido reservado.</strong> Estamos gerando o seu PIX; mantenha esta página aberta.
                        </div>
                        <div id="pix-payment-content" class="<?= trim((string) $pix_code) === '' ? 'd-none' : '' ?>">
                        <div class="row justify-content-center mb-2">
                            <div class="col-12 text-start">
                                <div class="mb-1"><span class="badge bg-success badge-xs">1</span><span class="font-xxs"> Copie o código PIX abaixo.</span></div>
                                <div class="input-group mb-2">
                                    <input id="pixCopiaCola" type="text" class="form-control" value="<?= $pix_code; ?>">
                                    <div class="input-group-append">
                                        <button onclick="copyPix()" class="app-btn btn btn-success rounded-0 rounded-end">Copiar</button>
                                    </div>
                                </div>
                                <div class="mb-2"><span class="badge bg-success">2</span> <span class="font-xs">Abra o app do seu banco e escolha a opção PIX, como se fosse fazer uma transferência.</span></div>
                                <p><span class="badge bg-success">3</span> <span class="font-xs">Selecione a opção PIX cópia e cola, cole a chave copiada e confirme o pagamento.</span></p>
                            </div>


                        </div>
                        <div style="background-image: url('../assets/img/bg-btn-qr.png'); text-align: center;"><input id="btmqr" class="btn-qr" type="button" value="Mostrar QR Code"></div>
                        <div id="exibeqr" style="display: none; margin-top:24px; margin-bottom:24px; align-items:center" class="row justify-content-center ">

                            <div class="col-md-6 pb-3 mb-3">
                                <div class="d-block text-center">
                                   <div id="img-qrcode" class="d-inline-block bg-white rounded">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($pix_code) ?>" class="img-fluid">
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-6 pb-3">
                                <div style="text-align: center; font-size:0.9rem !important" class="font-xss">
                                    <h5><i class="bi bi-qr-code"></i> QR Code</h5>
                                    <div>Acesse o app do seu banco, escolha a opção de pagar com QR Code, escaneie o código acima e confirme o pagamento.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 my-2">
                            <button type="button" id="payment-confirmed-button" class="app-btn btn btn-success w-100 py-2 mb-2">
                                <i class="bi bi-check-circle me-1"></i> J&aacute; fiz o pagamento
                            </button>
                            <div id="payment-confirmation-feedback" class="alert alert-info p-2 mb-2 font-xss d-none" role="status" aria-live="polite"></div>
                            <p class="alert alert-warning p-2 font-xss" style="text-align: justify; margin-bottom:0.5rem !important">Este pagamento só pode ser realizado dentro do tempo, após este período, caso o pagamento não for confirmado os números voltam a ficar disponíveis.</p>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="detalhes-compra">
        <div class="compra-sorteio mb-2">
            <?php

            $order_items = $conn->query("SELECT o.*, p.name as product, p.price, p.qty_numbers, p.status_display, p.subtitle, p.image_path, p.slug, p.type_of_draw, p.cotas_premiadas_descricao FROM `order_list` o inner join product_list p on o.product_id = p.id where o.id = '{$id}' ");
            while ($row = $order_items->fetch_assoc()) :

                $gt += $row['price'] * $row['quantity'];
            ?>

                <div class="SorteioTpl_sorteioTpl__2s2Wu   pointer">
                    <div class="SorteioTpl_imagemContainer__2-pl4 col-auto ">
                        <div style="display: block; overflow: hidden; position: absolute; inset: 0px; box-sizing: border-box; margin: 0px;">
                            <img alt="Pop 110i 2022 0km" src="<?= validate_image($row['image_path']) ?>" decoding="async" data-nimg="fill" class="SorteioTpl_imagem__2GXxI" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: none; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%;">
                            <noscript></noscript>
                        </div>
                    </div>

                    <div class="SorteioTpl_info__t1BZr">
                        <h1 class="SorteioTpl_title__3RLtu"><a href="/campanha/<?= $row['slug'] ?>"><?= $row['product'] ?></a></h1>
                        <p class="SorteioTpl_descricao__1b7iL" style="margin-bottom: 1px;"><?php echo isset($row['subtitle']) ? $row['subtitle'] : ''; ?></p>
                        <?php if ($row['status_display'] == 1) { ?>
                            <span class="badge bg-success blink bg-opacity-75 font-xsss">Adquira já!</span>
                        <?php } ?>
                        <?php if ($row['status_display'] == 2) { ?>
                            <span class="badge bg-dark blink font-xsss mobile badge-status-1">Corre que está acabando!</span>
                        <?php } ?>
                        <?php if ($row['status_display'] == 3) { ?>
                            <span class="badge bg-dark font-xsss mobile badge-status-3">Aguarde o sorteio!</span>
                        <?php } ?>
                        <?php if ($row['status_display'] == 4) { ?>
                            <span class="badge bg-dark font-xsss">Concluído</span>
                        <?php } ?>

                    </div>
                </div>

        </div>

        <?php
                $numeros = [];
                $valoresDinheiro = [];
                $itens = explode(',', $cotas_premiadas_premios_roleta);
                $perdeuCount = 0;
                $counter = 0;

                // Adicionando "Perdeu" inicialmente
                $numeros[] = 'Perdeu';
                $valoresDinheiro[] = 'Perdeu';
                $indices[] = 'perdeu-0';

                foreach ($itens as $index => $item) {
                    $partes = explode(':', $item);
                    if (isset($partes[0]) && isset($partes[1])) {
                        // Se counter atingir 3, adicionar "Perdeu" e resetar
                        if ($counter == 3) {
                            $numeros[] = 'Perdeu';
                            $valoresDinheiro[] = 'Perdeu';
                            $indices[] = 'perdeu-' . ($index + 1);  // Ajuste para o índice correto
                            $counter = 0; // Resetando o contador
                        }

                        // Adicionando os dados reais do item
                        $numeros[] = $partes[0];
                        $valoresDinheiro[] = $partes[1];
                        $indices[] = $partes[2];

                        $counter++; // Incrementa o contador
                    }
                }

                $dadosCombinados = [];

                for ($i = 0; $i < count($numeros); $i++) {
                    $dadosCombinados[] = [
                        'numero' => $numeros[$i],
                        'valor' => $valoresDinheiro[$i],
                        'indices' => $indices[$i]
                    ];
                }
                $styles = [
                    6 => ['height' => '212px', 'border-radius' => '68px'],
                    8 => ['height' => '154px', 'border-radius' => '32px'],
                    10 => ['height' => '121px', 'border-radius' => '15px']
                ];

                if (isset($styles[count($dadosCombinados)])) {
                    $style = $styles[count($dadosCombinados)];
        ?>
            <style>
                .items {
                    height: <?= $style['height']; ?>;
                    border-radius: <?= $style['border-radius']; ?>;
                }
            </style>
        <?php
                }

                ###############################################################################

                $numerosb = [];
                $valoresDinheirob = [];
                $itensb = explode(',', $cotas_premiadas_premios_box);
                $perdeuCountb = 0;
                $counterb = 0;

                // Adicionando "Perdeu" inicialmente
                $numerosb[] = 'Perdeu';
                $valoresDinheirob[] = 'Perdeu';
                $indicesb[] = 'perdeu-0';

                foreach ($itensb as $index => $item) {
                    $partes = explode(':', $item);
                    if (isset($partes[0]) && isset($partes[1])) {
                        // Se counter atingir 3, adicionar "Perdeu" e resetar
                        if ($counter == 3) {
                            $numerosb[] = 'Perdeu';
                            $valoresDinheirob[] = 'Perdeu';
                            $indicesb[] = 'perdeu-' . ($index + 1);  // Ajuste para o índice correto
                            $counterb = 0; // Resetando o contador
                        }

                        // Adicionando os dados reais do item
                        $numerosb[] = $partes[0];
                        $valoresDinheirob[] = $partes[1];
                        $indicesb[] = $partes[2];

                        $counterb++; // Incrementa o contador
                    }
                }

                $dadosCombinadosb = [];

                for ($i = 0; $i < count($numerosb); $i++) {
                    $dadosCombinadosb[] = [
                        'numero' => $numerosb[$i],
                        'valor' => $valoresDinheirob[$i],
                        'indices' => $indicesb[$i]
                    ];
                }
                $cards = '';

                // Verificar o status de pagamento na tabela 'order_list'
                $stmt_status = $conn->prepare('SELECT status, roleta, box FROM order_list WHERE id = ?');
                $stmt_status->bind_param('s', $id);
                $stmt_status->execute();
                $result_status = $stmt_status->get_result();
                $row_status = $result_status->fetch_assoc();

                // Verifica se o status da ordem é 'pago'
                if ($row_status['status'] == 2 && $row['type_of_draw'] == 1) {
        ?>
            <div class="card">
                <div class="card-body mb-1 pb-1">
                    <?php if ($row_status['roleta'] > 0) {
                        if ($tipo_roleta || $tipo_box) { ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="opacity-50 font-xs text-dark">Você tem (<?= $row_status['roleta'] ?>) jogadas(s) disponíveis:</span>
                                <button class="btn btn-dark btn-sm <?= $tipo_roleta ? 'btn-pular' : '' ?> <?= $tipo_box ? 'btn-pular-caixa' : '' ?>"><i class="bi bi-play-btn"></i> Pular</button>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>

                <?php
                    // String para armazenar os números premiados encontrados
                    $numeros_premiados = [];
                    $numeros_premiados_roleta = [];
                    $numeros_premiados_box = [];

                    // Iterar sobre cada número comprado e verificar se algum deles é o número premiado
                    foreach ($cotas_premiadas as $num) {
                        if (empty($num)) {
                            continue;
                        } // Pula elementos vazios

                        $stmt = $conn->prepare("SELECT * FROM order_list WHERE FIND_IN_SET(?, order_numbers) AND id = $id");
                        $stmt->bind_param('s', $num);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            // Adiciona o número ao array de números premiados
                            $numeros_premiados[] = $num;
                        }
                    }
                    foreach ($cotas_premiadas_roleta as $num) {
                        if (empty($num)) {
                            continue;
                        } // Pula elementos vazios

                        $stmt = $conn->prepare("SELECT * FROM order_list WHERE FIND_IN_SET(?, order_numbers) AND id = $id");
                        $stmt->bind_param('s', $num);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            // Adiciona o número ao array de números premiados
                            $numeros_premiados_roleta[] = $num;
                        }
                    }
                    foreach ($cotas_premiadas_box as $num) {
                        if (empty($num)) {
                            continue;
                        } // Pula elementos vazios

                        $stmt = $conn->prepare("SELECT * FROM order_list WHERE FIND_IN_SET(?, order_numbers) AND id = $id");
                        $stmt->bind_param('s', $num);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            // Adiciona o número ao array de números premiados
                            $numeros_premiados_box[] = $num;
                        }
                    }
                    $cota_ganhadora = $numeros_premiados[0]; // Variável do ganhador - Pode estar vazia
                    $cota_ganhadora_roleta = $numeros_premiados_roleta; // Variável do ganhador - Pode estar vazia
                    $cota_ganhadora_box = $numeros_premiados_box; // Variável do ganhador - Pode estar vazia

                    $qtdPremiosRoleta = count($numeros_premiados_roleta);
                    $qtdPremiosBox = count($numeros_premiados_box);
                    $ganhador = empty($cota_ganhadora_roleta) ? ['perdeu-4'] : $cota_ganhadora_roleta; // Se estiver vazia, define como "Perdeu"

                    if (!empty($numeros_premiados) || !empty($numeros_premiados_roleta) || !empty($numeros_premiados_box)) {
                        $quantidade_premiados = count($numeros_premiados);
                        $numeros_encontrados = implode(', ', $numeros_premiados);
                        $numeros_encontrados = rtrim($numeros_encontrados, ', ');
                        $valorGanhador = [];
                        $valorGanhadorb = [];


                        foreach ($dadosCombinados as $dados) {
                            foreach ($cota_ganhadora_roleta as $cotawin) {
                                if ($dados['numero'] == $cotawin) {
                                    $valorGanhador[] = $dados['valor'];
                                }
                            }
                        }
                        // echo "<pre>";
                        foreach ($dadosCombinadosb as $dados) {
                            foreach ($cota_ganhadora_box as $cotawin) {
                                if ($dados['numero'] == $cotawin) {
                                    $valorGanhadorb[] = $dados['valor'];
                                }
                            }
                        }

                        ob_start();
                        foreach ($numeros_premiados as $num) {
                            $prize = explode(':', $cotas_array[$num]);
                            $prize = $prize[0]; ?>

                        <div class="d-flex" style="align-items: center; justify-content:flex-start;gap:12px; margin-block:4px; max-width:100%; overflow:hidden">
                            <div style="background-color:#387f57; color:white !important; border-radius:6px; min-width:37px; width:fit-content !important;font-size:0.9rem !important;line-height:1 !important; padding:6px 8px !important;font-weight:900 !important " class="font-xs text-dark"><?= (stripos($num, ',') !== false ? str_replace(',', '', $num) : $num) ?> </div>
                            <div style="font-weight: bold;line-height: 1;color: #ffffff !important;font-size: 0.85rem;opacity: 1 !important; text-wrap:nowrap !important; text-overflow:ellipsis; max-width:100%  "> <?= $prize ?></div>
                        </div>
                    <?php }
                        $output = ob_get_clean();
                    ?>
                    <?php if (!empty($numeros_premiados)) { ?>
                        <div class="achouacota detalhes app-card-winner card mb-2 " style="background: linear-gradient(85deg, #00307a, #33a570) !important; color: rgb(255, 255, 255); opacity: 1;">
                            <div class="card-body text-center">
                                <span style="color:#ffffff; font-size:1.5rem; font-weight:900">🥳Você foi Contemplado Parabéns!🥳</span>
                                <div class="font-xs mb-2 text-dark">
                                    <div class="pt-1 opacity-75 font-xs text-dark">Sua compra possui <strong><?= $quantidade_premiados ?> título(s) <br> contemplado(s)</strong> na modalidade <br> <strong>Premiação instantânea:</strong>
                                    </div>
                                    <div style="align-items:center; justify-content:center; gap:8px; margin-block:16px" class=""><?= $output ?></div>
                                    <div style="color:#ffffff !important; font-size:0.9rem !important; margin-block:0 !important; opacity: 1 !important; font-weight: 500 !important;" class=" opacity-75 font-xs text-dark">
                                        Em breve, nossa equipe entrará em contato com você para realizar a entrega do prêmio.!
                                    </div>
                                    <a href="https://api.whatsapp.com/send/?phone=55<?php echo $_settings->info('phone'); ?>" target="_blank" style="z-index: 99; position: relative;" class="btn btn-success btn-sm" id="wpp_btn">
                                        <i style="margin-right:4px" class="bi bi-whatsapp"></i> Falar com o suporte
                                    </a>
                                    
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <?php
                        if ($row_status['roleta'] > 0) {
                            if ($tipo_roleta) {
                                for ($i = 0; $i < $roleta_aberta; $i++) {  ?>
                                <div class="mb-2 card-aberto-perdeu<?= $i ?>">
                                    <div class="card-body mb-1 pb-1">
                                        <div class="roleta-premiada--giros">
                                            <div class="lista font-xs">
                                                <div class="roleta-premiada--item d-flex py-2 px-3 rounded-2
                                                            mb-1 text-white text-center pointer
                                                            bg-gradient-pink
                                                            font-weight-600 justify-content-between"><span><i class="bi bi-play-circle-fill"></i> Giro de Roleta</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                <div class="mb-2">
                                                    <div class="row justify-content-center align-items-center py-2 text-dark">
                                                        <div class="col-auto pe-0">
                                                            <h1><i class="bi bi-emoji-frown text-danger"></i></h1>
                                                        </div>
                                                        <div class="col-auto">
                                                            <p class="mb-1">Não foi dessa vez</p>
                                                            <p class="font-xxs">sua <b>roleta não premiou</b></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                                for ($i = 0; $i < $roleta; $i++) { ?>
                                <div class="mb-2 cardtotalgirar card-girar<?= $i ?>">
                                    <div class="card-body mb-1 pb-1">
                                        <div class="roleta-premiada--giros">
                                            <div class="lista font-xs">
                                                <div class="roleta-premiada--item d-flex py-2 px-3 rounded-2 mb-1 text-white text-center pointer bg-gradient-cyan font-weight-600 justify-content-between">
                                                    <span><i class="bi bi-play-circle-fill"></i> Giro de Roleta</span>
                                                    <span class="badge text-bg-light bg-opacity-75 text-uppercase btn-abrir-modal<?= $i ?>">Girar!</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2 card-perdeu<?= $i ?> d-none">
                                    <div class="card-body mb-1 pb-1">
                                        <div class="roleta-premiada--giros">
                                            <div class="lista font-xs">
                                                <div class="roleta-premiada--item d-flex py-2 px-3 rounded-2
                                                            mb-1 text-white text-center pointer
                                                            bg-gradient-pink
                                                            font-weight-600 justify-content-between"><span><i class="bi bi-play-circle-fill"></i> Giro de Roleta</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                <div class="mb-2">
                                                    <div class="row justify-content-center align-items-center py-2 text-dark">
                                                        <div class="col-auto pe-0">
                                                            <h1><i class="bi bi-emoji-frown text-danger"></i></h1>
                                                        </div>
                                                        <div class="col-auto">
                                                            <p class="mb-1">Não foi dessa vez</p>
                                                            <p class="font-xxs">sua <b>roleta não premiou</b></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2 card-ganhou<?= $i ?> d-none">
                                    <div class="card-body mb-1 pb-1">
                                        <div class="roleta-premiada--giros">
                                            <div class="lista font-xs">
                                                <div class="roleta-premiada--item d-flex py-2 px-3 rounded-2
                                                            mb-1 text-white text-center pointer
                                                            bg-gradient-green
                                                            font-weight-600 justify-content-between"><span><i class="bi bi-play-circle-fill"></i> Giro de Roleta</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                <div class="mb-2">
                                                    <div class="row justify-content-center align-items-center py-2 text-dark">
                                                        <div class="col-auto pe-0">
                                                            <h1><i class="bi bi-emoji-laughing text-success"></i></h1>
                                                        </div>
                                                        <div class="col-auto">
                                                            <p class="mb-1">Parabéns</p>
                                                            <p class="font-xxs">sua <b>roleta contem um prêmio</b></p>
                                                            <p class="font-xxs">você ganhou <b><?= $valorGanhador[$i] ?></b></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="roleta-premiada--roda<?= $i ?>" class="roleta-premiada--roda d-none" style="opacity: 1; scale: 1;">
                                    <div id="wheelOfFortune<?= $i ?>" class="wheelOfFortune">
                                        <audio id="spinAudio<?= $i ?>" src="/roleta.mp3" preload="true"></audio>
                                        <audio id="spinAudio-audio-ganhou<?= $i ?>" src="/roleta-ganhou.wav" preload="auto"></audio>
                                        <audio id="spinAudio-audio-perdeu<?= $i ?>" src="/roleta-perdeu.wav" preload="auto"></audio>
                                        <canvas id="wheel<?= $i ?>" width="350" height="350" style="transform: rotate(-1.5708rad);"></canvas>
                                        <div id="spin<?= $i ?>" class="spin" style="background: rgb(40, 63, 151); color: rgb(255, 255, 255); cursor: pointer;">Girar</div>
                                    </div>
                                </div>
                            <?php
                                }
                            }
                        }
                        if ($row_status['box'] > 0) {
                            for ($i = 0; $i < $box_aberta; $i++) {  ?>
                            <div id="card-caixa" class="card-caixa-perdeu<?= $i ?> d-none mb-2 ">
                                <div class="card-body mb-1 pb-1">
                                    <div class="caixa-premiada--giros">
                                        <div class="lista font-xs">
                                            <div>
                                                <div class="caixa-premiada--item d-flex py-2 px-3 rounded-2 mb-1 text-white text-center pointer bg-gradient-pink font-weight-600 justify-content-between" id="video-67644fc481cbe850420241219-btn"><span><i class="bi bi-gift-fill"></i> Caixa misteriosa</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                <div class="mb-2">
                                                    <div class="row justify-content-center align-items-center text-dark py-2">
                                                        <div class="col-1 px-0"></div>
                                                        <div class="col-auto ps-0">
                                                            <h1><i class="bi bi-box text-danger"></i></h1>
                                                        </div>
                                                        <div class="col">
                                                            <p class="my-1">Não foi dessa vez</p>
                                                            <p class="font-xxs my-1">sua <b>caixa misteriosa</b> veio vazia 🥲</p>
                                                        </div>
                                                        <div class="col-1 px-0"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }
                            for ($i = 0; $i < $box; $i++) {
                                if ($tipo_box) { ?>
                                <div id="card-caixa" class="cardtotalabre card-caixa-abrir<?= $i ?> mb-2">
                                    <audio id="caixa-audio-abrindo<?= $i ?>" src="/caixa-abrindo.mp3" preload="auto"></audio>
                                    <audio id="caixa-audio-ganhou<?= $i ?>" src="/roleta-ganhou.wav" preload="auto"></audio>
                                    <audio id="caixa-audio-perdeu<?= $i ?>" src="/roleta-perdeu.wav" preload="auto"></audio>
                                    <div class="card-body mb-1 pb-1">
                                        <div class="caixa-premiada--giros">
                                            <div class="lista font-xs">
                                                <div>
                                                    <div id="video-67644fc481cbe850420241219" class="caixaPremiada_video__3oQjY" style="pointer-events: none; opacity: 0;">

                                                    </div>
                                                    <div class="caixa-premiada--item d-flex py-2 px-3 rounded-2 mb-1 text-white text-center pointer bg-gradient-yellow
                                        font-weight-600 justify-content-between" id="video-67644fc481cbe850420241219-btn"><span>
                                                            <i class="bi bi-gift-fill"></i> Caixa misteriosa</span>
                                                        <span class="badge text-bg-light bg-opacity-75 text-uppercase btn-abrircaixa<?= $i ?>">Abrir!</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="card-caixa" class="card-caixa-perdeu<?= $i ?> d-none mb-2 ">
                                    <div class="card-body mb-1 pb-1">
                                        <div class="caixa-premiada--giros">
                                            <div class="lista font-xs">
                                                <div>
                                                    <div class="caixa-premiada--item d-flex py-2 px-3 rounded-2 mb-1 text-white text-center pointer bg-gradient-pink font-weight-600 justify-content-between" id="video-67644fc481cbe850420241219-btn"><span><i class="bi bi-gift-fill"></i> Caixa misteriosa</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                    <div class="mb-2">
                                                        <div class="row justify-content-center align-items-center py-2 text-dark">
                                                            <div class="col-1 px-0"></div>
                                                            <div class="col-auto ps-0">
                                                                <h1><i class="bi bi-box text-danger"></i></h1>
                                                            </div>
                                                            <div class="col">
                                                                <p class="my-1">Não foi dessa vez</p>
                                                                <p class="font-xxs my-1">sua <b>caixa misteriosa</b> veio vazia 🥲</p>
                                                            </div>
                                                            <div class="col-1 px-0"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2 card-caixa-ganhou<?= $i ?> d-none">
                                    <div class="card-body mb-1 pb-1">
                                        <div class="roleta-premiada--giros">
                                            <div class="lista font-xs">
                                                <div class="roleta-premiada--item d-flex py-2 px-3 rounded-2
                                                            mb-1 text-white text-center pointer
                                                            bg-gradient-green
                                                            font-weight-600 justify-content-between"><span><i class="bi bi-play-circle-fill"></i> Caixa misteriosa</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                <div class="mb-2">
                                                    <div class="row justify-content-center align-items-center py-2 text-dark">
                                                        <div class="col-auto pe-0">
                                                            <h1><i class="bi bi-emoji-laughing text-success"></i></h1>
                                                        </div>
                                                        <div class="col-auto">
                                                            <p class="mb-1">Parabéns</p>
                                                            <p class="font-xxs">sua <b>caixinha contem um prêmio</b></p>
                                                            <p class="font-xxs">você ganhou <b><?= $valorGanhadorb[$i] ?></b></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="area-box<?= $i ?>"></div>
                            <?php  } ?>
                            <?php }
                        }
                    } else {
                        $quantidade_premiados = count($numeros_premiados);
                        $numeros_encontrados = implode(', ', $numeros_premiados);
                        $numeros_encontrados = rtrim($numeros_encontrados, ', ');
                        $roletaOpen = false;

                        if (isset($cotas_p) && !empty($cotas_p) || isset($cotas_p_roleta) && !empty($cotas_p_roleta) || isset($cotas_p_box) && !empty($cotas_p_box)) {
                            if ($tipo_roleta) {
                                if ($roleta > 0) {
                                    for ($i = 0; $i < $roleta_aberta; $i++) {  ?>
                                    <div class="mb-2 card-aberto-perdeu<?= $i ?>">
                                        <div class="card-body mb-1 pb-1">
                                            <div class="roleta-premiada--giros">
                                                <div class="lista font-xs">
                                                    <div class="roleta-premiada--item d-flex py-2 px-3 rounded-2
                                                            mb-1 text-white text-center pointer
                                                            bg-gradient-pink
                                                            font-weight-600 justify-content-between"><span><i class="bi bi-play-circle-fill"></i> Giro de Roleta</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                    <div class="mb-2">
                                                        <div class="row justify-content-center align-items-center py-2 text-dark">
                                                            <div class="col-auto pe-0">
                                                                <h1><i class="bi bi-emoji-frown text-danger"></i></h1>
                                                            </div>
                                                            <div class="col-auto">
                                                                <p class="mb-1">Não foi dessa vez</p>
                                                                <p class="font-xxs">sua <b>roleta não premiou</b></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php }
                                    for ($i = 0; $i < $roleta; $i++) { ?>
                                    <div class=" mb-2 cardtotalgirar card-girar<?= $i ?>">
                                        <div class="card-body mb-1 pb-1">
                                            <div class="roleta-premiada--giros">
                                                <div class="lista font-xs">
                                                    <div class="roleta-premiada--item d-flex py-2 px-3 rounded-2
                                                        mb-1 text-white text-center pointer
                                                        bg-gradient-cyan
                                                        font-weight-600 justify-content-between"><span><i class="bi bi-play-circle-fill"></i> Giro de Roleta</span><span class="badge text-bg-light bg-opacity-75 text-uppercase btn-abrir-modal<?= $i ?>">Girar!</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2 card-perdeu<?= $i ?> d-none">
                                        <div class="card-body mb-1 pb-1">
                                            <div class="roleta-premiada--giros">
                                                <div class="lista font-xs">
                                                    <div class="roleta-premiada--item d-flex py-2 px-3 rounded-2
                                                            mb-1 text-white text-center pointer
                                                            bg-gradient-pink
                                                            font-weight-600 justify-content-between"><span><i class="bi bi-play-circle-fill"></i> Giro de Roleta</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                    <div class="mb-2">
                                                        <div class="row justify-content-center align-items-center py-2 text-dark">
                                                            <div class="col-auto pe-0">
                                                                <h1><i class="bi bi-emoji-frown text-danger"></i></h1>
                                                            </div>
                                                            <div class="col-auto">
                                                                <p class="mb-1">Não foi dessa vez</p>
                                                                <p class="font-xxs">sua <b>roleta não premiou</b></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="roleta-premiada--roda<?= $i ?>" class="roleta-premiada--roda d-none" style="opacity: 1; scale: 1;">
                                        <div id="wheelOfFortune<?= $i ?>" class="wheelOfFortune">
                                            <audio id="spinAudio<?= $i ?>" src="/roleta.mp3" preload="true"></audio>
                                            <audio id="spinAudio-audio-ganhou<?= $i ?>" src="/roleta-ganhou.wav" preload="auto"></audio>
                                            <audio id="spinAudio-audio-perdeu<?= $i ?>" src="/roleta-perdeu.wav" preload="auto"></audio>
                                            <canvas id="wheel<?= $i ?>" width="350" height="350" style="transform: rotate(-1.5708rad);"></canvas>
                                            <div id="spin<?= $i ?>" class="spin" style="background: rgb(40, 63, 151); color: rgb(255, 255, 255); cursor: pointer;">Girar</div>
                                        </div>
                                    </div>
                                <?php
                                    }
                                } else {

                                ?>

                                <div class="mb-2 card-perdeu d-none">
                                    <div class="card-body mb-1 pb-1">
                                        <div class="roleta-premiada--giros">
                                            <div class="lista font-xs">
                                                <div class="roleta-premiada--item d-flex py-2 px-3 rounded-2
                                                                    mb-1 text-white text-center pointer
                                                                    bg-gradient-pink
                                                                    font-weight-600 justify-content-between"><span><i class="bi bi-play-circle-fill"></i> Giro de Roleta</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                <div class="mb-2">
                                                    <div class="row justify-content-center align-items-center py-2 text-dark">
                                                        <div class="col-auto pe-0">
                                                            <h1><i class="bi bi-emoji-frown text-danger"></i></h1>
                                                        </div>
                                                        <div class="col-auto">
                                                            <p class="mb-1">Não foi dessa vez</p>
                                                            <p class="font-xxs">sua <b>roleta não premiou</b></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php

                                }
                            }
                            if ($tipo_box) {
                                for ($i = 0; $i < $box_aberta; $i++) {  ?>
                                <div id="card-caixa" class="card-caixa-perdeu<?= $i ?> d-none mb-2 ">
                                    <div class="card-body mb-1 pb-1">
                                        <div class="caixa-premiada--giros">
                                            <div class="lista font-xs">
                                                <div>
                                                    <div class="caixa-premiada--item d-flex py-2 px-3 rounded-2 mb-1 text-white text-center pointer bg-gradient-pink font-weight-600 justify-content-between" id="video-67644fc481cbe850420241219-btn"><span><i class="bi bi-gift-fill"></i> Caixa misteriosa</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                    <div class="mb-2">
                                                        <div class="row justify-content-center align-items-center text-dark py-2">
                                                            <div class="col-1 px-0"></div>
                                                            <div class="col-auto ps-0">
                                                                <h1><i class="bi bi-box text-danger"></i></h1>
                                                            </div>
                                                            <div class="col">
                                                                <p class="my-1">Não foi dessa vez</p>
                                                                <p class="font-xxs my-1">sua <b>caixa misteriosa</b> veio vazia 🥲</p>
                                                            </div>
                                                            <div class="col-1 px-0"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                                for ($i = 0; $i < $box; $i++) {

                            ?>
                                <div id="card-caixa" class="card-caixa-abrir<?= $i ?> mb-2">
                                    <audio id="caixa-audio-abrindo<?= $i ?>" src="/caixa-abrindo.mp3" preload="auto"></audio>
                                    <audio id="caixa-audio-ganhou<?= $i ?>" src="/roleta-ganhou.wav" preload="auto"></audio>
                                    <audio id="caixa-audio-perdeu<?= $i ?>" src="/roleta-perdeu.wav" preload="auto"></audio>
                                    <div class="card-body mb-1 pb-1">
                                        <div class="caixa-premiada--giros">
                                            <div class="lista font-xs">
                                                <div>
                                                    <div id="video-67644fc481cbe850420241219" class="caixaPremiada_video__3oQjY" style="pointer-events: none; opacity: 0;">

                                                    </div>
                                                    <div class="caixa-premiada--item d-flex py-2 px-3 rounded-2 mb-1 text-white text-center pointer bg-gradient-yellow
                                    font-weight-600 justify-content-between" id="video-67644fc481cbe850420241219-btn"><span>
                                                            <i class="bi bi-gift-fill"></i> Caixa misteriosa</span>
                                                        <span class="badge text-bg-light bg-opacity-75 text-uppercase btn-abrircaixa<?= $i ?>">Abrir!</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="card-caixa" class="card-caixa-perdeu<?= $i ?> d-none mb-2 ">
                                    <audio id="caixa-audio-abrindo" src="/caixa-abrindo.mp3" preload="auto"></audio>
                                    <audio id="caixa-audio-ganhou" src="/roleta-ganhou.wav" preload="auto"></audio>
                                    <audio id="caixa-audio-perdeu" src="/roleta-perdeu.wav" preload="auto"></audio>
                                    <div class="card-body mb-1 pb-1">
                                        <div class="caixa-premiada--giros">
                                            <div class="lista font-xs">
                                                <div>
                                                    <div class="caixa-premiada--item d-flex py-2 px-3 rounded-2 mb-1 text-white text-center pointer bg-gradient-pink font-weight-600 justify-content-between" id="video-67644fc481cbe850420241219-btn"><span><i class="bi bi-gift-fill"></i> Caixa misteriosa</span><span class="badge text-bg-light bg-opacity-75 text-uppercase">Aberta</span></div>
                                                    <div class="mb-2">
                                                        <div class="row justify-content-center align-items-center py-2 text-dark">
                                                            <div class="col-1 px-0"></div>
                                                            <div class="col-auto ps-0">
                                                                <h1><i class="bi bi-box text-danger"></i></h1>
                                                            </div>
                                                            <div class="col">
                                                                <p class="my-1">Não foi dessa vez</p>
                                                                <p class="font-xxs my-1">sua <b>caixa misteriosa</b> veio vazia 🥲</p>
                                                            </div>
                                                            <div class="col-1 px-0"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="area-box<?= $i ?>"></div>
                            <?php

                                }
                            } else {
                                if (!empty($cotas_premiadas_premios)) { ?>
                                <div class="detalhes app-card-winner card mb-2 " style="background:#DC0000 !important; color: rgb(255, 255, 255); opacity: 1;">
                                    <div class="card-body text-center">
                                        <span style="color:#fff; font-size:1.5rem; font-weight:900">😢Não Foi dessa Vez!😢</span>

                                        <div class="font-xs mb-2 text-dark">
                                            <div style="color:#fff !important" class="pt-1 opacity-75 font-xs text-dark">Sua compra não possui <strong> títulos <br> contemplados</strong> na modalidade <br> <strong>Premiação instantânea:</strong></div>
                                            <div style="color:#fff !important; font-size:0.9rem !important; margin-block:0 !important;opacity: 1 !important; font-weight: 500 !important;" class=" opacity-75 font-xs text-dark">
                                            </div>

                                            <div style="color:#fff !important; font-size:0.9rem !important; margin-block:0 !important; opacity: 1 !important; font-weight: 500 !important;" class=" opacity-75 font-xs text-dark">
                                                Não fique triste, você continua concorrendo ao <strong>prêmio principal</strong> <br> boa sorte!</div>

                                        </div>
                                    </div>
                                </div>
                            <?php }
                            ?>

            <?php }
                        } else {
                            $cards = "";
                        }
                    }
                }
            ?>

            <div style="opacity: 1!important; color:#000" class="detalhes app-card card mb-2">
                <div class="card-body font-xs">
                    <div class="font-xs opacity-75 mb-2 border-bottom-rgba text-dark d-flex justify-content-between">
                        <div>
                            <i class="bi bi-info-circle"></i> Detalhes da sua compra&nbsp;
                            <div class="pt-1 opacity-50 mb-1">
                                <?= isset($order_token) ? $order_token : '' ?>
                            </div>
                        </div>
                    </div>
                    <div class="item d-flex align-items-baseline mb-1 pb-1">

                        <div class="result font-xs text-dark" style="text-transform: uppercase;">
                            <?php
                            $customerQuery = $conn->query("SELECT firstname, lastname, phone FROM `customer_list` WHERE id = '{$customer_id}'");

                            if ($customerQuery && $customerQuery->num_rows > 0) {
                                $customer = $customerQuery->fetch_assoc();
                                $firstname = $customer['firstname'];
                                $lastname = $customer['lastname'];
                                $phone = $customer['phone'];
                            }
                            $firstname = ucwords($firstname);
                            $lastname = ucwords($lastname);
                            echo $firstname . ' ' . $lastname . '';
                            ?>
                        </div>
                    </div>
                    <div class="item d-flex align-items-baseline mb-1 pb-1">
                        <div class="title me-1 text-dark">
                            <i class="bi bi-check-circle"></i> Transação
                        </div>
                        <div class="result font-xs text-dark">
                            <?= $id ?>
                        </div>
                    </div>
                    <div class="item d-flex align-items-baseline mb-1 pb-1">
                        <div class="title me-1 text-dark"><i class="bi bi-phone"></i> Telefone</div>
                        <div class="result font-xs text-dark">
                            <?= substr(formatPhoneNumber($phone), 0, -4) . '****'; ?>
                            
                        </div>
                    </div>
                    <div class="item d-flex align-items-baseline mb-1 pb-1">
                        <div class="title me-1 text-dark"><i class="bi bi-calendar-week"></i> Data/Hora</div>
                        <div class="result font-xs text-dark"><?php echo date('d-m-Y H:i', strtotime($date_created)); ?>
                        </div>
                    </div>
                    <div class="item d-flex align-items-baseline mb-1 pb-1">
                        <div class="title me-1 text-dark">
                            <i class="bi bi-card-list"></i>
                            <?= $quantity ?> Cota(s)
                        </div>
                    </div>
                    <div class="item d-flex align-items-baseline mb-1 pb-1 border-bottom-rgba">
                        <div class="title me-1 mb-1 text-dark">
                            <i class="bi bi-wallet2"></i> Valor
                        </div>
                        <div class="result font-xs text-dark">R$
                            <?= number_format($total_amount, 2, ',', '.') ?>
                        </div>
                    </div>
                    <div class="item  align-items-baseline container">
                        <?php if ($type_of_draw == 1 && $status == 1 && $enable_hide_numbers == 1) {
                            echo ' <div style="margin-left:-12px" class="title font-weight-500 me-1">                       <i class="bi me-1 bi-card-list"></i> 
                                                                                                                                                                                                                                                                                                                                                                                                                                                               Cotas:</div>';
                        } ?>
                        <div class="result font-xs  row" data-nosnippet="true" style="overflow:hidden; gap:4px;">
                            <?php
                            if ($type_of_draw > 2) {
                                echo leowp_format_luck_numbers($my_numbers, $row['qty_numbers'], $class = 'alert-success', $opt = true, $type_of_draw);
                            } elseif ($type_of_draw == 1 && $status == 1 && $enable_hide_numbers == 1) {
                                echo '            <p class="alert alert-warning p-2 mt-2 font-xss" style="text-align: justify; margin-bottom:0.5rem !important">As cotas serão geradas após o pagamento.</p>
       ';
                            } else {
                                echo leowp_format_luck_numbers($sliced_numbers, $limit, $class = 'alert-success ', $opt = true, $type_of_draw);

                            ?>
                        </div>
                        <div style="margin-top: 16px">

                            <?php
                                // Calcule o número total de páginas
                                $total_pages = ceil(count($my_numbers) / $limit);
                                if ($total_pages > 1) {
                            ?>
                                <nav aria-label="Page navigation example">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?= $currentUrl ?>&p<?php echo $current_page - 1; ?>">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php

                                        // Calcule o número total de páginas

                                        // Mostre a página anterior, atual e próxima
                                        if ($current_page > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="' . $currentUrl . '&pg=' . ($current_page - 1) . '" >' . ($current_page - 1) . '</a></li>';
                                        }
                                        echo '<li class="page-item active"><a class="page-link" href="' . $currentUrl . '&pg=' . $current_page . '" >' . $current_page . '</a></li>';

                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';

                                        ?>
                                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?= $currentUrl ?>&pg=<?php echo $current_page + 1; ?>">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php
                                }
                            ?>
                        </div>
                    </div>
                <?php    } ?>
                <div class="item d-flex align-items-baseline mb-1 pb-1 border-bottom-rgba border-1"></div>
                <?php echo $mensagem; ?>
                </div>
            </div>
            </div>
    </div>
</div>


<?php
            endwhile;
?>
<div class="gerar-aqui"></div>

<script>
    $("#btmqr").on('click', (function() {
        if (document.getElementById('exibeqr').style.display == 'flex') {
            document.getElementById('exibeqr').style.display = 'none';
            document.getElementById('btmqr').value = "Mostrar QR Code";
        } else {
            document.getElementById('exibeqr').style.display = "flex";
            document.getElementById('btmqr').value = "Ocultar QR Code";
        }
    }));

    function copyPix() {
        var copyText = document.getElementById("pixCopiaCola");

        copyText.select();
        copyText.setSelectionRange(0, 99999);

        document.execCommand("copy");
        navigator.clipboard.writeText(copyText.value);

        alert("Chave pix 'Copia e Cola' copiada com sucesso!");
    }
    $(document).ready(function() {
        <?php if (!empty($numeros_premiados)): ?>
            $('.achoucotadetal').removeClass('d-none')
        <?php endif; ?>
        var tempoInicial = parseInt('<?= $order_expiration; ?>');
        var token = '<?= isset($order_token) ? $order_token : '' ?>';
        var progressoMaximo = 100;
        var tempoRestante;

        if (localStorage.getItem(token)) {
            tempoRestante = parseInt(localStorage.getItem(token));
        } else {
            tempoRestante = tempoInicial * 60;
            localStorage.setItem(token, tempoRestante);
        }

        var intervalo = setInterval(function() {
            var minutos = Math.floor(tempoRestante / 60);
            var segundos = tempoRestante % 60;
            var tempoFormatado = minutos.toString().padStart(2, '0') + ':' + segundos.toString().padStart(2, '0');
            $('#tempo-restante').text(tempoFormatado);
            var progresso = ((tempoInicial * 60 - tempoRestante) / (tempoInicial * 60)) * progressoMaximo;
            $('#barra-progresso').css('width', progresso + '%').attr('aria-valuenow', progresso);
            tempoRestante--;
            localStorage.setItem(token, tempoRestante);
            if (tempoRestante < 0) {
                clearInterval(intervalo);
                localStorage.removeItem(token);
            }
        }, 1000);

        var pixIsReady = <?= trim((string) $pix_code) !== '' ? 'true' : 'false' ?>;
        var pixGenerationRequest = null;
        var pixGenerationFailures = 0;

        function schedulePixGeneration(delay) {
            window.setTimeout(generateQueuedPix, Math.max(500, Number(delay || 1200)));
        }

        function generateQueuedPix() {
            if (pixIsReady || pixGenerationRequest) {
                return;
            }
            pixGenerationRequest = $.ajax({
                type: 'POST',
                url: _base_url_ + 'class/Main.php?action=generate_order_pix',
                dataType: 'json',
                timeout: 70000,
                data: { order_token: '<?= $order_token ?>' },
                success: function(resp) {
                    if (resp.status === 'success' || resp.status === 'paid') {
                        pixIsReady = true;
                        window.location.reload();
                        return;
                    }
                    if (resp.status === 'queued') {
                        $('#pix-generation-state').html(
                            '<div class="spinner-border spinner-border-sm me-2" aria-hidden="true"></div>' +
                            '<strong>Pedido reservado.</strong> Seu PIX está na fila e será exibido automaticamente.'
                        );
                        schedulePixGeneration(resp.retry_after_ms || 1400);
                        return;
                    }
                    pixGenerationFailures++;
                    var message = resp.msg || 'O gateway ainda não respondeu.';
                    if (pixGenerationFailures < 3) {
                        $('#pix-generation-state').html('<strong>' + message + '</strong> Tentaremos novamente automaticamente.');
                        schedulePixGeneration(4000);
                    } else {
                        $('#pix-generation-state').html(
                            '<strong>' + message + '</strong><br>' +
                            '<button type="button" id="retry-pix-generation" class="btn btn-sm btn-primary mt-2">Tentar gerar novamente</button>'
                        );
                    }
                },
                error: function() {
                    $('#pix-generation-state').html(
                        '<strong>Seu pedido está reservado.</strong> A conexão oscilou e tentaremos gerar o PIX novamente.'
                    );
                    schedulePixGeneration(2500);
                },
                complete: function() {
                    pixGenerationRequest = null;
                }
            });
        }

        $(document).on('click', '#retry-pix-generation', function() {
            pixGenerationFailures = 0;
            $('#pix-generation-state').html(
                '<div class="spinner-border spinner-border-sm me-2" aria-hidden="true"></div>' +
                '<strong>Gerando seu PIX...</strong>'
            );
            generateQueuedPix();
        });

        if (!pixIsReady) {
            generateQueuedPix();
        }

        <?php if ($status == 1 && trim((string) $pix_code) !== '') { ?>
            var paymentStatusInterval = null;
            var paymentStatusRequest = null;
            var paymentWasInformed = false;

            function redirectApprovedOrder() {
                if (paymentStatusInterval) {
                    clearInterval(paymentStatusInterval);
                }
                $('#payment-confirmation-feedback')
                    .removeClass('d-none alert-info alert-warning')
                    .addClass('alert-success')
                    .html('<strong>Pagamento aprovado!</strong> Estamos atualizando a sua compra.');
                $('#payment-confirmed-button')
                    .prop('disabled', true)
                    .html('<i class="bi bi-check-circle-fill me-1"></i> Pagamento aprovado');

                <?php if ($_SESSION['ads']) { ?>
                    window.location.replace('<?= BASE_URL ?>sucesso/<?= $order_token ?>');
                <?php } else { ?>
                    var separator = window.location.search ? '&' : '?';
                    window.location.replace(window.location.href + separator + 'payment_confirmed=' + Date.now());
                <?php } ?>
            }

            function checkPaymentStatus(manualCheck) {
                if (paymentStatusRequest) {
                    return;
                }

                var check = {
                    order_token: '<?= $order_token ?>',
                };
                paymentStatusRequest = $.ajax({
                    type: 'POST',
                    url: _base_url_ + "class/Main.php?action=check_order",
                    dataType: 'json',
                    data: check,

                    success: function(resp) {
                        if (String(resp.status) === '2') {
                            redirectApprovedOrder();
                            return;
                        }

                        if (manualCheck || paymentWasInformed) {
                            $('#payment-confirmation-feedback')
                                .removeClass('d-none alert-warning alert-success')
                                .addClass('alert-info')
                                .html('<strong>Tudo certo!</strong> A confirma&ccedil;&atilde;o do PIX pode levar alguns instantes. N&atilde;o fa&ccedil;a outro pagamento: esta p&aacute;gina continuar&aacute; verificando automaticamente.');
                        }
                    },
                    error: function() {
                        if (manualCheck || paymentWasInformed) {
                            $('#payment-confirmation-feedback')
                                .removeClass('d-none alert-success')
                                .addClass('alert-info')
                                .html('Seu pagamento continuar&aacute; sendo verificado automaticamente. N&atilde;o &eacute; necess&aacute;rio fazer outro PIX.');
                        }
                    },
                    complete: function() {
                        paymentStatusRequest = null;
                        if (paymentWasInformed) {
                            $('#payment-confirmed-button')
                                .prop('disabled', true)
                                .html('<i class="bi bi-check-circle me-1"></i> Pagamento informado');
                        }
                    },
                });
            }

            $('#payment-confirmed-button').on('click', function() {
                paymentWasInformed = true;
                $(this)
                    .prop('disabled', true)
                    .html('<i class="bi bi-clock-history me-1"></i> Verificando pagamento...');
                $('#payment-confirmation-feedback')
                    .removeClass('d-none alert-warning alert-success')
                    .addClass('alert-info')
                    .html('Estamos consultando o pagamento diretamente no gateway. Aguarde s&oacute; um instante.');
                checkPaymentStatus(true);
            });

            checkPaymentStatus(false);
            paymentStatusInterval = setInterval(function() {
                checkPaymentStatus(false);
            }, 3000);
        <?php } ?>

    });
</script>

<?php


for ($i = 0; $i < $roleta; $i++) {

    if ($tipo_roleta) {
?>

        <div id="girarroleta<?= $i ?>" class="d-none" style="position: fixed; top:0;left: 0;width: 100vw;height: 100vh;display:flex;justify-content: center; align-items-center;background-color: rgb(0,0,0,0.7);">
            <div class="carousel-wrapper carousel-wrapper<?= $i ?> bg-white">
                <div class="circle circle<?= $i ?> d-flex justify-content-center align-items-center">
                    <button class="botao-girar botao-girar<?= $i ?> fw-bold btn btn-primary">
                        Girar
                    </button>
                </div>
                <div class="carousel-items carousel-items<?= $i ?>">
                    <?php foreach ($dadosCombinados as $index => $dados) { ?>
                        <div class="items items<?= $i ?> <?= $dados['numero'] ?> <?= $dados['indices'] ?>" style="transform: rotate(<?= 360 / count($dadosCombinados) * $index ?>deg) translateX(100px);">
                            <?= $dados['valor'] ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="indicador  indicador<?= $i ?>">
                    <div class="icone">
                        <i class="bi bi-cursor-fill text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('.btn-abrir-modal<?= $i ?>').click(function() {
                    $('#girarroleta<?= $i ?>').removeClass('d-none')
                })
                $('.botao-girar<?= $i ?>').click(function() {
                    $(this).prop('disabled', true);
                    var items = $('.carousel-items<?= $i ?> .items<?= $i ?>');
                    var totalItems = items.length;
                    var itemWidth = items.outerWidth(true); // Largura do item incluindo margens
                    var carouselWidth = $('.carousel-wrapper<?= $i ?>').width(); // Largura do carrossel (container)

                    var ganhador = '<?= $ganhador[$i] ?>'; // Altere conforme necessário ou passe de forma dinâmica

                    // Variável para contar os passes pelo ganhador
                    var ganhadorPasses = 0;
                    var hasPassedGanhador = false;

                    var rotationAngle = 0; // Ângulo inicial
                    var rotationSpeed = 5; // Velocidade inicial da rotação
                    var slowdownThreshold = 360; // Quantos graus antes do ganhador começar a desacelerar
                    var minimumSpeed = 0.8; // Velocidade mínima da rotação (não vai parar de vez)

                    const spinAudio<?= $i ?> = document.getElementById('spinAudio<?= $i ?>');
                    const spinPerdeu<?= $i ?> = document.getElementById('spinAudio-audio-perdeu<?= $i ?>');
                    const spinGanhou<?= $i ?> = document.getElementById('spinAudio-audio-ganhou<?= $i ?>');

                    spinAudio<?= $i ?>.play();

                    function rotateCarousel<?= $i ?>() {
                        rotationAngle -= rotationSpeed; // Ajuste para rotacionar mais lentamente
                        $('.carousel-items<?= $i ?>').css('transform', 'rotate(' + rotationAngle + 'deg)');

                        // Verifica se a roleta está perto do ganhador
                        if (Math.abs(rotationAngle % 360) < slowdownThreshold) {
                            // A roleta começa a desacelerar quando se aproxima do ganhador
                            if (rotationSpeed > minimumSpeed) {
                                rotationSpeed -= 0.01; // Reduz a velocidade a cada iteração
                            }
                        }

                        // Verifica se a roleta passou pelo ganhador
                        checkForWinner<?= $i ?>();
                    }

                    // Função para verificar se o item central é o ganhador
                    function checkForWinner<?= $i ?>() {
                        var centralItem = $('.carousel-items<?= $i ?> .items<?= $i ?>.centralizado');
                        if (centralItem.length > 0 && centralItem.hasClass(ganhador)) {
                            // Verifica se o ganhador já foi centralizado antes e está sendo contado
                            if (!hasPassedGanhador) {
                                ganhadorPasses++; // Incrementa o contador de passes
                                hasPassedGanhador = true; // Marca que já passou pelo ganhador
                            }
                        } else {
                            hasPassedGanhador = false; // Caso o ganhador não esteja centralizado, podemos considerar uma nova passagem
                        }

                        // Se o ganhador passou 3 vezes, paramos a roleta
                        if (ganhadorPasses >= 3) {
                            clearInterval(carouselInterval); // Para a rotação
                        }
                    }

                    // Função para calcular e adicionar a classe centralizada ao item sob o indicador
                    function setCentralItem<?= $i ?>() {
                        var indicador = $('.indicador<?= $i ?>'); // Seleciona o indicador
                        var indicadorOffset = indicador.offset(); // Posição do indicador na tela
                        var indicadorPositionX = indicadorOffset.left + (indicador.outerWidth() / 2); // Posição horizontal do centro do indicador
                        var indicadorPositionY = indicadorOffset.top + (indicador.outerHeight() / 2); // Posição vertical do centro do indicador

                        // Variáveis para encontrar o item mais próximo do indicador
                        var closestItem = null;
                        var minDistance = Infinity;

                        // Percorre todos os itens para encontrar o mais próximo ao indicador
                        items.each(function() {
                            var itemOffset = $(this).offset(); // Posição de cada item
                            var itemPositionX = itemOffset.left + (itemWidth / 2); // Posição horizontal do centro do item
                            var itemPositionY = itemOffset.top + (itemWidth / 2); // Posição vertical do centro do item

                            // Distância entre o centro do item e o indicador
                            var distance = Math.sqrt(Math.pow(itemPositionX - indicadorPositionX, 2) + Math.pow(itemPositionY - indicadorPositionY, 2));

                            // Se o item estiver mais próximo do indicador, atualiza o item central
                            if (distance < minDistance) {
                                minDistance = distance;
                                closestItem = $(this);
                            }
                        });

                        // Remove a classe "centralizado" de todos os itens
                        items.removeClass('centralizado');

                        // Adiciona a classe "centralizado" ao item mais próximo do indicador
                        if (closestItem) {
                            closestItem.addClass('centralizado');
                        }
                    }

                    // Inicia a rotação contínua
                    var carouselInterval = setInterval(function() {
                        rotateCarousel<?= $i ?>(); // Gira os itens
                        setCentralItem<?= $i ?>(); // Verifica qual item está mais próximo do indicador
                    }, 20); // Intervalo inicial para a rotação (rápido no começo)
                    var qtdPremiosRoleta = <?= $qtdPremiosRoleta ?>;
                    setTimeout(() => {
                        $('#girarroleta<?= $i ?>').addClass('d-none')
                        <?php if (!empty($numeros_premiados_roleta)): ?>
                            if (qtdPremiosRoleta > 0) {
                                <?php $ganhador[] = 'perdeu-4' ?>
                                qtdPremiosRoleta--
                                $('.card-ganhou<?= $i ?>').removeClass('d-none')
                                spinGanhou<?= $i ?>.play()
                            } else {
                                <?php $ganhador[] = 'perdeu-4' ?>
                                $('.card-perdeu<?= $i ?>').removeClass('d-none')
                                spinPerdeu<?= $i ?>.play()
                            }
                            console.log(qtdPremiosRoleta)
                        <?php else:
                            $ganhador[] = 'perdeu-4';
                        ?>
                            $('.card-perdeu<?= $i ?>').removeClass('d-none')
                            spinPerdeu<?= $i ?>.play()
                        <?php endif ?>
                        $('.card-girar<?= $i ?>').addClass('d-none')
                        var check = {
                            order_token: '<?= $order_token ?>',
                            roleta: '<?= $roleta ?>',
                        };
                        $.ajax({
                            type: 'POST',
                            url: _base_url_ + "class/Main.php?action=att_roleta",
                            dataType: 'json',
                            data: check,
                            success: function(resp) {

                            },
                        });
                    }, 7000);
                })
                $('.btn-pular').click(function() {
                    // Para cada roleta, executa o efeito final sem animação
                    for (let i = 0; i < <?= $roleta ?>; i++) {
                        // Simular o clique no botão de girar (sem animação)
                        $('#roleta-premiada--roda<?= $i ?>').addClass('d-none');

                        <?php if (!empty($numeros_premiados_roleta)):
                            if ($qtdPremiosRoleta > 0) {
                                $qtdPremiosRoleta--;
                                $ganhador[] = 'perdeu-4'
                        ?>
                                console.log()
                                $('.card-ganhou<?= $i ?>').removeClass('d-none')
                            <?php } else {
                                $numeros_premiados_roleta = null;
                                $ganhador[] = 'perdeu-4';
                            ?>
                                $('.card-perdeu<?= $i ?>').removeClass('d-none')
                            <?php } ?>
                        <?php else:
                            $ganhador[] = 'perdeu-4';
                        ?>
                            $('.card-perdeu<?= $i ?>').removeClass('d-none')
                        <?php endif ?>
                        $('.card-girar<?= $i ?>').addClass('d-none');

                        // Enviar a atualização para o servidor
                        var check = {
                            order_token: '<?= $order_token ?>',
                            roleta: '<?= $roleta ?>',
                            ganhou: <?= (!empty($numeros_premiados_roleta)) ? 'true' : 'false' ?>,
                            pulou: true
                        };
                        $.ajax({
                            type: 'POST',
                            url: _base_url_ + "class/Main.php?action=att_roleta",
                            dataType: 'json',
                            data: check,
                            success: function(resp) {
                                <?php if (!empty($numeros_premiados_roleta)): ?>
                                    $('.cardtotalgirar').addClass('d-none');
                                <?php endif; ?>
                            },
                        });
                    }
                });
            })
        </script>
<?php }
}
?>

<?php
for ($i = 0; $i < $box; $i++) {
    if ($tipo_box) { ?>
        <script>
            $(document).ready(function() {
                $('.btn-abrircaixa<?= $i ?>').click(function() {
                    const caixaAudio<?= $i ?> = document.getElementById('caixa-audio-abrindo<?= $i ?>');
                    const caixaPerdeu<?= $i ?> = document.getElementById('caixa-audio-perdeu<?= $i ?>');
                    const caixaGanhou<?= $i ?> = document.getElementById('caixa-audio-ganhou<?= $i ?>');
                    caixaAudio<?= $i ?>.play();
                    $('.area-box<?= $i ?>').append(`<div class="caixabox">
                        <img src="<?= BASE_URL ?>uploads/caixa-abrindo.gif?time=${new Date().getTime()}" alt="" style="z-index: 9999;filter: blur(0px);">
                    </div>`);

                    var qtdPremiosBox = <?= $qtdPremiosBox ?>;
                    setTimeout(() => {
                        $('.area-box<?= $i ?>').addClass('d-none')
                        $('.area-box<?= $i ?>').html('')
                        $('.card-caixa-abrir<?= $i ?>').addClass('d-none')
                        <?php if (!empty($numeros_premiados_box)): ?>
                            if (qtdPremiosBox > 0) {
                                <?php $ganhador[] = 'perdeu-4' ?>
                                qtdPremiosBox--
                                $('.card-caixa-ganhou<?= $i ?>').removeClass('d-none')
                                caixaGanhou<?= $i ?>.play()
                            } else {
                                <?php $numeros_premiados_box = null; ?>
                                $('.card-caixa-perdeu<?= $i ?>').removeClass('d-none')
                                caixaPerdeu<?= $i ?>.play()
                            }
                        <?php else: ?>
                            caixaPerdeu<?= $i ?>.play();
                            $('.card-caixa-perdeu<?= $i ?>').removeClass('d-none')
                        <?php endif ?>
                        var check = {
                            order_token: '<?= $order_token ?>',
                            roleta: '<?= $box ?>',
                        };
                        $.ajax({
                            type: 'POST',
                            url: _base_url_ + "class/Main.php?action=att_box",
                            dataType: 'json',
                            data: check,
                            success: function(resp) {
                                console.log(resp)

                            },
                        });
                    }, 4000);
                })
                $('.btn-pular-caixa').click(function() {
                    // Para cada roleta, executa o efeito final sem animação
                    for (let i = 0; i < <?= $roleta ?>; i++) {
                        // Simular o clique no botão de girar (sem animação)
                        $('.area-box<?= $i ?>').addClass('d-none')
                        $('.card-caixa-abrir<?= $i ?>').addClass('d-none')
                        <?php if (!empty($numeros_premiados_box)): ?>
                            $('.achouacota').removeClass('d-none')
                        <?php else: ?>
                            $('.card-caixa-perdeu<?= $i ?>').removeClass('d-none')
                        <?php endif ?>

                        // Enviar a atualização para o servidor
                        var check = {
                            order_token: '<?= $order_token ?>',
                            roleta: '<?= $roleta ?>',
                            ganhou: <?= (!empty($numeros_premiados_box)) ? 'true' : 'false' ?>,
                            pulou: true
                        };
                        $.ajax({
                            type: 'POST',
                            url: _base_url_ + "class/Main.php?action=att_box",
                            dataType: 'json',
                            data: check,
                            success: function(resp) {
                                <?php if (!empty($numeros_premiados_box)): ?>
                                    $('.cardtotalabre').addClass('d-none');
                                <?php endif; ?>
                            },
                        });
                    }
                });

            })
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

    <?php } ?>
<?php } ?>
