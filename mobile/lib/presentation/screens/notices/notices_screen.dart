import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import 'new_occurrence_screen.dart';
import 'notice_detail_modal.dart';
import 'occurrence_detail_modal.dart';

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

/// Tela de Avisos e Ocorrências com interatividade completa e detalhamento
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

  void _openNewOccurrenceScreen() {
    HapticFeedback.lightImpact();
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => NewOccurrenceScreen(
          onOccurrenceCreated: (protocol, title, category, date, status) {
            setState(() {
              _myOccurrences.insert(
                0,
                OccurrenceItem(
                  protocol: protocol,
                  title: title,
                  category: category,
                  date: date,
                  status: status,
                ),
              );
              _activeTab = 1;
            });
          },
        ),
      ),
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
            ElevatedButton.icon(
              onPressed: _openNewOccurrenceScreen,
              icon: const Icon(Icons.add, size: 16, color: Colors.white),
              label: const Text('Novo Chamado', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              ),
            ),
          ],
        ),
        const SizedBox(height: 18),

        // Alternador de Abas (Avisos Oficiais / Minhas Ocorrências)
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
          // Comunicado 1
          FrostedGlassCard(
            borderRadius: 22,
            onTap: () {
              HapticFeedback.lightImpact();
              NoticeDetailModal.show(
                context,
                title: 'Manutenção Preventiva dos Elevadores',
                category: 'COMUNICADO OFICIAL',
                date: 'Hoje às 09:30',
                content: 'Informamos que nesta quinta-feira das 14h às 16h haverá manutenção programada preventiva de lubrificação e calibragem no Elevador Social do Bloco Primavera. O elevador de serviço permanecerá em operação normal.\n\nAgradecemos a colaboração de todos.',
              );
            },
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
                  'Informamos que nesta quinta-feira das 14h às 16h haverá manutenção programada no Elevador Social...',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Text('Toque para ler comunicado completo', style: AppTextStyles.labelSmall.copyWith(color: AppColors.primary, fontWeight: FontWeight.w600)),
                    const SizedBox(width: 4),
                    const Icon(Icons.arrow_forward_ios_rounded, size: 11, color: AppColors.primary),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          // Comunicado 2
          FrostedGlassCard(
            borderRadius: 22,
            onTap: () {
              HapticFeedback.lightImpact();
              NoticeDetailModal.show(
                context,
                title: 'Convocação: Assembleia Geral Ordinária',
                category: 'ASSEMBLEIA VIRTUAL',
                date: 'Ontem',
                content: 'Convocamos todos os senhores condôminos para a Assembleia Geral Ordinária com votação digital diretamente através deste aplicativo CondoAI.\n\nPautas:\n1. Prestação de contas do exercício 2025/2026.\n2. Previsão orçamentária do próximo ano.\n3. Pintura e revitalização das fachadas.\n\nA votação estará aberta do dia 15 ao dia 20.',
              );
            },
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
                  'A votação digital para aprovação das contas do exercício anterior estará aberta pelo app...',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Text('Toque para ler pautas e detalhes', style: AppTextStyles.labelSmall.copyWith(color: AppColors.success, fontWeight: FontWeight.w600)),
                    const SizedBox(width: 4),
                    const Icon(Icons.arrow_forward_ios_rounded, size: 11, color: AppColors.success),
                  ],
                ),
              ],
            ),
          ),
        ] else ...[
          // Minhas Ocorrências
          ..._myOccurrences.map((occ) {
            return FrostedGlassCard(
              borderRadius: 20,
              margin: const EdgeInsets.only(bottom: 12),
              onTap: () {
                HapticFeedback.lightImpact();
                OccurrenceDetailModal.show(
                  context,
                  protocol: occ.protocol,
                  title: occ.title,
                  category: occ.category,
                  date: occ.date,
                  status: occ.status,
                );
              },
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
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Text('Ver histórico e respostas do síndico', style: AppTextStyles.labelSmall.copyWith(color: AppColors.primary, fontWeight: FontWeight.w600)),
                      const SizedBox(width: 4),
                      const Icon(Icons.arrow_forward_ios_rounded, size: 11, color: AppColors.primary),
                    ],
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
