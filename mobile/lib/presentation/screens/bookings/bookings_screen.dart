import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';
import '../../../core/widgets/optional_network_image.dart';
import 'booking_detail_modal.dart';

/// Modelo simples para gerenciar as reservas ativas
class BookingItem {
  final String id;
  final String amenityName;
  final DateTime date;
  final String period;
  final String status;
  final String photoUrl;

  BookingItem({
    required this.id,
    required this.amenityName,
    required this.date,
    required this.period,
    this.status = 'Confirmada',
    required this.photoUrl,
  });
}

/// Tela de Reservas de Espaços Comuns 100% Funcional estilo Airbnb
class BookingsScreen extends StatefulWidget {
  const BookingsScreen({super.key});

  @override
  State<BookingsScreen> createState() => _BookingsScreenState();
}

class _BookingsScreenState extends State<BookingsScreen> {
  int _selectedTab = 0; // 0 = Catálogo de Espaços, 1 = Minhas Reservas

  final List<BookingItem> _myBookings = [
    BookingItem(
      id: 'RES-8921',
      amenityName: 'Espaço Gourmet & Churrasqueira',
      date: DateTime.now().add(const Duration(days: 3)),
      period: 'Noite (18:00 às 23:00)',
      photoUrl: '',
    ),
  ];

  final amenities = [
    {
      'id': 'gourmet',
      'name': 'Espaço Gourmet & Churrasqueira',
      'capacity': 'Até 30 convidados',
      'price': 'Taxa de limpeza: R\$ 80,00',
      'imageUrl': '',
      'rules': 'Proibido som automotivo após as 22h. Entregar limpo e sem resíduos nos ralos.',
    },
    {
      'id': 'salao',
      'name': 'Salão de Festas Principal',
      'capacity': 'Até 80 convidados',
      'price': 'Taxa de limpeza: R\$ 150,00',
      'imageUrl': '',
      'rules': 'Uso exclusivo para confraternizações sociais. Vistoria obrigatória antes e após o evento.',
    },
    {
      'id': 'quadra',
      'name': 'Quadra Poliesportiva',
      'capacity': 'Reserva por hora (Máx 2h/dia)',
      'price': 'Gratuito',
      'imageUrl': '',
      'rules': 'Uso obrigatório de calçado apropriado para quadra. Luzes apagadas às 22h.',
    },
  ];

  void _openBookingForm(Map<String, String> amenity) {
    DateTime selectedDate = DateTime.now().add(const Duration(days: 1));
    String selectedPeriod = 'Noite (18:00 às 23:00)';

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;
        final dateFormatter = DateFormat('dd/MM/yyyy');

        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
            return FrostedGlassCard(
              borderRadius: 32,
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
              child: SingleChildScrollView(
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
                    Text(
                      'Reservar ${amenity['name']}',
                      style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      amenity['price']!,
                      style: AppTextStyles.bodyMedium.copyWith(color: AppColors.primary),
                    ),
                    const SizedBox(height: 16),

                    // Seleção de Data
                    Text('Data do Evento', style: AppTextStyles.labelSmall),
                    const SizedBox(height: 6),
                    OutlinedButton.icon(
                      onPressed: () async {
                        final picked = await showDatePicker(
                          context: ctx,
                          initialDate: selectedDate,
                          firstDate: DateTime.now().add(const Duration(days: 1)),
                          lastDate: DateTime.now().add(const Duration(days: 90)),
                        );
                        if (picked != null) {
                          setModalState(() => selectedDate = picked);
                        }
                      },
                      icon: const Icon(Icons.calendar_month_rounded, size: 18),
                      label: Text(dateFormatter.format(selectedDate)),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Turno / Período
                    Text('Turno / Horário', style: AppTextStyles.labelSmall),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      initialValue: selectedPeriod,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                      items: const [
                        DropdownMenuItem(
                          value: 'Almoço (11:00 às 16:00)',
                          child: Text('Almoço (11:00 às 16:00)'),
                        ),
                        DropdownMenuItem(
                          value: 'Noite (18:00 às 23:00)',
                          child: Text('Noite (18:00 às 23:00)'),
                        ),
                        DropdownMenuItem(
                          value: 'Dia Inteiro (10:00 às 22:00)',
                          child: Text('Dia Inteiro (10:00 às 22:00)'),
                        ),
                      ],
                      onChanged: (val) => setModalState(() => selectedPeriod = val!),
                    ),

                    const SizedBox(height: 16),

                    // Regras da Convenção
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0x330F172A) : const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.info_outline_rounded, size: 16, color: AppColors.info),
                              const SizedBox(width: 6),
                              Text('Regras de Uso', style: AppTextStyles.badge.copyWith(color: AppColors.info)),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            amenity['rules']!,
                            style: AppTextStyles.labelSmall.copyWith(
                              color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),

                    ElevatedButton(
                      onPressed: () {
                        HapticFeedback.heavyImpact();
                        setState(() {
                          _myBookings.add(
                            BookingItem(
                              id: 'RES-${DateTime.now().millisecondsSinceEpoch.toString().substring(8)}',
                              amenityName: amenity['name']!,
                              date: selectedDate,
                              period: selectedPeriod,
                              photoUrl: amenity['imageUrl']!,
                            ),
                          );
                          _selectedTab = 1;
                        });
                        Navigator.pop(ctx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Reserva de ${amenity['name']} confirmada com sucesso!'),
                            backgroundColor: AppColors.success,
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: const Text('Confirmar Agendamento', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dateFormatter = DateFormat('dd/MM/yyyy');
    final canPop = Navigator.canPop(context);

    return BokehScaffold(
      appBar: canPop
          ? AppBar(
              backgroundColor: Colors.transparent,
              elevation: 0,
              leading: IconButton(
                icon: Icon(
                  Icons.arrow_back_ios_new_rounded,
                  color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                ),
                onPressed: () => Navigator.pop(context),
              ),
              title: Text(
                'Espaços & Reservas',
                style: AppTextStyles.titleMedium.copyWith(
                  fontWeight: FontWeight.bold,
                  color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                ),
              ),
              centerTitle: true,
            )
          : null,
      body: SafeArea(
        top: !canPop,
        bottom: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 120),
          children: [
            if (!canPop) ...[
              Text(
                'Espaços & Reservas',
                style: AppTextStyles.titleLarge.copyWith(
                  color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                ),
              ),
              const SizedBox(height: 4),
            ],
            Text(
              'Agende áreas comuns do condomínio com confirmação imediata',
              style: AppTextStyles.bodyMedium.copyWith(
                color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
              ),
            ),
            const SizedBox(height: 18),

        // Alternador de Abas
        Container(
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(
            color: isDark ? const Color(0x330F172A) : const Color(0xFFE2E8F0),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () {
                    HapticFeedback.lightImpact();
                    setState(() => _selectedTab = 0);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    decoration: BoxDecoration(
                      color: _selectedTab == 0
                          ? (isDark ? const Color(0xFF1E293B) : Colors.white)
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Center(
                      child: Text(
                        'Espaços Disponíveis',
                        style: AppTextStyles.titleSmall.copyWith(
                          fontSize: 13,
                          fontWeight: _selectedTab == 0 ? FontWeight.bold : FontWeight.w500,
                          color: _selectedTab == 0
                              ? AppColors.primary
                              : (isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              Expanded(
                child: GestureDetector(
                  onTap: () {
                    HapticFeedback.lightImpact();
                    setState(() => _selectedTab = 1);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    decoration: BoxDecoration(
                      color: _selectedTab == 1
                          ? (isDark ? const Color(0xFF1E293B) : Colors.white)
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Center(
                      child: Text(
                        'Minhas Reservas (${_myBookings.length})',
                        style: AppTextStyles.titleSmall.copyWith(
                          fontSize: 13,
                          fontWeight: _selectedTab == 1 ? FontWeight.bold : FontWeight.w500,
                          color: _selectedTab == 1
                              ? AppColors.primary
                              : (isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 20),

        if (_selectedTab == 0)
          // Catálogo de Espaços
          ...amenities.map((item) {
            return FrostedGlassCard(
              borderRadius: 24,
              padding: EdgeInsets.zero,
              margin: const EdgeInsets.only(bottom: 18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                    child: AspectRatio(
                      aspectRatio: 16 / 9,
                      child: OptionalNetworkImage(
                        url: item['imageUrl'] ?? '',
                        fit: BoxFit.cover,
                        placeholderIcon: Icons.apartment_rounded,
                      ),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                item['name']!,
                                style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: AppColors.successLight,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                'Disponível',
                                style: AppTextStyles.badge.copyWith(color: AppColors.success),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${item['capacity']} • ${item['price']}',
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                          ),
                        ),
                        const SizedBox(height: 12),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            onPressed: () => _openBookingForm(item),
                            icon: const Icon(Icons.event_available_rounded, size: 18),
                            label: const Text('Reservar Agora', style: TextStyle(fontWeight: FontWeight.bold)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          })
        else
          // Lista de Minhas Reservas
          if (_myBookings.isEmpty)
            Center(
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 60),
                child: Column(
                  children: [
                    Icon(Icons.event_busy_outlined, size: 60, color: Colors.grey.shade500),
                    const SizedBox(height: 12),
                    Text('Você não possui reservas ativas', style: AppTextStyles.titleSmall),
                  ],
                ),
              ),
            )
          else
            ..._myBookings.map((b) {
              return FrostedGlassCard(
                borderRadius: 22,
                margin: const EdgeInsets.only(bottom: 14),
                onTap: () {
                  HapticFeedback.lightImpact();
                  BookingDetailModal.show(context, b, () {
                    setState(() => _myBookings.remove(b));
                  });
                },
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.successLight,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            'VOUCHER #${b.id}',
                            style: AppTextStyles.badge.copyWith(color: AppColors.success),
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.cancel_outlined, color: AppColors.danger, size: 20),
                          tooltip: 'Cancelar Reserva',
                          onPressed: () {
                            HapticFeedback.mediumImpact();
                            showDialog(
                              context: context,
                              builder: (dialogCtx) => AlertDialog(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
                                title: const Text('Cancelar Agendamento?'),
                                content: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Atenção à Política de Cancelamento:',
                                      style: AppTextStyles.labelSmall.copyWith(fontWeight: FontWeight.bold, color: AppColors.danger),
                                    ),
                                    const SizedBox(height: 6),
                                    const Text(
                                      '• Cancelamento gratuito até 48 horas antes da data do evento.\n'
                                      '• Cancelamento tardio libera a área para outros condôminos mas retém a taxa de limpeza.\n\n'
                                      'Tem certeza de que deseja cancelar esta reserva?',
                                      style: TextStyle(fontSize: 13, height: 1.4),
                                    ),
                                  ],
                                ),
                                actions: [
                                  TextButton(
                                    onPressed: () => Navigator.pop(dialogCtx),
                                    child: const Text('Manter Reserva'),
                                  ),
                                  ElevatedButton(
                                    onPressed: () {
                                      Navigator.pop(dialogCtx);
                                      setState(() => _myBookings.remove(b));
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(
                                          content: Text('Reserva cancelada conforme a política do condomínio.'),
                                          backgroundColor: AppColors.danger,
                                        ),
                                      );
                                    },
                                    style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
                                    child: const Text('Sim, Cancelar', style: TextStyle(color: Colors.white)),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      b.amenityName,
                      style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Data: ${dateFormatter.format(b.date)} • ${b.period}',
                      style: AppTextStyles.bodyMedium.copyWith(
                        color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Text('Toque para ver voucher e QR Code de entrada', style: AppTextStyles.labelSmall.copyWith(color: AppColors.primary, fontWeight: FontWeight.w600)),
                        const SizedBox(width: 4),
                        const Icon(Icons.arrow_forward_ios_rounded, size: 11, color: AppColors.primary),
                      ],
                    ),
                  ],
                ),
              );
            }),
          ],
        ),
      ),
    );
  }
}
