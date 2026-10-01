import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';

class OccurrenceItem {
  final String protocol;
  final String title;
  final String category;
  final String date;
  final String status;

  OccurrenceItem({
    required this.protocol,
    required this.title,
    required this.category,
    required this.date,
    this.status = 'Em Análise',
  });
}

/// Tela de Avisos e Ocorrências com interatividade completa
class NoticesScreen extends StatefulWidget {
  const NoticesScreen({super.key});

  @override
  State<NoticesScreen> createState() => _NoticesScreenState();
}

class _NoticesScreenState extends State<NoticesScreen> {
  int _activeTab = 0; // 0 = Avisos Oficiais, 1 = Minhas Ocorrências

  final List<OccurrenceItem> _myOccurrences = [
    OccurrenceItem(
      protocol: 'OC-9841',
      title: 'Lâmpada queimada no corredor do 4º andar',
      category: 'Manutenção Predial',
      date: 'Ontem às 18:20',
      status: 'Em Atendimento',
    ),
  ];

  void _openNewOccurrenceModal() {
    final titleController = TextEditingController();
    final descController = TextEditingController();
    String selectedCategory = 'Manutenção Predial';

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;

        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
            return FrostedGlassCard(
              borderRadius: 32,
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Center(
                      child: Container(
                        width: 44,
                        height: 4,
                        decoration: BoxDecoration(
                          color: isDark ? Colors.white24 : Colors.black12,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'Registrar Ocorrência',
                      style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Seu relato será enviado diretamente para a administração',
                      style: AppTextStyles.bodyMedium.copyWith(
                        color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                      ),
                    ),
                    const SizedBox(height: 18),

                    // Categoria
                    DropdownButtonFormField<String>(
                      initialValue: selectedCategory,
                      decoration: InputDecoration(
                        labelText: 'Categoria',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'Manutenção Predial', child: Text('Manutenção Predial')),
                        DropdownMenuItem(value: 'Barulho Excessivo', child: Text('Barulho / Convivência')),
                        DropdownMenuItem(value: 'Vaga de Garagem', child: Text('Vaga de Garagem')),
                        DropdownMenuItem(value: 'Limpeza de Áreas', child: Text('Limpeza e Conservação')),
                        DropdownMenuItem(value: 'Segurança', child: Text('Segurança Patrimonial')),
                      ],
                      onChanged: (val) => setModalState(() => selectedCategory = val!),
                    ),
                    const SizedBox(height: 14),

                    // Assunto / Título
                    TextField(
                      controller: titleController,
                      style: TextStyle(
                        color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                      ),
                      decoration: InputDecoration(
                        labelText: 'Título da Ocorrência',
                        hintText: 'Ex: Barulho após horário permitido',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Descrição
                    TextField(
                      controller: descController,
                      maxLines: 3,
                      style: TextStyle(
                        color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                      ),
                      decoration: InputDecoration(
                        labelText: 'Descrição Detalhada',
                        hintText: 'Descreva o que ocorreu e local exato...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                    const SizedBox(height: 18),

                    ElevatedButton.icon(
                      onPressed: () {
                        if (titleController.text.trim().isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Por favor, informe o título da ocorrência.'),
                              backgroundColor: AppColors.danger,
                            ),
                          );
                          return;
                        }

                        HapticFeedback.heavyImpact();
                        final newProtocol = 'OC-${DateTime.now().millisecondsSinceEpoch.toString().substring(8)}';
                        setState(() {
                          _myOccurrences.insert(
                            0,
                            OccurrenceItem(
                              protocol: newProtocol,
                              title: titleController.text.trim(),
                              category: selectedCategory,
                              date: 'Agora',
                            ),
                          );
                          _activeTab = 1;
                        });
                        Navigator.pop(ctx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Ocorrência #$newProtocol registrada com sucesso!'),
                            backgroundColor: AppColors.success,
                          ),
                        );
                      },
                      icon: const Icon(Icons.send_rounded, color: Colors.white, size: 18),
                      label: const Text('Enviar para o Síndico', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 120),
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Comunicação',
                  style: AppTextStyles.titleLarge.copyWith(
                    color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'Mural oficial e abertura de chamados',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                  ),
                ),
              ],
            ),
            IconButton(
              onPressed: _openNewOccurrenceModal,
              icon: Container(
                padding: const EdgeInsets.all(8),
                decoration: const BoxDecoration(
                  color: AppColors.primary,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.add, color: Colors.white, size: 20),
              ),
              tooltip: 'Abrir Ocorrência',
            ),
          ],
        ),
        const SizedBox(height: 18),

        // Alternador de Abas
        Container(
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(
            color: isDark ? const Color(0x330F172A) : const Color(0xFFE2E8F0),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () {
                    HapticFeedback.lightImpact();
                    setState(() => _activeTab = 0);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    decoration: BoxDecoration(
                      color: _activeTab == 0
                          ? (isDark ? const Color(0xFF1E293B) : Colors.white)
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Center(
                      child: Text(
                        'Avisos Oficiais',
                        style: AppTextStyles.titleSmall.copyWith(
                          fontSize: 13,
                          fontWeight: _activeTab == 0 ? FontWeight.bold : FontWeight.w500,
                          color: _activeTab == 0
                              ? AppColors.primary
                              : (isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              Expanded(
                child: GestureDetector(
                  onTap: () {
                    HapticFeedback.lightImpact();
                    setState(() => _activeTab = 1);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    decoration: BoxDecoration(
                      color: _activeTab == 1
                          ? (isDark ? const Color(0xFF1E293B) : Colors.white)
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Center(
                      child: Text(
                        'Minhas Ocorrências (${_myOccurrences.length})',
                        style: AppTextStyles.titleSmall.copyWith(
                          fontSize: 13,
                          fontWeight: _activeTab == 1 ? FontWeight.bold : FontWeight.w500,
                          color: _activeTab == 1
                              ? AppColors.primary
                              : (isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 20),

        if (_activeTab == 0) ...[
          FrostedGlassCard(
            borderRadius: 22,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.primarySoft,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'COMUNICADO OFICIAL',
                        style: AppTextStyles.badge.copyWith(color: AppColors.primary),
                      ),
                    ),
                    Text(
                      'Hoje às 09:30',
                      style: AppTextStyles.labelSmall.copyWith(
                        color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  'Manutenção Preventiva dos Elevadores',
                  style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 6),
                Text(
                  'Informamos que nesta quinta-feira das 14h às 16h haverá manutenção programada no Elevador Social do Bloco Primavera. O elevador de serviço permanecerá em operação normal.',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          FrostedGlassCard(
            borderRadius: 22,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.successLight,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'ASSEMBLEIA VIRTUAL',
                        style: AppTextStyles.badge.copyWith(color: AppColors.success),
                      ),
                    ),
                    Text(
                      'Ontem',
                      style: AppTextStyles.labelSmall.copyWith(
                        color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  'Convocação: Assembleia Geral Ordinária',
                  style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 6),
                Text(
                  'A votação digital para aprovação das contas do exercício anterior estará aberta pelo app a partir do dia 15 deste mês.',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ] else ...[
          OutlinedButton.icon(
            onPressed: _openNewOccurrenceModal,
            icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
            label: const Text('Nova Ocorrência ou Reclamação'),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            ),
          ),
          const SizedBox(height: 16),
          ..._myOccurrences.map((occ) {
            return FrostedGlassCard(
              borderRadius: 20,
              margin: const EdgeInsets.only(bottom: 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppColors.warningLight,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          occ.protocol,
                          style: AppTextStyles.badge.copyWith(color: AppColors.warning),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppColors.info.withAlpha(30),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          occ.status,
                          style: AppTextStyles.badge.copyWith(color: AppColors.info),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    occ.title,
                    style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${occ.category} • ${occ.date}',
                    style: AppTextStyles.labelSmall.copyWith(
                      color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ],
    );
  }
}
