import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../core/widgets/registry_avatar.dart';
import '../../../data/local/local_registry_store.dart';
import '../../../data/models/registry_models.dart';
import 'resident_form_screen.dart';

/// Gestão de moradores/dependentes com formulário completo e persistência local.
class ManageResidentsScreen extends StatefulWidget {
  const ManageResidentsScreen({super.key});

  @override
  State<ManageResidentsScreen> createState() => _ManageResidentsScreenState();
}

class _ManageResidentsScreenState extends State<ManageResidentsScreen> {
  LocalRegistryStore? _store;
  List<ResidentRegistry> _residents = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final store = await LocalRegistryStore.create();
    final items = await store.loadResidents();
    if (mounted) {
      setState(() {
        _store = store;
        _residents = items;
        _loading = false;
      });
    }
  }

  Future<void> _openForm({ResidentRegistry? existing}) async {
    HapticFeedback.lightImpact();
    final result = await Navigator.push<ResidentRegistry>(
      context,
      MaterialPageRoute(builder: (_) => ResidentFormScreen(existing: existing)),
    );
    if (result == null || _store == null) return;

    setState(() {
      final index = _residents.indexWhere((r) => r.id == result.id);
      if (index >= 0) {
        _residents[index] = result;
      } else {
        _residents.add(result);
      }
    });
    await _store!.saveResidents(_residents);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${result.name} cadastrado com sucesso!'),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return BokehScaffold(
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
          'Moradores & Dependentes',
          style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
        ),
        centerTitle: true,
      ),
      body: SafeArea(
        top: false,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
            : ListView(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
                children: [
                  Text(
                    'Gerencie as pessoas com acesso autorizado à sua unidade condominial.',
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    ),
                  ),
                  const SizedBox(height: 18),
                  ..._residents.map((resident) {
                    return FrostedGlassCard(
                      borderRadius: 20,
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(16),
                      onTap: () => _openForm(existing: resident),
                      child: Row(
                        children: [
                          RegistryAvatar(
                            photoPath: resident.photoPath,
                            size: 52,
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
                                        resident.name,
                                        style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    if (resident.isOwner)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: AppColors.primarySoft,
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          'Titular',
                                          style: AppTextStyles.badge.copyWith(color: AppColors.primary),
                                        ),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '${resident.relationship} • ${resident.cpf.isNotEmpty ? resident.cpf : resident.rg}',
                                  style: AppTextStyles.labelSmall.copyWith(
                                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                if (resident.whatsapp.isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    'WhatsApp: ${resident.whatsapp}',
                                    style: AppTextStyles.labelSmall,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ],
                      ),
                    );
                  }),
                  const SizedBox(height: 16),
                  ElevatedButton.icon(
                    onPressed: () => _openForm(),
                    icon: const Icon(Icons.person_add_rounded, color: Colors.white),
                    label: const Text(
                      'Cadastrar Novo Morador',
                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}
