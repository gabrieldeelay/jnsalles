<?php
// Define o código de resposta HTTP 404
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página não encontrada - Erro 404</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            color: #333;
            text-align: center;
            padding: 60px;
        }
        h1 {
            font-size: 72px;
            color: #dc3545;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        a {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>404</h1>
    <h2>Página não encontrada</h2>
    <p>Desculpe, a página que você está procurando não existe ou foi movida.</p>
    <a href="/">Voltar para a página inicial</a>
</body>
</html>
