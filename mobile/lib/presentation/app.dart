import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../core/theme/app_theme.dart';
import 'blocs/packages/packages_cubit.dart';
import 'blocs/unit/unit_cubit.dart';
import 'blocs/visitor_call/visitor_call_cubit.dart';
import 'screens/main_shell_screen.dart';

/// Ponto de entrada do Widget da Aplicação PrediHub Mobile
class PrediHubApp extends StatefulWidget {
  const PrediHubApp({super.key});

  @override
  State<PrediHubApp> createState() => _PrediHubAppState();
}

class _PrediHubAppState extends State<PrediHubApp> {
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
        title: 'PrediHub Morador',
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
