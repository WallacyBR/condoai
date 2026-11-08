import 'package:flutter/material.dart';

/// Exibe imagem de rede ou placeholder quando URL vazia (sem mocks externos).
class OptionalNetworkImage extends StatelessWidget {
  final String url;
  final BoxFit fit;
  final double? height;
  final double? width;
  final IconData placeholderIcon;

  const OptionalNetworkImage({
    super.key,
    required this.url,
    this.fit = BoxFit.cover,
    this.height,
    this.width,
    this.placeholderIcon = Icons.image_outlined,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    if (url.isEmpty) {
      return Container(
        height: height,
        width: width,
        color: isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0),
        child: Center(child: Icon(placeholderIcon, size: 48, color: Colors.grey)),
      );
    }

    return Image.network(
      url,
      fit: fit,
      height: height,
      width: width,
      errorBuilder: (_, __, ___) => Container(
        height: height,
        width: width,
        color: isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0),
        child: Center(child: Icon(placeholderIcon, size: 48, color: Colors.grey)),
      ),
    );
  }
}
