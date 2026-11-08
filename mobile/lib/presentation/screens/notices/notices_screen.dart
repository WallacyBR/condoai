import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/local/occurrence_local_store.dart';
import '../../../data/local/tenant_scope.dart';
import '../../../data/models/occurrence_model.dart';
import '../../blocs/unit/unit_cubit.dart';
import '../../blocs/unit/unit_state.dart';
import 'new_occurrence_screen.dart';
import 'notice_detail_modal.dart';
import 'occurrence_detail_modal.dart';

/// Tela de Avisos e Ocorrências com interatividade completa e detalhamento
class NoticesScreen extends StatefulWidget {
  const NoticesScreen({super.key});

  @override
  State<NoticesScreen> createState() => _NoticesScreenState();
}

class _NoticesScreenState extends State<NoticesScreen> {
  int _activeTab = 0;
  List<OccurrenceItem> _myOccurrences = [];

  TenantScope? _scope() {
    final unitState = context.read<UnitCubit>().state;
    if (unitState is! UnitLoaded) return null;
    return TenantScope.fromUnit(unitState.activeUnit);
  }

  @override
  void initState() {
    super.initState();
    _loadOccurrences();
  }

  void _loadOccurrences() {
    final scope = _scope();
    if (scope == null) return;
    setState(() {
      _myOccurrences = OccurrenceLocalStore.load(scope);
    });
  }

  void _openNewOccurrenceScreen() {
    HapticFeedback.lightImpact();
    final scope = _scope();
    Navigator.push<OccurrenceItem>(
      context,
      MaterialPageRoute(builder: (_) => const NewOccurrenceScreen()),
    ).then((item) {
      if (item != null && scope != null) {
        OccurrenceLocalStore.add(scope, item);
        setState(() {
          _myOccurrences = OccurrenceLocalStore.load(scope);
          _activeTab = 1;
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final canPop = Navigator.canPop(context);

    return BokehScaffold(
      appBar: canPop
          ? AppBar(
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
                'Comunicação',
                style: AppTextStyles.titleMedium.copyWith(
                  fontWeight: FontWeight.bold,
                  color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                ),
              ),
              centerTitle: true,
            )
          : null,
      body: SafeArea(
        top: !canPop,
        bottom: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 120),
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (!canPop) ...[
                        Text(
                          'Comunicação',
                          style: AppTextStyles.titleLarge.copyWith(
                            color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                          ),
                        ),
                        const SizedBox(height: 2),
                      ],
                      Text(
                        'Mural oficial e abertura de chamados',
                        style: AppTextStyles.bodyMedium.copyWith(
                          color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                IconButton.filled(
                  onPressed: _openNewOccurrenceScreen,
                  icon: const Icon(Icons.add_rounded, size: 20, color: Colors.white),
                  tooltip: 'Novo Chamado',
                  style: IconButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    padding: const EdgeInsets.all(10),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 18),
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
                onTap: () {
                  HapticFeedback.lightImpact();
                  NoticeDetailModal.show(
                    context,
                    title: 'Manutenção Preventiva dos Elevadores',
                    category: 'COMUNICADO OFICIAL',
                    date: 'Hoje às 09:30',
                    content:
                        'Informamos que nesta quinta-feira das 14h às 16h haverá manutenção programada preventiva de lubrificação e calibragem no Elevador Social do Bloco Primavera.',
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
                  ],
                ),
              ),
            ] else ...[
              ..._myOccurrences.map((occ) {
                return FrostedGlassCard(
                  borderRadius: 20,
                  margin: const EdgeInsets.only(bottom: 12),
                  onTap: () {
                    HapticFeedback.lightImpact();
                    OccurrenceDetailModal.show(context, occ);
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
                      if (occ.location.isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(
                          '📍 ${occ.location}',
                          style: AppTextStyles.labelSmall,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                      if (occ.attachments.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          '${occ.attachments.length} anexo(s)',
                          style: AppTextStyles.labelSmall.copyWith(color: AppColors.primary),
                        ),
                      ],
                    ],
                  ),
                );
              }),
            ],
          ],
        ),
      ),
    );
  }
}
