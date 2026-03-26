<?php
// Classe UsuarioDTO - Data Transfer Object
//Responsável por transportar os dados do usuário entre as camadas
class UsuarioDTO {

 // Atributos privados
 private $id;
 private $nome;
 private $email;
 private $senha;

 // Getters
 public function getId() {
 return $this->id;
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

 // Setters
 public function setId($id) {
 $this->id = $id;
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
}
?>