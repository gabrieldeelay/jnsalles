<?php


echo '<div class="container app-main">' . "\r\n" . '   <div class="app-title">' . "\r\n" . '      <h1><i class="icone bi bi-blockquote-right"></i> Termos de utilização </h1>' . "\r\n" . '      <div class="app-title-desc"></div>' . "\r\n" . '   </div>' . "\r\n" . '   <div>' . "\r\n" . '      ';
$terms = trim((string) $_settings->info('terms'));
echo $terms !== '' ? $terms : jnsalles_default_terms();
echo '   </div>' . "\r\n" . '</div>';

?>
