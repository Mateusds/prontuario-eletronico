<?php
function registrarLog($pdo, $usuario_id, $tipo_usuario, $acao) {
    $stmt = $pdo->prepare("INSERT INTO logs_acesso (usuario_id, tipo_usuario, acao) VALUES (?, ?, ?)");
    $stmt->execute([$usuario_id, $tipo_usuario, $acao]);
}

function enviarNotificacao($pdo, $usuario_id, $mensagem) {
    $stmt = $pdo->prepare("INSERT INTO notificacoes (usuario_id, mensagem) VALUES (?, ?)");
    $stmt->execute([$usuario_id, $mensagem]);
}
?>
