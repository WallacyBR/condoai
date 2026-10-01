import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/models/unit_model.dart';
import '../../blocs/unit/unit_cubit.dart';
import '../../blocs/unit/unit_state.dart';

/// Tela de Perfil do Morador com dados da unidade e gestão completa de dependentes, veículos e boletos
class ProfileScreen extends StatelessWidget {
  final VoidCallback onToggleTheme;
  final bool isDarkMode;

  const ProfileScreen({
    super.key,
    required this.onToggleTheme,
    required this.isDarkMode,
  });

  void _showDependentsModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;

        return FrostedGlassCard(
          borderRadius: 32,
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text('Moradores & Dependentes', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Text('Pessoas autorizadas a entrar e receber visitantes nesta unidade', style: AppTextStyles.bodyMedium),
              const SizedBox(height: 16),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const CircleAvatar(backgroundColor: AppColors.primary, child: Text('WS', style: TextStyle(color: Colors.white))),
                title: const Text('Wallacy Silva', style: TextStyle(fontWeight: FontWeight.bold)),
                subtitle: const Text('Titular da Unidade'),
                trailing: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: AppColors.primarySoft, borderRadius: BorderRadius.circular(8)),
                  child: Text('Titular', style: AppTextStyles.badge.copyWith(color: AppColors.primary)),
                ),
              ),
              const Divider(),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: CircleAvatar(backgroundColor: Colors.pink.shade400, child: const Text('LS', style: TextStyle(color: Colors.white))),
                title: const Text('Larissa Silva', style: TextStyle(fontWeight: FontWeight.bold)),
                subtitle: const Text('Cônjuge • Acesso facial liberado'),
                trailing: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: isDark ? const Color(0x33EC4899) : Colors.pink.shade50, borderRadius: BorderRadius.circular(8)),
                  child: Text('Dependente', style: AppTextStyles.badge.copyWith(color: Colors.pink.shade400)),
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Convite para novo morador enviado por e-mail!'), backgroundColor: AppColors.primary),
                  );
                },
                icon: const Icon(Icons.person_add_rounded, color: Colors.white),
                label: const Text('Adicionar Novo Morador', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _showVehiclesModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return FrostedGlassCard(
          borderRadius: 32,
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text('Veículos Cadastrados', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Text('Vagas vinculadas: Vaga 42 (Coberta)', style: AppTextStyles.bodyMedium),
              const SizedBox(height: 16),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.directions_car_filled_rounded, color: AppColors.primary, size: 32),
                title: const Text('Jeep Compass Cinza', style: TextStyle(fontWeight: FontWeight.bold)),
                subtitle: const Text('Placa: BRA-4X91 • Tag Sem Parar Ativa'),
              ),
              const Divider(),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.two_wheeler_rounded, color: AppColors.info, size: 32),
                title: const Text('Honda CB 500F Preta', style: TextStyle(fontWeight: FontWeight.bold)),
                subtitle: const Text('Placa: SP-9912 • Vaga de Moto M-08'),
              ),
            ],
          ),
        );
      },
    );
  }

  void _showPetsModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return FrostedGlassCard(
          borderRadius: 32,
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text('Pets da Unidade', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Text('Cadastro para circulação nas áreas pet do condomínio', style: AppTextStyles.bodyMedium),
              const SizedBox(height: 16),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const CircleAvatar(backgroundColor: AppColors.warningLight, child: Icon(Icons.pets, color: AppColors.warning)),
                title: const Text('Thor', style: TextStyle(fontWeight: FontWeight.bold)),
                subtitle: const Text('Golden Retriever • Porte Grande • Vacinação em dia'),
                trailing: const Icon(Icons.verified_rounded, color: AppColors.success, size: 20),
              ),
            ],
          ),
        );
      },
    );
  }

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
      builder: (ctx) {
        return FrostedGlassCard(
          borderRadius: 32,
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text('Boletos & Taxas de Condomínio', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Text('Histórico financeiro da unidade própria', style: AppTextStyles.bodyMedium),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.warningLight,
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
                    const Text('R\$ 780,00', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.primary)),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: () {
                              Clipboard.setData(const ClipboardData(text: '34191.79001 01043.510047 91020.150008 5 91820000078000'));
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Linha digitável copiada com sucesso!'), backgroundColor: AppColors.primary),
                              );
                            },
                            icon: const Icon(Icons.copy_rounded, size: 16),
                            label: const Text('Copiar Código'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
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

            // Itens de Gestão da Unidade (100% Funcionais com Modais)
            _ProfileNavOption(
              icon: Icons.people_outline_rounded,
              title: 'Dependentes & Moradores',
              subtitle: 'Gerenciar pessoas autorizadas na unidade',
              onTap: () {
                HapticFeedback.lightImpact();
                _showDependentsModal(context);
              },
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.directions_car_outlined,
              title: 'Veículos Cadastrados',
              subtitle: '2 veículos vinculados às vagas',
              onTap: () {
                HapticFeedback.lightImpact();
                _showVehiclesModal(context);
              },
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.pets_outlined,
              title: 'Pets da Unidade',
              subtitle: '1 pet registrado',
              onTap: () {
                HapticFeedback.lightImpact();
                _showPetsModal(context);
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
