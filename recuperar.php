<?php
session_start();

require 'vendor/autoload.php';

$conn = new mysqli("localhost", "root", "", "cadastro_db");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$etapa = 1;

if (isset($_POST["enviar_email"])) {
    $email = $_POST["email"];
    
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE login=? AND tipo='email'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        
        $codigo = rand(100000, 999999);
        $expira = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        
        $stmt = $conn->prepare("INSERT INTO recuperacao (login, codigo, expira_em) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $codigo, $expira);
        $stmt->execute();
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer();

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'SEU_EMAIL@gmail.com';
            $mail->Password = 'SENHA_DE_APP';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            $mail->setFrom('SEU_EMAIL@gmail.com', 'Sistema');
            $mail->addAddress($email);
            
            $mail->Subject = 'Recuperação de senha';
            $mail->Body = "Seu código é: $codigo";
            
            $mail->send();
            
            $_SESSION["email"] = $email;
            $mensagem = "Código enviado!";
            $etapa = 2;

        } catch (Exception $e) {
            $mensagem = "Erro ao enviar email.";
        }
        
    } else {
        $mensagem = "Email não encontrado.";
    }
}

if (isset($_POST["verificar_codigo"])) {
    $codigo = $_POST["codigo"];
    $email = $_SESSION["email"];
    
    $stmt = $conn->prepare("SELECT * FROM recuperacao WHERE login=? AND codigo=? AND expira_em > NOW()");
    $stmt->bind_param("ss", $email, $codigo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $etapa = 3;
    } else {
        $mensagem = "Código inválido ou expirado.";
        $etapa = 2;
    }
}

if (isset($_POST["nova_senha"])) {
    $email = $_SESSION["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE usuarios SET senha=? WHERE login=?");
    $stmt->bind_param("ss", $senha, $email);
    $stmt->execute();
    
    session_destroy();
    $mensagem = "Senha alterada com sucesso!";
    $etapa = 1;
}
?>