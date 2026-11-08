import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../core/widgets/photo_picker_field.dart';
import '../../../data/models/registry_models.dart';

/// Formulário completo de pet espelhando PetFormPage (web).
class PetFormScreen extends StatefulWidget {
  final PetRegistry? existing;

  const PetFormScreen({super.key, this.existing});

  @override
  State<PetFormScreen> createState() => _PetFormScreenState();
}

class _PetFormScreenState extends State<PetFormScreen> {
  final _nameCtrl = TextEditingController();
  final _customSpeciesCtrl = TextEditingController();
  final _breedCtrl = TextEditingController();
  final _ageCtrl = TextEditingController();
  final _weightCtrl = TextEditingController();
  final _healthCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();

  String _species = '';
  String _gender = '';
  String? _photoPath;

  static const _speciesOptions = [
    'Cão',
    'Gato',
    'Pássaro',
    'Peixe',
    'Hamster',
    'Coelho',
    'Tartaruga',
    'Outro',
  ];

  @override
  void initState() {
    super.initState();
    final p = widget.existing;
    if (p != null) {
      _nameCtrl.text = p.name;
      _species = p.species;
      _customSpeciesCtrl.text = p.customSpecies;
      _breedCtrl.text = p.breed;
      _gender = p.gender;
      _ageCtrl.text = p.age;
      _weightCtrl.text = p.weight;
      _healthCtrl.text = p.healthInfo;
      _notesCtrl.text = p.notes;
      _photoPath = p.photoPath;
    }
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _customSpeciesCtrl.dispose();
    _breedCtrl.dispose();
    _ageCtrl.dispose();
    _weightCtrl.dispose();
    _healthCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  void _submit() {
    if (_nameCtrl.text.trim().isEmpty || _species.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Informe nome e espécie do pet.'), backgroundColor: AppColors.danger),
      );
      return;
    }
    if (_species == 'Outro' && _customSpeciesCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Especifique a espécie.'), backgroundColor: AppColors.danger),
      );
      return;
    }

    HapticFeedback.heavyImpact();
    final pet = PetRegistry(
      id: widget.existing?.id ?? 'pet-${DateTime.now().millisecondsSinceEpoch}',
      name: _nameCtrl.text.trim(),
      species: _species,
      customSpecies: _customSpeciesCtrl.text.trim(),
      breed: _breedCtrl.text.trim(),
      gender: _gender,
      age: _ageCtrl.text.trim(),
      weight: _weightCtrl.text.trim(),
      healthInfo: _healthCtrl.text.trim(),
      notes: _notesCtrl.text.trim(),
      photoPath: _photoPath,
    );
    Navigator.pop(context, pet);
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
          isEditing ? 'Editar Pet' : 'Novo Pet',
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
              PhotoPickerField(
                photoPath: _photoPath,
                onChanged: (path) => setState(() => _photoPath = path),
                label: 'Foto do Pet',
                hint: 'PNG, JPG até 5MB',
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _nameCtrl,
                decoration: InputDecoration(
                  labelText: 'Nome do Pet *',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _species.isEmpty ? null : _species,
                decoration: InputDecoration(
                  labelText: 'Espécie *',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
                items: _speciesOptions
                    .map((s) => DropdownMenuItem(value: s, child: Text(s)))
                    .toList(),
                onChanged: (v) => setState(() => _species = v ?? ''),
              ),
              if (_species == 'Outro') ...[
                const SizedBox(height: 12),
                TextField(
                  controller: _customSpeciesCtrl,
                  decoration: InputDecoration(
                    labelText: 'Especifique a Espécie *',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ],
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _breedCtrl,
                      decoration: InputDecoration(
                        labelText: 'Raça',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      initialValue: _gender.isEmpty ? null : _gender,
                      decoration: InputDecoration(
                        labelText: 'Sexo',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'Macho', child: Text('Macho')),
                        DropdownMenuItem(value: 'Fêmea', child: Text('Fêmea')),
                      ],
                      onChanged: (v) => setState(() => _gender = v ?? ''),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _ageCtrl,
                      decoration: InputDecoration(
                        labelText: 'Idade',
                        hintText: '3 anos, 6 meses...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: TextField(
                      controller: _weightCtrl,
                      decoration: InputDecoration(
                        labelText: 'Peso',
                        hintText: '5.2 kg, 300g...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _healthCtrl,
                maxLines: 3,
                decoration: InputDecoration(
                  labelText: 'Informações de Saúde',
                  hintText: 'Vacinação, medicamentos, alergias...',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _notesCtrl,
                maxLines: 3,
                decoration: InputDecoration(
                  labelText: 'Observações Gerais',
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
                  isEditing ? 'Atualizar Pet' : 'Cadastrar Pet',
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
