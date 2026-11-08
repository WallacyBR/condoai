import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../data/models/access_request_model.dart';
import '../../../domain/entities/access_decision.dart';
import '../../blocs/visitor_call/visitor_call_cubit.dart';

/// Tela de Chamada de Visitante estilo Noknox (Interfone Virtual com Efeito Glassmorphism & Bokeh)
class IncomingCallScreen extends StatelessWidget {
  final AccessRequestModel call;

  const IncomingCallScreen({
    super.key,
    required this.call,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        fit: StackFit.expand,
        children: [
          // 1. Fundo com imagem e desfoque profundo
          if (call.visitorPhotoUrl != null)
            Image.network(
              call.visitorPhotoUrl!,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) => Container(
                color: const Color(0xFF0F172A),
              ),
            )
          else
            Container(color: const Color(0xFF0F172A)),

          // 2. Camada escura de desfoque atmosférico (Bokeh overlay)
          BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 45, sigmaY: 45),
            child: Container(
              color: Colors.black.withAlpha(165),
            ),
          ),

          // 3. Conteúdo da Chamada
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              child: Column(
                children: [
                  // Topo: Indicador de Interfone / Portaria
                  FrostedGlassCard(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    borderRadius: 24,
                    customColor: const Color(0x33FFFFFF),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 10,
                              height: 10,
                              decoration: const BoxDecoration(
                                color: AppColors.success,
                                shape: BoxShape.circle,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'Interfone • Portaria Principal',
                              style: AppTextStyles.labelSmall.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withAlpha(38),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            'Chamada ao Vivo',
                            style: AppTextStyles.badge.copyWith(
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const Spacer(flex: 1),

                  // Cartão Central: Foto e Identificação do Visitante
                  FrostedGlassCard(
                    padding: const EdgeInsets.all(16),
                    borderRadius: 28,
                    customColor: const Color(0x40FFFFFF),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Foto do Visitante
                        ClipRRect(
                          borderRadius: BorderRadius.circular(20),
                          child: AspectRatio(
                            aspectRatio: 4 / 3,
                            child: Stack(
                              fit: StackFit.expand,
                              children: [
                                if (call.visitorPhotoUrl != null)
                                  Image.network(
                                    call.visitorPhotoUrl!,
                                    fit: BoxFit.cover,
                                    errorBuilder: (context, error, stackTrace) => const Center(
                                      child: Icon(
                                        Icons.person_rounded,
                                        size: 80,
                                        color: Colors.white54,
                                      ),
                                    ),
                                  )
                                else
                                  const Center(
                                    child: Icon(
                                      Icons.person_rounded,
                                      size: 80,
                                      color: Colors.white54,
                                    ),
                                  ),
                                Positioned(
                                  bottom: 10,
                                  left: 10,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 10,
                                      vertical: 4,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Colors.black.withAlpha(153),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Text(
                                      'Câmera da Portaria',
                                      style: AppTextStyles.badge.copyWith(
                                        color: Colors.white70,
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        // Nome e Empresa do Visitante
                        Text(
                          call.visitorName,
                          style: AppTextStyles.titleMedium.copyWith(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          call.subtitleInfo,
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: Colors.white70,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        if (call.vehiclePlate != null) ...[
                          const SizedBox(height: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.white.withAlpha(25),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              'Veículo: ${call.vehiclePlate} (${call.vehicleModel ?? 'Não informado'})',
                              style: AppTextStyles.labelSmall.copyWith(
                                color: Colors.white60,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),

                  const Spacer(flex: 2),

                  // 4 Botões de Ação Imediata (Estilo Noknox)
                  FrostedGlassCard(
                    padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 12),
                    borderRadius: 32,
                    customColor: const Color(0x33FFFFFF),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      children: [
                        // 1. Autorizar
                        _CallActionButton(
                          decision: AccessDecision.authorized,
                          onPressed: () {
                            HapticFeedback.heavyImpact();
                            context.read<VisitorCallCubit>().answerCall(AccessDecision.authorized);
                          },
                        ),
                        // 2. Aguardar na Portaria
                        _CallActionButton(
                          decision: AccessDecision.awaitingAtGatehouse,
                          onPressed: () {
                            HapticFeedback.mediumImpact();
                            context.read<VisitorCallCubit>().answerCall(AccessDecision.awaitingAtGatehouse);
                          },
                        ),
                        // 3. Não estou em casa
                        _CallActionButton(
                          decision: AccessDecision.informNotHome,
                          onPressed: () {
                            HapticFeedback.mediumImpact();
                            context.read<VisitorCallCubit>().answerCall(AccessDecision.informNotHome);
                          },
                        ),
                        // 4. Não autorizar
                        _CallActionButton(
                          decision: AccessDecision.unauthorized,
                          onPressed: () {
                            HapticFeedback.heavyImpact();
                            context.read<VisitorCallCubit>().answerCall(AccessDecision.unauthorized);
                          },
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _CallActionButton extends StatelessWidget {
  final AccessDecision decision;
  final VoidCallback onPressed;

  const _CallActionButton({
    required this.decision,
    required this.onPressed,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        GestureDetector(
          onTap: onPressed,
          child: Container(
            width: 58,
            height: 58,
            decoration: BoxDecoration(
              color: decision.color,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: decision.color.withAlpha(128),
                  blurRadius: 16,
                  offset: const Offset(0, 6),
                ),
              ],
              border: Border.all(
                color: Colors.white.withAlpha(76),
                width: 2,
              ),
            ),
            child: Center(
              child: Icon(
                decision.icon,
                color: Colors.white,
                size: 26,
              ),
            ),
          ),
        ),
        const SizedBox(height: 8),
        SizedBox(
          width: 72,
          child: Text(
            decision.label,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 10.5,
              fontWeight: FontWeight.w600,
              height: 1.2,
            ),
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}
