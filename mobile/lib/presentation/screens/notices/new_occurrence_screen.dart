import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/utils/media_picker_helper.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/models/occurrence_model.dart';

/// Tela completa de Nova Ocorrência com suporte a fotos, vídeos e níveis de gravidade.
class NewOccurrenceScreen extends StatefulWidget {
  final void Function(OccurrenceItem item)? onOccurrenceCreated;

  const NewOccurrenceScreen({
    super.key,
    this.onOccurrenceCreated,
  });

  @override
  State<NewOccurrenceScreen> createState() => _NewOccurrenceScreenState();
}

class _NewOccurrenceScreenState extends State<NewOccurrenceScreen> {
  final _titleController = TextEditingController();
  final _descController = TextEditingController();
  final _locationController = TextEditingController();
  String _selectedCategory = 'Manutenção Predial';
  String _selectedUrgency = 'Média';
  final List<MediaAttachment> _attachments = [];

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    _locationController.dispose();
    super.dispose();
  }

  Future<void> _addMedia() async {
    if (_attachments.length >= 3) return;

    await MediaPickerHelper.showPhotoVideoPickerSheet(
      context,
      maxItems: 3,
      currentCount: _attachments.length,
      onSelected: (attachment) {
        HapticFeedback.lightImpact();
        setState(() => _attachments.add(attachment));
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(attachment.isVideo ? 'Vídeo anexado!' : 'Foto anexada com sucesso!'),
            duration: const Duration(seconds: 1),
          ),
        );
      },
    );
  }

  OccurrenceItem _buildItem() {
    final protocol = 'OC-${DateTime.now().millisecondsSinceEpoch.toString().substring(8)}';
    return OccurrenceItem(
      protocol: protocol,
      title: _titleController.text.trim(),
      category: _selectedCategory,
      date: 'Hoje às ${TimeOfDay.now().format(context)}',
      status: 'Em Análise',
      description: _descController.text.trim(),
      location: _locationController.text.trim(),
      urgency: _selectedUrgency,
      attachments: _attachments
          .map((a) => OccurrenceAttachment(path: a.path, isVideo: a.isVideo))
          .toList(),
    );
  }

  void _submit() {
    if (_titleController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Informe o título do chamado.'), backgroundColor: AppColors.danger),
      );
      return;
    }

    HapticFeedback.heavyImpact();
    final item = _buildItem();
    widget.onOccurrenceCreated?.call(item);
    Navigator.pop(context, item);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Ocorrência #${item.protocol} enviada à administração!'),
        backgroundColor: AppColors.success,
      ),
    );
  }

  Widget _attachmentPreview(MediaAttachment attachment) {
    if (attachment.isVideo) {
      return Container(
        color: Colors.black87,
        child: const Center(
          child: Icon(Icons.videocam_rounded, color: Colors.white, size: 28),
        ),
      );
    }

    return Image.file(File(attachment.path), fit: BoxFit.cover);
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
          'Nova Ocorrência',
          style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            FrostedGlassCard(
              borderRadius: 24,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Classificação', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 14),
                  DropdownButtonFormField<String>(
                    initialValue: _selectedCategory,
                    decoration: InputDecoration(
                      labelText: 'Categoria do Chamado',
                      prefixIcon: const Icon(Icons.category_outlined),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    items: const [
                      DropdownMenuItem(value: 'Manutenção Predial', child: Text('Manutenção Predial')),
                      DropdownMenuItem(value: 'Barulho Excessivo', child: Text('Barulho / Convivência')),
                      DropdownMenuItem(value: 'Vaga de Garagem', child: Text('Vaga de Garagem')),
                      DropdownMenuItem(value: 'Limpeza de Áreas', child: Text('Limpeza e Conservação')),
                      DropdownMenuItem(value: 'Segurança', child: Text('Segurança Patrimonial')),
                    ],
                    onChanged: (val) => setState(() => _selectedCategory = val!),
                  ),
                  const SizedBox(height: 14),
                  Text('Gravidade / Urgência', style: AppTextStyles.labelSmall),
                  const SizedBox(height: 6),
                  Row(
                    children: ['Baixa', 'Média', 'Alta / Urgente'].map((urgency) {
                      final isSelected = _selectedUrgency == urgency;
                      final color = urgency.startsWith('Alta')
                          ? AppColors.danger
                          : urgency == 'Média'
                              ? AppColors.warning
                              : AppColors.info;

                      return Expanded(
                        child: GestureDetector(
                          onTap: () => setState(() => _selectedUrgency = urgency),
                          child: Container(
                            margin: const EdgeInsets.symmetric(horizontal: 4),
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            decoration: BoxDecoration(
                              color: isSelected ? color.withAlpha(isDark ? 60 : 30) : Colors.transparent,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: isSelected ? color : (isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder),
                                width: isSelected ? 2 : 1,
                              ),
                            ),
                            child: Center(
                              child: Text(
                                urgency,
                                style: AppTextStyles.badge.copyWith(
                                  color: isSelected ? color : (isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary),
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                                  fontSize: 11,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            FrostedGlassCard(
              borderRadius: 24,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Detalhes do Ocorrido', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 14),
                  TextField(
                    controller: _titleController,
                    decoration: InputDecoration(
                      labelText: 'Título do Chamado',
                      hintText: 'Ex: Lâmpada queimada no 4º andar',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                  const SizedBox(height: 14),
                  TextField(
                    controller: _locationController,
                    decoration: InputDecoration(
                      labelText: 'Local Específico',
                      hintText: 'Ex: Hall dos elevadores do Bloco Primavera',
                      prefixIcon: const Icon(Icons.place_outlined),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                  const SizedBox(height: 14),
                  TextField(
                    controller: _descController,
                    maxLines: 4,
                    decoration: InputDecoration(
                      labelText: 'Descrição Completa',
                      hintText: 'Descreva detalhadamente o ocorrido...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            FrostedGlassCard(
              borderRadius: 24,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Fotos & Comprovantes', style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold)),
                      Text('${_attachments.length}/3 anexos', style: AppTextStyles.labelSmall),
                    ],
                  ),
                  const SizedBox(height: 12),
                  if (_attachments.isNotEmpty)
                    SizedBox(
                      height: 80,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        itemCount: _attachments.length,
                        itemBuilder: (context, index) {
                          return Stack(
                            children: [
                              Container(
                                margin: const EdgeInsets.only(right: 10),
                                width: 80,
                                height: 80,
                                clipBehavior: Clip.hardEdge,
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                child: _attachmentPreview(_attachments[index]),
                              ),
                              Positioned(
                                top: 2,
                                right: 12,
                                child: GestureDetector(
                                  onTap: () => setState(() => _attachments.removeAt(index)),
                                  child: Container(
                                    padding: const EdgeInsets.all(3),
                                    decoration: const BoxDecoration(
                                      color: Colors.black87,
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(Icons.close, color: Colors.white, size: 14),
                                  ),
                                ),
                              ),
                            ],
                          );
                        },
                      ),
                    ),
                  const SizedBox(height: 12),
                  InkWell(
                    onTap: _attachments.length >= 3 ? null : _addMedia,
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0x266366F1) : AppColors.primarySoft,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isDark ? const Color(0x4D818CF8) : const Color(0x334F46E5),
                          width: 1.5,
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.add_a_photo_rounded, color: AppColors.primary, size: 20),
                          const SizedBox(width: 10),
                          Flexible(
                            child: Text(
                              _attachments.length >= 3
                                  ? 'Limite de 3 anexos atingido'
                                  : 'Tirar Foto ou Anexar Vídeo',
                              style: AppTextStyles.titleSmall.copyWith(
                                color: AppColors.primary,
                                fontWeight: FontWeight.w600,
                                fontSize: 14,
                              ),
                              textAlign: TextAlign.center,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _submit,
              icon: const Icon(Icons.send_rounded, color: Colors.white),
              label: const Text(
                'Registrar Ocorrência',
                style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
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
