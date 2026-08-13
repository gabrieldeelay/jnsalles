<?php

$raffle = isset($_GET['raffle']) ? $_GET['raffle'] : '';
$top = isset($_GET['top']) ? $_GET['top'] : '';
$raffleb = isset($_GET['raffleb']) ? $_GET['raffleb'] : '';
$rafflec = isset($_GET['rafflec']) ? $_GET['rafflec'] : '';
$raffled = isset($_GET['raffled']) ? $_GET['raffled'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$where = '';
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : '';

if (!empty($start_date) && !empty($end_date)) {
    $start_datetime = date('Y-m-d H:i:s', strtotime($start_date));
    $end_datetime = date('Y-m-d H:i:s', strtotime($end_date));
    $where .= " AND o.date_created BETWEEN '{$start_datetime}' AND '{$end_datetime}'";
}
?>


<script>
    var _BASE_URL_ = '<?= BASE_URL ?>';
</script>

<style>
    @media (min-width: 1024px) {
        .lg\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    tr.text-gray-700.dark\:text-gray-400 {
        vertical-align: text-bottom;
    }

    .span-whats {
        width: 100%;
        justify-content: space-around;
        margin-top: 10px;
    }



    .span-whats>a {
        display: flex;
        justify-content: center;
        align-content: center;
        align-items: center;
        padding: 8px;
        gap: 3px;
        width: 45%;
        max-height: 38px;
        background-color: #0e9f6e;
    }

    .span-whats>button {
        justify-content: space-evenly;
        align-content: center;
        align-items: center;
        padding: 8px;
        gap: 3px;
        max-height: 38px;
        width: 45%;
    }

    #btn-whats>svg {
        width: 25px;
        height: 25px;
        max-width: 25px;
        min-width: 15px;
        max-height: 25px;
        min-height: 15px;
    }

    .winner-info span {
        display: block;
    }

    .btwp-new {
        background-color: #0e9f6e;
        display: inline-flex;
    }

    .flex-container {
        display: flex;

    }

    #overlay {
        position: fixed;
        top: 0;
        z-index: 100;
        width: 100%;
        height: 100%;
        display: none;
        background: rgba(0, 0, 0, 0.6);
        z-index: 99999999;
    }

    .cv-spinner {
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px #ddd solid;
        border-top: 4px #2e93e6 solid;
        border-radius: 50%;
        animation: sp-anime 0.8s infinite linear;
    }

    .px-2 {
        padding-left: .25rem;
        padding-right: .25rem;
    }
</style>

<div id="overlay" style="display: none;">
    <div class="cv-spinner">
        <span class="spinner"></span>
    </div>

</div>

<main class="h-full overflow-y-auto">
    <div class="container px-6 mx-auto grid">
        <p class="my-6 mb-2 text-xl font-medium text-gray-600 dark:text-gray-400">
            <?php
            $now = new DateTime();
            $now->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            $hour = $now->format('H');
            if ($hour >= 5 && $hour < 12) {
                echo 'Bom dia';
            } elseif ($hour >= 12 && $hour < 18) {
                echo 'Boa tarde';
            } elseif ($hour >= 0 && $hour < 5) {
                echo 'Boa madrugada';
            } else {
                echo 'Boa noite';
            }
            ?>

            <br> <?php echo $_settings->info('name'); ?>!
        </p>
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Central de Comando do <?php echo $_settings->info('name'); ?>!</h2>

        <!--Busca Ganhador x Ranking -->
        <div class="grid gap-6 mb-8 lg:grid-cols-2 xl:grid-cols-2">
            <!-- Card -->



            <div class="flex  items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
                style="display: flex; align-items: flex-start;">
                <div class="w-full">
                    <p class="mb-2 text-sm text-gray-600 dark:text-gray-400 font-semibold">
                        <i style="display:inline-flex"
                            class="p-3 mr-3 text-orange-500 bg-orange-100 rounded-full text-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-search " viewBox="0 0 16 16">
                                <path
                                    d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                            </svg></i>
                        BUSCAR GANHADOR
                    </p>
                    <form action="" id="buscar-ganhador" style="margin-bottom:10px">
                        <table style="width:100%">
                            <tbody>
                                <tr>
                                    <td>
                                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Selecione a
                                            rifa</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <select name="raffle" id="raffle"
                                            class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                            <option value="">Selecione</option>
                                            <?php
                                            $qry = $conn->query("SELECT * FROM `product_list`");
                                            while ($row = $qry->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>" <?php if ($raffle == $row['id']) {
                                                                                        echo 'selected';
                                                                                    } ?>><?= $row['name'] ?>
                                                </option>
                                            <?php } ?>

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Número /
                                            bicho sorteado</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td style="width: 82.3%"><input type="text" name="number"
                                                            class="block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                                                            placeholder="ex: 15 / Leão" required="">
                                                    </td>
                                                    <td style="width: 10px;"></td>
                                                    <td><button
                                                            class="px-4 py-2 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple filtrar">
                                                            Buscar</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>

                    <span id="error" class="text-sm text-red-600 bg-red-100 w-full "
                        style="border-radius: .5rem; padding:.5rem;display:none ">
                    </span>
                    <div
                        class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400 winner-info results-container hidden">
                        <span id="name"></span>
                        <span id="raffle"></span>
                        <span id="date"></span>
                        <span id="payment_status"></span>
                        <span id="number"></span>
                        <span id="payment_date"></span>
                        <span id="whatsapp_msg"></span>
                        <span class="winner"></span>
                        <span id="setWinner"></span>
                        <span id="whatsapp_msg">
                            <div class="flex-container mt-4" style="gap:8px">
                                <a id="btn-whats" href="" target="_blank" style="border-radius:.5rem">
                                    <div style="justify-content:center;"
                                        class="btwp-new mr-3 flex items-center  px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 border border-transparent rounded-lg focus:outline-none focus:shadow-outline-purple">
                                        <i class=" mr-2" style="font-size:18px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                <path
                                                    d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                            </svg>
                                        </i>
                                        <span>Enviar mensagem</span>
                                    </div>
                                </a>
                                <button id="set-winner" data-id="" data-winner="" data-number=""
                                    class="px-4 py-2 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple setWinner"
                                    @click="openModal">Salvar ganhador
                                </button>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
            <div class="items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
                style="display: flex; align-items: flex-start;">

                <div class="w-full">
                    <p class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">
                        <i style="display: inline-flex"
                            class=" p-3 mr-3 text-green-500 bg-green-100 rounded-full text-lg">

                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green"
                                viewBox="0 0 512 512">
                                <path
                                    d="M223.8 130.8L154.6 15.5A32 32 0 0 0 127.2 0H16C3.1 0-4.5 14.6 2.9 25.2l111.3 159c29.7-27.8 67.5-46.8 109.6-53.4zM496 0H384.8c-11.2 0-21.7 5.9-27.4 15.5l-69.1 115.2c42 6.6 79.8 25.6 109.6 53.4L509.1 25.2C516.5 14.6 508.9 0 496 0zM256 160c-97.2 0-176 78.8-176 176s78.8 176 176 176 176-78.8 176-176-78.8-176-176-176zm92.5 157.3l-37.9 37 9 52.2c1.6 9.4-8.3 16.5-16.7 12.1L256 393.9l-46.9 24.7c-8.4 4.5-18.3-2.7-16.7-12.1l9-52.2-37.9-37c-6.8-6.6-3.1-18.2 6.4-19.6l52.4-7.6 23.4-47.5c2.1-4.3 6.2-6.4 10.3-6.4 4.1 0 8.2 2.1 10.3 6.4l23.4 47.5 52.4 7.6c9.4 1.4 13.2 13 6.4 19.6z" />
                            </svg>
                        </i>
                        RANKING DE COMPRADORES
                    </p>
                    <form action="" id="filter-form">

                        <table style="width: 100%">
                            <tbody>
                                <tr>
                                    <td>
                                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Selecione a
                                            rifa</p>
                                    </td>
                                    <td>
                                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Quantidade
                                        </p>
                                    </td>
                                    <td></td>
                                </tr>
                                
                                <tr>
                                    <td><select name="raffle" id="raffle"
                                            class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">xxx
                                            <option value="">Selecione</option>
                                            <?php
                                            $qry = $conn->query("SELECT * FROM `product_list`");
                                            while ($row = $qry->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>" <?php if ($raffle == $row['id']) {
                                                                                        echo 'selected';
                                                                                    } ?>><?= $row['name'] ?>
                                                </option>

                                            <?php } ?>
                                        </select></td>
                                    <td><select name="top" id="top"
                                            class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                            <option value="1" <?php if ($top == 1) {
                                                                    echo 'selected';
                                                                } ?>>1</option>
                                            <option value="2" <?php if ($top == 2) {
                                                                    echo 'selected';
                                                                } ?>>2</option>
                                            <option value="3" <?php if ($top == 3) {
                                                                    echo 'selected';
                                                                } ?>>3</option>
                                            <option value="4" <?php if ($top == 4) {
                                                                    echo 'selected';
                                                                } ?>>4</option>
                                            <option value="5" <?php if ($top == 5) {
                                                                    echo 'selected';
                                                                } ?>>5</option>
                                            <option value="6" <?php if ($top == 6) {
                                                                    echo 'selected';
                                                                } ?>>6</option>
                                            <option value="7" <?php if ($top == 7) {
                                                                    echo 'selected';
                                                                } ?>>7</option>
                                            <option value="8" <?php if ($top == 8) {
                                                                    echo 'selected';
                                                                } ?>>8</option>
                                            <option value="9" <?php if ($top == 9) {
                                                                    echo 'selected';
                                                                } ?>>9</option>
                                            <option value="10" <?php if ($top == 10) {
                                                                    echo 'selected';
                                                                } ?>>10</option>
                                        </select></td>
                                        
                                    <td style="width: 10px;"></td>
                                    <td><button
                                            class="px-4 py-2 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple filtrar">Gerar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                          <tr>
                                    <td>
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <p
                                                            class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                                            Data inicial</p>
                                                    </td>
                                                    <td>
                                                        <p
                                                            class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                                            Data final</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" placeholder="dd/mm/aaaa --:--"
                                                            readonly="true"
                                                            onfocus="this.removeAttribute('readonly');this.type='datetime-local';this.setAttribute('onfocus','');this.blur();this.focus();"
                                                            name="start_date" id="start_date"
                                                            value="<?php echo $start_date; ?>"
                                                            class="space block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    </td>
                                                    <td>
                                                        <input type="text" placeholder="dd/mm/aaaa --:--"
                                                            readonly="true"
                                                            onfocus="this.removeAttribute('readonly');this.type='datetime-local';this.setAttribute('onfocus','');this.blur();this.focus();"
                                                            name="end_date" id="end_date"
                                                            value="<?php echo $end_date; ?>"
                                                            class="space block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                </tr>       


                    </form>
                    <div class="mb-3 text-sm font-medium text-gray-600 dark:text-gray-400">
                        <table style="width: 100%">


                        </table>
                    </div>
                    <div class="mb-3 text-sm font-medium text-gray-600 dark:text-gray-400">
                        <table style="width: 100%">


                            <tbody>
                                <?php
$g_total = 0;
$i = 0;
if ($raffle && $top) {
    $query = "
        SELECT 
            c.firstname, c.lastname, c.phone, 
            SUM(o.quantity) AS total_quantity, 
            SUM(o.total_amount) AS total_amount, 
            o.code, 
            CONCAT(' ', o.product_name) AS product
        FROM order_list o   
        INNER JOIN customer_list c ON o.customer_id = c.id
        WHERE o.product_id = {$raffle} AND o.status = 2
        {$where}
        GROUP BY o.customer_id 
        ORDER BY total_quantity DESC
        LIMIT {$top}
    ";

    $requests = $conn->query($query);

    while ($row = $requests->fetch_assoc()) {
        $i++;
        // continue com o restante da exibição dos dados como já está no seu código
?>

                                        <tr style="border-bottom:1px solid #94979f;">
                                            <td class="px-2 py-3" style="width: 15%; text-align: center">
                                                <spam style="font-size:22px"><?= $i ?>º</spam>
                                            </td>
                                            <td class="px-2 py-3">
                                                <div class="text-sm uppercase mt-3">
                                                    <?= $row['firstname'] . ' ' . $row['lastname'] ?>
                                                </div>
                                                <spam class="text-xs text-gray-500">
                                                    <?= substr(formatPhoneNumber($row['phone']), 0, -4) . '****'; ?>
                                                    <br>
                                                </spam>
                                                <spam class="text-xs text-gray-500">Qtd. Cotas: <?= $row['total_quantity'] ?> /
                                                    Total: R$ <?= $row['total_amount'] ?> </spam>
                                            </td>
                                            <td class="px-2 py-3" style="width: 20%; text-align: center;">
                                                <a href="https://api.whatsapp.com/send?phone=<?= $row['phone'] ?>&amp;text=Parabéns,<?= $row['firstname'] . $row['lastname'] ?> !%0A%0AVocê foi o *TOP <?= $i ?>* do ranking, na ação <?= $row['product'] ?> ."
                                                    target="_blank">
                                                    <button title="Whatsapp">
                                                        <i class=" btwp-new-icon" style="font-size:22px;"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                                fill="green" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                                <path
                                                                    d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                                            </svg></i>
                                                    </button>
                                                </a>

                                            </td>
                                        </tr>




                                <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
                style="display: flex; align-items: flex-start;">

                <div class="w-full">
                    <p class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">
                        <i style="display: inline-flex"
                            class=" p-3 mr-3 text-blue-500 bg-blue-100 rounded-full text-lg">
                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ticket-detailed-fill" viewBox="0 0 16 16">
  <path d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 0 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 1 0 0-3A.5.5 0 0 1 0 6zm4 1a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5m0 5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5M4 8a1 1 0 0 0 1 1h6a1 1 0 1 0 0-2H5a1 1 0 0 0-1 1"/>
</svg>
                        </i>COTAS PREMIADAS
                    </p>
                    <form action="" id="filter-form">

                        <table>
                            <tbody>
                                <tr>
                                    <td style="width: 50%">
                                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Selecione
                                            a
                                            rifa</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%">
                                        <select name="raffleb" id="raffle"
                                            class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">xxx
                                            <option value="">Selecione</option>
                                            <?php
                                            $qry = $conn->query("SELECT * FROM `product_list` WHERE `cotas_premiadas` <> '' AND `cotas_premiadas` IS NOT NULL");
                                            while ($row = $qry->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>" <?php if ($raffle == $row['id']) {
                                                                                        echo 'selected';
                                                                                    } ?>><?= $row['name'] ?>
                                                </option>

                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td style="width: 10px;"></td>

                                    <td style="width: 100%">
                                        <button
                                            class="px-4 py-2 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple filtrar">
                                            Consultar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </form>

                    <!-- Mantenha o cabeçalho da tabela -->
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-2 py-3">Cota</th>
                                <th class="px-2 py-3">Status</th>
                                <th class="px-2 py-3">Comprador</th>
                                <th style="text-align: center" class="px-2 py-3">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            <?php
                            if ($raffleb) {
                                $requests = $conn->query("SELECT * FROM `product_list` WHERE `id` = {$raffleb} AND `cotas_premiadas` <> '' AND `cotas_premiadas` IS NOT NULL");
                                $row = $requests->fetch_assoc();

                                if ($row) {
                                    $cotas = explode(',', $row['cotas_premiadas']);
                                    $whereClauses = [];
                                    foreach ($cotas as $cota) {
                                        $cota = trim($cota);
                                        $whereClauses[] = "FIND_IN_SET('$cota', order_numbers)";
                                    }

                                    $whereClause = implode(' OR ', $whereClauses);

                                    // Modifique a query para incluir todos os campos necessários
                                    $query = "SELECT 
                    o.order_numbers, 
                    o.product_name, 
                    o.customer_id, 
                    o.product_id, 
                    o.quantity, 
                    o.order_token,  -- Certifique-se de selecionar o order_token
                    o.total_amount, 
                    c.firstname, 
                    c.lastname, 
                    c.phone 
                    FROM order_list o 
                    INNER JOIN customer_list c ON c.id = o.customer_id 
                    WHERE ($whereClause) AND o.product_id = {$raffleb} AND o.status = 2";

                                    $results = $conn->query($query);

                                    if ($results && $results->num_rows > 0) {
                                        $orders = [];
                                        while ($order = $results->fetch_assoc()) {
                                            $numbers = explode(',', $order['order_numbers']);
                                            foreach ($cotas as $cota) {
                                                $cota = trim($cota);
                                                if (in_array($cota, $numbers)) {
                                                    $order['cota'] = $cota;
                                                    $orders[$cota] = $order;
                                                }
                                            }
                                        }
                                    }

                                    foreach ($cotas as $value) {
                                        $value = trim($value);
                                        $status = 'Disponível';
                                        $name = '-----------';
                                        $phone = '';
                                        $product_name = '';
                                        $order_token = '';  // Inicialize o token

                                        if (isset($orders[$value])) {
                                            $status = 'Comprada';
                                            $name = $orders[$value]['firstname'] . ' ' . $orders[$value]['lastname'];
                                            $phone = $orders[$value]['phone'];
                                            $product_name = $orders[$value]['product_name'];
                                            $order_token = $orders[$value]['order_token'];  // Pegue o token específico deste pedido
                                        }
                            ?>
                                        <tr class="text-gray-700 dark:text-gray-400">
                                            <td class="px-2 py-3 text-sm" style="vertical-align:middle !important">
                                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full dark:text-gray-100 dark:bg-gray-700">
                                                    <?= $value ?>
                                                </span>
                                            </td>
                                            <td class="px-2 py-3 text-sm" style="vertical-align:middle !important">
                                                <span class="px-2 py-1 text-xs font-semibold leading-tight <?= $status == 'Comprada' ? 'text-red-700 bg-red-100 dark:text-red-100 dark:bg-red-700' : 'text-green-700 bg-green-100 dark:text-green-100 dark:bg-green-700' ?> rounded-full">
                                                    ● <?= $status ?>
                                                </span>
                                            </td>
                                            <td class="px-2 py-3 text-sm">
                                                <div class="trespontos2">
                                                    <?= $name ?>
                                                </div>
                                                <spam class="text-xs text-gray-500">
                                                    <?= $phone != '' ? formatPhoneNumber($phone) : '' ?>
                                                </spam>
                                            </td>
                                            <td class="px-2 py-3 text-sm" style="text-align: center; vertical-align:middle !important">
                                                <?php if ($status == 'Comprada'): ?>
                                                    <a href="https://api.whatsapp.com/send?phone=55<?= $phone ?>&amp;text=Parabéns, <?= $name ?> !%0A%0AVocê encontrou a cota premiada *<?= $value ?>*, na ação <?= $product_name ?>." target="_blank">
                                                        <button title="Whatsapp">
                                                            <i class="btwp-new-icon" style="font-size:22px;">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                                    <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                                                </svg>
                                                            </i>
                                                        </button>
                                                    </a>
                                                    <a class="ml-2" href="/compra/<?= $order_token ?>" target="_blank">
                                                        <button title="Ver Pedido">
                                                            <i class="btwp-new-icon" style="font-size:22px;">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                                                </svg>
                                                            </i>
                                                        </button>
                                                    </a>
                                                <?php else: ?>
                                                    <span>---</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                            <?php
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>

                    </table>


                </div>
            </div>
 <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800" style="display: flex; align-items: flex-start;">
                <div>
                    <p class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">
                        <i style="display: inline-flex" class="far fa-clock  p-3 mr-3 text-green-700 bg-green-100 rounded-full text-lg"></i>
                        HORA PREMIADA
                    </p>
                    <form action="" id="buscar-hora-premiada">
                        <table style="width:100%">
                            <tbody>
                                <tr>
                                    <td>
                                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Selecione
                                            a rifa</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td><select name="raffle" id="raffle"
                                            class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                            <option value="">Selecione</option>
                                            <?php
                                            $qry = $conn->query("SELECT * FROM `product_list` ");
                                            while ($row = $qry->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>" <?php if ($raffle == $row['id']) {
                                                                                        echo 'selected';
                                                                                    } ?>><?= $row['name'] ?>
                                                </option>

                                            <?php } ?>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td>
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <p
                                                            class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                                            Data inicial</p>
                                                    </td>
                                                    <td>
                                                        <p
                                                            class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                                            Data final</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" placeholder="dd/mm/aaaa --:--"
                                                            readonly="true"
                                                            onfocus="this.removeAttribute('readonly');this.type='datetime-local';this.setAttribute('onfocus','');this.blur();this.focus();"
                                                            name="start_date" id="start_date"
                                                            value="<?php echo $start_date; ?>"
                                                            class="space block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    </td>
                                                    <td>
                                                        <input type="text" placeholder="dd/mm/aaaa --:--"
                                                            readonly="true"
                                                            onfocus="this.removeAttribute('readonly');this.type='datetime-local';this.setAttribute('onfocus','');this.blur();this.focus();"
                                                            name="end_date" id="end_date"
                                                            value="<?php echo $end_date; ?>"
                                                            class="space block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    </td>
                                                </tr>


                                            </tbody>
                                        </table>

                                    </td>
                                </tr>
                                <tr>
                                    <td>

                                        <table style="width:100%" class="mt-2">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <p
                                                            class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                                            A partir de</p>
                                                    </td>

                                                    <td> </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" placeholder="10.00"
                                                            name="valor_minimo" id="valor_minimo"
                                                            value="<?php echo $valor_minimo; ?>"
                                                            class="space block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    </td>

                                                    <td>
                                                        <button id="maior_menor_cota"
                                                            class="ml-2 px-4 py-2 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple filtrar">Buscar</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </form>
                    <div
                        class="mb-2 mt-4 text-sm font-medium text-gray-600 dark:text-gray-400 winner-info results-container2">
                        <span id="h_name2"></span>
                        <span id="h_raffle2"></span>
                        <span id="h_date2"></span>
                        <span id="h_payment_status2"></span>
                        <span id="h_total_amount2"></span>
                        <span id="h_number2"></span>
                        <span id="h_date_updated2"></span>
                        <span id="h_whatsapp_msg2"></span>
                        <span class="h_winner2"></span>
                    </div>
                </div>

            </div>
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
                style="display: flex; align-items: flex-start;">

                <div>
                    <p class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">
                        <i style="display: inline-flex"
                            class="fa-solid fa-arrow-up-arrow-down p-3 mr-3 text-red-700 bg-red-100 rounded-full text-lg">

                        </i>
                        MAIOR E MENOR COTA
                    </p>
                    <form action="" id="buscar-ganhador-menor-maior-cota">
                        <table style="width:100%">
                            <tbody>
                                <tr>
                                    <td>
                                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Selecione
                                            a rifa</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td><select name="raffle" id="raffle"
                                            class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                            <option value="">Selecione</option>
                                            <?php
                                            $qry = $conn->query("SELECT * FROM `product_list` ");
                                            while ($row = $qry->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>" <?php if ($raffle == $row['id']) {
                                                                                        echo 'selected';
                                                                                    } ?>><?= $row['name'] ?>
                                                </option>

                                            <?php } ?>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td>
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <p
                                                            class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                                            Data inicial</p>
                                                    </td>
                                                    <td>
                                                        <p
                                                            class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                                            Data final</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" placeholder="dd/mm/aaaa --:--"
                                                            readonly="true"
                                                            onfocus="this.removeAttribute('readonly');this.type='datetime-local';this.setAttribute('onfocus','');this.blur();this.focus();"
                                                            name="start_date" id="start_date"
                                                            value="<?php echo $start_date; ?>"
                                                            class="space block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    </td>
                                                    <td>
                                                        <input type="text" placeholder="dd/mm/aaaa --:--"
                                                            readonly="true"
                                                            onfocus="this.removeAttribute('readonly');this.type='datetime-local';this.setAttribute('onfocus','');this.blur();this.focus();"
                                                            name="end_date" id="end_date"
                                                            value="<?php echo $end_date; ?>"
                                                            class="space block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                </tr>
                                <tr>
                                    <td>

                                        <table style="width:100%" class="mt-2">
                                            <tbody>
                                                <tr>
                                                    <td style="width:75%"><select name="search_type" id="search_type"
                                                            class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                                            <option <?php if ($search_type == 1) {
                                                                        echo 'selected';
                                                                    } ?> value="1">Menor cota
                                                            </option>
                                                            <option <?php if ($search_type == 2) {
                                                                        echo 'selected';
                                                                    } ?> value="2">Maior cota
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <button id="maior_menor_cota"
                                                            class="ml-2 px-4 py-2 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple filtrar">Buscar</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </form>
                    <div
                        class="mb-2 mt-4 text-sm font-medium text-gray-600 dark:text-gray-400 winner-info results-container2">
                        <span id="name2"></span>
                        <span id="raffle2"></span>
                        <span id="date2"></span>
                        <span id="payment_status2"></span>
                        
                        <span id="number2"></span>
                        <span id="date_updated2"></span>
                        <span id="whatsapp_msg2"></span>
                        <span class="winner2"></span>
                    </div>
                </div>

            </div>


            <div class="items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
                style="display: flex; align-items: flex-start;">

                <div class="w-full">
                    <p class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">
                        <i style="display: inline-flex"
                            class=" p-3 mr-3 text-blue-500 bg-blue-100 rounded-full text-lg">
                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-vinyl-fill" viewBox="0 0 16 16">
  <path d="M8 6a2 2 0 1 0 0 4 2 2 0 0 0 0-4m0 3a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M4 8a4 4 0 1 0 8 0 4 4 0 0 0-8 0"/>
</svg>
                        </i>ROLETAS
                    </p>
                    <form action="" id="filter-form">

                        <table>
                            <tbody>
                                <tr>
                                    <td style="width: 50%">
                                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Selecione
                                            a
                                            rifa</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%">
                                        <select name="rafflec" id="raffle"
                                            class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">xxx
                                            <option value="">Selecione</option>
                                            <?php
                                            $qry = $conn->query("SELECT * FROM `product_list` WHERE `cotas_premiadas_roleta` <> '' AND `cotas_premiadas_roleta` IS NOT NULL");
                                            while ($row = $qry->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>" <?php if ($raffle == $row['id']) {
                                                                                        echo 'selected';
                                                                                    } ?>><?= $row['name'] ?>
                                                </option>

                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td style="width: 10px;"></td>

                                    <td style="width: 100%">
                                        <button
                                            class="px-4 py-2 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple filtrar">
                                            Consultar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>

                    <!-- Mantenha o cabeçalho da tabela -->
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-2 py-3">Titulos</th>
                                <th class="px-2 py-3">Status</th>
                                <th class="px-2 py-3">Comprador</th>
                                <th style="text-align: center" class="px-2 py-3">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            <?php
                            if ($rafflec) {
                                $requests = $conn->query("SELECT * FROM `product_list` WHERE `id` = {$rafflec} AND `cotas_premiadas_roleta` <> '' AND `cotas_premiadas_roleta` IS NOT NULL");
                                $row = $requests->fetch_assoc();

                                if ($row) {
                                    $cotas = explode(',', $row['cotas_premiadas_roleta']);
                                    $whereClauses = [];
                                    foreach ($cotas as $cota) {
                                        $cota = trim($cota);
                                        $whereClauses[] = "FIND_IN_SET('$cota', order_numbers)";
                                    }

                                    $whereClause = implode(' OR ', $whereClauses);

                                    // Modifique a query para incluir todos os campos necessários
                                    $query = "SELECT 
                                    o.order_numbers, 
                                    o.product_name, 
                                    o.customer_id, 
                                    o.product_id, 
                                    o.quantity, 
                                    o.order_token,  -- Certifique-se de selecionar o order_token
                                    o.total_amount, 
                                    c.firstname, 
                                    c.lastname, 
                                    c.phone 
                                    FROM order_list o 
                                    INNER JOIN customer_list c ON c.id = o.customer_id 
                                    WHERE ($whereClause) AND o.product_id = {$rafflec} AND o.status = 2";

                                    $results = $conn->query($query);

                                    if ($results && $results->num_rows > 0) {
                                        $orders = [];
                                        while ($order = $results->fetch_assoc()) {
                                            $numbers = explode(',', $order['order_numbers']);
                                            foreach ($cotas as $cota) {
                                                $cota = trim($cota);
                                                if (in_array($cota, $numbers)) {
                                                    $order['cota'] = $cota;
                                                    $orders[$cota] = $order;
                                                }
                                            }
                                        }
                                    }

                                    foreach ($cotas as $value) {
                                        $value = trim($value);
                                        $status = 'Disponível';
                                        $name = '-----------';
                                        $phone = '';
                                        $product_name = '';
                                        $order_token = '';  // Inicialize o token

                                        if (isset($orders[$value])) {
                                            $status = 'Comprada';
                                            $name = $orders[$value]['firstname'] . ' ' . $orders[$value]['lastname'];
                                            $phone = $orders[$value]['phone'];
                                            $product_name = $orders[$value]['product_name'];
                                            $order_token = $orders[$value]['order_token'];  // Pegue o token específico deste pedido
                                        }
                            ?>
                                        <tr class="text-gray-700 dark:text-gray-400">
                                            <td class="px-2 py-3 text-sm" style="vertical-align:middle !important">
                                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full dark:text-gray-100 dark:bg-gray-700">
                                                    <?= $value ?>
                                                </span>
                                            </td>
                                            <td class="px-2 py-3 text-sm" style="vertical-align:middle !important">
                                                <span class="px-2 py-1 text-xs font-semibold leading-tight <?= $status == 'Comprada' ? 'text-red-700 bg-red-100 dark:text-red-100 dark:bg-red-700' : 'text-green-700 bg-green-100 dark:text-green-100 dark:bg-green-700' ?> rounded-full">
                                                    ● <?= $status ?>
                                                </span>
                                            </td>
                                            <td class="px-2 py-3 text-sm">
                                                <div class="trespontos2">
                                                    <?= $name ?>
                                                </div>
                                                <spam class="text-xs text-gray-500">
                                                    <?= $phone != '' ? formatPhoneNumber($phone) : '' ?>
                                                </spam>
                                            </td>
                                            <td class="px-2 py-3 text-sm" style="text-align: center; vertical-align:middle !important">
                                                <?php if ($status == 'Comprada'): ?>
                                                    <a href="https://api.whatsapp.com/send?phone=55<?= $phone ?>&amp;text=Parabéns, <?= $name ?> !%0A%0AVocê encontrou a cota premiada *<?= $value ?>*, na ação <?= $product_name ?>." target="_blank">
                                                        <button title="Whatsapp">
                                                            <i class="btwp-new-icon" style="font-size:22px;">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                                    <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                                                </svg>
                                                            </i>
                                                        </button>
                                                    </a>
                                                    <a class="ml-2" href="/compra/<?= $order_token ?>" target="_blank">
                                                        <button title="Ver Pedido">
                                                            <i class="btwp-new-icon" style="font-size:22px;">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                                                </svg>
                                                            </i>
                                                        </button>
                                                    </a>
                                                <?php else: ?>
                                                    <span>---</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                            <?php
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>





                </div>
                <!-- Busca Ganhador x Ranking -->

            </div>
            <div class="items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800"
                style="display: flex; align-items: flex-start;">

                <div class="w-full">
                    <p class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">
                        <i style="display: inline-flex"
                            class=" p-3 mr-3 text-blue-500 bg-blue-100 rounded-full text-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-gift" viewBox="0 0 16 16">
                                <path
                                    d="M3 2.5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1 5 0v.006c0 .07 0 .27-.038.494H15a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 1 14.5V7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2.038A3 3 0 0 1 3 2.506zm1.068.5H7v-.5a1.5 1.5 0 1 0-3 0c0 .085.002.274.045.43zM9 3h2.932l.023-.07c.043-.156.045-.345.045-.43a1.5 1.5 0 0 0-3 0zM1 4v2h6V4zm8 0v2h6V4zm5 3H9v8h4.5a.5.5 0 0 0 .5-.5zm-7 8V7H2v7.5a.5.5 0 0 0 .5.5z" />
                            </svg>
                        </i>CAIXINHAS
                    </p>
                    <form action="" id="filter-form">

                        <table>
                            <tbody>
                                <tr>
                                    <td style="width: 50%">
                                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Selecione
                                            a
                                            rifa</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%">
                                        <select name="raffled" id="raffle"
                                            class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">xxx
                                            <option value="">Selecione</option>
                                            <?php
                                            $qry = $conn->query("SELECT * FROM `product_list` WHERE `cotas_premiadas_box` <> '' AND `cotas_premiadas_box` IS NOT NULL");
                                            while ($row = $qry->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>" <?php if ($raffle == $row['id']) {
                                                                                        echo 'selected';
                                                                                    } ?>><?= $row['name'] ?>
                                                </option>

                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td style="width: 10px;"></td>

                                    <td style="width: 100%">
                                        <button
                                            class="px-4 py-2 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple filtrar">
                                            Consultar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>

                    <!-- Mantenha o cabeçalho da tabela -->
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-2 py-3">Titulos</th>
                                <th class="px-2 py-3">Status</th>
                                <th class="px-2 py-3">Comprador</th>
                                <th style="text-align: center" class="px-2 py-3">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            <?php
                            if ($raffled) {
                                $requests = $conn->query("SELECT * FROM `product_list` WHERE `id` = {$raffled} AND `cotas_premiadas_box` <> '' AND `cotas_premiadas_box` IS NOT NULL");
                                $row = $requests->fetch_assoc();

                                if ($row) {
                                    $cotas = explode(',', $row['cotas_premiadas_box']);
                                    $whereClauses = [];
                                    foreach ($cotas as $cota) {
                                        $cota = trim($cota);
                                        $whereClauses[] = "FIND_IN_SET('$cota', order_numbers)";
                                    }

                                    $whereClause = implode(' OR ', $whereClauses);

                                    // Modifique a query para incluir todos os campos necessários
                                    $query = "SELECT 
                                    o.order_numbers, 
                                    o.product_name, 
                                    o.customer_id, 
                                    o.product_id, 
                                    o.quantity, 
                                    o.order_token,  -- Certifique-se de selecionar o order_token
                                    o.total_amount, 
                                    c.firstname, 
                                    c.lastname, 
                                    c.phone 
                                    FROM order_list o 
                                    INNER JOIN customer_list c ON c.id = o.customer_id 
                                    WHERE ($whereClause) AND o.product_id = {$raffled} AND o.status = 2";

                                    $results = $conn->query($query);

                                    if ($results && $results->num_rows > 0) {
                                        $orders = [];
                                        while ($order = $results->fetch_assoc()) {
                                            $numbers = explode(',', $order['order_numbers']);
                                            foreach ($cotas as $cota) {
                                                $cota = trim($cota);
                                                if (in_array($cota, $numbers)) {
                                                    $order['cota'] = $cota;
                                                    $orders[$cota] = $order;
                                                }
                                            }
                                        }
                                    }

                                    foreach ($cotas as $value) {
                                        $value = trim($value);
                                        $status = 'Disponível';
                                        $name = '-----------';
                                        $phone = '';
                                        $product_name = '';
                                        $order_token = '';  // Inicialize o token

                                        if (isset($orders[$value])) {
                                            $status = 'Comprada';
                                            $name = $orders[$value]['firstname'] . ' ' . $orders[$value]['lastname'];
                                            $phone = $orders[$value]['phone'];
                                            $product_name = $orders[$value]['product_name'];
                                            $order_token = $orders[$value]['order_token'];  // Pegue o token específico deste pedido
                                        }
                            ?>
                                        <tr class="text-gray-700 dark:text-gray-400">
                                            <td class="px-2 py-3 text-sm" style="vertical-align:middle !important">
                                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full dark:text-gray-100 dark:bg-gray-700">
                                                    <?= $value ?>
                                                </span>
                                            </td>
                                            <td class="px-2 py-3 text-sm" style="vertical-align:middle !important">
                                                <span class="px-2 py-1 text-xs font-semibold leading-tight <?= $status == 'Comprada' ? 'text-red-700 bg-red-100 dark:text-red-100 dark:bg-red-700' : 'text-green-700 bg-green-100 dark:text-green-100 dark:bg-green-700' ?> rounded-full">
                                                    ● <?= $status ?>
                                                </span>
                                            </td>
                                            <td class="px-2 py-3 text-sm">
                                                <div class="trespontos2">
                                                    <?= $name ?>
                                                </div>
                                                <spam class="text-xs text-gray-500">
                                                    <?= $phone != '' ? formatPhoneNumber($phone) : '' ?>
                                                </spam>
                                            </td>
                                            <td class="px-2 py-3 text-sm" style="text-align: center; vertical-align:middle !important">
                                                <?php if ($status == 'Comprada'): ?>
                                                    <a href="https://api.whatsapp.com/send?phone=55<?= $phone ?>&amp;text=Parabéns, <?= $name ?> !%0A%0AVocê encontrou a cota premiada *<?= $value ?>*, na ação <?= $product_name ?>." target="_blank">
                                                        <button title="Whatsapp">
                                                            <i class="btwp-new-icon" style="font-size:22px;">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                                    <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                                                </svg>
                                                            </i>
                                                        </button>
                                                    </a>
                                                    <a class="ml-2" href="/compra/<?= $order_token ?>" target="_blank">
                                                        <button title="Ver Pedido">
                                                            <i class="btwp-new-icon" style="font-size:22px;">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                                                </svg>
                                                            </i>
                                                        </button>
                                                    </a>
                                                <?php else: ?>
                                                    <span>---</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                            <?php
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>





                </div>
                <!-- Busca Ganhador x Ranking -->

            </div>








        </div>
        <!-- Busca Ganhador x Ranking -->


    </div>

    <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
        style="display: none;">
        <!-- Modal -->
        <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 transform translate-y-1/2" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0  transform translate-y-1/2" @click.away="closeModal"
            @keydown.escape="closeModal"
            class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl"
            role="dialog" id="modal" style="display: none;">
            <!-- Remove header if you don't want a close icon. Use modal body to place modal tile. -->
            <header class="flex justify-end">
                <button
                    class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover: hover:text-gray-700"
                    aria-label="close" @click="closeModal">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" role="img" aria-hidden="true">
                        <path
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" fill-rule="evenodd"></path>
                    </svg>
                </button>
            </header>
            <div class="">
                <p class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
                    Salvar ganhador?
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-400">
                    Você realmente deseja salvar o ganhador no sorteio?
                </p><br>
                <div class="flex items-center bg-yellow rounded-lg mb-2 p-2">
                    <div class="mr-3">
                        <i class="" style="font-size:34px">
                            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="white"
                                class="bi bi-info-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                                <path
                                    d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0" />
                            </svg>
                        </i>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-400">
                        Após salvar o ganhador o sorteio será marcado como concluído.
                    </div>
                </div>



            </div>
            <footer
                class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
                <button @click="closeModal"
                    class="close-modal w-full px-5 py-3 text-sm font-medium leading-5 text-white bg-red transition-colors duration-150 border border-transparent rounded-lg dark:text-white sm:px-4 sm:py-2 sm:w-auto active:bg-red focus:border-transparent active:text-white focus:outline-none focus:shadow-outline-red">
                    Não
                </button>
                <button id="save_winner"
                    class="delete_data w-full px-5 py-3 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg sm:w-auto sm:px-4 sm:py-2 active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Sim
                </button>
            </footer>
        </div>
    </div>
</main>



<script>
    function hideView() {
        var x = document.getElementById("hide-view");
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }

    $(document).ready(function() {

        $('#get-ganhador').on('click', function() {
            $("#error").empty()

            if ($('#raffle').val() == '') {
                alert('Selecione uma campanha');
                return false;

            }
            if ($('#number').val() == '') {
                alert('Digite um número');
                return false;

            }

            $('#buscar-ganhador').submit();
        });
        $('#buscar-ganhador').submit(function(e) {
            e.preventDefault()
            $('.results-container').addClass('hidden')
            $('#overlay').css('display', 'block')
            $("#error").empty().css('display', 'none')


            $.ajax({
                url: _BASE_URL_ + "class/Main.php?action=search_raffle_winner",
                method: 'POST',
                type: 'POST',
                data: new FormData($(this)[0]),
                dataType: 'json',
                cache: false,
                processData: false,
                contentType: false,
                error: err => {
                    console.log(err)


                },
                success: function(resp) {
                    if (resp.status == 'success' && resp.name) {
                        var whats_number = resp.phone.replace(/\D/g, '');
                        
                        console.log(resp);
                        $('#pedido').html('<strong>Campanha:</strong> ' + resp
                            .product_name);
                        $('#name').html('<strong>Nome:</strong> ' + resp.name);
                        $('#date').html('<strong>Data da compra:</strong> ' + resp.date);
                        $('#number').html('<strong>Número/Bicho:</strong> ' + resp.number);
                        $('#payment_date').html('<strong>Data do pagamento:</strong> ' +
                            resp.payment_date);
                        $('#set-winner').attr('data-id', $('#raffle').val()).attr(
                            'data-number', resp.number).attr('data-winner', resp.phone);

                        $('#payment_status').html('<strong>Status:</strong> ' + resp
                            .payment_status);
                        $('#overlay').css('display', 'none')
                        $(".results-container").removeClass('hidden')
                        $("#btn-whats").attr('href',
                            'https://api.whatsapp.com/send?phone=55' + whats_number +
                            '&text=Olá, ' + resp.name +
                            ', você foi o ganhador da campanha ' + resp.product_name +
                            ' com o número ' + resp.number + '! Parabéns!');




                    } else {
                        $('#overlay').css('display', 'none')
                        $('#name').html('');
                        $('#phone').html('');
                        $('#date').html('');
                        $('#number').html('');
                        $('#payment_status').html('');
                        $("#error").html('Vencedor não encontrado, tente outro número').css(
                            'display', 'block');
                        $("#span-whats").addClass('hidden').removeClass('flex');
                        $('#winner_info').addClass('hidden')


                        console.log(resp)
                    }
                }
            })
        })

        $('#save_winner').on('click', function() {
            $('#overlay').css('display', 'block')
            var id = $('#set-winner').attr('data-id');
            var draw_number = $('#set-winner').attr('data-number');
            var draw_winner = $('#set-winner').attr('data-winner');



            $.ajax({
                url: _BASE_URL_ + "class/Main.php?action=save_raffle_winner",
                method: 'POST',
                type: 'POST',
                data: {
                    id: id,
                    draw_number: draw_number,
                    draw_winner: draw_winner
                },
                dataType: 'json',
                error: err => {
                    console.log(err)
                },
                success: function(resp) {
                    if (resp.status == 'success') {
                        $('#overlay').css('display', 'none')
                        $('.close-modal').click();
                        alert('Ganhador salvo com sucesso');

                    } else {
                        $('#overlay').css('display', 'none')
                        $('#error').html('Erro ao salvar ganhador').css('display', 'block');


                    }
                }
            })

        })

        $('#buscar-ganhador-menor-maior-cota').submit(function(e) {
            e.preventDefault();


            $('#overlay').fadeIn(300);
            $.ajax({
                url: _base_url_ +
                    "class/Main.php?action=search_raffle_smallest_and_largest_number",
                method: 'POST',
                type: 'POST',
                data: new FormData($(this)[0]),
                dataType: 'json',
                cache: false,
                processData: false,
                contentType: false,
                error: function(err) {
                    console.log(err);
                    alert('An error occurred');
                },
                success: function(resp) {
                    console.log(resp)
                    if (resp.status == 'success') {
                        $('.result-container2').remove();
                        $('#name2, #phone2, #date2, #number2, #payment_status2, #whatsapp_msg2, #date_updated2').html('');

                        var selectedValue = $('#search_type').val();
                        console.log(selectedValue)
                        if (selectedValue == 1) {
                            // Acesse major ou minor diretamente
                            var result = resp.minor; // ou resp.minor, dependendo de qual você deseja mostrar

                            $('#name2').append('<strong>Nome:</strong> <span class="uppercase">' + result.name + '</span><br>');
                            $('#date2').append('<strong>Data da compra:</strong> ' + result.date + '<br>');
                            $('#number2').append('<strong>Número:</strong> ' + result.cota + '<br>');
                            $('#payment_status2').append('<strong>Status:</strong> ' + result.payment_status + '<br>');

                            if (result.payment_status == 'Pago') {
                                var phone = result.phone;
                                var phoned = phone.replace(/\D/g, '');
                                var date_updated = result.date_updated;

                                $('#date_updated2').append('<strong>Data do pagamento:</strong> ' + result.date_updated + '<br>');
                                $('#whatsapp_msg2').html(
                                    '<div class="flex-container mt-4"><a href="https://api.whatsapp.com/send?phone=55' +
                                    phoned + '&text=' + result.msg_ganhador +
                                    '" target="_blank"><div class="btwp-new mr-3 flex items-center justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 border border-transparent rounded-lg focus:outline-none focus:shadow-outline-purple"><i class=" mr-2" style="font-size:18px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-whatsapp" viewBox="0 0 16 16"> <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" /></svg></i><span>Enviar mensagem</span></div></a></div>'
                                );
                            }

                            $('.winner2').html('');

                        } else if (selectedValue == 2) {
                            // Acesse major ou minor diretamente
                            var result = resp.major; // ou resp.minor, dependendo de qual você deseja mostrar

                            $('#name2').append('<strong>Nome:</strong> <span class="uppercase">' + result.name + '</span><br>');
                            $('#date2').append('<strong>Data da compra:</strong> ' + result.date + '<br>');
                            $('#number2').append('<strong>Número:</strong> ' + result.cota + '<br>');
                            $('#payment_status2').append('<strong>Status:</strong> ' + result.payment_status + '<br>');

                            if (result.payment_status == 'Pago') {
                                var phone = result.phone;
                                var phoned = phone.replace(/\D/g, '');
                                var date_updated = result.date_updated;

                                $('#date_updated2').append('<strong>Data do pagamento:</strong> ' + result.date_updated + '<br>');
                                $('#whatsapp_msg2').html(
                                    '<div class="flex-container mt-4"><a href="https://api.whatsapp.com/send?phone=55' +
                                    phoned + '&text=' + result.msg_ganhador +
                                    '" target="_blank"><div class="btwp-new mr-3 flex items-center justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 border border-transparent rounded-lg focus:outline-none focus:shadow-outline-purple"><i class=" mr-2" style="font-size:18px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-whatsapp" viewBox="0 0 16 16"> <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" /></svg></i><span>Enviar mensagem</span></div></a></div>'
                                );
                            }

                            $('.winner2').html('');
                        }

                    } else {
                        // No result found case
                        $('#name2').html('');
                        $('#date2').html('');
                        $('#number2').html('');
                        $('#payment_status2').html('');
                        $('#date_updated2').html('');
                        $('#whatsapp_msg2').html('');
                        $('#setWinner').html('');
                        $('.winner2').html('<div class="rounded-lg p-4 mb-2 text-sm leading-tight text-orange-700 bg-orange-100 dark:text-white dark:bg-orange-600"> Nenhum ganhador encontrado!<br>O número ou bicho sorteado não foi comprado, portanto, não há ganhador neste sorteio.</div>');
                    }

                    $('#overlay').fadeOut(300);
                }
            });
        });
        $('#buscar-hora-premiada').submit(function(e) {
            e.preventDefault();


            $('#overlay').fadeIn(300);
            $.ajax({
                url: _base_url_ +
                    "class/Main.php?action=buscar_hora_premiada",
                method: 'POST',
                type: 'POST',
                data: new FormData($(this)[0]),
                dataType: 'json',
                cache: false,
                processData: false,
                contentType: false,
                error: function(err) {
                    console.log(err);
                    alert('An error occurred');
                },
                success: function(resp) {
                    console.log(resp)
                    if (resp.status == 'success') {
                        $('.result-container2').remove();
                        $('#h_name2, #h_phone2, #h_date2, #h_number2, #h_payment_status2, #h_whatsapp_msg2, #h_date_updated2, #h_total_amount2').html('');

                        var selectedValue = $('#search_type').val();
                        // Acesse major ou minor diretamente

                        $('#h_name2').append('<strong>Nome:</strong> <span class="uppercase">' + resp.name + '</span><br>');
                        $('#h_date2').append('<strong>Data da compra:</strong> ' + resp.date + '<br>');
                        $('#h_number2').append('<strong>Número:</strong> ' + resp.cota + '<br>');
                        $('#h_payment_status2').append('<strong>Status:</strong> ' + resp.payment_status + '<br>');
                        $('#h_total_amount2').append('<strong>Valor:</strong>R$ ' + resp.total_amount + '<br>');

                        if (resp.payment_status == 'Pago') {
                            var phone = resp.phone;
                            var phoned = phone.replace(/\D/g, '');
                            var date_updated = resp.date_updated;

                            $('#h_date_updated2').append('<strong>Data do pagamento:</strong> ' + resp.date_updated + '<br>');
                            $('#h_whatsapp_msg2').html(
                                '<div class="flex-container mt-4"><a href="https://api.whatsapp.com/send?phone=55' +
                                phoned + '&text=' + resp.msg_ganhador +
                                '" target="_blank"><div class="btwp-new mr-3 flex items-center justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 border border-transparent rounded-lg focus:outline-none focus:shadow-outline-purple"><i class=" mr-2" style="font-size:18px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-whatsapp" viewBox="0 0 16 16"> <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" /></svg></i><span>Enviar mensagem</span></div></a></div>'
                            );
                        }

                        $('.h_winner2').html('');



                    } else {
                        // No result found case
                        $('#h_name2').html('');
                        $('#h_date2').html('');
                        $('#h_number2').html('');
                        $('#h_payment_status2').html('');
                        $('#h_date_updated2').html('');
                        $('#h_total_amount2').html('');
                        $('#h_whatsapp_msg2').html('');
                        $('#h_setWinner').html('');
                        $('.h_winner2').html('<div class="rounded-lg p-4 mb-2 text-sm leading-tight text-orange-700 bg-orange-100 dark:text-white dark:bg-orange-600"> Nenhum ganhador encontrado!<br>O número ou bicho sorteado não foi comprado, portanto, não há ganhador neste sorteio.</div>');
                    }

                    $('#overlay').fadeOut(300);
                }
            });
        });

    })
</script>