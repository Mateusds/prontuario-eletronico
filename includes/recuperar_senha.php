<?php
session_start();
require 'config.php'; // Certifique-se de que o caminho para o config.php está correto
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Ajuste o caminho conforme necessário

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Verificar se o email existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Gerar token de recuperação
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Salvar token no banco
        $stmt = $pdo->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expira = ? WHERE id = ?");
        $stmt->execute([$token, $expira, $usuario['id']]);

        // Enviar email com o link de recuperação
        $link = "http://seusite.com/pages/redefinir_senha.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Exemplo para Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'mateusmarquesds@gmail.com'; // Seu email
            $mail->Password = '0103Mmds@@@@@'; // Sua senha ou senha de aplicativo
            $mail->SMTPSecure = 'tls'; // ou 'ssl'
            $mail->Port = 587; // ou 465 para SSL

            // Destinatários
            $mail->setFrom('no-reply@seusite.com', 'Nome do Site');
            $mail->addAddress($email);

            // Conteúdo do email
            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha';
            $mail->Body    = "Clique no link para redefinir sua senha: <a href='$link'>$link</a>";

            $mail->send();
            $_SESSION['success'] = "Um link de recuperação foi enviado para o seu email.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Falha ao enviar o email: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "Email não encontrado.";
    }
    header('Location: ../pages/recuperar_senha.php'); // Redireciona de volta para a página de recuperação
    exit();
}
?> 