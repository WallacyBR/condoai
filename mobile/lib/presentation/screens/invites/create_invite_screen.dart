import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';

/// Tela funcional de Criação de Convite com Passe QR Code para Visitantes
class CreateInviteScreen extends StatefulWidget {
  const CreateInviteScreen({super.key});

  @override
  State<CreateInviteScreen> createState() => _CreateInviteScreenState();
}

class _CreateInviteScreenState extends State<CreateInviteScreen> {
  final _nameController = TextEditingController();
  final _plateController = TextEditingController();
  DateTime _selectedDate = DateTime.now();
  TimeOfDay _selectedTime = const TimeOfDay(hour: 14, minute: 0);
  String _selectedType = 'Visitante';
  bool _generated = false;
  String _inviteToken = '';

  @override
  void dispose() {
    _nameController.dispose();
    _plateController.dispose();
    super.dispose();
  }

  void _generateInvite() {
    if (_nameController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Por favor, informe o nome do visitante.'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    HapticFeedback.mediumImpact();
    setState(() {
      _inviteToken = 'PASS-${DateTime.now().millisecondsSinceEpoch.toString().substring(7)}';
      _generated = true;
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dateFormatter = DateFormat('dd/MM/yyyy');

    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: Icon(
            Icons.arrow_back_ios_new_rounded,
            color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
          ),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          _generated ? 'Passe de Acesso Gerado' : 'Novo Convite de Visitante',
          style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 10, 20, 40),
        child: _generated
            ? _buildGeneratedPass(isDark, dateFormatter)
            : _buildInviteForm(isDark, dateFormatter),
      ),
    );
  }

  Widget _buildInviteForm(bool isDark, DateFormat dateFormatter) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        FrostedGlassCard(
          borderRadius: 24,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Dados do Visitante',
                style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 14),

              // Nome
              TextField(
                controller: _nameController,
                style: TextStyle(
                  color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                ),
                decoration: InputDecoration(
                  labelText: 'Nome Completo',
                  hintText: 'Ex: Marcos Andrade',
                  prefixIcon: const Icon(Icons.person_outline_rounded),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 14),

              // Tipo
              DropdownButtonFormField<String>(
                initialValue: _selectedType,
                decoration: InputDecoration(
                  labelText: 'Tipo de Acesso',
                  prefixIcon: const Icon(Icons.category_outlined),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
                items: const [
                  DropdownMenuItem(value: 'Visitante', child: Text('Visitante / Amigo')),
                  DropdownMenuItem(value: 'Prestador', child: Text('Prestador de Serviço')),
                  DropdownMenuItem(value: 'Festa', child: Text('Convidado de Evento')),
                ],
                onChanged: (val) => setState(() => _selectedType = val!),
              ),
              const SizedBox(height: 14),

              // Placa do Veículo
              TextField(
                controller: _plateController,
                textCapitalization: TextCapitalization.characters,
                style: TextStyle(
                  color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                ),
                decoration: InputDecoration(
                  labelText: 'Placa do Veículo (Opcional)',
                  hintText: 'Ex: BRA2E19',
                  prefixIcon: const Icon(Icons.directions_car_outlined),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 16),

              // Data e Hora da Visita
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () async {
                        final picked = await showDatePicker(
                          context: context,
                          initialDate: _selectedDate,
                          firstDate: DateTime.now(),
                          lastDate: DateTime.now().add(const Duration(days: 30)),
                        );
                        if (picked != null) {
                          setState(() => _selectedDate = picked);
                        }
                      },
                      icon: const Icon(Icons.calendar_today_rounded, size: 16),
                      label: Text(dateFormatter.format(_selectedDate)),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () async {
                        final picked = await showTimePicker(
                          context: context,
                          initialTime: _selectedTime,
                        );
                        if (picked != null) {
                          setState(() => _selectedTime = picked);
                        }
                      },
                      icon: const Icon(Icons.access_time_rounded, size: 16),
                      label: Text(_selectedTime.format(context)),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),

        const SizedBox(height: 24),

        ElevatedButton.icon(
          onPressed: _generateInvite,
          icon: const Icon(Icons.qr_code_rounded, color: Colors.white),
          label: const Text(
            'Gerar Convite com QR Code',
            style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
          ),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          ),
        ),
      ],
    );
  }

  Widget _buildGeneratedPass(bool isDark, DateFormat dateFormatter) {
    final inviteMessage = 'Olá ${_nameController.text}! Seu passe de entrada para o Condomínio Residencial Jardins está pronto.\n'
        'Data: ${dateFormatter.format(_selectedDate)} às ${_selectedTime.format(context)}\n'
        'Código de Acesso: $_inviteToken\n'
        'Apresente o QR Code na portaria para liberação automática.';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        FrostedGlassCard(
          borderRadius: 28,
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.successLight,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'PASSE LIBERADO',
                  style: AppTextStyles.badge.copyWith(color: AppColors.success),
                ),
              ),
              const SizedBox(height: 12),
              Text(
                _nameController.text,
                style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
              ),
              Text(
                '$_selectedType • Apto 402 Bloco Primavera',
                style: AppTextStyles.bodyMedium.copyWith(
                  color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                ),
              ),
              const SizedBox(height: 20),

              // QR Code Real
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x1A000000),
                      blurRadius: 16,
                      offset: Offset(0, 6),
                    ),
                  ],
                ),
                child: QrImageView(
                  data: 'condoai:guest:$_inviteToken:${_nameController.text}',
                  version: QrVersions.auto,
                  size: 190.0,
                  backgroundColor: Colors.white,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'TOKEN: $_inviteToken',
                style: AppTextStyles.titleSmall.copyWith(
                  letterSpacing: 2,
                  fontWeight: FontWeight.w800,
                  color: AppColors.primary,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                'Válido para ${dateFormatter.format(_selectedDate)}',
                style: AppTextStyles.labelSmall.copyWith(
                  color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 20),

        ElevatedButton.icon(
          onPressed: () {
            HapticFeedback.lightImpact();
            Clipboard.setData(ClipboardData(text: inviteMessage));
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Mensagem de convite copiada! Pronta para colar no WhatsApp.'),
                backgroundColor: AppColors.success,
              ),
            );
          },
          icon: const Icon(Icons.share_rounded, color: Colors.white),
          label: const Text(
            'Compartilhar Convite no WhatsApp',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
          ),
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF25D366), // WhatsApp Green
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          ),
        ),
        const SizedBox(height: 10),
        OutlinedButton(
          onPressed: () {
            setState(() {
              _generated = false;
              _nameController.clear();
              _plateController.clear();
            });
          },
          style: OutlinedButton.styleFrom(
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          ),
          child: const Text('Criar Outro Convite'),
        ),
      ],
    );
  }
}
