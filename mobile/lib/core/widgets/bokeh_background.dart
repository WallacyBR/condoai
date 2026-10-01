import 'dart:ui';
import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// Fundo dinâmico com esferas orgânicas de luz Bokeh e desfoque gaussiano
class BokehBackground extends StatelessWidget {
  final Widget child;

  const BokehBackground({
    super.key,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Stack(
      children: [
        // Cor base de fundo
        Container(
          color: isDark ? AppColors.darkBackground : AppColors.lightBackground,
        ),

        // Círculos de luz Bokeh
        Positioned(
          top: -60,
          left: -40,
          child: Container(
            width: 260,
            height: 260,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isDark ? AppColors.bokehIndigo : const Color(0x33C7D2FE),
            ),
          ),
        ),
        Positioned(
          top: 180,
          right: -80,
          child: Container(
            width: 300,
            height: 300,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isDark ? AppColors.bokehTeal : const Color(0x22FDE68A),
            ),
          ),
        ),
        Positioned(
          bottom: 120,
          left: -40,
          child: Container(
            width: 240,
            height: 240,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isDark ? const Color(0x226366F1) : const Color(0x26FECDD3),
            ),
          ),
        ),

        // Filtro de desfoque atmosférico
        BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 70, sigmaY: 70),
          child: Container(
            color: Colors.transparent,
          ),
        ),

        // Conteúdo da aplicação
        SafeArea(
          bottom: false,
          child: child,
        ),
      ],
    );
  }
}
