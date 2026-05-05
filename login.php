<?php
$conn = new mysqli("localhost", "root", "", "cadastro_db");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = preg_replace('/[^0-9a-zA-Z@.]/', '', $_POST["login"]);
    $senha = $_POST["senha"];

    $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($senha, $user["senha"])) {
            $mensagem = "Login realizado com sucesso!";
        } else {
            $mensagem = "Senha incorreta.";
        }
    } else {
        $mensagem = "Usuário não encontrado.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        * {margin:0;padding:0;box-sizing:border-box;font-family:Arial;}
        body {
            height:100vh;display:flex;justify-content:center;align-items:center;
            background: linear-gradient(135deg,#0f172a,#1e3a8a,#2563eb);
        }
        .container {
            background: rgba(255,255,255,0.08);
            padding:40px;border-radius:20px;width:320px;
            backdrop-filter: blur(10px);
            box-shadow:0 10px 30px rgba(0,0,0,0.4);
            text-align:center;
        }
        h2 {color:#fff;margin-bottom:20px;}
        input {
            width:100%;padding:12px;margin:10px 0;
            border:none;border-radius:10px;
            background: rgba(255,255,255,0.15);
            color:#fff;outline:none;
        }
        input::placeholder {color:#cbd5f5;}
        button {
            width:100%;padding:12px;margin-top:10px;
            border:none;border-radius:10px;
            background: linear-gradient(135deg,#3b82f6,#1d4ed8);
            color:#fff;cursor:pointer;
        }
        button:hover {transform:scale(1.05);}
        a {display:block;margin-top:15px;color:#93c5fd;text-decoration:none;}
        a:hover {color:#fff;}
        .msg {color:#fff;margin-top:10px;}
    </style>
</head>
<body>

<div class="container">
    <h2>Login</h2>

    <?php if (!empty($mensagem)) echo "<div class='msg'>$mensagem</div>"; ?>

    <form method="POST">
        <input type="text" name="login" placeholder="Email, telefone ou CPF" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>

    <a href="recuperar.php">Esqueci minha senha</a>
    <a href="cadastro.php">Ir para Cadastro</a>
</div>

</body>
</html>