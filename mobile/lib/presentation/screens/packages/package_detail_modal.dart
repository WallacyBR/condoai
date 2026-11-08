import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../core/widgets/info_row.dart';
import '../../../core/widgets/optional_network_image.dart';
import '../../../data/models/package_item_model.dart';
import '../../blocs/packages/packages_cubit.dart';

/// Modal de Detalhe Individual da Encomenda com Foto Real, QR Code e Código Único
class PackageDetailModal extends StatefulWidget {
  final PackageItemModel package;

  const PackageDetailModal({
    super.key,
    required this.package,
  });

  static void show(BuildContext context, PackageItemModel package) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      useSafeArea: true,
      isDismissible: true,
      enableDrag: true,
      builder: (ctx) => ConstrainedBox(
        constraints: BoxConstraints(
          maxHeight: MediaQuery.of(ctx).size.height * 0.88,
        ),
        child: PackageDetailModal(package: package),
      ),
    );
  }

  @override
  State<PackageDetailModal> createState() => _PackageDetailModalState();
}

class _PackageDetailModalState extends State<PackageDetailModal> {
  bool _isCodeRevealed = false;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final timeFormatter = DateFormat('dd/MM HH:mm');

    return FrostedGlassCard(
      borderRadius: 32,
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Indicador de arraste
            Center(
              child: Container(
                width: 44,
                height: 4,
                decoration: BoxDecoration(
                  color: isDark ? Colors.white24 : Colors.black12,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Cabeçalho com Nome da Transportadora, Status e Botão Fechar
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0x33F59E0B) : AppColors.warningLight,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.inventory_2_rounded,
                    color: AppColors.warning,
                    size: 22,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.package.company,
                        style: AppTextStyles.titleSmall.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        widget.package.trackingCode,
                        style: AppTextStyles.labelSmall.copyWith(
                          color: isDark
                              ? AppColors.darkTextSecondary
                              : AppColors.lightTextSecondary,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: widget.package.isWithdrawn
                        ? AppColors.successLight
                        : AppColors.warningLight,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    widget.package.isWithdrawn ? 'Retirado' : 'Aguardando',
                    style: AppTextStyles.badge.copyWith(
                      color: widget.package.isWithdrawn
                          ? AppColors.success
                          : AppColors.warning,
                    ),
                  ),
                ),
                const SizedBox(width: 6),
                IconButton(
                  icon: Icon(
                    Icons.close_rounded,
                    size: 20,
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                  ),
                  tooltip: 'Fechar',
                  onPressed: () => Navigator.pop(context),
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                ),
              ],
            ),

            const SizedBox(height: 18),

            // 1. FOTO REAL DA ENCOMENDA (Comprovante capturado pela Portaria)
            ClipRRect(
              borderRadius: BorderRadius.circular(20),
              child: Stack(
                alignment: Alignment.bottomLeft,
                children: [
                  AspectRatio(
                    aspectRatio: 16 / 10,
                    child: OptionalNetworkImage(
                      url: widget.package.photoUrl,
                      fit: BoxFit.cover,
                      placeholderIcon: Icons.inventory_2_outlined,
                    ),
                  ),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Colors.black87, Colors.transparent],
                        begin: Alignment.bottomCenter,
                        end: Alignment.topCenter,
                      ),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.camera_alt_outlined, size: 14, color: Colors.white70),
                        const SizedBox(width: 6),
                        Text(
                          'Foto do pacote tirada pela portaria na chegada',
                          style: AppTextStyles.labelSmall.copyWith(color: Colors.white),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 18),

            // 2. QR CODE INDIVIDUAL DESTA ENCOMENDA
            if (!widget.package.isWithdrawn) ...[
              Container(
                padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0x330F172A) : const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
                  ),
                ),
                child: Column(
                  children: [
                    Text(
                      'QR CODE DE RETIRADA',
                      style: AppTextStyles.badge.copyWith(
                        color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                        letterSpacing: 1.2,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x14000000),
                            blurRadius: 10,
                            offset: Offset(0, 4),
                          ),
                        ],
                      ),
                      child: QrImageView(
                        data: 'condoai:pkg:${widget.package.id}:${widget.package.withdrawalCode}',
                        version: QrVersions.auto,
                        size: 150.0,
                        backgroundColor: Colors.white,
                        eyeStyle: const QrEyeStyle(
                          eyeShape: QrEyeShape.square,
                          color: Color(0xFF0F172A),
                        ),
                        dataModuleStyle: const QrDataModuleStyle(
                          dataModuleShape: QrDataModuleShape.square,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Apresente este QR Code para o leitor ótico da portaria',
                      style: AppTextStyles.labelSmall.copyWith(
                        color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 14),

              // 3. CÓDIGO ALFANUMÉRICO INDIVIDUAL (Com Máscara de Proteção)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0x330F172A) : const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'CÓDIGO ALFANUMÉRICO',
                          style: AppTextStyles.badge.copyWith(
                            color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                            letterSpacing: 1.0,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _isCodeRevealed ? widget.package.withdrawalCode : '••••••',
                          style: AppTextStyles.titleMedium.copyWith(
                            letterSpacing: _isCodeRevealed ? 1.5 : 4,
                            fontWeight: FontWeight.bold,
                            color: AppColors.primary,
                          ),
                        ),
                      ],
                    ),
                    Row(
                      children: [
                        IconButton(
                          onPressed: () {
                            HapticFeedback.lightImpact();
                            setState(() {
                              _isCodeRevealed = !_isCodeRevealed;
                            });
                          },
                          icon: Icon(
                            _isCodeRevealed
                                ? Icons.visibility_off_outlined
                                : Icons.visibility_outlined,
                            color: AppColors.primary,
                          ),
                          tooltip: 'Revelar ou ocultar código',
                        ),
                        IconButton(
                          onPressed: () {
                            HapticFeedback.lightImpact();
                            Clipboard.setData(ClipboardData(text: widget.package.withdrawalCode));
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('Código ${widget.package.withdrawalCode} copiado!'),
                                duration: const Duration(seconds: 1),
                              ),
                            );
                          },
                          icon: const Icon(Icons.copy_rounded, size: 20, color: AppColors.primary),
                          tooltip: 'Copiar código',
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 16),

            // 4. Detalhes de Recebimento
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isDark ? const Color(0x1AFFFFFF) : Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
                ),
              ),
              child: Column(
                children: [
                  InfoRow(
                    icon: Icons.person_outline_rounded,
                    label: 'Destinatário',
                    value: widget.package.recipientName,
                    isDark: isDark,
                  ),
                  const Divider(height: 14),
                  InfoRow(
                    icon: Icons.access_time_rounded,
                    label: 'Recebido em',
                    value: timeFormatter.format(widget.package.receivedAt),
                    isDark: isDark,
                  ),
                  const Divider(height: 14),
                  InfoRow(
                    icon: Icons.badge_outlined,
                    label: 'Recebido por',
                    value: widget.package.porterName,
                    isDark: isDark,
                  ),
                  const Divider(height: 14),
                  InfoRow(
                    icon: Icons.place_outlined,
                    label: 'Localização',
                    value: widget.package.storageLocation ?? 'Portaria',
                    isDark: isDark,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // 5. Botão de Ação: Simular Baixa de Retirada
            if (!widget.package.isWithdrawn)
              ElevatedButton.icon(
                onPressed: () {
                  HapticFeedback.heavyImpact();
                  context.read<PackagesCubit>().confirmWithdrawal(widget.package.id);
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Row(
                        children: [
                          const Icon(Icons.check_circle_rounded, color: Colors.white),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'Retirada do pacote ${widget.package.company} confirmada!',
                            ),
                          ),
                        ],
                      ),
                      backgroundColor: AppColors.success,
                      behavior: SnackBarBehavior.floating,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  );
                },
                icon: const Icon(Icons.check_rounded, color: Colors.white),
                label: const Text(
                  'Confirmar Retirada na Portaria',
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.success,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              )
            else
              Container(
                padding: const EdgeInsets.symmetric(vertical: 14),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0x3310B981) : AppColors.successLight,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Center(
                  child: Text(
                    'Pacote já retirado com sucesso',
                    style: AppTextStyles.titleSmall.copyWith(
                      color: AppColors.success,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

