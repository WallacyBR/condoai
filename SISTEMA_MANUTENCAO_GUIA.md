# 🔧 **GUIA COMPLETO: Sistema de Manutenção**

## 📋 **PROBLEMAS CORRIGIDOS**

✅ **Botões "Novo Equipamento" e "Nova Área"** agora funcionam corretamente  
✅ **Formulários simplificados** usando apenas campos que existem no sistema  
✅ **APIs corrigidas** com endpoints corretos  
✅ **Fluxo de manutenção documentado** para facilitar o uso  

---

## 🏗️ **COMO FUNCIONA O SISTEMA**

### **1. Estrutura Hierárquica:**
```
🏢 Condomínio
├── 🏷️ Áreas de Manutenção (ex: Piscina, Elevadores)
│   └── ⚙️ Equipamentos (ex: Bomba da Piscina, Elevador 1)
│       └── 📅 Agendamentos (ex: Verificar bomba toda segunda)
│           └── 📝 Registros (quando a manutenção foi feita)
```

### **2. Fluxo de Trabalho:**

#### **Passo 1: Criar Áreas de Manutenção**
- Acesse: **Manutenção > Áreas de Manutenção**
- Clique em **"Nova Área"**
- Preencha: Nome e Descrição
- Exemplos: "Piscina", "Elevadores", "Jardim", "Portaria"

#### **Passo 2: Cadastrar Equipamentos**
- Acesse: **Manutenção > Equipamentos**
- Clique em **"Novo Equipamento"**
- Preencha:
  - **Nome**: Ex: "Bomba da Piscina", "Elevador 1"
  - **Área**: Selecione a área criada anteriormente
  - **Localização**: Ex: "Casa de máquinas, subsolo"
  - **Descrição**: Detalhes do equipamento
  - **Acesso QR**: Quem pode ver relatórios públicos

#### **Passo 3: Criar Agendamentos de Manutenção**
- Acesse: **Manutenção > Agendamentos**
- Clique em **"Novo Agendamento"**
- Configure:
  - **Título**: Ex: "Limpeza semanal da piscina"
  - **Equipamento**: Selecione o equipamento
  - **Tipo**: Diário, Semanal, Mensal, etc.
  - **Responsáveis**: Atribua usuários

#### **Passo 4: Registrar Execução**
- Quando a manutenção for feita, registre em **Registros**
- Pode incluir fotos e assinatura
- Status: Pendente, Concluída, Atrasada, etc.

---

## 👥 **COMO ATRIBUIR E CADASTRAR MANUTENÇÕES**

### **Atribuindo Responsáveis:**
1. No **Agendamento**, selecione os **"Responsáveis"**
2. Escolha usuários do condomínio (zeladores, porteiros, etc.)
3. Os responsáveis verão as tarefas em **"Minhas Tarefas Diárias"**

### **Cadastrando Nova Manutenção:**
1. **Método 1**: Criar agendamento recorrente (automático)
2. **Método 2**: Registrar manutenção avulsa em **Registros**

### **Acompanhando Status:**
- **Dashboard** mostra estatísticas gerais
- **Por Equipamento**: Vê status de cada item
- **Por Área**: Visão geral das áreas

---

## 📱 **RECURSOS ESPECIAIS**

### **QR Codes:**
- Cada equipamento gera um QR Code
- Permite acesso rápido a relatórios
- 3 níveis de acesso: Público, Usuários Internos, Específicos

### **Tipos de Agendamento:**
- **Diário**: Todo dia
- **Semanal**: Dias específicos da semana
- **Quinzenal**: A cada 15 dias
- **Mensal**: Todo mês no dia X
- **Semestral/Anual**: Períodos longos

### **Status de Registros:**
- **Pendente**: Aguardando execução
- **Concluída**: Manutenção realizada
- **Atrasada**: Passou do prazo
- **Em Revisão**: Sendo conferida
- **Reaberta**: Precisa de nova atenção

---

## 🎯 **EXEMPLO PRÁTICO**

### **Cenário: Manutenção de Piscina**

1. **Criar Área**: "Piscina"
2. **Cadastrar Equipamentos**:
   - "Bomba de Filtragem"
   - "Sistema de Aquecimento"
   - "Iluminação Subaquática"

3. **Criar Agendamentos**:
   - "Limpeza Diária" → Todo dia às 7h
   - "Verificação da Bomba" → Toda segunda às 9h
   - "Manutenção Geral" → Todo mês dia 15

4. **Atribuir Responsáveis**:
   - Zelador: Limpeza diária
   - Técnico: Verificação da bomba
   - Empresa terceirizada: Manutenção geral

5. **Registrar Execução**:
   - Zelador marca "Limpeza" como concluída
   - Adiciona fotos se necessário
   - Sistema mostra próxima data automaticamente

---

## ⚠️ **DICAS IMPORTANTES**

### **Organização:**
- Use nomes claros e padronizados
- Agrupe equipamentos por localização
- Mantenha descrições detalhadas

### **Agendamentos:**
- Configure horários realistas
- Atribua responsáveis corretos
- Use recorrências para tarefas regulares

### **Controle:**
- Monitore o dashboard regularmente
- Acompanhe equipamentos críticos
- Revise registros para qualidade

---

## 🚀 **PRÓXIMOS PASSOS**

1. **Comece criando 2-3 áreas principais**
2. **Cadastre equipamentos críticos primeiro**
3. **Configure agendamentos básicos**
4. **Treine a equipe no sistema**
5. **Expanda gradualmente**

---

## 📞 **SUPORTE**

Agora o sistema está funcionando corretamente! 

- ✅ Botões de criação funcionam
- ✅ Formulários simplificados e funcionais
- ✅ APIs corrigidas
- ✅ Fluxo documentado

**Teste o sistema seguindo este guia e reporte qualquer problema.** 