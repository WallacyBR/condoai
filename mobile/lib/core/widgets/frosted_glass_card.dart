import 'dart:ui';
import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// Card translúcido com efeito de vidro fosco (Frosted Glass / Glassmorphism)
class FrostedGlassCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final EdgeInsetsGeometry? margin;
  final VoidCallback? onTap;
  final double borderRadius;
  final double blur;
  final Color? customColor;
  final Border? customBorder;

  const FrostedGlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.margin,
    this.onTap,
    this.borderRadius = 22,
    this.blur = 16,
    this.customColor,
    this.customBorder,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final defaultBackground = isDark
        ? const Color(0x991E293B) // Slate 800 com 60% opacidade
        : const Color(0xB3FFFFFF); // Branco com 70% opacidade

    final defaultBorder = Border.all(
      color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
      width: 1.2,
    );

    Widget content = Container(
      margin: margin,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(borderRadius),
        boxShadow: [
          BoxShadow(
            color: isDark
                ? const Color(0x33000000)
                : const Color(0x0D0F172A),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(borderRadius),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: blur, sigmaY: blur),
          child: Container(
            padding: padding,
            decoration: BoxDecoration(
              color: customColor ?? defaultBackground,
              borderRadius: BorderRadius.circular(borderRadius),
              border: customBorder ?? defaultBorder,
            ),
            child: child,
          ),
        ),
      ),
    );

    if (onTap != null) {
      return GestureDetector(
        onTap: onTap,
        child: content,
      );
    }

    return content;
  }
}
