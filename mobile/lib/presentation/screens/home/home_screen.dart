import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/models/unit_model.dart';
import '../../blocs/unit/unit_cubit.dart';
import '../../blocs/unit/unit_state.dart';
import '../../blocs/visitor_call/visitor_call_cubit.dart';

/// Tela Inicial do Morador (Airbnb Style com Glassmorphism & Bokeh)
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

  void _showPackagesModal(BuildContext context) {
    bool isRevealed = false;

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
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 44,
                    height: 4,
                    decoration: BoxDecoration(
                      color: isDark ? Colors.white24 : Colors.black12,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(height: 18),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? const Color(0x33F59E0B)
                                  : AppColors.warningLight,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(
                              Icons.inventory_2_rounded,
                              color: AppColors.warning,
                              size: 24,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Retirada de Encomendas',
                                style: AppTextStyles.titleSmall.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Text(
                                '2 volumes aguardando na portaria',
                                style: AppTextStyles.labelSmall.copyWith(
                                  color: isDark
                                      ? AppColors.darkTextSecondary
                                      : AppColors.lightTextSecondary,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.warning,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text(
                          'Disponível',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Cartão Seguro do Código de Retirada
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0x330F172A)
                          : const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
                      ),
                    ),
                    child: Column(
                      children: [
                        Text(
                          'CÓDIGO DE LIBERAÇÃO',
                          style: AppTextStyles.badge.copyWith(
                            color: isDark
                                ? AppColors.darkTextSecondary
                                : AppColors.lightTextSecondary,
                            letterSpacing: 1.2,
                          ),
                        ),
                        const SizedBox(height: 10),
                        AnimatedSwitcher(
                          duration: const Duration(milliseconds: 300),
                          child: isRevealed
                              ? SelectableText(
                                  '#4029-A',
                                  key: const ValueKey('revealed'),
                                  style: AppTextStyles.titleLarge.copyWith(
                                    fontSize: 32,
                                    fontWeight: FontWeight.w800,
                                    color: AppColors.primary,
                                    letterSpacing: 2,
                                  ),
                                )
                              : Text(
                                  '••••••',
                                  key: const ValueKey('hidden'),
                                  style: AppTextStyles.titleLarge.copyWith(
                                    fontSize: 32,
                                    fontWeight: FontWeight.w800,
                                    color: isDark
                                        ? AppColors.darkTextSecondary
                                        : AppColors.lightTextSecondary,
                                    letterSpacing: 6,
                                  ),
                                ),
                        ),
                        const SizedBox(height: 12),
                        OutlinedButton.icon(
                          onPressed: () {
                            HapticFeedback.lightImpact();
                            setModalState(() {
                              isRevealed = !isRevealed;
                            });
                          },
                          icon: Icon(
                            isRevealed
                                ? Icons.visibility_off_outlined
                                : Icons.visibility_outlined,
                            size: 18,
                            color: AppColors.primary,
                          ),
                          label: Text(
                            isRevealed
                                ? 'Ocultar Código'
                                : 'Toque para Revelar Código',
                            style: const TextStyle(
                              color: AppColors.primary,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          style: OutlinedButton.styleFrom(
                            side: const BorderSide(color: AppColors.primary),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Lista dos Pacotes
                  _PackageItemRow(
                    company: 'Mercado Livre',
                    tracking: 'Rastreio: ML-7829103BR',
                    receivedTime: 'Hoje às 10:14 • Portaria A',
                    isDark: isDark,
                  ),
                  const SizedBox(height: 8),
                  _PackageItemRow(
                    company: 'Amazon Brasil',
                    tracking: 'Rastreio: AMZ-449102X',
                    receivedTime: 'Hoje às 11:30 • Portaria A',
                    isDark: isDark,
                  ),

                  const SizedBox(height: 16),
                  Text(
                    'Apresente este código na portaria no momento da retirada.',
                    style: AppTextStyles.labelSmall.copyWith(
                      color: isDark
                          ? AppColors.darkTextSecondary
                          : AppColors.lightTextSecondary,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
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

    return BlocBuilder<UnitCubit, UnitState>(
      builder: (context, unitState) {
        final activeUnit = unitState is UnitLoaded ? unitState.activeUnit : null;
        final currentRole = activeUnit?.role ?? ResidentRole.morador;

        return ListView(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 120),
          children: [
            // 1. Cabeçalho de Boas-Vindas
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
                // Badge de Papel + Botão de Notificação
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
                    const SizedBox(width: 8),
                    Container(
                      width: 42,
                      height: 42,
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0x33FFFFFF) : Colors.white,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
                        ),
                      ),
                      child: Icon(
                        Icons.notifications_none_rounded,
                        color: isDark ? Colors.white : AppColors.lightTextPrimary,
                        size: 22,
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
                          'Simule a chegada de um visitante na portaria agora',
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

            const SizedBox(height: 20),

            // 3. Grid de Cartões Rápidos Estilo Airbnb
            Text(
              'Acesso & Serviços',
              style: AppTextStyles.titleSmall.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),

            // Card 1: Liberação Rápida de Visitante
            FrostedGlassCard(
              onTap: onOpenQuickAction,
              child: Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0x336366F1) : AppColors.primarySoft,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(
                      Icons.person_add_alt_1_rounded,
                      color: AppColors.primary,
                      size: 26,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Liberação Rápida de Visitante',
                          style: AppTextStyles.titleSmall,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Gere QR Code de acesso ou passe expresso',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: isDark
                                ? AppColors.darkTextSecondary
                                : AppColors.lightTextSecondary,
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

            // Card 2: Encomendas
            FrostedGlassCard(
              onTap: () => _showPackagesModal(context),
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
                                '2 Encomendas na Portaria',
                                style: AppTextStyles.titleSmall,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
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
                          'Amazon Brasil • Mercado Livre',
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
                              Icons.lock_outline_rounded,
                              size: 13,
                              color: AppColors.warning,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              'Toque para ver código de retirada',
                              style: AppTextStyles.labelSmall.copyWith(
                                color: AppColors.warning,
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
            ),

            const SizedBox(height: 12),

            // Card 3: Reservas de Áreas Comuns
            FrostedGlassCard(
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
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Reservar Espaço Gourmet',
                          style: AppTextStyles.titleSmall,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Churrasqueira, Salão de Festas e Quadra',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: isDark
                                ? AppColors.darkTextSecondary
                                : AppColors.lightTextSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.arrow_forward_ios_rounded, size: 14),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Card Informativo das Regras do Perfil Atual
            FrostedGlassCard(
              customColor: isDark
                  ? const Color(0x336366F1)
                  : const Color(0x0D4F46E5),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.verified_user_outlined, color: AppColors.primary, size: 20),
                      const SizedBox(width: 8),
                      Text(
                        'Regras Ativas: ${currentRole.label}',
                        style: AppTextStyles.titleSmall.copyWith(
                          color: AppColors.primary,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    currentRole == ResidentRole.inquilino
                        ? '• Acesso total a portaria, convites e reservas do dia a dia.\n• Boletos de taxas estruturais são restritos ao proprietário.\n• Votações em assembleias liberadas mediante procuração.'
                        : currentRole == ResidentRole.proprietario
                            ? '• Acesso financeiro completo, boletos e cotas extraordinárias.\n• Direito a voto direto em todas as assembleias virtuais.\n• Gestão de titulares e contratos da unidade.'
                            : '• Acesso a interfone virtual, convites e reservas de lazer.',
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}

class _PackageItemRow extends StatelessWidget {
  final String company;
  final String tracking;
  final String receivedTime;
  final bool isDark;

  const _PackageItemRow({
    required this.company,
    required this.tracking,
    required this.receivedTime,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: isDark ? const Color(0x26FFFFFF) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
        ),
      ),
      child: Row(
        children: [
          Icon(
            Icons.local_shipping_outlined,
            color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
            size: 20,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  company,
                  style: AppTextStyles.titleSmall.copyWith(fontSize: 13.5),
                ),
                Text(
                  '$tracking • $receivedTime',
                  style: AppTextStyles.labelSmall.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

