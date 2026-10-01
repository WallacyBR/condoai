import 'dart:io';

import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// Avatar que exibe foto local, URL remota ou ícone fallback.
class RegistryAvatar extends StatelessWidget {
  final String? photoPath;
  final IconData fallbackIcon;
  final double size;

  const RegistryAvatar({
    super.key,
    required this.photoPath,
    this.fallbackIcon = Icons.person,
    this.size = 52,
  });

  @override
  Widget build(BuildContext context) {
    if (photoPath == null || photoPath!.isEmpty) {
      return _fallback();
    }

    if (photoPath!.startsWith('http')) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Image.network(
          photoPath!,
          width: size,
          height: size,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _fallback(),
        ),
      );
    }

    final file = File(photoPath!);
    if (file.existsSync()) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Image.file(file, width: size, height: size, fit: BoxFit.cover),
      );
    }

    return _fallback();
  }

  Widget _fallback() {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: AppColors.primarySoft,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Icon(fallbackIcon, color: AppColors.primary),
    );
  }
}
