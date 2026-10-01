import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';

class OccurrenceMessage {
  final String sender;
  final String text;
  final String time;
  final bool isFromResident;
  final String? photoUrl;

  OccurrenceMessage({
    required this.sender,
    required this.text,
    required this.time,
    required this.isFromResident,
    this.photoUrl,
  });
}

/// Modal de Acompanhamento e Iterações da Ocorrência
class OccurrenceDetailModal extends StatefulWidget {
  final String protocol;
  final String title;
  final String category;
  final String date;
  final String status;

  const OccurrenceDetailModal({
    super.key,
    required this.protocol,
    required this.title,
    required this.category,
    required this.date,
    required this.status,
  });

  static void show(
    BuildContext context, {
    required String protocol,
    required String title,
    required String category,
    required String date,
    required String status,
  }) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      useSafeArea: true,
      builder: (ctx) => OccurrenceDetailModal(
        protocol: protocol,
        title: title,
        category: category,
        date: date,
        status: status,
      ),
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
    _messages = [
      OccurrenceMessage(
        sender: 'Wallacy Silva (Morador)',
        text: 'A lâmpada do corredor em frente ao apartamento 402 está piscando e apagou completamente. Local muito escuro à noite.',
        time: widget.date,
        isFromResident: true,
        photoUrl: 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=500&auto=format&fit=crop&q=80',
      ),
      OccurrenceMessage(
        sender: 'Síndico Geral (Administração)',
        text: 'Olá Wallacy, recebemos seu chamado. A equipe de zeladoria já foi notificada e a substituição da lâmpada de LED está agendada para hoje até às 16h.',
        time: 'Hoje às 10:15',
        isFromResident: false,
      ),
    ];
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

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

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

            // Topo: Protocolo, Status e Botão Fechar
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.warningLight,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'PROTOCOLO #${widget.protocol}',
                        style: AppTextStyles.badge.copyWith(color: AppColors.warning),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.info.withAlpha(30),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        widget.status,
                        style: AppTextStyles.badge.copyWith(color: AppColors.info),
                      ),
                    ),
                  ],
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
              widget.title,
              style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
            ),
            Text(
              '${widget.category} • Aberto em ${widget.date}',
              style: AppTextStyles.labelSmall.copyWith(
                color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
              ),
            ),

            const SizedBox(height: 16),
            const Divider(),

            // Linha do tempo de mensagens / iterações
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
                            Text(
                              msg.sender,
                              style: AppTextStyles.badge.copyWith(
                                color: msg.isFromResident
                                    ? AppColors.primary
                                    : (isDark ? Colors.white : AppColors.lightTextPrimary),
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
                        if (msg.photoUrl != null) ...[
                          const SizedBox(height: 10),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.network(
                              msg.photoUrl!,
                              height: 130,
                              width: double.infinity,
                              fit: BoxFit.cover,
                            ),
                          ),
                        ],
                      ],
                    ),
                  );
                },
              ),
            ),

            // Campo para enviar réplica
            Container(
              padding: const EdgeInsets.only(top: 10),
              child: Row(
                children: [
                  IconButton(
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Foto anexada da galeria.')),
                      );
                    },
                    icon: const Icon(Icons.attach_file_rounded, color: AppColors.primary),
                  ),
                  Expanded(
                    child: TextField(
                      controller: _replyController,
                      style: TextStyle(
                        color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                      ),
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
