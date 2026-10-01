import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';

/// Tela de Gestão e Cadastro de Veículos com Placa, Vagas e Tag de Acesso
class ManageVehiclesScreen extends StatefulWidget {
  const ManageVehiclesScreen({super.key});

  @override
  State<ManageVehiclesScreen> createState() => _ManageVehiclesScreenState();
}

class VehicleItem {
  final String model;
  final String plate;
  final String color;
  final String parkingSpot;
  final String type; // car, bike, motorcycle
  final bool hasGateTag;
  final String photoUrl;

  VehicleItem({
    required this.model,
    required this.plate,
    required this.color,
    required this.parkingSpot,
    required this.type,
    this.hasGateTag = true,
    required this.photoUrl,
  });
}

class _ManageVehiclesScreenState extends State<ManageVehiclesScreen> {
  final List<VehicleItem> _vehicles = [
    VehicleItem(
      model: 'Jeep Compass Limited',
      plate: 'BRA-4X91',
      color: 'Cinza Metálico',
      parkingSpot: 'Vaga G1 - 42 (Coberta)',
      type: 'car',
      hasGateTag: true,
      photoUrl: 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=400&auto=format&fit=crop&q=80',
    ),
    VehicleItem(
      model: 'Honda CB 500F',
      plate: 'SP-9912',
      color: 'Preta',
      parkingSpot: 'Vaga de Moto M-08',
      type: 'motorcycle',
      hasGateTag: true,
      photoUrl: 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=400&auto=format&fit=crop&q=80',
    ),
  ];

  void _openCreateVehicleModal() {
    final modelCtrl = TextEditingController();
    final plateCtrl = TextEditingController();
    final colorCtrl = TextEditingController();
    final spotCtrl = TextEditingController(text: 'Vaga 42 (G1)');
    String vehicleType = 'car';
    bool hasTag = true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;

        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
            return FrostedGlassCard(
              borderRadius: 28,
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 20),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Cadastrar Novo Veículo',
                          style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                        ),
                        IconButton(
                          icon: Icon(
                            Icons.close_rounded,
                            size: 20,
                            color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                          ),
                          onPressed: () => Navigator.pop(modalCtx),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Tipo de Veículo
                    Row(
                      children: [
                        _TypeSelector(
                          icon: Icons.directions_car_rounded,
                          label: 'Carro',
                          isSelected: vehicleType == 'car',
                          onTap: () => setModalState(() => vehicleType = 'car'),
                          isDark: isDark,
                        ),
                        const SizedBox(width: 8),
                        _TypeSelector(
                          icon: Icons.two_wheeler_rounded,
                          label: 'Moto',
                          isSelected: vehicleType == 'motorcycle',
                          onTap: () => setModalState(() => vehicleType = 'motorcycle'),
                          isDark: isDark,
                        ),
                        const SizedBox(width: 8),
                        _TypeSelector(
                          icon: Icons.pedal_bike_rounded,
                          label: 'Bicicleta',
                          isSelected: vehicleType == 'bike',
                          onTap: () => setModalState(() => vehicleType = 'bike'),
                          isDark: isDark,
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    TextField(
                      controller: modelCtrl,
                      decoration: InputDecoration(
                        labelText: 'Marca e Modelo *',
                        hintText: 'Ex: Toyota Corolla / Honda Civic',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: plateCtrl,
                            textCapitalization: TextCapitalization.characters,
                            decoration: InputDecoration(
                              labelText: 'Placa *',
                              hintText: 'ABC-1D23',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: colorCtrl,
                            decoration: InputDecoration(
                              labelText: 'Cor',
                              hintText: 'Ex: Prata / Preto',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: spotCtrl,
                      decoration: InputDecoration(
                        labelText: 'Vaga Vinculada',
                        hintText: 'Ex: Vaga 42 - Subsolo G1',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    SwitchListTile.adaptive(
                      contentPadding: EdgeInsets.zero,
                      value: hasTag,
                      activeTrackColor: AppColors.primary,
                      title: const Text('Ativar Leitura LPR de Placa / Tag da Cancela', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      onChanged: (val) => setModalState(() => hasTag = val),
                    ),

                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () {
                        if (modelCtrl.text.trim().isEmpty || plateCtrl.text.trim().isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Informe modelo e placa do veículo.'), backgroundColor: AppColors.danger),
                          );
                          return;
                        }

                        HapticFeedback.heavyImpact();
                        setState(() {
                          _vehicles.add(
                            VehicleItem(
                              model: modelCtrl.text.trim(),
                              plate: plateCtrl.text.trim().toUpperCase(),
                              color: colorCtrl.text.trim().isNotEmpty ? colorCtrl.text.trim() : 'Não informada',
                              parkingSpot: spotCtrl.text.trim().isNotEmpty ? spotCtrl.text.trim() : 'Vaga Livre',
                              type: vehicleType,
                              hasGateTag: hasTag,
                              photoUrl: vehicleType == 'motorcycle'
                                  ? 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=400&auto=format&fit=crop&q=80'
                                  : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=400&auto=format&fit=crop&q=80',
                            ),
                          );
                        });
                        Navigator.pop(modalCtx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Veículo ${plateCtrl.text.trim().toUpperCase()} cadastrado com sucesso!'),
                            backgroundColor: AppColors.success,
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: const Text('Salvar Veículo', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
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
        child: ListView(
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
                child: Row(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Image.network(
                        v.photoUrl,
                        width: 60,
                        height: 60,
                        fit: BoxFit.cover,
                        errorBuilder: (_, error, stack) => Container(
                          width: 60,
                          height: 60,
                          color: AppColors.primarySoft,
                          child: Icon(
                            v.type == 'car' ? Icons.directions_car_rounded : Icons.two_wheeler_rounded,
                            color: AppColors.primary,
                          ),
                        ),
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
                                  border: Border.all(color: Colors.blueGrey.shade400, width: 1),
                                ),
                                child: Text(
                                  v.plate,
                                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 11, letterSpacing: 0.8),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '${v.color} • ${v.parkingSpot}',
                            style: AppTextStyles.labelSmall.copyWith(
                              color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Row(
                            children: [
                              Icon(
                                v.hasGateTag ? Icons.sensors_rounded : Icons.sensors_off_rounded,
                                size: 14,
                                color: v.hasGateTag ? AppColors.success : Colors.grey,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                v.hasGateTag ? 'Acesso Automático LPR Ativo' : 'Acesso Manual',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: v.hasGateTag ? AppColors.success : Colors.grey,
                                  fontWeight: FontWeight.w500,
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
            }),

            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _openCreateVehicleModal,
              icon: const Icon(Icons.add_circle_outline_rounded, color: Colors.white),
              label: const Text('Cadastrar Novo Veículo', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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

class _TypeSelector extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool isSelected;
  final VoidCallback onTap;
  final bool isDark;

  const _TypeSelector({
    required this.icon,
    required this.label,
    required this.isSelected,
    required this.onTap,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primarySoft : Colors.transparent,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected ? AppColors.primary : (isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder),
              width: isSelected ? 2 : 1,
            ),
          ),
          child: Column(
            children: [
              Icon(icon, size: 20, color: isSelected ? AppColors.primary : Colors.grey),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  color: isSelected ? AppColors.primary : (isDark ? Colors.white70 : Colors.black87),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
