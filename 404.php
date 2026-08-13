<?php
// Define o código de resposta HTTP 404
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - Erro 404</title>
    <style>
        :root {
            --bg-color: #f8f9fa;
            --text-color: #333;
            --highlight: #dc3545;
            --button-bg: #007bff;
            --button-hover: #0056b3;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #121212;
                --text-color: #eee;
                --highlight: #ff6b6b;
                --button-bg: #1e88e5;
                --button-hover: #1565c0;
            }
        }

        body {
            background-color: var(--bg-color);
            font-family: Arial, sans-serif;
            color: var(--text-color);
            text-align: center;
            padding: 40px 20px;
        }

        h1 {
            font-size: 72px;
            color: var(--highlight);
            margin-bottom: 10px;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            margin-bottom: 30px;
        }

        a {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--button-bg);
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: var(--button-hover);
        }

        .img-404 {
            width: 100%;
            max-width: 350px;
            height: auto;
            object-fit: contain;
            margin: 30px auto;
            display: block;
        }

        @media (min-width: 768px) {
            h1 {
                font-size: 80px;
            }

            h2 {
                font-size: 32px;
            }

            p {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <h2>Desculpe, página não encontrada!</h2>
    <img src="/assets/img/404.png" alt="Erro 404" class="img-404">
    <p>Lamentamos, mas não conseguimos encontrar a página <br>
    que procura. Talvez você tenha digitado incorretamente o <br>URL? Verifique a ortografia.</p>
    <a href="/">Voltar para a página inicial</a>
</body>
</html>
