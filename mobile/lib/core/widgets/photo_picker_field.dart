import 'dart:io';

import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../theme/app_text_styles.dart';
import '../utils/media_picker_helper.dart';

/// Campo de upload de foto com preview, espelhando o FileUpload do formulário web.
class PhotoPickerField extends StatelessWidget {
  final String? photoPath;
  final ValueChanged<String?> onChanged;
  final String label;
  final String hint;

  const PhotoPickerField({
    super.key,
    required this.photoPath,
    required this.onChanged,
    this.label = 'Foto',
    this.hint = 'Toque para selecionar uma foto',
  });

  Future<void> _pick(BuildContext context) async {
    final attachment = await MediaPickerHelper.pickPhoto();
    if (attachment != null) {
      onChanged(attachment.path);
    }
  }

  Widget _buildPreview() {
    if (photoPath == null || photoPath!.isEmpty) {
      return const Icon(Icons.add_a_photo_outlined, color: AppColors.primary, size: 32);
    }

    if (photoPath!.startsWith('http')) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: Image.network(photoPath!, width: 72, height: 72, fit: BoxFit.cover),
      );
    }

    final file = File(photoPath!);
    if (file.existsSync()) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: Image.file(file, width: 72, height: 72, fit: BoxFit.cover),
      );
    }

    return const Icon(Icons.broken_image_outlined, color: AppColors.danger);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: AppTextStyles.labelSmall.copyWith(fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        InkWell(
          onTap: () => _pick(context),
          borderRadius: BorderRadius.circular(16),
          child: Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
                width: 1.5,
              ),
              color: isDark ? const Color(0x1AFFFFFF) : const Color(0xFFF8FAFC),
            ),
            child: Row(
              children: [
                _buildPreview(),
                const SizedBox(width: 14),
                Expanded(
                  child: Text(
                    photoPath == null ? hint : 'Toque para alterar a foto',
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                    ),
                  ),
                ),
                if (photoPath != null)
                  IconButton(
                    icon: const Icon(Icons.close_rounded, size: 18),
                    onPressed: () => onChanged(null),
                    tooltip: 'Remover foto',
                  ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
