<?php

if (isset($_GET['id']) && $_GET['id'] > 0 && is_numeric($_GET['id'])) {
    $qry = $conn->query('SELECT r.*, c.* FROM referral r INNER JOIN customer_list c ON c.id = r.customer_id WHERE r.id = \'' . $_GET['id'] . '\'');

    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    }
}
?>

<style>
    #cimg {
        max-width: 100%;
        max-height: 25em;
        object-fit: scale-down;
        object-position: center center;
    }
</style>

<main class="h-full pb-16 overflow-y-auto">
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            <?= isset($id) && is_numeric($id) ? 'Editar afiliado' : 'Novo afiliado' ?>
            <a href="./?page=affiliates">
                <button class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Voltar
                </button>
            </a>
        </h2>

        <div class="px-4 py-3 mb-2 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <form action="" id="manage-order" autocomplete="off">
                <?php if ($id && is_numeric($id)): ?>
                    <label class="block text-sm mb-2">
                        <span class="text-gray-700 dark:text-gray-400">Nome</span>
                        <input name="name" id="name" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Nome do afiliado" disabled value="<?= isset($firstname) ? $firstname . ' ' . $lastname : '' ?>" />
                    </label>
                <?php endif; ?>

                <label class="block text-sm mb-2">
                    <span class="text-gray-700 dark:text-gray-400">Telefone</span>
                    <input name="customer" id="customer" type="number" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Informe o telefone do afiliado" value="<?= isset($phone) ? $phone : '' ?>" />
                </label>

                <label class="block mt-4 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Comissão</span>
                    <input name="percentage" id="percentage" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10" value="<?= isset($percentage) ? $percentage : '' ?>" />
                </label>

                <label class="block mt-4 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Status</span>
                    <select name="status" id="status" class="mr-2 block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                        <option value="1" <?= isset($status) && $status == '1' ? 'selected' : '' ?>>Ativo</option>
                        <option value="0" <?= isset($status) && $status == '0' ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </label>
            </form>
        </div>

        <div class="mt-2">
            <button form="manage-order" class="px-5 py-3 font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                <?= $id && is_numeric($id) ? 'Atualizar' : 'Cadastrar' ?>
            </button>
        </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
<script>
    $('#manage-order').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: _base_url_ + 'class/Main.php?action=create_affiliate',
           
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            error: function(err) {
                console.log(err);
            },
            
            success: function(resp) {
                var returnedData = JSON.parse(resp);
                if (returnedData && !returnedData.status) {
                    alert('Erro ao cadastrar afiliado');
                    return;
                }
                if (returnedData.status == 'success') {
                    alert(returnedData.msg);
                    location.href = '/admin/?page=affiliates'
                } else if (returnedData.status == 'failed') {
                    alert(returnedData.msg);
                } else {
                    console.log(resp);
                    alert("[CP01] - Erro ao cadastrar pagamento");
                }
            }
        });
    });
</script>
