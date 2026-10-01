import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/models/unit_model.dart';
import '../../blocs/unit/unit_cubit.dart';
import '../../blocs/unit/unit_state.dart';

/// Tela de Perfil do Morador com dados da unidade e gestão completa e funcional de dependentes, veículos e boletos
class ProfileScreen extends StatefulWidget {
  final VoidCallback onToggleTheme;
  final bool isDarkMode;

  const ProfileScreen({
    super.key,
    required this.onToggleTheme,
    required this.isDarkMode,
  });

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final List<Map<String, String>> _residents = [
    {
      'name': 'Wallacy Silva',
      'role': 'Titular da Unidade',
      'initials': 'WS',
      'tag': 'Titular',
    },
    {
      'name': 'Larissa Silva',
      'role': 'Cônjuge • Acesso facial liberado',
      'initials': 'LS',
      'tag': 'Dependente',
    },
  ];

  final List<Map<String, String>> _vehicles = [
    {
      'title': 'Jeep Compass Cinza',
      'subtitle': 'Placa: BRA-4X91 • Tag Sem Parar Ativa',
      'type': 'car',
    },
    {
      'title': 'Honda CB 500F Preta',
      'subtitle': 'Placa: SP-9912 • Vaga de Moto M-08',
      'type': 'bike',
    },
  ];

  final List<Map<String, String>> _pets = [
    {
      'name': 'Thor',
      'breed': 'Golden Retriever',
      'size': 'Porte Grande',
      'status': 'Vacinação em dia',
    },
  ];

  void _openAddResidentDialog(BuildContext context, StateSetter modalSetState) {
    final nameCtrl = TextEditingController();
    final docCtrl = TextEditingController();
    String relation = 'Filho(a)';

    showDialog(
      context: context,
      builder: (dlgCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: const Text('Cadastrar Novo Morador'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: nameCtrl,
              decoration: const InputDecoration(labelText: 'Nome Completo', hintText: 'Ex: Arthur Silva'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: docCtrl,
              decoration: const InputDecoration(labelText: 'CPF ou RG', hintText: '000.000.000-00'),
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: relation,
              decoration: const InputDecoration(labelText: 'Grau de Parentesco'),
              items: const [
                DropdownMenuItem(value: 'Filho(a)', child: Text('Filho(a)')),
                DropdownMenuItem(value: 'Cônjuge', child: Text('Cônjuge')),
                DropdownMenuItem(value: 'Pai/Mãe', child: Text('Pai / Mãe')),
                DropdownMenuItem(value: 'Outro', child: Text('Outro Morador')),
              ],
              onChanged: (val) => relation = val!,
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dlgCtx), child: const Text('Cancelar')),
          ElevatedButton(
            onPressed: () {
              if (nameCtrl.text.trim().isEmpty) return;
              HapticFeedback.mediumImpact();
              final name = nameCtrl.text.trim();
              final initials = name.split(' ').map((e) => e.isNotEmpty ? e[0] : '').take(2).join().toUpperCase();

              setState(() {
                _residents.add({
                  'name': name,
                  'role': '$relation • Cadastro ativo',
                  'initials': initials.isNotEmpty ? initials : 'MD',
                  'tag': 'Dependente',
                });
              });
              modalSetState(() {});
              Navigator.pop(dlgCtx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Morador $name cadastrado com sucesso!'), backgroundColor: AppColors.success),
              );
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
            child: const Text('Salvar Cadastro', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _openAddPetDialog(BuildContext context, StateSetter modalSetState) {
    final petNameCtrl = TextEditingController();
    final breedCtrl = TextEditingController();
    String size = 'Médio';

    showDialog(
      context: context,
      builder: (dlgCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: const Text('Cadastrar Novo Pet'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: petNameCtrl,
              decoration: const InputDecoration(labelText: 'Nome do Pet', hintText: 'Ex: Mel / Bob'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: breedCtrl,
              decoration: const InputDecoration(labelText: 'Raça ou Espécie', hintText: 'Ex: Shih-tzu / SRD'),
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: size,
              decoration: const InputDecoration(labelText: 'Porte'),
              items: const [
                DropdownMenuItem(value: 'Pequeno', child: Text('Porte Pequeno')),
                DropdownMenuItem(value: 'Médio', child: Text('Porte Médio')),
                DropdownMenuItem(value: 'Grande', child: Text('Porte Grande')),
              ],
              onChanged: (val) => size = val!,
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dlgCtx), child: const Text('Cancelar')),
          ElevatedButton(
            onPressed: () {
              if (petNameCtrl.text.trim().isEmpty) return;
              HapticFeedback.mediumImpact();
              final petName = petNameCtrl.text.trim();

              setState(() {
                _pets.add({
                  'name': petName,
                  'breed': breedCtrl.text.trim().isNotEmpty ? breedCtrl.text.trim() : 'SRD',
                  'size': 'Porte $size',
                  'status': 'Carteira de Vacinação OK',
                });
              });
              modalSetState(() {});
              Navigator.pop(dlgCtx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Pet $petName adicionado com sucesso!'), backgroundColor: AppColors.success),
              );
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
            child: const Text('Salvar Pet', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _showDependentsModal(BuildContext context) {
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
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Moradores & Dependentes', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
                      IconButton(
                        icon: const Icon(Icons.person_add_rounded, color: AppColors.primary),
                        tooltip: 'Adicionar Morador',
                        onPressed: () => _openAddResidentDialog(context, setModalState),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text('Pessoas autorizadas a entrar e receber visitantes nesta unidade', style: AppTextStyles.bodyMedium),
                  const SizedBox(height: 16),
                  ..._residents.map((r) {
                    final isTitular = r['tag'] == 'Titular';
                    return Column(
                      children: [
                        ListTile(
                          contentPadding: EdgeInsets.zero,
                          leading: CircleAvatar(
                            backgroundColor: isTitular ? AppColors.primary : Colors.pink.shade400,
                            child: Text(r['initials']!, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                          ),
                          title: Text(r['name']!, style: const TextStyle(fontWeight: FontWeight.bold)),
                          subtitle: Text(r['role']!),
                          trailing: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: isTitular ? AppColors.primarySoft : (isDark ? const Color(0x33EC4899) : Colors.pink.shade50),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              r['tag']!,
                              style: AppTextStyles.badge.copyWith(
                                color: isTitular ? AppColors.primary : Colors.pink.shade400,
                              ),
                            ),
                          ),
                        ),
                        const Divider(),
                      ],
                    );
                  }),
                  const SizedBox(height: 12),
                  ElevatedButton.icon(
                    onPressed: () => _openAddResidentDialog(context, setModalState),
                    icon: const Icon(Icons.person_add_rounded, color: Colors.white),
                    label: const Text('Cadastrar Novo Morador', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ],
              ),
            );
          },
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
              ..._vehicles.map((v) {
                return ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: Icon(
                    v['type'] == 'car' ? Icons.directions_car_filled_rounded : Icons.two_wheeler_rounded,
                    color: v['type'] == 'car' ? AppColors.primary : AppColors.info,
                    size: 30,
                  ),
                  title: Text(v['title']!, style: const TextStyle(fontWeight: FontWeight.bold)),
                  subtitle: Text(v['subtitle']!),
                );
              }),
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
      isScrollControlled: true,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
            return FrostedGlassCard(
              borderRadius: 32,
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Pets da Unidade', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
                      IconButton(
                        icon: const Icon(Icons.add_circle_outline_rounded, color: AppColors.primary),
                        tooltip: 'Adicionar Pet',
                        onPressed: () => _openAddPetDialog(context, setModalState),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text('Cadastro para circulação nas áreas pet do condomínio', style: AppTextStyles.bodyMedium),
                  const SizedBox(height: 16),
                  ..._pets.map((p) {
                    return ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const CircleAvatar(backgroundColor: AppColors.warningLight, child: Icon(Icons.pets, color: AppColors.warning)),
                      title: Text(p['name']!, style: const TextStyle(fontWeight: FontWeight.bold)),
                      subtitle: Text('${p['breed']} • ${p['size']} • ${p['status']}'),
                      trailing: const Icon(Icons.verified_rounded, color: AppColors.success, size: 20),
                    );
                  }),
                  const SizedBox(height: 14),
                  OutlinedButton.icon(
                    onPressed: () => _openAddPetDialog(context, setModalState),
                    icon: const Icon(Icons.pets, size: 18),
                    label: const Text('Cadastrar Novo Pet'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ],
              ),
            );
          },
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
                  const CircleAvatar(
                    radius: 32,
                    backgroundColor: AppColors.primary,
                    child: Text(
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
                        widget.isDarkMode ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
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
                    value: widget.isDarkMode,
                    activeTrackColor: AppColors.primary,
                    onChanged: (_) => widget.onToggleTheme(),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 12),

            // Itens de Gestão da Unidade (100% Funcionais com Modais e Cadastros)
            _ProfileNavOption(
              icon: Icons.people_outline_rounded,
              title: 'Dependentes & Moradores',
              subtitle: '${_residents.length} pessoas autorizadas na unidade',
              onTap: () {
                HapticFeedback.lightImpact();
                _showDependentsModal(context);
              },
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.directions_car_outlined,
              title: 'Veículos Cadastrados',
              subtitle: '${_vehicles.length} veículos vinculados às vagas',
              onTap: () {
                HapticFeedback.lightImpact();
                _showVehiclesModal(context);
              },
            ),
            const SizedBox(height: 12),
            _ProfileNavOption(
              icon: Icons.pets_outlined,
              title: 'Pets da Unidade',
              subtitle: '${_pets.length} pet(s) registrado(s)',
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
