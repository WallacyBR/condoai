import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';

/// Tela completa de Gestão e Cadastro de Moradores / Dependentes da Unidade
class ManageResidentsScreen extends StatefulWidget {
  const ManageResidentsScreen({super.key});

  @override
  State<ManageResidentsScreen> createState() => _ManageResidentsScreenState();
}

class ResidentData {
  final String name;
  final String document;
  final String phone;
  final String email;
  final String relationship;
  final String photoUrl;
  final bool hasFacialAccess;
  final bool canAuthorizeVisitors;
  final bool isOwner;

  ResidentData({
    required this.name,
    required this.document,
    required this.phone,
    required this.email,
    required this.relationship,
    required this.photoUrl,
    this.hasFacialAccess = true,
    this.canAuthorizeVisitors = true,
    this.isOwner = false,
  });
}

class _ManageResidentsScreenState extends State<ManageResidentsScreen> {
  final List<ResidentData> _residents = [
    ResidentData(
      name: 'Wallacy Silva',
      document: '412.***.***-89',
      phone: '(11) 98765-4321',
      email: 'wallacy@condoai.com',
      relationship: 'Titular / Proprietário',
      photoUrl: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80',
      hasFacialAccess: true,
      canAuthorizeVisitors: true,
      isOwner: true,
    ),
    ResidentData(
      name: 'Larissa Silva',
      document: '389.***.***-20',
      phone: '(11) 97654-3210',
      email: 'larissa@condoai.com',
      relationship: 'Cônjuge',
      photoUrl: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&auto=format&fit=crop&q=80',
      hasFacialAccess: true,
      canAuthorizeVisitors: true,
      isOwner: false,
    ),
  ];

  void _openCreateResidentForm() {
    final nameCtrl = TextEditingController();
    final docCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final emailCtrl = TextEditingController();
    String relation = 'Filho(a)';
    bool facialAccess = true;
    bool visitorAccess = true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;

        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
            return FrostedGlassCard(
              borderRadius: 28,
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 20),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Novo Morador / Dependente',
                          style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                        ),
                        IconButton(
                          icon: Icon(
                            Icons.close_rounded,
                            size: 20,
                            color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                          ),
                          onPressed: () => Navigator.pop(modalCtx),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Avatar com foto simulada
                    Center(
                      child: Stack(
                        children: [
                          const CircleAvatar(
                            radius: 36,
                            backgroundColor: AppColors.primarySoft,
                            child: Icon(Icons.person_outline_rounded, size: 38, color: AppColors.primary),
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              padding: const EdgeInsets.all(4),
                              decoration: const BoxDecoration(
                                color: AppColors.primary,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.camera_alt_rounded, size: 14, color: Colors.white),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    TextField(
                      controller: nameCtrl,
                      decoration: InputDecoration(
                        labelText: 'Nome Completo *',
                        prefixIcon: const Icon(Icons.badge_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: docCtrl,
                            keyboardType: TextInputType.number,
                            decoration: InputDecoration(
                              labelText: 'CPF ou RG',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            initialValue: relation,
                            decoration: InputDecoration(
                              labelText: 'Parentesco',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                            items: const [
                              DropdownMenuItem(value: 'Filho(a)', child: Text('Filho(a)')),
                              DropdownMenuItem(value: 'Cônjuge', child: Text('Cônjuge')),
                              DropdownMenuItem(value: 'Pai / Mãe', child: Text('Pai / Mãe')),
                              DropdownMenuItem(value: 'Inquilino', child: Text('Inquilino')),
                              DropdownMenuItem(value: 'Prestador', child: Text('Funcionário')),
                            ],
                            onChanged: (val) => setModalState(() => relation = val!),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: phoneCtrl,
                      keyboardType: TextInputType.phone,
                      decoration: InputDecoration(
                        labelText: 'Celular / WhatsApp',
                        prefixIcon: const Icon(Icons.phone_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: emailCtrl,
                      keyboardType: TextInputType.emailAddress,
                      decoration: InputDecoration(
                        labelText: 'E-mail para Acesso ao App',
                        prefixIcon: const Icon(Icons.email_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Permissões
                    SwitchListTile.adaptive(
                      contentPadding: EdgeInsets.zero,
                      value: facialAccess,
                      activeTrackColor: AppColors.primary,
                      title: const Text('Liberação de Acesso Facial / Biometria', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      onChanged: (val) => setModalState(() => facialAccess = val),
                    ),
                    SwitchListTile.adaptive(
                      contentPadding: EdgeInsets.zero,
                      value: visitorAccess,
                      activeTrackColor: AppColors.primary,
                      title: const Text('Pode Autorizar Visitantes e Encomendas', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      onChanged: (val) => setModalState(() => visitorAccess = val),
                    ),

                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () {
                        if (nameCtrl.text.trim().isEmpty) return;
                        HapticFeedback.heavyImpact();
                        setState(() {
                          _residents.add(
                            ResidentData(
                              name: nameCtrl.text.trim(),
                              document: docCtrl.text.trim().isNotEmpty ? docCtrl.text.trim() : '000.***.***-00',
                              phone: phoneCtrl.text.trim().isNotEmpty ? phoneCtrl.text.trim() : '(11) 90000-0000',
                              email: emailCtrl.text.trim(),
                              relationship: relation,
                              photoUrl: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&auto=format&fit=crop&q=80',
                              hasFacialAccess: facialAccess,
                              canAuthorizeVisitors: visitorAccess,
                            ),
                          );
                        });
                        Navigator.pop(modalCtx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('${nameCtrl.text.trim()} cadastrado com sucesso!'),
                            backgroundColor: AppColors.success,
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: const Text('Salvar Cadastro', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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

    return BokehScaffold(
      appBar: AppBar(
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
          'Moradores & Dependentes',
          style: AppTextStyles.titleMedium.copyWith(fontWeight: FontWeight.bold),
        ),
        centerTitle: true,
      ),
      body: SafeArea(
        top: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
          children: [
            Text(
              'Gerencie as pessoas com acesso autorizado à sua unidade condominial.',
              style: AppTextStyles.bodyMedium.copyWith(
                color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
              ),
            ),
            const SizedBox(height: 18),

            ..._residents.map((resident) {
              return FrostedGlassCard(
                borderRadius: 20,
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Image.network(
                        resident.photoUrl,
                        width: 52,
                        height: 52,
                        fit: BoxFit.cover,
                        errorBuilder: (_, error, stack) => const CircleAvatar(
                          radius: 26,
                          backgroundColor: AppColors.primary,
                          child: Icon(Icons.person, color: Colors.white),
                        ),
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  resident.name,
                                  style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              if (resident.isOwner)
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: AppColors.primarySoft,
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text('Titular', style: AppTextStyles.badge.copyWith(color: AppColors.primary)),
                                ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '${resident.relationship} • ${resident.document}',
                            style: AppTextStyles.labelSmall.copyWith(
                              color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Row(
                            children: [
                              Icon(
                                resident.hasFacialAccess ? Icons.face_rounded : Icons.face_retouching_off_rounded,
                                size: 14,
                                color: resident.hasFacialAccess ? AppColors.success : Colors.grey,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                resident.hasFacialAccess ? 'Facial ativo' : 'Sem biometria',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: resident.hasFacialAccess ? AppColors.success : Colors.grey,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              const SizedBox(width: 10),
                              Icon(
                                resident.canAuthorizeVisitors ? Icons.check_circle_outline_rounded : Icons.block_rounded,
                                size: 14,
                                color: resident.canAuthorizeVisitors ? AppColors.primary : Colors.grey,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                resident.canAuthorizeVisitors ? 'Libera visitas' : 'Apenas entrada',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: resident.canAuthorizeVisitors ? AppColors.primary : Colors.grey,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            }),

            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _openCreateResidentForm,
              icon: const Icon(Icons.person_add_rounded, color: Colors.white),
              label: const Text('Cadastrar Novo Morador', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
