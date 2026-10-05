import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../core/widgets/optional_network_image.dart';
import '../../../data/models/package_item_model.dart';
import '../../blocs/packages/packages_cubit.dart';
import '../../blocs/packages/packages_state.dart';
import 'package:predihub_app/presentation/screens/packages/package_detail_modal.dart';

/// Tela completa de Encomendas do Morador (Aguardando e Histórico)
class PackagesScreen extends StatefulWidget {
  const PackagesScreen({super.key});

  @override
  State<PackagesScreen> createState() => _PackagesScreenState();
}

class _PackagesScreenState extends State<PackagesScreen> {
  int _selectedTab = 0; // 0 = Aguardando, 1 = Histórico

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final timeFormatter = DateFormat('dd/MM HH:mm');

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
          'Minhas Encomendas',
          style: AppTextStyles.titleMedium.copyWith(
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: BlocBuilder<PackagesCubit, PackagesState>(
        builder: (context, state) {
          if (state is! PackagesLoaded) {
            return const Center(child: CircularProgressIndicator());
          }

          final packagesToShow = _selectedTab == 0
              ? state.pendingPackages
              : state.withdrawnPackages;

          return ListView(
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
            children: [
              // Seletor de Abas (Aguardando / Histórico)
              Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0x330F172A) : const Color(0xFFE2E8F0),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: GestureDetector(
                        onTap: () {
                          HapticFeedback.lightImpact();
                          setState(() => _selectedTab = 0);
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          decoration: BoxDecoration(
                            color: _selectedTab == 0
                                ? (isDark ? const Color(0xFF1E293B) : Colors.white)
                                : Colors.transparent,
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: _selectedTab == 0
                                ? [
                                    const BoxShadow(
                                      color: Color(0x0F000000),
                                      blurRadius: 6,
                                      offset: Offset(0, 2),
                                    ),
                                  ]
                                : null,
                          ),
                          child: Center(
                            child: Text(
                              'Aguardando (${state.pendingCount})',
                              style: AppTextStyles.titleSmall.copyWith(
                                fontSize: 13,
                                fontWeight: _selectedTab == 0
                                    ? FontWeight.bold
                                    : FontWeight.w500,
                                color: _selectedTab == 0
                                    ? AppColors.primary
                                    : (isDark
                                        ? AppColors.darkTextSecondary
                                        : AppColors.lightTextSecondary),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                    Expanded(
                      child: GestureDetector(
                        onTap: () {
                          HapticFeedback.lightImpact();
                          setState(() => _selectedTab = 1);
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          decoration: BoxDecoration(
                            color: _selectedTab == 1
                                ? (isDark ? const Color(0xFF1E293B) : Colors.white)
                                : Colors.transparent,
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: _selectedTab == 1
                                ? [
                                    const BoxShadow(
                                      color: Color(0x0F000000),
                                      blurRadius: 6,
                                      offset: Offset(0, 2),
                                    ),
                                  ]
                                : null,
                          ),
                          child: Center(
                            child: Text(
                              'Histórico (${state.withdrawnPackages.length})',
                              style: AppTextStyles.titleSmall.copyWith(
                                fontSize: 13,
                                fontWeight: _selectedTab == 1
                                    ? FontWeight.bold
                                    : FontWeight.w500,
                                color: _selectedTab == 1
                                    ? AppColors.primary
                                    : (isDark
                                        ? AppColors.darkTextSecondary
                                        : AppColors.lightTextSecondary),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              if (packagesToShow.isEmpty)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 60),
                    child: Column(
                      children: [
                        Icon(
                          Icons.inbox_outlined,
                          size: 64,
                          color: isDark
                              ? AppColors.darkTextSecondary
                              : AppColors.lightTextSecondary,
                        ),
                        const SizedBox(height: 14),
                        Text(
                          _selectedTab == 0
                              ? 'Nenhuma encomenda aguardando retirada'
                              : 'Nenhuma encomenda no histórico',
                          style: AppTextStyles.titleSmall.copyWith(
                            color: isDark
                                ? AppColors.darkTextSecondary
                                : AppColors.lightTextSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              else
                ...packagesToShow.map((package) {
                  return _PackageCard(
                    package: package,
                    isDark: isDark,
                    timeFormatter: timeFormatter,
                    onTap: () => PackageDetailModal.show(context, package),
                  );
                }),
            ],
          );
        },
      ),
    );
  }
}

class _PackageCard extends StatelessWidget {
  final PackageItemModel package;
  final bool isDark;
  final DateFormat timeFormatter;
  final VoidCallback onTap;

  const _PackageCard({
    required this.package,
    required this.isDark,
    required this.timeFormatter,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return FrostedGlassCard(
      borderRadius: 24,
      padding: EdgeInsets.zero,
      margin: const EdgeInsets.only(bottom: 16),
      onTap: onTap,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Foto com etiqueta e badge
          ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            child: AspectRatio(
              aspectRatio: 16 / 9,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  OptionalNetworkImage(
                    url: package.photoUrl,
                    fit: BoxFit.cover,
                    placeholderIcon: Icons.inventory_2_outlined,
                  ),
                  Positioned(
                    top: 12,
                    right: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: package.isWithdrawn
                            ? AppColors.success
                            : AppColors.warning,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        package.isWithdrawn ? 'Retirado' : 'Aguardando',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    bottom: 10,
                    left: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.black.withAlpha(160),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Foto da portaria',
                        style: AppTextStyles.labelSmall.copyWith(color: Colors.white),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      package.company,
                      style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                    ),
                    Text(
                      timeFormatter.format(package.receivedAt),
                      style: AppTextStyles.labelSmall.copyWith(
                        color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  package.trackingCode,
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: onTap,
                        icon: const Icon(Icons.qr_code_2_rounded, size: 18),
                        label: const Text('Ver QR Code & Retirar'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 10),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
