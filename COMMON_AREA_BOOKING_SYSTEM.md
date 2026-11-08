# Sistema de Agendamento de Áreas Comuns - Por Dia da Semana

## Visão Geral

O sistema de agendamento de áreas comuns foi redesenhado para oferecer máxima flexibilidade, permitindo configurar diferentes tipos de reserva para cada dia da semana.

## Tipos de Reserva

### 1. **Por Hora (Hourly)**
- Reservas baseadas em tempo mínimo e máximo configurados
- Slots gerados automaticamente baseados no `min_booking_hours`
- Preço calculado por hora × duração da reserva
- Ideal para áreas que precisam de flexibilidade de horário

### 2. **Por Período (Period)**
- Reservas baseadas em períodos pré-definidos:
  - **Manhã**: 08:00 - 12:00 (4 horas)
  - **Tarde**: 13:00 - 17:00 (4 horas)
  - **Noite**: 18:00 - 22:00 (4 horas)
  - **Dia Todo**: 08:00 - 22:00 (14 horas)
- Preços fixos por período
- Ideal para áreas com horários padronizados

## Configuração por Dia da Semana

### Exemplo de Configuração

```json
{
  "booking_schedule": {
    "0": { "type": "period", "enabled": true },   // Domingo - Por Período
    "1": { "type": "hourly", "enabled": false },  // Segunda - Fechado
    "2": { "type": "period", "enabled": true },   // Terça - Por Período
    "3": { "type": "period", "enabled": true },   // Quarta - Por Período
    "4": { "type": "period", "enabled": true },   // Quinta - Por Período
    "5": { "type": "period", "enabled": true },   // Sexta - Por Período
    "6": { "type": "hourly", "enabled": true }    // Sábado - Por Hora
  }
}
```

### Cenários de Uso

#### **Cenário 1: Academia**
- **Segunda a Sexta**: Por período (manhã, tarde, noite)
- **Sábado e Domingo**: Por hora (mais flexibilidade)
- **Segunda**: Fechado para manutenção

#### **Cenário 2: Salão de Festas**
- **Todos os dias**: Por período (dia todo)
- **Preço fixo**: R$ 200,00 por dia

#### **Cenário 3: Piscina**
- **Segunda a Sexta**: Por hora (1h mínimo, 4h máximo)
- **Fins de semana**: Por período (manhã, tarde)
- **Segunda**: Fechado para limpeza

## Interface de Configuração

### Formulário de Área Comum

1. **Horários de Funcionamento**
   - Configurar disponibilidade por dia
   - Definir horários de abertura e fechamento

2. **Tipo de Reserva por Dia**
   - Habilitar/desabilitar cada dia
   - Escolher entre "Por Hora" ou "Por Período"

3. **Períodos Padrão** (quando aplicável)
   - Configurar horários dos períodos
   - Definir preços por período

4. **Configurações de Reserva**
   - Tempo mínimo e máximo (para reservas por hora)
   - Preço por hora
   - Requer aprovação

## Interface de Reserva

### Para Usuários

1. **Seleção de Data**
   - Calendário mostra disponibilidade
   - Indica tipo de reserva disponível

2. **Tipos de Reserva Disponíveis**
   - **Períodos**: Manhã, Tarde, Noite, Dia Todo
   - **Por Hora**: Slots de tempo configuráveis
   - **Personalizada**: Apenas se permitido

3. **Validação Automática**
   - Sistema verifica tipo correto para o dia
   - Mensagens de erro específicas

## API Endpoints

### Obter Slots Disponíveis
```http
GET /api/common-areas/{id}/available-slots?date=2024-01-15
```

**Resposta:**
```json
{
  "date": "2024-01-15",
  "day_of_week": 1,
  "day_name": "Segunda",
  "booking_type": "period",
  "available_slots": [
    {
      "type": "period",
      "period_type": "morning",
      "name": "Manhã",
      "start_time": "2024-01-15 08:00:00",
      "end_time": "2024-01-15 12:00:00",
      "formatted_start": "08:00",
      "formatted_end": "12:00",
      "price": 40.00
    }
  ]
}
```

### Criar Reserva
```http
POST /api/common-areas/{id}/bookings
{
  "apartment_id": 123,
  "start_time": "2024-01-15 08:00:00",
  "end_time": "2024-01-15 12:00:00",
  "booking_type": "morning",
  "notes": "Aniversário"
}
```

## Validações

### Por Dia da Semana
- Sistema verifica se o dia está habilitado
- Valida tipo de reserva correto para o dia
- Mensagens de erro específicas por dia

### Por Tipo de Reserva
- **Período**: Apenas períodos configurados
- **Por Hora**: Dentro dos limites mínimo/máximo
- **Personalizada**: Apenas se `allow_custom_periods = true`

### Disponibilidade
- Verifica conflitos com reservas existentes
- Respeita horários de funcionamento
- Considera períodos de indisponibilidade

## Benefícios

1. **Flexibilidade Máxima**: Diferentes configurações por dia
2. **Simplicidade**: Interface intuitiva para usuários
3. **Controle**: Gestão granular de disponibilidade
4. **Automação**: Validações e cálculos automáticos
5. **Escalabilidade**: Fácil adaptação para diferentes tipos de área

## Exemplos de Configuração

### Academia
```json
{
  "booking_schedule": {
    "0": { "type": "hourly", "enabled": true },   // Domingo
    "1": { "type": "hourly", "enabled": false },  // Segunda (fechado)
    "2": { "type": "period", "enabled": true },   // Terça
    "3": { "type": "period", "enabled": true },   // Quarta
    "4": { "type": "period", "enabled": true },   // Quinta
    "5": { "type": "period", "enabled": true },   // Sexta
    "6": { "type": "hourly", "enabled": true }    // Sábado
  },
  "min_booking_hours": 1,
  "max_booking_hours": 4,
  "price_per_hour": 10.00
}
```

### Salão de Festas
```json
{
  "booking_schedule": {
    "0": { "type": "period", "enabled": true },   // Domingo
    "1": { "type": "period", "enabled": true },   // Segunda
    "2": { "type": "period", "enabled": true },   // Terça
    "3": { "type": "period", "enabled": true },   // Quarta
    "4": { "type": "period", "enabled": true },   // Quinta
    "5": { "type": "period", "enabled": true },   // Sexta
    "6": { "type": "period", "enabled": true }    // Sábado
  },
  "booking_periods": {
    "full_day": {
      "enabled": true,
      "start_time": "08:00",
      "end_time": "22:00",
      "price": 200.00
    }
  }
}
```

Este sistema oferece a flexibilidade necessária para atender às diferentes necessidades de cada condomínio e tipo de área comum. 