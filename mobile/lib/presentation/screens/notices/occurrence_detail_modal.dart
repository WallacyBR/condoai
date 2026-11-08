import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/utils/media_picker_helper.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/models/occurrence_model.dart';

class OccurrenceMessage {
  final String sender;
  final String text;
  final String time;
  final bool isFromResident;
  final String? photoPath;
  final bool isVideo;

  OccurrenceMessage({
    required this.sender,
    required this.text,
    required this.time,
    required this.isFromResident,
    this.photoPath,
    this.isVideo = false,
  });
}

/// Modal de Acompanhamento e Iterações da Ocorrência
class OccurrenceDetailModal extends StatefulWidget {
  final OccurrenceItem occurrence;

  const OccurrenceDetailModal({
    super.key,
    required this.occurrence,
  });

  static void show(BuildContext context, OccurrenceItem occurrence) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      useSafeArea: true,
      isDismissible: true,
      enableDrag: true,
      builder: (ctx) => OccurrenceDetailModal(occurrence: occurrence),
    );
  }

  @override
  State<OccurrenceDetailModal> createState() => _OccurrenceDetailModalState();
}

class _OccurrenceDetailModalState extends State<OccurrenceDetailModal> {
  final _replyController = TextEditingController();
  late List<OccurrenceMessage> _messages;

  @override
  void initState() {
    super.initState();
    final occ = widget.occurrence;
    final bodyParts = <String>[
      if (occ.description.isNotEmpty) occ.description,
      if (occ.location.isNotEmpty) '📍 Local: ${occ.location}',
      if (occ.urgency.isNotEmpty) '⚠️ Urgência: ${occ.urgency}',
    ];

    _messages = [
      OccurrenceMessage(
        sender: 'Wallacy Silva (Morador)',
        text: bodyParts.isNotEmpty
            ? bodyParts.join('\n\n')
            : 'Chamado registrado sem descrição adicional.',
        time: occ.date,
        isFromResident: true,
        photoPath: occ.attachments.isNotEmpty && !occ.attachments.first.isVideo
            ? occ.attachments.first.path
            : null,
        isVideo: occ.attachments.isNotEmpty && occ.attachments.first.isVideo,
      ),
      if (occ.protocol == 'OC-9841')
        OccurrenceMessage(
          sender: 'Síndico Geral (Administração)',
          text:
              'Olá Wallacy, recebemos seu chamado. A equipe de zeladoria já foi notificada e a substituição da lâmpada de LED está agendada para hoje até às 16h.',
          time: 'Hoje às 10:15',
          isFromResident: false,
        ),
    ];

    // Anexos adicionais além do primeiro (já exibido na mensagem inicial)
    for (var i = 1; i < occ.attachments.length; i++) {
      final att = occ.attachments[i];
      _messages.insert(
        1,
        OccurrenceMessage(
          sender: 'Wallacy Silva (Morador)',
          text: att.isVideo ? 'Vídeo anexado ao chamado.' : 'Foto anexada ao chamado.',
          time: occ.date,
          isFromResident: true,
          photoPath: att.isVideo ? null : att.path,
          isVideo: att.isVideo,
        ),
      );
    }
  }

  @override
  void dispose() {
    _replyController.dispose();
    super.dispose();
  }

  void _sendReply() {
    if (_replyController.text.trim().isEmpty) return;

    HapticFeedback.lightImpact();
    setState(() {
      _messages.add(
        OccurrenceMessage(
          sender: 'Wallacy Silva (Morador)',
          text: _replyController.text.trim(),
          time: 'Agora mesmo',
          isFromResident: true,
        ),
      );
      _replyController.clear();
    });
  }

  Widget _mediaPreview(OccurrenceMessage msg) {
    if (msg.isVideo) {
      return Container(
        height: 130,
        width: double.infinity,
        decoration: BoxDecoration(
          color: Colors.black87,
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Center(
          child: Icon(Icons.videocam_rounded, color: Colors.white, size: 40),
        ),
      );
    }
    if (msg.photoPath != null) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: Image.file(
          File(msg.photoPath!),
          height: 130,
          width: double.infinity,
          fit: BoxFit.cover,
        ),
      );
    }
    return const SizedBox.shrink();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final occ = widget.occurrence;

    return FrostedGlassCard(
      borderRadius: 32,
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: SizedBox(
        height: MediaQuery.of(context).size.height * 0.78,
        child: Column(
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
                Flexible(
                  child: Wrap(
                    spacing: 8,
                    runSpacing: 4,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.warningLight,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          'PROTOCOLO #${occ.protocol}',
                          style: AppTextStyles.badge.copyWith(color: AppColors.warning),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.info.withAlpha(30),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          occ.status,
                          style: AppTextStyles.badge.copyWith(color: AppColors.info),
                        ),
                      ),
                    ],
                  ),
                ),
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
            const SizedBox(height: 10),
            Text(
              occ.title,
              style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
            ),
            Text(
              '${occ.category} • Aberto em ${occ.date}',
              style: AppTextStyles.labelSmall.copyWith(
                color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
              ),
            ),
            if (occ.urgency.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                'Urgência: ${occ.urgency}',
                style: AppTextStyles.labelSmall.copyWith(color: AppColors.warning),
              ),
            ],
            const SizedBox(height: 16),
            const Divider(),
            Expanded(
              child: ListView.builder(
                itemCount: _messages.length,
                itemBuilder: (context, index) {
                  final msg = _messages[index];

                  return Container(
                    margin: const EdgeInsets.symmetric(vertical: 8),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: msg.isFromResident
                          ? (isDark ? const Color(0x334F46E5) : AppColors.primarySoft)
                          : (isDark ? const Color(0x330F172A) : const Color(0xFFF1F5F9)),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: msg.isFromResident
                            ? AppColors.primary.withAlpha(80)
                            : (isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Flexible(
                              child: Text(
                                msg.sender,
                                style: AppTextStyles.badge.copyWith(
                                  color: msg.isFromResident
                                      ? AppColors.primary
                                      : (isDark ? Colors.white : AppColors.lightTextPrimary),
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            Text(
                              msg.time,
                              style: AppTextStyles.labelSmall.copyWith(fontSize: 10),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(
                          msg.text,
                          style: AppTextStyles.bodyMedium.copyWith(
                            height: 1.3,
                            color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                          ),
                        ),
                        if (msg.photoPath != null || msg.isVideo) ...[
                          const SizedBox(height: 10),
                          _mediaPreview(msg),
                        ],
                      ],
                    ),
                  );
                },
              ),
            ),
            Container(
              padding: const EdgeInsets.only(top: 10),
              child: Row(
                children: [
                  IconButton(
                    onPressed: () async {
                      final attachment = await MediaPickerHelper.pickPhoto();
                      if (attachment == null || !mounted) return;
                      setState(() {
                        _messages.add(
                          OccurrenceMessage(
                            sender: 'Wallacy Silva (Morador)',
                            text: 'Anexo enviado.',
                            time: 'Agora mesmo',
                            isFromResident: true,
                            photoPath: attachment.isVideo ? null : attachment.path,
                            isVideo: attachment.isVideo,
                          ),
                        );
                      });
                    },
                    icon: const Icon(Icons.attach_file_rounded, color: AppColors.primary),
                  ),
                  Expanded(
                    child: TextField(
                      controller: _replyController,
                      decoration: InputDecoration(
                        hintText: 'Responder ao síndico...',
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(20)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    onPressed: _sendReply,
                    icon: const Icon(Icons.send_rounded, color: AppColors.primary),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
