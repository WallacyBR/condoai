import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import 'bokeh_background.dart';

/// Scaffold padrão do CondoAI que garante que o fundo Bokeh e cores do tema
/// sejam sempre renderizados corretamente, eliminando falhas de tela preta em transições.
class BokehScaffold extends StatelessWidget {
  final PreferredSizeWidget? appBar;
  final Widget body;
  final Widget? bottomNavigationBar;
  final Widget? floatingActionButton;
  final bool extendBody;

  const BokehScaffold({
    super.key,
    this.appBar,
    required this.body,
    this.bottomNavigationBar,
    this.floatingActionButton,
    this.extendBody = false,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppColors.darkBackground : AppColors.lightBackground,
      extendBody: extendBody,
      appBar: appBar,
      body: BokehBackground(
        child: body,
      ),
      bottomNavigationBar: bottomNavigationBar,
      floatingActionButton: floatingActionButton,
    );
  }
}
