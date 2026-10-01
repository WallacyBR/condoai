import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../blocs/unit/unit_cubit.dart';
import '../../blocs/unit/unit_state.dart';

/// Tela de Perfil do Morador com dados da unidade e configurações
class ProfileScreen extends StatelessWidget {
  final VoidCallback onToggleTheme;
  final bool isDarkMode;

  const ProfileScreen({
    super.key,
    required this.onToggleTheme,
    required this.isDarkMode,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return BlocBuilder<UnitCubit, UnitState>(
      builder: (context, unitState) {
        final activeUnit = unitState is UnitLoaded ? unitState.activeUnit : null;

        return ListView(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 120),
          children: [
            Text(
              'Perfil & Conta',
              style: AppTextStyles.titleLarge.copyWith(
                color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
              ),
            ),
            const SizedBox(height: 20),

            // Card com Avatar e Dados do Morador
            FrostedGlassCard(
              borderRadius: 24,
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 32,
                    backgroundColor: AppColors.primary,
                    child: const Text(
                      'WS',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 22,
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
                          activeUnit?.displayName ?? 'Apto 402',
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
                            activeUnit?.role.label ?? 'Morador',
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

            const SizedBox(height: 20),

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

            const SizedBox(height: 12),

            // Itens de Gestão da Unidade
            _ProfileNavOption(
              icon: Icons.people_outline_rounded,
              title: 'Dependentes & Moradores',
              subtitle: 'Gerenciar pessoas autorizadas na unidade',
              onTap: () {},
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.directions_car_outlined,
              title: 'Veículos Cadastrados',
              subtitle: '2 carros cadastrados nas vagas vinculadas',
              onTap: () {},
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.pets_outlined,
              title: 'Pets da Unidade',
              subtitle: '1 pet registrado',
              onTap: () {},
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.receipt_long_outlined,
              title: 'Boletos & Taxas de Condomínio',
              subtitle: activeUnit?.role == null || activeUnit?.role.name == 'inquilino'
                  ? 'Acesso restrito ao proprietário'
                  : 'Histórico de cotas condominiais',
              onTap: () {},
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
          Icon(icon, color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: AppTextStyles.titleSmall.copyWith(fontSize: 14.5)),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: AppTextStyles.labelSmall.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                  ),
                ),
              ],
            ),
          ),
          const Icon(Icons.arrow_forward_ios_rounded, size: 14),
        ],
      ),
    );
  }
}
