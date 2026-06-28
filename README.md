💧 Medida Certa - Sistema de Gestão e Monitoramento de Consumo de Água em Condomínios

O Medida Certa é uma plataforma web full-stack desenvolvida para digitalizar, automatizar e gerenciar a medição do consumo de água em complexos residenciais. O sistema substitui processos manuais e planilhas por uma solução robusta que garante auditoria, cálculos tarifários automáticos e transparência para administradores e clientes.

🚀 Principais Funcionalidades

🔸 Área do Administrador (Gestão e Governança)
- Lançamento de Leituras com Automação: Entrada de dados dos hidrômetros com cálculo automatizado de consumo e geração imediata de faturas em PDF;
- Rastreabilidade e Auditoria: Vinculo obrigatório do administrador responsável por cada lançamento hídrico, garantindo segurança na prestação de contas;
- Controle de Acesso Robusto (RBAC): Módulos protegidos por níveis de permissão com travas de segurança a nível de aplicação;
- Filtros Avançados: Busca inteligente de clientes e unidades integrando a biblioteca `TomSelect` para alta performance na interface.

🔸 Área do Cliente (Transparência)
- Dashboard de Consumo: Visualização limpa e intuitiva do histórico de consumo hídrico;
- Emissão de Faturas: Download e consulta detalhada das faturas geradas para a sua unidade comercial.

🛠️ Stack Tecnológica

O projeto foi construído seguindo o padrão arquitetural **MVC (Model-View-Controller)** para garantir separação de responsabilidades e facilidade de manutenção:

- Backend: PHP (PDO para persistência segura);
- Banco de Dados: MySQL (Modelagem relacional com integridade referencial e chaves estrangeiras);
- Frontend: HTML5, CSS3, JavaScript (ES6), Bootstrap (Design responsivo);
- Bibliotecas & Ferramentas: TomSelect (Otimização de inputs), VS Code, XAMPP, Astah Community (Modelagem UML).

🏗️ Arquitetura e Modelagem do Banco

O banco de dados foi planejado para mitigar falhas operacionais humanas. A relação entre `Unidade` e `Hidrometro` é estritamente 1:1 (One-to-One), prevenindo via restrição de integridade (`Integrity constraint violation`) que uma mesma loja possua duplicidade de dispositivos ativos.

🔧 Como Executar o Projeto Localmente

1 - Realize um git clone do repositório:

- [https://github.com/Brunu-diaz/Projeto_TCC.git]

2 - Configure o Ambiente:

- Mova a pasta do projeto para o diretório htdocs do seu XAMPP.
- Importe o arquivo do banco de dados no seu phpMyAdmin / MySQL Workbench.

3 - Acesse no navegador:

- http://localhost/Projeto_TCC

👤 Desenvolvedor

- Nome: Bruno da Silva Dias
- Linkedin: https://www.linkedin.com/in/bruno-dias-76983a1aa
- E-mail: brunudiaz@gmail.com
