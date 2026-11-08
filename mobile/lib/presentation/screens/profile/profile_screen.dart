import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/models/unit_model.dart';
import '../../blocs/unit/unit_cubit.dart';
import '../../blocs/unit/unit_state.dart';
import 'manage_pets_screen.dart';
import 'manage_residents_screen.dart';
import 'manage_vehicles_screen.dart';

/// Tela de Perfil do Morador com dados da unidade e gestão completa de dependentes, veículos, pets e financeiro
class ProfileScreen extends StatelessWidget {
  final VoidCallback onToggleTheme;
  final bool isDarkMode;

  const ProfileScreen({
    super.key,
    required this.onToggleTheme,
    required this.isDarkMode,
  });

  void _showFinancesModal(BuildContext context, ResidentRole role) {
    if (role == ResidentRole.inquilino) {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Text('Acesso Restrito'),
          content: const Text(
            'Conforme as regras do condomínio, os boletos de taxa condominial e despesas ordinárias/extraordinárias são de visualização exclusiva do proprietário da unidade.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Entendi'),
            ),
          ],
        ),
      );
      return;
    }

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      useSafeArea: true,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;

        return ConstrainedBox(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(ctx).size.height * 0.85,
          ),
          child: FrostedGlassCard(
            borderRadius: 32,
            margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Boletos & Finanças',
                        style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                      ),
                      IconButton(
                        icon: Icon(
                          Icons.close_rounded,
                          size: 20,
                          color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                        ),
                        onPressed: () => Navigator.pop(ctx),
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text('Histórico financeiro e boletos da unidade própria', style: AppTextStyles.bodyMedium),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0x33F59E0B) : AppColors.warningLight,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.warning.withAlpha(80)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Cota Condominial • Outubro/2026', style: TextStyle(fontWeight: FontWeight.bold)),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(color: AppColors.warning, borderRadius: BorderRadius.circular(6)),
                              child: const Text('Vence dia 10', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        const Text('R\$ 780,00', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.primary)),
                        const SizedBox(height: 12),
                        ElevatedButton.icon(
                          onPressed: () {
                            Clipboard.setData(const ClipboardData(text: '34191.79001 01043.510047 91020.150008 5 91820000078000'));
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Linha digitável copiada com sucesso!'), backgroundColor: AppColors.primary),
                            );
                          },
                          icon: const Icon(Icons.copy_rounded, size: 16),
                          label: const Text('Copiar Linha Digitável'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
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
            Text(
              'Perfil & Conta',
              style: AppTextStyles.titleLarge.copyWith(
                color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
              ),
            ),
            const SizedBox(height: 16),

            // Card com Avatar e Dados do Morador
            FrostedGlassCard(
              borderRadius: 24,
              child: Row(
                children: [
                  const CircleAvatar(
                    radius: 30,
                    backgroundColor: AppColors.primary,
                    child: Text(
                      'WS',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Wallacy Silva',
                          style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          activeUnit?.displayName ?? 'Apto 402 - Torre A',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0x336366F1) : AppColors.primarySoft,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            currentRole.label,
                            style: AppTextStyles.badge.copyWith(
                              color: isDark ? AppColors.primaryLight : AppColors.primary,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Alternância de Tema Claro / Escuro
            FrostedGlassCard(
              borderRadius: 20,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(
                        isDarkMode ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
                        color: AppColors.primary,
                        size: 24,
                      ),
                      const SizedBox(width: 14),
                      Text(
                        'Modo Escuro',
                        style: AppTextStyles.titleSmall.copyWith(fontSize: 14.5),
                      ),
                    ],
                  ),
                  Switch.adaptive(
                    value: isDarkMode,
                    activeTrackColor: AppColors.primary,
                    onChanged: (_) => onToggleTheme(),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Itens de Gestão da Unidade (Abrem Telas Dedicadas com Botão de Voltar)
            _ProfileNavOption(
              icon: Icons.people_outline_rounded,
              title: 'Dependentes & Moradores',
              subtitle: 'Gerenciar pessoas, fotos e biometrias da unidade',
              onTap: () {
                HapticFeedback.lightImpact();
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const ManageResidentsScreen()),
                );
              },
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.directions_car_outlined,
              title: 'Veículos Cadastrados',
              subtitle: 'Cadastrar veículos, placas e tags de cancela',
              onTap: () {
                HapticFeedback.lightImpact();
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const ManageVehiclesScreen()),
                );
              },
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.pets_outlined,
              title: 'Pets da Unidade',
              subtitle: 'Cadastrar animais, vacinas e carteirinha pet',
              onTap: () {
                HapticFeedback.lightImpact();
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const ManagePetsScreen()),
                );
              },
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.receipt_long_outlined,
              title: 'Boletos & Taxas de Condomínio',
              subtitle: currentRole == ResidentRole.inquilino
                  ? 'Acesso restrito ao proprietário'
                  : 'Cota de Outubro disponível para pagamento',
              onTap: () {
                HapticFeedback.lightImpact();
                _showFinancesModal(context, currentRole);
              },
            ),
          ],
        );
      },
    );
  }
}

class _ProfileNavOption extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  const _ProfileNavOption({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return FrostedGlassCard(
      borderRadius: 18,
      onTap: onTap,
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: isDark ? const Color(0x22FFFFFF) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(
              icon,
              color: isDark ? Colors.white70 : const Color(0xFF334155),
              size: 22,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: AppTextStyles.titleSmall.copyWith(
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    fontSize: 12,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          Icon(
            Icons.chevron_right_rounded,
            color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
            size: 20,
          ),
        ],
      ),
    );
  }
}
