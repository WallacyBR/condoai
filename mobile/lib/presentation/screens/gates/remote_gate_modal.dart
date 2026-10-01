import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';

/// Modal interativo de Abertura Remota de Portão com contagem regressiva de segurança
class RemoteGateModal extends StatefulWidget {
  const RemoteGateModal({super.key});

  static void show(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) => const RemoteGateModal(),
    );
  }

  @override
  State<RemoteGateModal> createState() => _RemoteGateModalState();
}

class _RemoteGateModalState extends State<RemoteGateModal> {
  String _selectedGate = 'Portão Social de Pedestres';
  bool _isOpening = false;
  bool _isUnlocked = false;
  int _countdown = 7;
  Timer? _timer;

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  void _triggerUnlock() {
    HapticFeedback.heavyImpact();
    setState(() {
      _isOpening = true;
    });

    // Simula comunicação com a controladora de acesso do condomínio
    Future.delayed(const Duration(milliseconds: 700), () {
      if (!mounted) return;
      HapticFeedback.heavyImpact();
      setState(() {
        _isOpening = false;
        _isUnlocked = true;
        _countdown = 7;
      });

      _timer = Timer.periodic(const Duration(seconds: 1), (t) {
        if (_countdown > 1) {
          setState(() => _countdown--);
        } else {
          t.cancel();
          if (mounted) {
            setState(() {
              _isUnlocked = false;
            });
          }
        }
      });
    });
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
        children: [
          Container(
            width: 44,
            height: 4,
            decoration: BoxDecoration(
              color: isDark ? Colors.white24 : Colors.black12,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'Abertura Remota de Portão',
            style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(
            'Comando direto para as fechaduras eletrônicas',
            style: AppTextStyles.bodyMedium.copyWith(
              color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
            ),
          ),
          const SizedBox(height: 20),

          // Seletor de Portão
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            decoration: BoxDecoration(
              color: isDark ? const Color(0x330F172A) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isDark ? AppColors.darkCardBorder : AppColors.lightCardBorder,
              ),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: _selectedGate,
                isExpanded: true,
                items: const [
                  DropdownMenuItem(
                    value: 'Portão Social de Pedestres',
                    child: Text('Portão Social de Pedestres'),
                  ),
                  DropdownMenuItem(
                    value: 'Portão de Veículos (Garagem)',
                    child: Text('Portão de Veículos (Garagem)'),
                  ),
                  DropdownMenuItem(
                    value: 'Porta de Entrada do Bloco',
                    child: Text('Porta de Entrada do Bloco'),
                  ),
                ],
                onChanged: _isUnlocked || _isOpening
                    ? null
                    : (val) => setState(() => _selectedGate = val!),
              ),
            ),
          ),

          const SizedBox(height: 28),

          // Botão Interativo de Abertura / Status
          if (_isOpening)
            Column(
              children: [
                const SizedBox(
                  width: 50,
                  height: 50,
                  child: CircularProgressIndicator(color: AppColors.primary, strokeWidth: 3),
                ),
                const SizedBox(height: 16),
                Text(
                  'Enviando comando para a controladora...',
                  style: AppTextStyles.bodyMedium,
                ),
              ],
            )
          else if (_isUnlocked)
            Column(
              children: [
                Container(
                  width: 90,
                  height: 90,
                  decoration: BoxDecoration(
                    color: AppColors.success,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.success.withAlpha(128),
                        blurRadius: 24,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: const Center(
                    child: Icon(Icons.lock_open_rounded, color: Colors.white, size: 44),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Portão Aberto!',
                  style: AppTextStyles.titleMedium.copyWith(
                    color: AppColors.success,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Travamento automático em $_countdown segundos',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                  ),
                ),
              ],
            )
          else
            GestureDetector(
              onTap: _triggerUnlock,
              child: Container(
                width: 110,
                height: 110,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF6366F1), Color(0xFF4338CA)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withAlpha(120),
                      blurRadius: 24,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: const Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.touch_app_rounded, color: Colors.white, size: 40),
                    SizedBox(height: 4),
                    Text(
                      'DESTRANCAR',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 1.0,
                      ),
                    ),
                  ],
                ),
              ),
            ),

          const SizedBox(height: 24),
          Text(
            'Seu acesso é registrado no log de segurança da portaria.',
            style: AppTextStyles.labelSmall.copyWith(
              color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
