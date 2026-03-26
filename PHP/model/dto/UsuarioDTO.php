<?php
// Classe UsuarioDTO - Data Transfer Object
//Responsável por transportar os dados do usuário entre as camadas
class UsuarioDTO {

 // Atributos privados
 private $id_usuario;
 private $nome;
 private $email;
 private $senha;
 private $ativo;

 // Getters
 public function getIdUsuario() {
 return $this->id_usuario;
 }

 public function getNome() {
 return $this->nome;
 }

 public function getEmail() {
 return $this->email;
 }

 public function getSenha() {
 return $this->senha;
 }

 public function getAtivo() {
 return $this->ativo;
 }

 // Setters
 public function setIdUsuario($id_usuario) {
 $this->id_usuario = $id_usuario;
 }

 public function setNome($nome) {
 $this->nome = $nome;
 }

 public function setEmail($email) {
 $this->email = $email;
 }

 public function setSenha($senha) {
 $this->senha = $senha;
 }

 public function setAtivo($ativo) {
 $this->ativo = $ativo;
 }
}
?>