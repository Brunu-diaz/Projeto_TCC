<?php
require_once 'Conexao.php';

class UsuarioDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getConexao();
    }

    public function cadastrarUsuario(UsuarioDTO $usuario)
    {
        try {
            $this->pdo->beginTransaction();
            // Inserção do usuário (Note que o status 'ativo' começa em 0 para o primeiro acesso)
            $sqlUser = "INSERT INTO usuario (nome, email, cpf_cnpj, telefone, id_perfil, ativo) VALUES (?, ?, ?, ?, ?, 0)";
            $stmtUser = $this->pdo->prepare($sqlUser);
            $stmtUser->execute([
                $usuario->getNome(),
                $usuario->getEmail(),
                $usuario->getCpfCnpj(),
                $usuario->getTelefone(),
                $usuario->getIdPerfil()
            ]);
            $idUsuario = $this->pdo->lastInsertId();

            // Inserção do login (senha deve vir criptografada do DTO ou controller)
            $sqlLogin = "INSERT INTO login (id_usuario, username, senha_hash) VALUES (?, ?, ?)";
            $stmtLogin = $this->pdo->prepare($sqlLogin);
            $stmtLogin->execute([$idUsuario, $usuario->getUsername(), $usuario->getSenha()]);

            // Inserção da Unidade
            $sqlUnidade = "INSERT INTO unidade (id_usuario, endereco, numero, bloco, complemento) VALUES (?, ?, ?, ?, ?)";
            $stmtUnidade = $this->pdo->prepare($sqlUnidade);
            $stmtUnidade->execute([
                $idUsuario,
                $usuario->getEndereco(),
                $usuario->getNumero(),
                $usuario->getBloco(),
                $usuario->getTipoUnidade()
            ]);

            $idUnidade = $this->pdo->lastInsertId();

            // Inserção do Hidrômetro
            $sqlHidro = "INSERT INTO hidrometro (id_unidade, codigo) VALUES (?, ?)";
            $stmtHidro = $this->pdo->prepare($sqlHidro);
            $stmtHidro->execute([$idUnidade, $usuario->getCodigoHidrometro()]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Erro ao cadastrar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * NOVO MÉTODO: Cadastra apenas a pessoa e o login.
     * Ideal para o fluxo onde o morador ainda não tem uma unidade vinculada.
     */
    public function cadastrarUsuarioIsolado(UsuarioDTO $usuario)
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Inserção do usuário (ativo = 0)
            $sqlUser = "INSERT INTO usuario (nome, email, cpf_cnpj, telefone, id_perfil, ativo, data_cadastro) 
                        VALUES (?, ?, ?, ?, ?, 0, NOW())";
            $stmtUser = $this->pdo->prepare($sqlUser);
            $stmtUser->execute([
                $usuario->getNome(),
                $usuario->getEmail(),
                $usuario->getCpfCnpj(),
                $usuario->getTelefone(),
                $usuario->getIdPerfil()
            ]);

            $idUsuario = $this->pdo->lastInsertId();

            // 2. Inserção do login
            $sqlLogin = "INSERT INTO login (id_usuario, username, senha_hash) VALUES (?, ?, ?)";
            $stmtLogin = $this->pdo->prepare($sqlLogin);
            $stmtLogin->execute([
                $idUsuario,
                $usuario->getUsername(),
                $usuario->getSenha()
            ]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Erro no cadastro isolado: " . $e->getMessage());
            return false;
        }
    }

    public function autenticar($username, $senha)
    {
        try {
            // AJUSTE AQUI: Adicionado u.id_perfil para a trava de segurança funcionar
            $sql = "SELECT l.id_usuario, l.username, l.senha_hash, u.nome, u.ativo, 
                       u.bloqueado, u.id_perfil, p.nome as nome_perfil 
                FROM login l
                INNER JOIN usuario u ON l.id_usuario = u.id_usuario
                LEFT JOIN perfil p ON u.id_perfil = p.id_perfil
                WHERE l.username = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$username]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dados && password_verify($senha, $dados['senha_hash'])) {
                return $dados;
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            return null;
        }
    }

    public function alternarBloqueio($id_usuario, $statusBloqueio)
    {
        try {
            // statusBloqueio: 1 para Bloqueado, 0 para Desbloqueado
            $sql = "UPDATE usuario SET bloqueado = ? WHERE id_usuario = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$statusBloqueio, $id_usuario]);
        } catch (PDOException $e) {
            error_log("Erro ao alternar bloqueio do usuário: " . $e->getMessage());
            return false;
        }
    }

    public function ativarConta($id_usuario, $novo_login, $nova_senha)
    {
        try {
            $this->pdo->beginTransaction();
            $senhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);

            $sqlLogin = "UPDATE login SET username = ?, senha_hash = ? WHERE id_usuario = ?";
            $stmt1 = $this->pdo->prepare($sqlLogin);
            $stmt1->execute([$novo_login, $senhaHash, $id_usuario]);

            $sqlUser = "UPDATE usuario SET ativo = 1 WHERE id_usuario = ?";
            $stmt2 = $this->pdo->prepare($sqlUser);
            $stmt2->execute([$id_usuario]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function alternarStatus($id_usuario, $status)
    {
        try {
            // Status 1 = Ativo, Status 0 = Inativo
            $sql = "UPDATE usuario SET ativo = ? WHERE id_usuario = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$status, $id_usuario]);
        } catch (PDOException $e) {
            error_log("Erro ao alternar status do usuário: " . $e->getMessage());
            return false;
        }
    }

    public function listarUsuarios($busca = null)
    {
        try {
            // SQL Base
            $sql = "SELECT u.*, p.nome AS nome_perfil, 
                    DATE_FORMAT(u.data_cadastro, '%d/%m/%Y %H:%i') AS data_formatada 
                    FROM usuario u
                    LEFT JOIN perfil p ON u.id_perfil = p.id_perfil";

            // Se houver uma busca, adicionamos a cláusula WHERE
            if ($busca) {
                $sql .= " WHERE u.nome LIKE :busca 
                          OR u.email LIKE :busca 
                          OR u.cpf_cnpj LIKE :busca";
            }

            $sql .= " ORDER BY u.data_cadastro DESC";

            $stmt = $this->pdo->prepare($sql);

            if ($busca) {
                // O sinal de % permite que a busca encontre o termo em qualquer parte do texto
                $termo = "%$busca%";
                $stmt->bindValue(':busca', $termo);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Atualiza os dados do perfil do cliente (MedidaCerta)
     * Inclui a lógica para atualizar a foto apenas se uma nova for enviada.
     */
    public function atualizarPerfil($id, $dto, $nomeFoto)
    {
        try {
            // Início da query base
            $sql = "UPDATE usuario SET nome = :nome, email = :email, telefone = :telefone";

            // 1. Lógica dinâmica para a coluna 'foto'
            if ($nomeFoto === null) {
                // Caso o botão de remover (lixo) tenha sido clicado
                $sql .= ", foto = NULL";
            } elseif ($nomeFoto !== "manter") {
                // Caso uma nova foto tenha sido enviada (upload)
                $sql .= ", foto = :foto";
            }
            // Se for "manter", a coluna foto simplesmente não entra no UPDATE, preservando a atual.

            $sql .= " WHERE id_usuario = :id";

            $stmt = $this->pdo->prepare($sql);

            // Binds obrigatórios
            $stmt->bindValue(':nome', $dto->getNome());
            $stmt->bindValue(':email', $dto->getEmail());
            $stmt->bindValue(':telefone', $dto->getTelefone());
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // 2. Bind condicional apenas se houver uma nova foto
            if ($nomeFoto !== null && $nomeFoto !== "manter") {
                // Se você estiver salvando o caminho/nome do arquivo (como 'perfil_1.jpg')
                $stmt->bindValue(':foto', $nomeFoto, PDO::PARAM_STR);

                /* NOTA: Se o seu banco usa MEDIUMBLOB para salvar o arquivo direto no banco, 
               use: $stmt->bindValue(':foto', $nomeFoto, PDO::PARAM_LOB); 
            */
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            // Em desenvolvimento, use error_log($e->getMessage()) para debugar
            return false;
        }
    }

    public function buscarUsuarioPorId($id)
    {
        // Note que usamos u.id_perfil (tabela usuario) em vez de l.id_perfil
        $sql = "SELECT u.id_usuario, u.nome, u.email, u.telefone, u.cpf_cnpj, u.foto, u.id_perfil, 
                   l.username 
            FROM usuario u 
            INNER JOIN login l ON u.id_usuario = l.id_usuario 
            WHERE u.id_usuario = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Isso ajudará a identificar se houver outro erro de sintaxe
            die("Erro no Banco de Dados: " . $e->getMessage());
        }
    }

    public function buscarPorId($id)
    {
        return $this->buscarUsuarioPorId($id);
    }
}