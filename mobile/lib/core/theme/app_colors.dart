import 'package:flutter/material.dart';

/// Cores do Design System do PrediHub Mobile
/// Inspirado na sobriedade corporativa do DESIGN_MANUAL.md com a sofisticação acolhedora do Airbnb.
class AppColors {
  // Paleta Brand / Indigo
  static const Color primary = Color(0xFF4F46E5); // Indigo 600
  static const Color primaryDark = Color(0xFF4338CA); // Indigo 700
  static const Color primaryLight = Color(0xFF818CF8); // Indigo 400
  static const Color primarySoft = Color(0xFFEEF2FF); // Indigo 50

  // Semânticas
  static const Color success = Color(0xFF10B981); // Emerald 500
  static const Color successLight = Color(0xFFECFDF5);
  static const Color warning = Color(0xFFF59E0B); // Amber 500
  static const Color warningLight = Color(0xFFFFFBEB);
  static const Color danger = Color(0xFFE11D48); // Rose 600
  static const Color dangerLight = Color(0xFFFFF1F2);
  static const Color info = Color(0xFF0EA5E9); // Sky 500

  // Cores do Modo Claro
  static const Color lightBackground = Color(0xFFF8FAFC); // Slate 50
  static const Color lightSurface = Color(0xFFFFFFFF);
  static const Color lightCardBorder = Color(0x33CBD5E1); // Slate 300 translúcido
  static const Color lightTextPrimary = Color(0xFF0F172A); // Slate 900
  static const Color lightTextSecondary = Color(0xFF64748B); // Slate 500
  static const Color lightTextTertiary = Color(0xFF94A3B8); // Slate 400

  // Cores do Modo Escuro
  static const Color darkBackground = Color(0xFF090D16); // Slate ultra profundo
  static const Color darkSurface = Color(0xFF0F172A); // Slate 900
  static const Color darkCardBorder = Color(0x26FFFFFF); // Branco translúcido sutil
  static const Color darkTextPrimary = Color(0xFFF8FAFC); // Slate 50
  static const Color darkTextSecondary = Color(0xFF94A3B8); // Slate 400
  static const Color darkTextTertiary = Color(0xFF64748B); // Slate 500

  // Efeitos Bokeh & Gradientes
  static const Color bokehIndigo = Color(0x4D6366F1); // Indigo difuso
  static const Color bokehTeal = Color(0x3314B8A6); // Teal difuso
  static const Color bokehAmber = Color(0x33F59E0B); // Amber difuso
  static const Color bokehRose = Color(0x33F43F5E); // Rose difuso
}
