import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../theme/app_colors.dart';

/// Barra de navegação inferior estilo Airbnb
/// Cápsula de vidro flutuante com 5 botões e o 3º botão central elevado para ações rápidas.
class AirbnbBottomNav extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTabSelected;
  final VoidCallback onCenterActionTap;

  const AirbnbBottomNav({
    super.key,
    required this.currentIndex,
    required this.onTabSelected,
    required this.onCenterActionTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Padding(
      padding: const EdgeInsets.only(left: 20, right: 20, bottom: 24),
      child: Stack(
        alignment: Alignment.bottomCenter,
        clipBehavior: Clip.none,
        children: [
          // Cápsula de Vidro Principal
          ClipRRect(
            borderRadius: BorderRadius.circular(36),
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
              child: Container(
                height: 70,
                decoration: BoxDecoration(
                  color: isDark
                      ? const Color(0xCC0F172A)
                      : const Color(0xE6FFFFFF),
                  borderRadius: BorderRadius.circular(36),
                  border: Border.all(
                    color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
                    width: 1.2,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: isDark ? const Color(0x66000000) : const Color(0x140F172A),
                      blurRadius: 24,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    // 1. Início
                    _NavItem(
                      icon: Icons.home_rounded,
                      label: 'Início',
                      isSelected: currentIndex == 0,
                      onTap: () {
                        HapticFeedback.lightImpact();
                        onTabSelected(0);
                      },
                    ),
                    // 2. Avisos
                    _NavItem(
                      icon: Icons.notifications_none_rounded,
                      selectedIcon: Icons.notifications_rounded,
                      label: 'Avisos',
                      badgeCount: 2,
                      isSelected: currentIndex == 1,
                      onTap: () {
                        HapticFeedback.lightImpact();
                        onTabSelected(1);
                      },
                    ),
                    // Espaço reservado para o botão central
                    const SizedBox(width: 56),
                    // 4. Reservas
                    _NavItem(
                      icon: Icons.calendar_today_outlined,
                      selectedIcon: Icons.calendar_month_rounded,
                      label: 'Reservas',
                      isSelected: currentIndex == 3,
                      onTap: () {
                        HapticFeedback.lightImpact();
                        onTabSelected(3);
                      },
                    ),
                    // 5. Perfil
                    _NavItem(
                      icon: Icons.person_outline_rounded,
                      selectedIcon: Icons.person_rounded,
                      label: 'Perfil',
                      isSelected: currentIndex == 4,
                      onTap: () {
                        HapticFeedback.lightImpact();
                        onTabSelected(4);
                      },
                    ),
                  ],
                ),
              ),
            ),
          ),

          // 3. Botão Central Flutuante e Elevado (Ação Rápida de Acesso/Portão/QR Code)
          Positioned(
            top: -18,
            child: GestureDetector(
              onTap: () {
                HapticFeedback.mediumImpact();
                onCenterActionTap();
              },
              child: Container(
                width: 62,
                height: 62,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [
                      Color(0xFF6366F1), // Indigo 500
                      Color(0xFF4338CA), // Indigo 700
                    ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withAlpha(115),
                      blurRadius: 18,
                      offset: const Offset(0, 8),
                    ),
                  ],
                  border: Border.all(
                    color: isDark ? const Color(0x4DFFFFFF) : Colors.white,
                    width: 3.5,
                  ),
                ),
                child: const Center(
                  child: Icon(
                    Icons.vpn_key_rounded,
                    color: Colors.white,
                    size: 28,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  final IconData icon;
  final IconData? selectedIcon;
  final String label;
  final bool isSelected;
  final int? badgeCount;
  final VoidCallback onTap;

  const _NavItem({
    required this.icon,
    this.selectedIcon,
    required this.label,
    required this.isSelected,
    this.badgeCount,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final color = isSelected
        ? (isDark ? AppColors.primaryLight : AppColors.primary)
        : (isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary);

    return InkWell(
      onTap: onTap,
      splashColor: Colors.transparent,
      highlightColor: Colors.transparent,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                Icon(
                  isSelected ? (selectedIcon ?? icon) : icon,
                  color: color,
                  size: 24,
                ),
                if (badgeCount != null && badgeCount! > 0)
                  Positioned(
                    top: -4,
                    right: -6,
                    child: Container(
                      padding: const EdgeInsets.all(4),
                      decoration: const BoxDecoration(
                        color: AppColors.danger,
                        shape: BoxShape.circle,
                      ),
                      constraints: const BoxConstraints(
                        minWidth: 16,
                        minHeight: 16,
                      ),
                      child: Text(
                        '$badgeCount',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 3),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontSize: 11,
                fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
