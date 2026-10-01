import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../core/widgets/registry_avatar.dart';
import '../../../data/local/local_registry_store.dart';
import '../../../data/local/tenant_scope.dart';
import '../../../data/models/registry_models.dart';
import '../../blocs/unit/unit_cubit.dart';
import '../../blocs/unit/unit_state.dart';
import 'pet_form_screen.dart';

/// Gestão de pets com formulário completo e persistência local.
class ManagePetsScreen extends StatefulWidget {
  const ManagePetsScreen({super.key});

  @override
  State<ManagePetsScreen> createState() => _ManagePetsScreenState();
}

class _ManagePetsScreenState extends State<ManagePetsScreen> {
  LocalRegistryStore? _store;
  List<PetRegistry> _pets = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final unitState = context.read<UnitCubit>().state;
    if (unitState is! UnitLoaded) return;
    final scope = TenantScope.fromUnit(unitState.activeUnit);
    final store = await LocalRegistryStore.create(scope: scope);
    final items = await store.loadPets();
    if (mounted) {
      setState(() {
        _store = store;
        _pets = items;
        _loading = false;
      });
    }
  }

  Future<void> _openForm({PetRegistry? existing}) async {
    HapticFeedback.lightImpact();
    final result = await Navigator.push<PetRegistry>(
      context,
      MaterialPageRoute(builder: (_) => PetFormScreen(existing: existing)),
    );
    if (result == null || _store == null) return;

    setState(() {
      final index = _pets.indexWhere((p) => p.id == result.id);
      if (index >= 0) {
        _pets[index] = result;
      } else {
        _pets.add(result);
      }
    });
    await _store!.savePets(_pets);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Pet ${result.name} cadastrado com sucesso!'),
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
          'Pets da Unidade',
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
                    'Pets cadastrados para circulação e acesso ao Pet Place do condomínio.',
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    ),
                  ),
                  const SizedBox(height: 18),
                  ..._pets.map((pet) {
                    return FrostedGlassCard(
                      borderRadius: 20,
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(16),
                      onTap: () => _openForm(existing: pet),
                      child: Row(
                        children: [
                          RegistryAvatar(
                            photoPath: pet.photoPath,
                            fallbackIcon: Icons.pets,
                            size: 60,
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  pet.name,
                                  style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '${pet.displaySpecies} • ${pet.breed.isNotEmpty ? pet.breed : 'SRD'} • ${pet.age}',
                                  style: AppTextStyles.labelSmall.copyWith(
                                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                if (pet.gender.isNotEmpty || pet.weight.isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    [
                                      if (pet.gender.isNotEmpty) pet.gender,
                                      if (pet.weight.isNotEmpty) pet.weight,
                                    ].join(' • '),
                                    style: AppTextStyles.bodyMedium.copyWith(fontSize: 12),
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
                    icon: const Icon(Icons.add_circle_outline_rounded, color: Colors.white),
                    label: const Text(
                      'Cadastrar Novo Pet',
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
