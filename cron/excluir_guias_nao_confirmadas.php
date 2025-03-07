<?php
require '../includes/config.php';

try {
    // Exclui as guias não confirmadas
    $stmt = $pdo->prepare("DELETE FROM guias WHERE status = 'aguardando' AND data_consulta < CURDATE()");
    $stmt->execute();

    echo "Guias não confirmadas excluídas com sucesso.";
} catch (PDOException $e) {
    echo "Erro ao excluir guias não confirmadas: " . $e->getMessage();
}
?> 