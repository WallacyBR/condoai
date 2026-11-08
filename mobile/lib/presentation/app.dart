import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../core/theme/app_theme.dart';
import 'blocs/packages/packages_cubit.dart';
import 'blocs/unit/unit_cubit.dart';
import 'blocs/visitor_call/visitor_call_cubit.dart';
import 'screens/main_shell_screen.dart';

/// Ponto de entrada do Widget da Aplicação CondoAI Mobile
class CondoAIApp extends StatefulWidget {
  const CondoAIApp({super.key});

  @override
  State<CondoAIApp> createState() => _CondoAIAppState();
}

class _CondoAIAppState extends State<CondoAIApp> {
  ThemeMode _themeMode = ThemeMode.light;

  void _toggleTheme() {
    setState(() {
      _themeMode = _themeMode == ThemeMode.light ? ThemeMode.dark : ThemeMode.light;
    });
  }

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider(create: (_) => UnitCubit()),
        BlocProvider(create: (_) => VisitorCallCubit()),
        BlocProvider(create: (_) => PackagesCubit()),
      ],
      child: MaterialApp(
        title: 'CondoAI Morador',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        darkTheme: AppTheme.darkTheme,
        themeMode: _themeMode,
        home: MainShellScreen(
          onToggleTheme: _toggleTheme,
          isDarkMode: _themeMode == ThemeMode.dark,
        ),
      ),
    );
  }
}
