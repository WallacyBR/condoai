import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/models/registry_models.dart';

/// Formulário completo de veículo espelhando VehicleFormPage (web).
class VehicleFormScreen extends StatefulWidget {
  final VehicleRegistry? existing;

  const VehicleFormScreen({super.key, this.existing});

  @override
  State<VehicleFormScreen> createState() => _VehicleFormScreenState();
}

class _VehicleFormScreenState extends State<VehicleFormScreen> {
  final _plateCtrl = TextEditingController();
  final _modelCtrl = TextEditingController();
  final _colorCtrl = TextEditingController();
  final _yearCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();

  String _parkingSpot = '';

  static const _mockParkingSpots = [
    '',
    'Vaga G1 - 42 (Coberta)',
    'Vaga G1 - 43 (Coberta)',
    'Vaga de Moto M-08',
    'Vaga Visitante V-12',
  ];

  @override
  void initState() {
    super.initState();
    final v = widget.existing;
    if (v != null) {
      _plateCtrl.text = v.licensePlate;
      _modelCtrl.text = v.model;
      _colorCtrl.text = v.color;
      _yearCtrl.text = v.year;
      _parkingSpot = v.parkingSpot;
      _notesCtrl.text = v.notes;
    }
  }

  @override
  void dispose() {
    _plateCtrl.dispose();
    _modelCtrl.dispose();
    _colorCtrl.dispose();
    _yearCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  void _formatPlate(String value) {
    var cleaned = value.replaceAll(RegExp(r'[^A-Za-z0-9]'), '').toUpperCase();
    if (cleaned.length > 7) cleaned = cleaned.substring(0, 7);
    if (cleaned.length > 3) {
      cleaned = '${cleaned.substring(0, 3)}-${cleaned.substring(3)}';
    }
    _plateCtrl.value = TextEditingValue(
      text: cleaned,
      selection: TextSelection.collapsed(offset: cleaned.length),
    );
  }

  void _submit() {
    if (_plateCtrl.text.trim().isEmpty || _modelCtrl.text.trim().isEmpty || _colorCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Informe placa, modelo e cor do veículo.'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    HapticFeedback.heavyImpact();
    final vehicle = VehicleRegistry(
      id: widget.existing?.id ?? 'veh-${DateTime.now().millisecondsSinceEpoch}',
      licensePlate: _plateCtrl.text.trim().toUpperCase(),
      model: _modelCtrl.text.trim(),
      color: _colorCtrl.text.trim(),
      year: _yearCtrl.text.trim(),
      parkingSpot: _parkingSpot,
      notes: _notesCtrl.text.trim(),
    );
    Navigator.pop(context, vehicle);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final isEditing = widget.existing != null;

    return BokehScaffold(
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios_new_rounded,
              color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          isEditing ? 'Editar Veículo' : 'Novo Veículo',
          style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: FrostedGlassCard(
          borderRadius: 24,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextField(
                controller: _plateCtrl,
                textCapitalization: TextCapitalization.characters,
                onChanged: _formatPlate,
                decoration: InputDecoration(
                  labelText: 'Placa do Veículo *',
                  hintText: 'ABC-1234',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _modelCtrl,
                decoration: InputDecoration(
                  labelText: 'Modelo *',
                  hintText: 'Honda Civic, Toyota Corolla...',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _colorCtrl,
                      decoration: InputDecoration(
                        labelText: 'Cor *',
                        hintText: 'Branco, Preto, Azul...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: TextField(
                      controller: _yearCtrl,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: 'Ano',
                        hintText: '2020',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _parkingSpot,
                decoration: InputDecoration(
                  labelText: 'Vaga de Estacionamento',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
                items: _mockParkingSpots
                    .map((s) => DropdownMenuItem(
                          value: s,
                          child: Text(s.isEmpty ? 'Sem vaga fixa' : s),
                        ))
                    .toList(),
                onChanged: (v) => setState(() => _parkingSpot = v ?? ''),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _notesCtrl,
                maxLines: 3,
                decoration: InputDecoration(
                  labelText: 'Observações',
                  hintText: 'Informações adicionais sobre o veículo...',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                child: Text(
                  isEditing ? 'Atualizar Veículo' : 'Cadastrar Veículo',
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
