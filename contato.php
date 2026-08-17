<?php
// Captura o nome do site da URL, se não houver, usa um valor padrão
$site = isset($_GET['site']) ? htmlspecialchars($_GET['site']) : 'meu site';

// Mensagem formatada para o WhatsApp
$mensagem = urlencode("Oi,Anilton, vim do site $site e tenho interesse no sistema de rifas.");

// Número do WhatsApp para onde será direcionado (substitua pelo seu número)
$numero_whatsapp = "5511916059141"; // Exemplo: 55 (código do Brasil) + DDD + número

// URL de redirecionamento para o WhatsApp
$whatsapp_url = "https://wa.me/$numero_whatsapp?text=$mensagem";

// Redireciona para o WhatsApp
header('Location: https://suportejnsalles.vercel.app/');
exit;
?>
