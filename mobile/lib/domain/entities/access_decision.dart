import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';

/// Decisões de acesso que o morador pode tomar ao receber a chamada do visitante
enum AccessDecision {
  authorized,
  awaitingAtGatehouse,
  informNotHome,
  unauthorized;

  String get apiValue {
    switch (this) {
      case AccessDecision.authorized:
        return 'authorized';
      case AccessDecision.awaitingAtGatehouse:
        return 'awaiting_at_gatehouse';
      case AccessDecision.informNotHome:
        return 'inform_not_home';
      case AccessDecision.unauthorized:
        return 'unauthorized';
    }
  }

  String get label {
    switch (this) {
      case AccessDecision.authorized:
        return 'Autorizar';
      case AccessDecision.awaitingAtGatehouse:
        return 'Aguardar na Portaria';
      case AccessDecision.informNotHome:
        return 'Não estou em casa';
      case AccessDecision.unauthorized:
        return 'Não autorizar';
    }
  }

  Color get color {
    switch (this) {
      case AccessDecision.authorized:
        return AppColors.success;
      case AccessDecision.awaitingAtGatehouse:
        return AppColors.warning;
      case AccessDecision.informNotHome:
        return AppColors.info;
      case AccessDecision.unauthorized:
        return AppColors.danger;
    }
  }

  IconData get icon {
    switch (this) {
      case AccessDecision.authorized:
        return Icons.key_rounded;
      case AccessDecision.awaitingAtGatehouse:
        return Icons.hourglass_top_rounded;
      case AccessDecision.informNotHome:
        return Icons.home_work_outlined;
      case AccessDecision.unauthorized:
        return Icons.close_rounded;
    }
  }
}
