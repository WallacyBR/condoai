import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Tipografia moderna baseada na família Inter / Plus Jakarta Sans.
class AppTextStyles {
  static TextStyle get titleLarge => GoogleFonts.inter(
        fontSize: 26,
        fontWeight: FontWeight.w700,
        letterSpacing: -0.6,
      );

  static TextStyle get titleMedium => GoogleFonts.inter(
        fontSize: 20,
        fontWeight: FontWeight.w600,
        letterSpacing: -0.4,
      );

  static TextStyle get titleSmall => GoogleFonts.inter(
        fontSize: 16,
        fontWeight: FontWeight.w600,
        letterSpacing: -0.2,
      );

  static TextStyle get bodyLarge => GoogleFonts.inter(
        fontSize: 15,
        fontWeight: FontWeight.w400,
        letterSpacing: -0.1,
      );

  static TextStyle get bodyMedium => GoogleFonts.inter(
        fontSize: 13.5,
        fontWeight: FontWeight.w400,
      );

  static TextStyle get labelSmall => GoogleFonts.inter(
        fontSize: 11.5,
        fontWeight: FontWeight.w500,
        letterSpacing: 0.2,
      );

  static TextStyle get badge => GoogleFonts.inter(
        fontSize: 10.5,
        fontWeight: FontWeight.w600,
        letterSpacing: 0.3,
      );
}
