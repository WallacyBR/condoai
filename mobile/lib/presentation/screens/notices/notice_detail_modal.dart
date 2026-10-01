import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';

/// Modal de Detalhe Completo do Comunicado Oficial
class NoticeDetailModal extends StatefulWidget {
  final String title;
  final String content;
  final String date;
  final String author;
  final String category;
  final bool initialRead;

  const NoticeDetailModal({
    super.key,
    required this.title,
    required this.content,
    required this.date,
    this.author = 'Administração / Síndico Geral',
    this.category = 'COMUNICADO OFICIAL',
    this.initialRead = false,
  });

  static void show(
    BuildContext context, {
    required String title,
    required String content,
    required String date,
    String author = 'Administração / Síndico Geral',
    String category = 'COMUNICADO OFICIAL',
    bool initialRead = false,
  }) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) => NoticeDetailModal(
        title: title,
        content: content,
        date: date,
        author: author,
        category: category,
        initialRead: initialRead,
      ),
    );
  }

  @override
  State<NoticeDetailModal> createState() => _NoticeDetailModalState();
}

class _NoticeDetailModalState extends State<NoticeDetailModal> {
  late bool _isRead;

  @override
  void initState() {
    super.initState();
    _isRead = widget.initialRead;
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return FrostedGlassCard(
      borderRadius: 32,
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.primarySoft,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  widget.category,
                  style: AppTextStyles.badge.copyWith(color: AppColors.primary),
                ),
              ),
              Text(
                widget.date,
                style: AppTextStyles.labelSmall.copyWith(
                  color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Text(
            widget.title,
            style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 6),
          Text(
            'Publicado por ${widget.author}',
            style: AppTextStyles.labelSmall.copyWith(
              color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
            ),
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: isDark ? const Color(0x330F172A) : const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
              ),
            ),
            child: Text(
              widget.content,
              style: AppTextStyles.bodyLarge.copyWith(
                height: 1.5,
                color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
              ),
            ),
          ),
          const SizedBox(height: 16),
          // Anexo de Documento
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: isDark ? const Color(0x26FFFFFF) : Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
              ),
            ),
            child: Row(
              children: [
                const Icon(Icons.picture_as_pdf_rounded, color: AppColors.danger, size: 24),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'comunicado_oficial_detalhado.pdf',
                        style: AppTextStyles.titleSmall.copyWith(fontSize: 13),
                      ),
                      Text(
                        'Documento assinado digitalmente • 240 KB',
                        style: AppTextStyles.labelSmall.copyWith(
                          color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.download_rounded, color: AppColors.primary),
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Download do anexo iniciado.')),
                    );
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          ElevatedButton.icon(
            onPressed: () {
              HapticFeedback.mediumImpact();
              setState(() {
                _isRead = true;
              });
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Confirmação de leitura registrada com sucesso!'),
                  backgroundColor: AppColors.success,
                ),
              );
            },
            icon: Icon(
              _isRead ? Icons.check_circle_rounded : Icons.mark_email_read_outlined,
              color: Colors.white,
            ),
            label: Text(
              _isRead ? 'Leitura Confirmada' : 'Confirmar Leitura do Comunicado',
              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: _isRead ? AppColors.success : AppColors.primary,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            ),
          ),
        ],
      ),
    );
  }
}
