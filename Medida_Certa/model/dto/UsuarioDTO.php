<?php
/**
 * UsuarioDTO - Objeto de Transferência de Dados para o sistema MedidaCerta
 */
class UsuarioDTO {
    // Dados Pessoais
    private $nome;
    private $email;
    private $cpf_cnpj;
    private $telefone;
    private $id_perfil;
    private $ativo;

    // Dados de Acesso
    private $username;
    private $senha;

    // Dados da Unidade e Hidrômetro
    private $tipo_unidade;
    private $endereco;
    private $numero;
    private $bloco;
    private $codigo_hidrometro;
    private $data_cadastro;

    // --- Getters e Setters ---

    public function setNome($nome) { $this->nome = $nome; }
    public function getNome() { return $this->nome; }

    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

    public function setCpfCnpj($cpf_cnpj) { $this->cpf_cnpj = $cpf_cnpj; }
    public function getCpfCnpj() { return $this->cpf_cnpj; }

    public function setTelefone($telefone) { $this->telefone = $telefone; }
    public function getTelefone() { return $this->telefone; }

    public function setUsername($username) { $this->username = $username; }
    public function getUsername() { return $this->username; }

    public function setSenha($senha) { $this->senha = $senha; }
    public function getSenha() { return $this->senha; }

    public function setIdPerfil($id_perfil) { $this->id_perfil = $id_perfil; }
    public function getIdPerfil() { return $this->id_perfil; }

    public function setAtivo($ativo) { $this->ativo = $ativo; }
    public function getAtivo() { return $this->ativo; }

    public function setTipoUnidade($tipo) { $this->tipo_unidade = $tipo; }
    public function getTipoUnidade() { return $this->tipo_unidade; }

    public function setEndereco($endereco) { $this->endereco = $endereco; }
    public function getEndereco() { return $this->endereco; }

    public function setNumero($numero) { $this->numero = $numero; }
    public function getNumero() { return $this->numero; }

    public function setBloco($bloco) { $this->bloco = $bloco; }
    public function getBloco() { return $this->bloco; }

    public function setCodigoHidrometro($codigo) { $this->codigo_hidrometro = $codigo; }
    public function getCodigoHidrometro() { return $this->codigo_hidrometro; }

    public function setDataCadastro($data) { $this->data_cadastro = $data; }
    public function getDataCadastro() { return $this->data_cadastro; }
}