<?php


require_once '../settings.php';
require_once 'inc/header.php';


	$page = (isset($_GET['page']) ? $_GET['page'] : 'home');
	$adminRoute = $page;


$session = $_SESSION['userdata'];
if (($session['firstname'] == '') || $session['lastname'] == '' || $session['username'] == '' || $session['date_added'] == '') {
	exit();
}
if (!file_exists($page . '.php') && !is_dir($page)) {
	include 'pages/404.php';
	exit();
}
else if (is_dir($page)) {
	include $page . '/index.php';
}
else {
	include $page . '.php';
}

$adminPageGuides = [
	'home' => ['Visão geral do painel', 'Use os cartões para consultar resultados e premiações já registradas.', ['Buscar ganhador localiza quem recebeu a cota sorteada.', 'Cotas premiadas confere se os números especiais já foram vendidos.', 'Hora premiada e Maior/Menor consultam compras confirmadas no período informado.']],
	'products' => ['Campanhas', 'Crie, edite e acompanhe as campanhas que aparecem no site.', ['Use “Criar novo” para começar uma campanha.', 'O status controla se ela aceita compras.', 'A edição permite alterar imagens, valores, limites, ranking e premiações.']],
	'products/manage_product' => ['Editor de campanha', 'Preencha uma seção de cada vez. As explicações abaixo de cada campo mostram o efeito no site.', ['Dados define preço, cotas e limites.', 'Imagens controla a vitrine e a galeria.', 'Premiadas liga números específicos aos prêmios extras.']],
	'orders' => ['Pedidos', 'Consulte compras, pagamentos e cotas reservadas.', ['Os filtros podem ser combinados.', 'Pedido pendente ainda aguarda confirmação.', 'Pedido pago entra nos rankings e relatórios.']],
	'orders/create_order' => ['Pedido manual', 'Crie uma compra administrativa e confira as cotas exatas antes de salvar.', ['A prévia muda junto com a quantidade.', 'As cotas exibidas são as mesmas gravadas na confirmação.', 'Pedidos marcados como pagos entram no ranking.']],
	'report' => ['Relatórios', 'Acompanhe clientes, pedidos e faturamento dentro do período escolhido.', ['Combine campanha, status e forma de pagamento.', 'O faturamento considera somente pedidos confirmados.', 'Datas vazias usam o período padrão mostrado na tela.']],
	'draw' => ['Sortear', 'Realize uma apuração entre cotas de pedidos pagos e mantenha um histórico auditável.', ['Cada cota elegível possui o mesmo peso.', 'A mesma cota não pode vencer duas vezes na mesma campanha.', 'O telefone é censurado somente na exibição do resultado.']],
	'ranking' => ['Top compradores', 'Consulte o ranking e configure o contador visual exibido na campanha.', ['O contador não altera pagamentos.', 'Ao iniciar um novo período, a lista diária começa a contar daquele ponto.', 'Somente pedidos pagos somam cotas.']],
	'customers' => ['Clientes', 'Consulte e organize os compradores cadastrados.', ['A pesquisa aceita nome e telefone.', 'Importações devem respeitar os campos indicados na tela.', 'Pedidos antigos permanecem ligados ao cadastro.']],
	'affiliates' => ['Afiliados', 'Gerencie parceiros, links e pagamentos de comissão.', ['Cada afiliado possui um identificador próprio.', 'Confira o período antes de registrar um pagamento.', 'Excluir um acesso não altera pedidos existentes.']],
	'gateway' => ['Gateway de pagamento', 'Escolha somente um provedor ativo e salve as credenciais fornecidas por ele.', ['Desativar interrompe novas cobranças sem apagar as credenciais.', 'Mostrar/ocultar permite revisar os tokens salvos.', 'O webhook confirma o PIX e libera as cotas automaticamente.']],
	'system_info' => ['Configurações do site', 'Altere identidade, logo, textos públicos e informações de contato.', ['A logo também pode ser usada no painel.', 'Revise a prévia antes de salvar.', 'Campos públicos nunca devem conter senhas ou tokens.']],
	'admin_accounts' => ['Administradores', 'Controle quem pode entrar no painel.', ['A conta principal não pode ser excluída.', 'Cada pessoa deve usar seu próprio acesso.', 'A alteração de senha afeta somente a conta indicada.']],
	'user/list' => ['Usuários do painel', 'Consulte os acessos administrativos cadastrados.', ['Crie acessos individuais.', 'Revogue contas que não são mais usadas.', 'Não compartilhe a conta principal.']],
];
if (isset($adminPageGuides[$adminRoute])) {
	$guide = $adminPageGuides[$adminRoute];
	?>
	<style>.admin-context-guide{margin:0 0 18px;border:1px solid #334155;border-radius:13px;background:linear-gradient(135deg,rgba(30,41,59,.8),rgba(15,23,42,.92));color:#cbd5e1;box-shadow:0 10px 26px rgba(0,0,0,.11)}.admin-context-guide summary{display:flex;align-items:center;gap:10px;padding:13px 15px;cursor:pointer;color:#f8fafc;font-size:13px;font-weight:750;list-style:none}.admin-context-guide summary::-webkit-details-marker{display:none}.admin-context-guide summary:before{content:'?';display:grid;width:23px;height:23px;place-items:center;border-radius:7px;background:rgba(124,58,237,.3);color:#ddd6fe;font-weight:850}.admin-context-guide__body{padding:0 15px 14px 48px;color:#9fb0c6;font-size:12px;line-height:1.55}.admin-context-guide__body p{margin:0 0 7px}.admin-context-guide__body ul{display:grid;gap:3px;margin:0;padding-left:17px}@media(max-width:650px){.admin-context-guide__body{padding:0 13px 13px 13px}}</style>
	<template id="admin-context-guide-template"><details class="admin-context-guide" open><summary><?= htmlspecialchars($guide[0], ENT_QUOTES, 'UTF-8') ?></summary><div class="admin-context-guide__body"><p><?= htmlspecialchars($guide[1], ENT_QUOTES, 'UTF-8') ?></p><ul><?php foreach ($guide[2] as $tip): ?><li><?= htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div></details></template>
	<script>document.addEventListener('DOMContentLoaded',function(){var template=document.getElementById('admin-context-guide-template');var container=document.querySelector('main .container');if(template&&container&&!container.querySelector('.admin-context-guide'))container.insertBefore(template.content.cloneNode(true),container.firstChild);});</script>
	<?php
}

require_once 'inc/footer.php';

?>
