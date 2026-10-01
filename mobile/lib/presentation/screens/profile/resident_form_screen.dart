import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../core/widgets/photo_picker_field.dart';
import '../../../data/models/registry_models.dart';

/// Formulário completo de morador espelhando ResidentFormPage (web).
class ResidentFormScreen extends StatefulWidget {
  final ResidentRegistry? existing;

  const ResidentFormScreen({super.key, this.existing});

  @override
  State<ResidentFormScreen> createState() => _ResidentFormScreenState();
}

class _ResidentFormScreenState extends State<ResidentFormScreen> {
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _rgCtrl = TextEditingController();
  final _cpfCtrl = TextEditingController();
  final _cellPhoneCtrl = TextEditingController();
  final _commercialPhoneCtrl = TextEditingController();
  final _whatsappCtrl = TextEditingController();
  final _cepCtrl = TextEditingController();
  final _addressCtrl = TextEditingController();
  final _neighborhoodCtrl = TextEditingController();
  final _cityCtrl = TextEditingController();
  final _stateCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();

  String _personType = 'fisica';
  String _classification = 'morador';
  String _relationship = 'Morador';
  String _billingAddressType = 'condominium';
  String? _photoPath;
  bool _facialAccess = true;
  bool _visitorAccess = true;

  static const _ufOptions = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
  ];

  @override
  void initState() {
    super.initState();
    final r = widget.existing;
    if (r != null) {
      _nameCtrl.text = r.name;
      _emailCtrl.text = r.email;
      _rgCtrl.text = r.rg;
      _cpfCtrl.text = r.cpf;
      _cellPhoneCtrl.text = r.cellPhone;
      _commercialPhoneCtrl.text = r.commercialPhone;
      _whatsappCtrl.text = r.whatsapp;
      _cepCtrl.text = r.cep;
      _addressCtrl.text = r.address;
      _neighborhoodCtrl.text = r.neighborhood;
      _cityCtrl.text = r.city;
      _stateCtrl.text = r.state;
      _notesCtrl.text = r.notes;
      _personType = r.personType;
      _classification = r.classification;
      _relationship = r.relationship;
      _billingAddressType = r.billingAddressType;
      _photoPath = r.photoPath;
      _facialAccess = r.hasFacialAccess;
      _visitorAccess = r.canAuthorizeVisitors;
    }
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _rgCtrl.dispose();
    _cpfCtrl.dispose();
    _cellPhoneCtrl.dispose();
    _commercialPhoneCtrl.dispose();
    _whatsappCtrl.dispose();
    _cepCtrl.dispose();
    _addressCtrl.dispose();
    _neighborhoodCtrl.dispose();
    _cityCtrl.dispose();
    _stateCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  void _submit() {
    if (_nameCtrl.text.trim().isEmpty) {
      _showError('Informe o nome completo.');
      return;
    }
    if (_cpfCtrl.text.trim().isEmpty) {
      _showError('Informe o CPF (campo obrigatório).');
      return;
    }
    if (_classification.isEmpty) {
      _showError('Selecione a classificação.');
      return;
    }

    HapticFeedback.heavyImpact();
    final resident = ResidentRegistry(
      id: widget.existing?.id ?? 'res-${DateTime.now().millisecondsSinceEpoch}',
      name: _nameCtrl.text.trim(),
      email: _emailCtrl.text.trim(),
      rg: _rgCtrl.text.trim(),
      cpf: _cpfCtrl.text.trim(),
      personType: _personType,
      cellPhone: _cellPhoneCtrl.text.trim(),
      commercialPhone: _commercialPhoneCtrl.text.trim(),
      whatsapp: _whatsappCtrl.text.trim(),
      billingAddressType: _billingAddressType,
      cep: _cepCtrl.text.trim(),
      address: _addressCtrl.text.trim(),
      neighborhood: _neighborhoodCtrl.text.trim(),
      city: _cityCtrl.text.trim(),
      state: _stateCtrl.text.trim(),
      notes: _notesCtrl.text.trim(),
      photoPath: _photoPath,
      classification: _classification,
      relationship: _relationship,
      hasFacialAccess: _facialAccess,
      canAuthorizeVisitors: _visitorAccess,
      isOwner: _classification == 'proprietario' || (widget.existing?.isOwner ?? false),
    );
    Navigator.pop(context, resident);
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: AppColors.danger),
    );
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
          isEditing ? 'Editar Morador' : 'Novo Morador',
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
              Text('Dados Básicos', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              PhotoPickerField(
                photoPath: _photoPath,
                onChanged: (path) => setState(() => _photoPath = path),
                label: 'Foto do Morador',
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _nameCtrl,
                decoration: InputDecoration(
                  labelText: 'Nome Completo *',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _emailCtrl,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: 'E-mail *',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _personType,
                decoration: InputDecoration(
                  labelText: 'Tipo de Pessoa *',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
                items: const [
                  DropdownMenuItem(value: 'fisica', child: Text('Física')),
                  DropdownMenuItem(value: 'juridica', child: Text('Jurídica')),
                ],
                onChanged: (v) => setState(() => _personType = v!),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _rgCtrl,
                      decoration: InputDecoration(
                        labelText: 'RG',
                        hintText: '00.000.000-0',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: TextField(
                      controller: _cpfCtrl,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: 'CPF *',
                        hintText: '000.000.000-00',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _cellPhoneCtrl,
                keyboardType: TextInputType.phone,
                decoration: InputDecoration(
                  labelText: 'Telefone Celular',
                  hintText: '(11) 99999-9999',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _commercialPhoneCtrl,
                keyboardType: TextInputType.phone,
                decoration: InputDecoration(
                  labelText: 'Telefone Comercial',
                  hintText: '(11) 3444-5555',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _whatsappCtrl,
                keyboardType: TextInputType.phone,
                decoration: InputDecoration(
                  labelText: 'WhatsApp',
                  hintText: '(11) 99999-9999',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 16),
              Text('Classificação & Parentesco', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _classification,
                decoration: InputDecoration(
                  labelText: 'Classificação *',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
                items: const [
                  DropdownMenuItem(value: 'proprietario', child: Text('Proprietário')),
                  DropdownMenuItem(value: 'morador', child: Text('Morador')),
                  DropdownMenuItem(value: 'inquilino', child: Text('Inquilino')),
                ],
                onChanged: (v) => setState(() => _classification = v!),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _relationship,
                decoration: InputDecoration(
                  labelText: 'Parentesco na Unidade',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
                items: const [
                  DropdownMenuItem(value: 'Morador', child: Text('Morador')),
                  DropdownMenuItem(value: 'Cônjuge', child: Text('Cônjuge')),
                  DropdownMenuItem(value: 'Filho(a)', child: Text('Filho(a)')),
                  DropdownMenuItem(value: 'Titular', child: Text('Titular')),
                  DropdownMenuItem(value: 'Prestador', child: Text('Funcionário')),
                ],
                onChanged: (v) => setState(() => _relationship = v!),
              ),
              const SizedBox(height: 16),
              Text('Endereço para Recebimento de Boletos', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              RadioListTile<String>(
                contentPadding: EdgeInsets.zero,
                title: const Text('Usar endereço do condomínio', style: TextStyle(fontSize: 13)),
                value: 'condominium',
                groupValue: _billingAddressType,
                onChanged: (v) => setState(() => _billingAddressType = v!),
              ),
              RadioListTile<String>(
                contentPadding: EdgeInsets.zero,
                title: const Text('Usar endereço personalizado', style: TextStyle(fontSize: 13)),
                value: 'custom',
                groupValue: _billingAddressType,
                onChanged: (v) => setState(() => _billingAddressType = v!),
              ),
              if (_billingAddressType == 'custom') ...[
                const SizedBox(height: 8),
                TextField(
                  controller: _cepCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: 'CEP',
                    hintText: '00000-000',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _addressCtrl,
                  decoration: InputDecoration(
                    labelText: 'Endereço',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _neighborhoodCtrl,
                        decoration: InputDecoration(
                          labelText: 'Bairro',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: TextField(
                        controller: _cityCtrl,
                        decoration: InputDecoration(
                          labelText: 'Cidade',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: _stateCtrl.text.isEmpty ? null : _stateCtrl.text,
                  decoration: InputDecoration(
                    labelText: 'UF',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  items: _ufOptions.map((uf) => DropdownMenuItem(value: uf, child: Text(uf))).toList(),
                  onChanged: (v) => setState(() => _stateCtrl.text = v ?? ''),
                ),
              ],
              const SizedBox(height: 12),
              TextField(
                controller: _notesCtrl,
                maxLines: 3,
                decoration: InputDecoration(
                  labelText: 'Observações',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
              const SizedBox(height: 8),
              SwitchListTile.adaptive(
                contentPadding: EdgeInsets.zero,
                value: _facialAccess,
                activeTrackColor: AppColors.primary,
                title: const Text('Liberação Facial / Biometria',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                onChanged: (v) => setState(() => _facialAccess = v),
              ),
              SwitchListTile.adaptive(
                contentPadding: EdgeInsets.zero,
                value: _visitorAccess,
                activeTrackColor: AppColors.primary,
                title: const Text('Pode Autorizar Visitantes',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                onChanged: (v) => setState(() => _visitorAccess = v),
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
                  isEditing ? 'Atualizar Morador' : 'Salvar Cadastro',
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
