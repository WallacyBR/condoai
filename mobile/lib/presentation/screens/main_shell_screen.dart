import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:predihub_app/core/widgets/airbnb_bottom_nav.dart';
import 'package:predihub_app/core/widgets/bokeh_background.dart';
import 'package:predihub_app/presentation/blocs/visitor_call/visitor_call_cubit.dart';
import 'package:predihub_app/presentation/blocs/visitor_call/visitor_call_state.dart';
import 'package:predihub_app/presentation/screens/bookings/bookings_screen.dart';
import 'package:predihub_app/presentation/screens/home/home_screen.dart';
import 'package:predihub_app/presentation/screens/notices/notices_screen.dart';
import 'package:predihub_app/presentation/screens/profile/profile_screen.dart';
import 'package:predihub_app/presentation/screens/quick_action/quick_action_modal.dart';
import 'package:predihub_app/presentation/screens/visitor_call/incoming_call_screen.dart';

/// Container Principal do Aplicativo (Shell com Navegação Airbnb e Interfone Noknox)
class MainShellScreen extends StatefulWidget {
  final VoidCallback onToggleTheme;
  final bool isDarkMode;

  const MainShellScreen({
    super.key,
    required this.onToggleTheme,
    required this.isDarkMode,
  });

  @override
  State<MainShellScreen> createState() => _MainShellScreenState();
}

class _MainShellScreenState extends State<MainShellScreen> {
  int _currentIndex = 0;

  void _onTabSelected(int index) {
    setState(() {
      _currentIndex = index;
    });
  }

  void _onCenterActionTap() {
    QuickActionModal.show(context);
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<VisitorCallCubit, VisitorCallState>(
      listener: (context, state) {
        if (state is VisitorCallIncoming) {
          // Abre a tela cheia de chamada estilo Noknox
          Navigator.of(context).push(
            PageRouteBuilder(
              opaque: false,
              pageBuilder: (context, animation, secondaryAnimation) =>
                  IncomingCallScreen(call: state.call),
              transitionsBuilder:
                  (context, animation, secondaryAnimation, child) {
                return FadeTransition(opacity: animation, child: child);
              },
            ),
          );
        } else if (state is VisitorCallFinished) {
          // Fecha tela de chamada se ainda estiver aberta e exibe toast de confirmação
          Navigator.of(context).popUntil((route) => route.isFirst);
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  Icon(state.decision.icon, color: Colors.white),
                  const SizedBox(width: 10),
                  Expanded(child: Text(state.message)),
                ],
              ),
              backgroundColor: state.decision.color,
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            ),
          );
        }
      },
      child: Scaffold(
        extendBody: true,
        body: BokehBackground(
          child: IndexedStack(
            index: _currentIndex,
            children: [
              HomeScreen(onOpenQuickAction: _onCenterActionTap),
              const NoticesScreen(),
              const SizedBox.shrink(), // Placeholder do índice 2 reservado para ação central
              const BookingsScreen(),
              ProfileScreen(
                onToggleTheme: widget.onToggleTheme,
                isDarkMode: widget.isDarkMode,
              ),
            ],
          ),
        ),
        bottomNavigationBar: AirbnbBottomNav(
          currentIndex: _currentIndex,
          onTabSelected: _onTabSelected,
          onCenterActionTap: _onCenterActionTap,
        ),
      ),
    );
  }
}
