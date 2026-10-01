import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import 'bookings_screen.dart';

/// Modal de Detalhe Completo do Voucher da Reserva e Política de Cancelamento
class BookingDetailModal extends StatelessWidget {
  final BookingItem booking;
  final VoidCallback onCancel;

  const BookingDetailModal({
    super.key,
    required this.booking,
    required this.onCancel,
  });

  static void show(BuildContext context, BookingItem booking, VoidCallback onCancel) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) => BookingDetailModal(booking: booking, onCancel: onCancel),
    );
  }

  void _confirmCancel(BuildContext context) {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: const Text('Cancelar Agendamento?'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Atenção à Política de Cancelamento do Condomínio:',
              style: AppTextStyles.labelSmall.copyWith(fontWeight: FontWeight.bold, color: AppColors.danger),
            ),
            const SizedBox(height: 6),
            const Text(
              '• Cancelamento gratuito até 48 horas antes da data do evento.\n'
              '• Cancelamento tardio libera a área para outros condôminos mas retém a taxa de reserva.\n\n'
              'Deseja realmente cancelar sua reserva no momento?',
              style: TextStyle(fontSize: 13, height: 1.4),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogCtx),
            child: const Text('Manter Reserva'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(dialogCtx); // Fecha dialog
              Navigator.pop(context); // Fecha modal
              onCancel();
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            child: const Text('Sim, Cancelar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dateFormatter = DateFormat('dd/MM/yyyy');

    return FrostedGlassCard(
      borderRadius: 32,
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
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

            // Topo: Status do Voucher
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.successLight,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    'VOUCHER #${booking.id}',
                    style: AppTextStyles.badge.copyWith(color: AppColors.success),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.primarySoft,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    booking.status,
                    style: AppTextStyles.badge.copyWith(color: AppColors.primary),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),
            Text(
              booking.amenityName,
              style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              'Apto 402 • Bloco Primavera',
              style: AppTextStyles.bodyMedium.copyWith(
                color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
              ),
            ),

            const SizedBox(height: 18),

            // QR Code do Voucher para Acesso ao Espaço
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: const [
                  BoxShadow(color: Color(0x14000000), blurRadius: 10, offset: Offset(0, 4)),
                ],
              ),
              child: Column(
                children: [
                  QrImageView(
                    data: 'condoai:booking:${booking.id}:${booking.amenityName}',
                    version: QrVersions.auto,
                    size: 160.0,
                    backgroundColor: Colors.white,
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Apresente este QR Code ao zelador para liberar as chaves',
                    style: TextStyle(color: Color(0xFF64748B), fontSize: 11),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 18),

            // Detalhes do Horário e Local
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isDark ? const Color(0x26FFFFFF) : const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder),
              ),
              child: Column(
                children: [
                  _RowItem(label: 'Data Reservada', value: dateFormatter.format(booking.date), icon: Icons.calendar_today_rounded, isDark: isDark),
                  const Divider(height: 12),
                  _RowItem(label: 'Horário / Turno', value: booking.period, icon: Icons.access_time_rounded, isDark: isDark),
                  const Divider(height: 12),
                  _RowItem(label: 'Vistoria Prévia', value: '1h antes do evento', icon: Icons.checklist_rounded, isDark: isDark),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Política de Cancelamento
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0x33F59E0B) : AppColors.warningLight,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.warning.withAlpha(80)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.policy_outlined, color: AppColors.warning, size: 16),
                      const SizedBox(width: 6),
                      Text('Política de Cancelamento', style: AppTextStyles.badge.copyWith(color: AppColors.warning)),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Cancelamentos realizados com mais de 48h de antecedência têm estorno integral. Após o prazo, o condomínio retém a taxa para cobrir a reserva do espaço.',
                    style: AppTextStyles.labelSmall.copyWith(
                      color: isDark ? Colors.white70 : const Color(0xFF78350F),
                      height: 1.3,
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            OutlinedButton.icon(
              onPressed: () => _confirmCancel(context),
              icon: const Icon(Icons.cancel_outlined, color: AppColors.danger, size: 18),
              label: const Text('Cancelar Reserva', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.bold)),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: AppColors.danger),
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _RowItem extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final bool isDark;

  const _RowItem({required this.label, required this.value, required this.icon, required this.isDark});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 16, color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary),
        const SizedBox(width: 8),
        Text(label, style: AppTextStyles.labelSmall.copyWith(color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary)),
        const Spacer(),
        Text(value, style: AppTextStyles.bodyMedium.copyWith(fontWeight: FontWeight.w600)),
      ],
    );
  }
}
