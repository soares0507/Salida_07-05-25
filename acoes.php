<?php
    session_start();
    require('conexao.php');

    // Botão do arquivo de login.php
    if (isset($_POST['login'])) {
        $cpf = mysqli_real_escape_string($conexao, trim($_POST['cpf']));
        // Remove caracteres especiais do CPF
        $cpf = str_replace(['-','.'],'',$cpf);
        $senha = mysqli_real_escape_string($conexao, trim($_POST['senha']));
        $tipo = mysqli_real_escape_string($conexao, trim($_POST['tipo']));

        // Verifica na tabela moderador
        $sqlModerador = "SELECT * FROM moderador WHERE cpf = '$cpf' AND senha = '$senha'";
        $resultModerador = mysqli_query($conexao, $sqlModerador);
        if (mysqli_num_rows($resultModerador) > 0) {
            $_SESSION['tipo'] = 'moderador';
            $_SESSION['cpf'] = $cpf;
            header('Location: pendentes.php'); // Redireciona para o painel do moderador
            exit();
        }

        // Verifica na tabela responsavel
        $sqlResponsavel = "SELECT * FROM responsavel WHERE cpf = '$cpf' AND senha = '$senha'";
        $resultResponsavel = mysqli_query($conexao, $sqlResponsavel);
        if (mysqli_num_rows($resultResponsavel) > 0) {
            $_SESSION['tipo'] = 'responsavel';
            $_SESSION['cpf'] = $cpf;
            header('Location: responsavel.php'); // Redireciona para o painel do responsável
            exit();
        }

          // Verifica na tabela porteiro
        $sqlResponsavel = "SELECT * FROM portaria WHERE cpf = '$cpf' AND senha = '$senha'";
        $resultResponsavel = mysqli_query($conexao, $sqlResponsavel);
        if (mysqli_num_rows($resultResponsavel) > 0) {
            $_SESSION['tipo'] = 'portaria';
            $_SESSION['cpf'] = $cpf;
            header('Location: porteiro.php'); // Redireciona para o painel do porteiro
            exit();
        }

        // Caso as credenciais estejam incorretas
        $_SESSION['login_erro'] = "Credenciais inválidas. Verifique seu CPF e senha.";
        header('Location: login.php'); // Redireciona para a página de login
        exit();
    }

    // Ação do moderador de confirmar uma requisição
    if (isset($_POST['confirmar_requisicao'])) {
        $id = mysqli_real_escape_string($conexao, ($_POST['id_requisicao']));
        $sql = "UPDATE requisicao SET estado = 'confirmado' WHERE id = '$id'";
        mysqli_query($conexao, $sql);
        header('Location: pendentes.php');
    }
    
    // Botão do modal da justificativa
    if (isset($_POST['enviar'])) {
        $just = mysqli_real_escape_string($conexao, ($_POST['justificativa']));
        $id = mysqli_real_escape_string($conexao, ($_POST['id_requisicao']));
        $sql = "UPDATE requisicao SET estado = 'recusado', justificativa = '$just' WHERE id = '$id'";
        mysqli_query($conexao, $sql);
        header('Location: pendentes.php');
    }
    // Acao do responsavel de gerar requisicao
    if(isset($_POST['gerar_requisicao'])){
        $data_saida = mysqli_real_escape_string($conexao, $_POST['data_saida']);
        $data_retorno = mysqli_real_escape_string($conexao, $_POST['data_retorno']);
        if(empty($data_retorno)){
            $data_retorno = null;
        }
        $id_responsavel_aluno = mysqli_real_escape_string($conexao, $_POST['id_aluno']);
        $sql = "INSERT INTO requisicao (id_responsavel_aluno, data_saida_agendada, data_retorno_agendada, estado) VALUES ('$id_responsavel_aluno', '$data_saida', '$data_retorno', 'pendente')";
        mysqli_query($conexao, $sql);
        header('Location: responsavel.php');
    }

    if(isset($_POST['cadastrar'])){
            //Cadastra o cpf do responsavel no sql
        $cpf = mysqli_real_escape_string($conexao, trim($_POST['cpf']));
        $cpf = str_replace(['-','.'],'',$cpf);
        $senha = mysqli_real_escape_string($conexao, trim($_POST['senha']));
        $nome = mysqli_real_escape_string($conexao, trim($_POST['nome']));
        $tipo = mysqli_real_escape_string($conexao, trim($_POST['tipo']));
        $telefone = mysqli_real_escape_string($conexao, trim($_POST['telefone']));

        if($tipo == 'responsavel'){
            $sqlModerador = "INSERT INTO responsavel (cpf, senha, nome, telefone) VALUES ('$cpf', '$senha', '$nome', '$telefone')";
            $resultModerador = mysqli_query($conexao, $sqlModerador);
            header('Location: cadastro.php');
        }
        else if($tipo == 'moderador'){
            $sqlModerador = "INSERT INTO moderador (cpf, senha, nome, telefone) VALUES ('$cpf', '$senha', '$nome', '$telefone')";
            $resultModerador = mysqli_query($conexao, $sqlModerador);
            header('Location: cadastro.php');
        }

        else{
            $_SESSION['cadastro_erro'] = "Prencha Todos os Campos!!";
            header('Location: cadastro.php');
        }
    }

          // Registrar saída
    if (isset($_POST['registrar_saida'])) {
        $id_requisicao = mysqli_real_escape_string($conexao, $_POST['id_requisicao']);
        $sql_check = "SELECT data_saida FROM requisicao WHERE id = '$id_requisicao'";
        $result_check = mysqli_query($conexao, $sql_check);
        $row = mysqli_fetch_assoc($result_check);

        if ($row['data_saida'] === null) {
            $data_saida = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');
            $sql = "UPDATE requisicao SET data_saida = '$data_saida' WHERE id = '$id_requisicao'";
            mysqli_query($conexao, $sql);
        }
        header('Location: porteiro.php');
        exit();
    }

    // Registrar retorno
    if (isset($_POST['registrar_retorno'])) {
        $id_requisicao = mysqli_real_escape_string($conexao, $_POST['id_requisicao']);
        $sql_check = "SELECT data_retorno FROM requisicao WHERE id = '$id_requisicao'";
        $result_check = mysqli_query($conexao, $sql_check);
        $row = mysqli_fetch_assoc($result_check);

        if ($row['data_retorno'] === null) {
            $data_retorno = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');
            $sql = "UPDATE requisicao SET data_retorno = '$data_retorno', estado = 'concluido' WHERE id = '$id_requisicao'";
            mysqli_query($conexao, $sql);
        }
        header('Location: porteiro.php');
        exit();
    }

    // Não vai retornar
    if (isset($_POST['nao_vai_retornar'])) {
        $id_requisicao = mysqli_real_escape_string($conexao, $_POST['id_requisicao']);
        $sql = "UPDATE requisicao SET estado = 'concluido' WHERE id = '$id_requisicao'";
        mysqli_query($conexao, $sql);
        header('Location: porteiro.php');
        exit();
    }
?>
