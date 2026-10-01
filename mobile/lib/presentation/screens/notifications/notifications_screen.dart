import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';

class NotificationItem {
  final String id;
  final String title;
  final String message;
  final String time;
  final IconData icon;
  final Color iconColor;
  bool isRead;

  NotificationItem({
    required this.id,
    required this.title,
    required this.message,
    required this.time,
    required this.icon,
    required this.iconColor,
    this.isRead = false,
  });
}

/// Tela de Notificações do Morador acionada pelo sino
class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final List<NotificationItem> _notifications = [
    NotificationItem(
      id: '1',
      title: 'Nova Encomenda na Portaria',
      message: 'Seu pacote da Amazon Brasil foi recebido pelo porteiro Rogério.',
      time: 'Há 25 minutos',
      icon: Icons.inventory_2_rounded,
      iconColor: AppColors.warning,
      isRead: false,
    ),
    NotificationItem(
      id: '2',
      title: 'Visitante Autorizado',
      message: 'Carlos Mendes teve sua entrada autorizada na Portaria Principal.',
      time: 'Há 1 hora',
      icon: Icons.verified_user_rounded,
      iconColor: AppColors.success,
      isRead: false,
    ),
    NotificationItem(
      id: '3',
      title: 'Manutenção de Elevador',
      message: 'Lembrete: elevador social do Bloco Primavera em manutenção das 14h às 16h.',
      time: 'Hoje às 08:30',
      icon: Icons.campaign_rounded,
      iconColor: AppColors.primary,
      isRead: true,
    ),
    NotificationItem(
      id: '4',
      title: 'Reserva Confirmada',
      message: 'Seu agendamento para o Espaço Gourmet foi aprovado para sábado.',
      time: 'Ontem',
      icon: Icons.event_available_rounded,
      iconColor: AppColors.info,
      isRead: true,
    ),
  ];

  void _markAllAsRead() {
    HapticFeedback.lightImpact();
    setState(() {
      for (var n in _notifications) {
        n.isRead = true;
      }
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Todas as notificações foram marcadas como lidas.'),
        duration: Duration(seconds: 1),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

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
          'Notificações',
          style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
        ),
        actions: [
          TextButton(
            onPressed: _markAllAsRead,
            child: Text(
              'Ler todas',
              style: TextStyle(
                color: isDark ? AppColors.primaryLight : AppColors.primary,
                fontWeight: FontWeight.w600,
                fontSize: 13,
              ),
            ),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: ListView.builder(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 30),
        itemCount: _notifications.length,
        itemBuilder: (context, index) {
          final item = _notifications[index];

          return FrostedGlassCard(
            borderRadius: 20,
            margin: const EdgeInsets.only(bottom: 12),
            onTap: () {
              setState(() {
                item.isRead = true;
              });
            },
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: item.iconColor.withAlpha(isDark ? 50 : 25),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(item.icon, color: item.iconColor, size: 22),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              item.title,
                              style: AppTextStyles.titleSmall.copyWith(
                                fontSize: 14,
                                fontWeight: item.isRead ? FontWeight.w600 : FontWeight.bold,
                              ),
                            ),
                          ),
                          if (!item.isRead)
                            Container(
                              width: 8,
                              height: 8,
                              decoration: const BoxDecoration(
                                color: AppColors.primary,
                                shape: BoxShape.circle,
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        item.message,
                        style: AppTextStyles.bodyMedium.copyWith(
                          color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                          height: 1.3,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        item.time,
                        style: AppTextStyles.labelSmall.copyWith(
                          color: isDark ? AppColors.darkTextTertiary : AppColors.lightTextTertiary,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
