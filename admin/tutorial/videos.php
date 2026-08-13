<?php
// Conexão com o banco
$conn = new mysqli("localhost", "usuario", "senha", "nome_do_banco");
if ($conn->connect_error) die("Erro de conexão: " . $conn->connect_error);

// Busca os vídeos
$result = $conn->query("SELECT * FROM videos");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Área de Membros</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

  <div class="container mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6 text-center">Área de Membros</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="bg-white p-4 rounded-lg shadow cursor-pointer" onclick="openVideo('https://www.youtube.com/embed/<?= $row['youtube_id'] ?>')">
          <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($row['titulo']) ?></h2>
          <p class="text-gray-600"><?= htmlspecialchars($row['descricao']) ?></p>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Modal -->
  <div id="videoModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg overflow-hidden max-w-3xl w-full relative">
      <button onclick="closeVideo()" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-xl font-bold">&times;</button>
      <div class="w-full h-0 pb-[56.25%] relative">
        <iframe id="videoFrame" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0" allowfullscreen></iframe>
      </div>
    </div>
  </div>

  <script>
    function openVideo(videoUrl) {
      document.getElementById('videoFrame').src = videoUrl + '?autoplay=1';
      document.getElementById('videoModal').classList.remove('hidden');
    }

    function closeVideo() {
      document.getElementById('videoFrame').src = '';
      document.getElementById('videoModal').classList.add('hidden');
    }
  </script>

</body>
</html>
