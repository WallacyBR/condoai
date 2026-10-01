import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/local/local_registry_store.dart';
import '../../../data/local/tenant_scope.dart';
import '../../../data/models/registry_models.dart';
import '../../blocs/unit/unit_cubit.dart';
import '../../blocs/unit/unit_state.dart';
import 'vehicle_form_screen.dart';

/// Gestão de veículos com formulário completo e persistência local.
class ManageVehiclesScreen extends StatefulWidget {
  const ManageVehiclesScreen({super.key});

  @override
  State<ManageVehiclesScreen> createState() => _ManageVehiclesScreenState();
}

class _ManageVehiclesScreenState extends State<ManageVehiclesScreen> {
  LocalRegistryStore? _store;
  List<VehicleRegistry> _vehicles = [];
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
    final items = await store.loadVehicles();
    if (mounted) {
      setState(() {
        _store = store;
        _vehicles = items;
        _loading = false;
      });
    }
  }

  Future<void> _openForm({VehicleRegistry? existing}) async {
    HapticFeedback.lightImpact();
    final result = await Navigator.push<VehicleRegistry>(
      context,
      MaterialPageRoute(builder: (_) => VehicleFormScreen(existing: existing)),
    );
    if (result == null || _store == null) return;

    setState(() {
      final index = _vehicles.indexWhere((v) => v.id == result.id);
      if (index >= 0) {
        _vehicles[index] = result;
      } else {
        _vehicles.add(result);
      }
    });
    await _store!.saveVehicles(_vehicles);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Veículo ${result.licensePlate} cadastrado com sucesso!'),
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
          'Veículos Cadastrados',
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
                    'Veículos autorizados para entrada automática pela portaria e cancelas.',
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    ),
                  ),
                  const SizedBox(height: 18),
                  ..._vehicles.map((v) {
                    return FrostedGlassCard(
                      borderRadius: 20,
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(16),
                      onTap: () => _openForm(existing: v),
                      child: Row(
                        children: [
                          Container(
                            width: 60,
                            height: 60,
                            decoration: BoxDecoration(
                              color: AppColors.primarySoft,
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: const Icon(Icons.directions_car_rounded, color: AppColors.primary),
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
                                        v.model,
                                        style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(color: Colors.blueGrey.shade400),
                                      ),
                                      child: Text(
                                        v.licensePlate,
                                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 11),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  '${v.color}${v.year.isNotEmpty ? ' • $v.year' : ''}',
                                  style: AppTextStyles.labelSmall.copyWith(
                                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                if (v.parkingSpot.isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    v.parkingSpot,
                                    style: AppTextStyles.bodyMedium.copyWith(fontSize: 12),
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
                    icon: const Icon(Icons.add_circle_outline_rounded, color: Colors.white),
                    label: const Text(
                      'Cadastrar Novo Veículo',
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
