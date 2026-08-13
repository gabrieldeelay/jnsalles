<?php

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT * FROM `order_list` WHERE id = '" . $_GET['id'] . "'");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    }
}
?>

<style>
    .order_numbers {
        padding: 10px;
        max-width: 150px;
        white-space: nowrap;
        overflow: auto;
    }
</style>

<main class="h-full pb-16 overflow-y-auto">
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            #<?php echo isset($id) ? $id : ''; ?> Detalhes
        </h2>

        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">Pedido:</span>
                <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="#<?php echo isset($id) ? $id : ''; ?>" disabled/>
            </label>

            <label class="block mt-4 text-sm">
                <span class="text-gray-700 dark:text-gray-400">Status</span>
                <select name="order_status" id="order_status" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                    <?php
                    $status = isset($status) ? $status : '';
                    switch ($status) {
                        case 1:
                            echo '<option value="1" selected>Pendente</option>';
                            echo '<option value="2">Pago</option>';
                            echo '<option value="3">Cancelado</option>';
                            break;
                        case 2:
                            echo '<option value="1">Pendente</option>';
                            echo '<option value="2" selected>Pago</option>';
                            echo '<option value="3">Cancelado</option>';
                            break;
                        case 3:
                            echo '<option value="1">Pendente</option>';
                            echo '<option value="2">Pago</option>';
                            echo '<option value="3" selected>Cancelado</option>';
                            break;
                    }
                    ?>
                </select>
            </label>

            <?php
            $gt = 0;
            $order_items = $conn->query("
                SELECT oi.*, p.name AS product, p.price, p.image_path, p.type_of_draw, ol.order_numbers, ol.quantity AS order_quantity, ol.discount_amount
                FROM `order_items` oi
                INNER JOIN product_list p ON oi.product_id = p.id
                INNER JOIN order_list ol ON oi.order_id = ol.id
                WHERE oi.order_id = '" . $id . "'
            ");
            $order_total = $conn->query("SELECT total_amount FROM `order_list` WHERE `id` = '" . $id . "'");
            $total = $order_total->fetch_assoc();

            while ($row = $order_items->fetch_assoc()) {
                $gt += $row['price'] * $row['order_quantity'];
            ?>
                <label class="block mt-4 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Campanha</span>
                    <input name="price" id="price" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?php echo $row['product']; ?>" disabled/>
                </label>

                <label class="block mt-4 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Quantidade de cotas</span>
                    <input name="price" id="price" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="<?php echo $row['order_quantity']; ?>" disabled/>
                </label>

                <label class="block mt-4 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Valor da cota</span>
                    <input name="price" id="price" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="R$ <?php echo format_num($row['price'], 2); ?>" disabled/>
                </label>

                <?php
                if ($row['discount_amount']) {
                    $subtotal = $total['total_amount'] + $row['discount_amount'];
                    $subtotal = format_num($subtotal, 2);
                ?>
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Subtotal</span>
                        <input name="price" id="price" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="R$ <?php echo $subtotal; ?>" disabled/>
                    </label>
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Desconto</span>
                        <input name="price" id="price" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="R$ <?php echo format_num($row['discount_amount'], 2); ?>" disabled/>
                    </label>
                <?php } ?>

                <label class="block mt-4 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Total</span>
                    <input name="price" id="price" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" value="R$ <?php echo format_num($total['total_amount'], 2); ?>" disabled/>
                </label>

                <label class="block mt-4 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Cotas</span>
                    <textarea class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" rows="3" placeholder="Descrição da campanha" disabled><?php
                        $type_of_draw = $row['type_of_draw'];
                        if ($type_of_draw > 2) {
                            $order_numbers = drope_format_luck_numbers($row['order_numbers'], $row['quantity'], false, true, $type_of_draw);
                            echo str_replace('<span class="comma-hide">', '', $order_numbers);
                        } else {
                            $order_numbers = drope_format_luck_numbers($row['order_numbers'], $row['quantity'], false, true, $type_of_draw);
                            echo str_replace('<span class="comma-hide">', '', $order_numbers);
                        }
                        ?>
                    </textarea>
                </label>
            <?php } ?>
        </div>
    </div>
</main>

<script>
    $(function() {
        $('#delete_data').click(function() {
            _conf("Are you sure to delete this order permanently?", "delete_order", ["<?php echo isset($id) ? $id : ''; ?>"])
        })
        $('#order_status').on('change', function() {
            let status = $('#order_status').val();
            update_order_status('<?php echo isset($id) ? $id : ''; ?>', status);
        })
    })

    function delete_order($id) {
        $.ajax({
            url: _base_url_ + "class/Main.php?action=delete_order",
            method: "POST",
            data: {
                id: $id
            },
            dataType: "json",
            error: err => {
                console.log(err)
                alert("[AO11] - An error occured.");
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.replace("./?page=orders");
                } else {
                    alert("[AO12] - An error occured.");
                }
            }
        })
    }

    function update_order_status($id, $status) {
        $.ajax({
            url: _base_url_ + "class/Main.php?action=update_order_status_sys",
            method: "POST",
            data: {
                id: $id,
                status: $status
            },
            dataType: "json",
            error: err => {
                console.log(err)
                alert("[AO13] - An error occured.");
            },
            success: function(resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.reload();
                } else {
                    alert("[AO14] - An error occured.");
                }
            }
        })
    }
</script>
