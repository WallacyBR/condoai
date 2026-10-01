import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/models/unit_model.dart';
import '../../blocs/packages/packages_cubit.dart';
import '../../blocs/packages/packages_state.dart';
import '../../blocs/unit/unit_cubit.dart';
import '../../blocs/unit/unit_state.dart';
import '../../blocs/visitor_call/visitor_call_cubit.dart';
import '../bookings/booking_detail_modal.dart';
import '../bookings/bookings_screen.dart';
import '../gates/remote_gate_modal.dart';
import '../invites/create_invite_screen.dart';
import '../notices/new_occurrence_screen.dart';
import '../notices/notice_detail_modal.dart';
import '../notices/occurrence_detail_modal.dart';
import '../notifications/notifications_screen.dart';
import '../packages/packages_screen.dart';

/// Tela Inicial do Morador (Airbnb Style com Glassmorphism, Bokeh e Resumo da Vida Condominial)
class HomeScreen extends StatelessWidget {
  final VoidCallback onOpenQuickAction;

  const HomeScreen({
    super.key,
    required this.onOpenQuickAction,
  });

  void _showUnitSelector(BuildContext context, UnitLoaded state) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;

        return FrostedGlassCard(
          borderRadius: 28,
          margin: const EdgeInsets.all(16),
          padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Suas Unidades',
                style: AppTextStyles.titleMedium.copyWith(
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                'Alterne entre seus imóveis ou perfis cadastrados',
                style: AppTextStyles.bodyMedium.copyWith(
                  color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                ),
              ),
              const SizedBox(height: 16),
              ...state.availableUnits.map((unit) {
                final isSelected = unit.apartmentId == state.activeUnit.apartmentId;
                return Container(
                  margin: const EdgeInsets.only(bottom: 10),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isSelected
                          ? AppColors.primary
                          : (isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder),
                      width: isSelected ? 2 : 1,
                    ),
                    color: isSelected
                        ? (isDark ? const Color(0x334F46E5) : const Color(0x1A4F46E5))
                        : Colors.transparent,
                  ),
                  child: ListTile(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                    title: Text(
                      unit.displayName,
                      style: AppTextStyles.titleSmall.copyWith(
                        fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                      ),
                    ),
                    subtitle: Text(
                      '${unit.condominiumName} • ${unit.role.label}',
                      style: AppTextStyles.bodyMedium,
                    ),
                    trailing: isSelected
                        ? const Icon(Icons.check_circle_rounded, color: AppColors.primary)
                        : null,
                    onTap: () {
                      context.read<UnitCubit>().switchUnit(unit);
                      Navigator.pop(ctx);
                    },
                  ),
                );
              }),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return BlocBuilder<UnitCubit, UnitState>(
      builder: (context, unitState) {
        final activeUnit = unitState is UnitLoaded ? unitState.activeUnit : null;
        final currentRole = activeUnit?.role ?? ResidentRole.morador;

        return ListView(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 120),
          children: [
            // 1. Cabeçalho de Boas-Vindas & Central de Notificações
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Olá, Wallace',
                      style: AppTextStyles.titleLarge.copyWith(
                        color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                      ),
                    ),
                    const SizedBox(height: 4),
                    if (unitState is UnitLoaded)
                      GestureDetector(
                        onTap: () => _showUnitSelector(context, unitState),
                        child: Row(
                          children: [
                            Text(
                              activeUnit?.displayName ?? 'Selecionar Unidade',
                              style: AppTextStyles.bodyLarge.copyWith(
                                color: isDark
                                    ? AppColors.darkTextSecondary
                                    : AppColors.lightTextSecondary,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(width: 4),
                            Icon(
                              Icons.keyboard_arrow_down_rounded,
                              size: 18,
                              color: isDark
                                  ? AppColors.darkTextSecondary
                                  : AppColors.lightTextSecondary,
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
                // Badge de Papel + Botão do Sino Funcional
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0x336366F1) : AppColors.primarySoft,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isDark ? const Color(0x4D818CF8) : const Color(0x334F46E5),
                        ),
                      ),
                      child: Text(
                        currentRole.label,
                        style: AppTextStyles.badge.copyWith(
                          color: isDark ? AppColors.primaryLight : AppColors.primary,
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    GestureDetector(
                      onTap: () {
                        HapticFeedback.lightImpact();
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const NotificationsScreen(),
                          ),
                        );
                      },
                      child: Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0x33FFFFFF) : Colors.white,
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
                          ),
                        ),
                        child: Stack(
                          alignment: Alignment.center,
                          children: [
                            Icon(
                              Icons.notifications_none_rounded,
                              color: isDark ? Colors.white : AppColors.lightTextPrimary,
                              size: 22,
                            ),
                            Positioned(
                              top: 9,
                              right: 9,
                              child: Container(
                                width: 8,
                                height: 8,
                                decoration: const BoxDecoration(
                                  color: AppColors.danger,
                                  shape: BoxShape.circle,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),

            const SizedBox(height: 20),

            // 2. Banner de Demonstração Noknox Interfone Virtual
            FrostedGlassCard(
              borderRadius: 22,
              customColor: isDark
                  ? const Color(0x3310B981)
                  : const Color(0x1A10B981),
              customBorder: Border.all(
                color: AppColors.success.withAlpha(100),
                width: 1.5,
              ),
              onTap: () {
                HapticFeedback.heavyImpact();
                context.read<VisitorCallCubit>().triggerMockCall();
              },
              child: Row(
                children: [
                  Container(
                    width: 46,
                    height: 46,
                    decoration: BoxDecoration(
                      color: AppColors.success,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(
                      Icons.phone_in_talk_rounded,
                      color: Colors.white,
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Testar Interfone Noknox',
                          style: AppTextStyles.titleSmall.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Simule a chegada de um visitante com vídeo ao vivo',
                          style: AppTextStyles.labelSmall.copyWith(
                            color: isDark
                                ? AppColors.darkTextSecondary
                                : AppColors.lightTextSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Icon(
                    Icons.arrow_forward_ios_rounded,
                    size: 14,
                    color: AppColors.success,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 22),

            // 3. Atalhos Rápidos da Unidade (Acesso Direto)
            Text(
              'Ações Rápidas',
              style: AppTextStyles.titleSmall.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _QuickActionButton(
                    icon: Icons.person_add_alt_1_rounded,
                    label: 'Convidar',
                    color: AppColors.primary,
                    isDark: isDark,
                    onTap: () {
                      HapticFeedback.lightImpact();
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const CreateInviteScreen()),
                      );
                    },
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _QuickActionButton(
                    icon: Icons.lock_open_rounded,
                    label: 'Portão',
                    color: AppColors.success,
                    isDark: isDark,
                    onTap: () => RemoteGateModal.show(context),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _QuickActionButton(
                    icon: Icons.calendar_month_rounded,
                    label: 'Reservar',
                    color: AppColors.info,
                    isDark: isDark,
                    onTap: () {
                      HapticFeedback.lightImpact();
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const BookingsScreen()),
                      );
                    },
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _QuickActionButton(
                    icon: Icons.report_problem_outlined,
                    label: 'Ocorrência',
                    color: AppColors.warning,
                    isDark: isDark,
                    onTap: () {
                      HapticFeedback.lightImpact();
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const NewOccurrenceScreen()),
                      );
                    },
                  ),
                ),
              ],
            ),

            const SizedBox(height: 26),

            // 4. Seção Resumo da Vida Condominial
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Resumo do Condomínio',
                  style: AppTextStyles.titleSmall.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  'Hoje',
                  style: AppTextStyles.labelSmall.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Item 1: Encomendas Pendentes
            BlocBuilder<PackagesCubit, PackagesState>(
              builder: (context, packageState) {
                final pendingCount = packageState is PackagesLoaded
                    ? packageState.pendingCount
                    : 2;

                return FrostedGlassCard(
                  onTap: () {
                    HapticFeedback.lightImpact();
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const PackagesScreen()),
                    );
                  },
                  child: Row(
                    children: [
                      Container(
                        width: 48,
                        height: 48,
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0x33F59E0B) : AppColors.warningLight,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Icon(
                          Icons.inventory_2_outlined,
                          color: AppColors.warning,
                          size: 26,
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    pendingCount > 0
                                        ? '$pendingCount Encomenda${pendingCount > 1 ? 's' : ''} na Portaria'
                                        : 'Nenhuma Encomenda Pendente',
                                    style: AppTextStyles.titleSmall,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                const SizedBox(width: 8),
                                if (pendingCount > 0)
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: AppColors.warning,
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: const Text(
                                      'Aguardando',
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontSize: 9,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 3),
                            Text(
                              pendingCount > 0
                                  ? 'Fotos reais da portaria • QR Codes e Código'
                                  : 'Histórico de entregas recebidas',
                              style: AppTextStyles.bodyMedium.copyWith(
                                color: isDark
                                    ? AppColors.darkTextSecondary
                                    : AppColors.lightTextSecondary,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                const Icon(
                                  Icons.qr_code_2_rounded,
                                  size: 14,
                                  color: AppColors.primary,
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  'Toque para ver fotos e retirar',
                                  style: AppTextStyles.labelSmall.copyWith(
                                    color: AppColors.primary,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 4),
                      const Icon(Icons.arrow_forward_ios_rounded, size: 14),
                    ],
                  ),
                );
              },
            ),

            const SizedBox(height: 12),

            // Item 2: Próxima Reserva Ativa
            FrostedGlassCard(
              onTap: () {
                HapticFeedback.lightImpact();
                final mockActiveBooking = BookingItem(
                  id: 'RES-9981',
                  amenityName: 'Espaço Gourmet & Churrasqueira 02',
                  date: DateTime.now().add(const Duration(days: 3)),
                  period: 'Tarde (12:00 às 18:00)',
                  status: 'Confirmada',
                  photoUrl: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&auto=format&fit=crop&q=80',
                );
                BookingDetailModal.show(
                  context,
                  mockActiveBooking,
                  () {},
                );
              },
              child: Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0x330EA5E9) : const Color(0xFFF0F9FF),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(
                      Icons.outdoor_grill_outlined,
                      color: AppColors.info,
                      size: 26,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                'Próxima Reserva Ativa',
                                style: AppTextStyles.titleSmall,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.success,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'Confirmada',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 3),
                        Text(
                          'Espaço Gourmet • Sáb, 12:00 às 18:00',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: isDark
                                ? AppColors.darkTextSecondary
                                : AppColors.lightTextSecondary,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(
                              Icons.confirmation_number_outlined,
                              size: 14,
                              color: AppColors.info,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              'Ver Voucher e Política de Cancelamento',
                              style: AppTextStyles.labelSmall.copyWith(
                                color: AppColors.info,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.arrow_forward_ios_rounded, size: 14),
                ],
              ),
            ),

            const SizedBox(height: 12),

            // Item 3: Mural / Último Comunicado
            FrostedGlassCard(
              onTap: () {
                HapticFeedback.lightImpact();
                NoticeDetailModal.show(
                  context,
                  title: 'Manutenção Preventiva dos Elevadores',
                  category: 'COMUNICADO IMPORTANTE',
                  date: 'Ontem às 14:30',
                  content: 'Prezados moradores,\n\nInformamos que nesta sexta-feira a partir das 09h realizaremos a manutenção preventiva dos elevadores sociais da Torre A. O serviço tem previsão de término às 13h.\n\nDurante o período, utilizem o elevador de serviço.\n\nAtenciosamente,\nAdministração',
                  author: 'Administração Predial',
                  initialRead: false,
                );
              },
              child: Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0x33EF4444) : const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(
                      Icons.campaign_outlined,
                      color: AppColors.danger,
                      size: 26,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                'Manutenção dos Elevadores',
                                style: AppTextStyles.titleSmall,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.danger,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'Importante',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 3),
                        Text(
                          'Torre A • Parada parcial na sexta-feira às 09h',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: isDark
                                ? AppColors.darkTextSecondary
                                : AppColors.lightTextSecondary,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Toque para ler comunicado na íntegra',
                          style: AppTextStyles.labelSmall.copyWith(
                            color: AppColors.primary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.arrow_forward_ios_rounded, size: 14),
                ],
              ),
            ),

            const SizedBox(height: 12),

            // Item 4: Minhas Ocorrências Recentes
            FrostedGlassCard(
              onTap: () {
                HapticFeedback.lightImpact();
                OccurrenceDetailModal.show(
                  context,
                  protocol: '2026-0891',
                  title: 'Infiltração na vaga de garagem G2',
                  category: 'MANUTENÇÃO PREDIAL',
                  date: 'Criado em 28/Set/2026',
                  status: 'Em Análise',
                );
              },
              child: Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0x338B5CF6) : const Color(0xFFF5F3FF),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(
                      Icons.chat_bubble_outline_rounded,
                      color: Color(0xFF8B5CF6),
                      size: 26,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                'Ocorrência #2026-0891',
                                style: AppTextStyles.titleSmall,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFF8B5CF6),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'Em Análise',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 3),
                        Text(
                          'Infiltração na garagem • Síndico respondeu há 2h',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: isDark
                                ? AppColors.darkTextSecondary
                                : AppColors.lightTextSecondary,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Toque para ver histórico e réplica',
                          style: AppTextStyles.labelSmall.copyWith(
                            color: const Color(0xFF8B5CF6),
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.arrow_forward_ios_rounded, size: 14),
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}

/// Widget auxiliar de Botão de Ação Rápida
class _QuickActionButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final bool isDark;
  final VoidCallback onTap;

  const _QuickActionButton({
    required this.icon,
    required this.label,
    required this.color,
    required this.isDark,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: isDark ? const Color(0x2AFFFFFF) : Colors.white.withAlpha(210),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withAlpha(isDark ? 30 : 10),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: color.withAlpha(isDark ? 45 : 28),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: AppTextStyles.labelSmall.copyWith(
                fontWeight: FontWeight.w600,
                color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
