import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_text_styles.dart';
import '../../../core/widgets/bokeh_scaffold.dart';
import '../../../core/widgets/frosted_glass_card.dart';

/// Tela completa de Gestão e Cadastro de Pets da Unidade
class ManagePetsScreen extends StatefulWidget {
  const ManagePetsScreen({super.key});

  @override
  State<ManagePetsScreen> createState() => _ManagePetsScreenState();
}

class PetItem {
  final String name;
  final String breed;
  final String size;
  final String age;
  final bool isVaccinated;
  final String photoUrl;
  final String species;

  PetItem({
    required this.name,
    required this.breed,
    required this.size,
    required this.age,
    this.isVaccinated = true,
    required this.photoUrl,
    required this.species,
  });
}

class _ManagePetsScreenState extends State<ManagePetsScreen> {
  final List<PetItem> _pets = [
    PetItem(
      name: 'Thor',
      breed: 'Golden Retriever',
      size: 'Porte Grande',
      age: '3 anos',
      isVaccinated: true,
      species: 'Cão',
      photoUrl: 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&auto=format&fit=crop&q=80',
    ),
  ];

  void _openCreatePetModal() {
    final nameCtrl = TextEditingController();
    final breedCtrl = TextEditingController();
    final ageCtrl = TextEditingController();
    String size = 'Médio';
    String species = 'Cachorro';
    bool vaccinated = true;

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
                          'Cadastrar Novo Pet',
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

                    // Avatar simulado do pet
                    Center(
                      child: Stack(
                        children: [
                          const CircleAvatar(
                            radius: 36,
                            backgroundColor: AppColors.primarySoft,
                            child: Icon(Icons.pets, size: 36, color: AppColors.primary),
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

                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            initialValue: species,
                            decoration: InputDecoration(
                              labelText: 'Espécie',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                            items: const [
                              DropdownMenuItem(value: 'Cachorro', child: Text('Cachorro')),
                              DropdownMenuItem(value: 'Gato', child: Text('Gato')),
                              DropdownMenuItem(value: 'Ave', child: Text('Pássaro / Ave')),
                              DropdownMenuItem(value: 'Outro', child: Text('Outro')),
                            ],
                            onChanged: (val) => setModalState(() => species = val!),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            initialValue: size,
                            decoration: InputDecoration(
                              labelText: 'Porte',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                            items: const [
                              DropdownMenuItem(value: 'Pequeno', child: Text('Pequeno (até 10kg)')),
                              DropdownMenuItem(value: 'Médio', child: Text('Médio (10-25kg)')),
                              DropdownMenuItem(value: 'Grande', child: Text('Grande (+25kg)')),
                            ],
                            onChanged: (val) => setModalState(() => size = val!),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: nameCtrl,
                      decoration: InputDecoration(
                        labelText: 'Nome do Pet *',
                        hintText: 'Ex: Thor / Mel / Luna',
                        prefixIcon: const Icon(Icons.badge_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: breedCtrl,
                            decoration: InputDecoration(
                              labelText: 'Raça',
                              hintText: 'Ex: Shih-tzu / SRD',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: ageCtrl,
                            decoration: InputDecoration(
                              labelText: 'Idade Aproximada',
                              hintText: 'Ex: 2 anos',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    SwitchListTile.adaptive(
                      contentPadding: EdgeInsets.zero,
                      value: vaccinated,
                      activeTrackColor: AppColors.success,
                      title: const Text('Carteira de Vacinação em Dia (V8/V10 e Antirrábica)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      onChanged: (val) => setModalState(() => vaccinated = val),
                    ),

                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () {
                        if (nameCtrl.text.trim().isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Informe o nome do pet.'), backgroundColor: AppColors.danger),
                          );
                          return;
                        }

                        HapticFeedback.heavyImpact();
                        setState(() {
                          _pets.add(
                            PetItem(
                              name: nameCtrl.text.trim(),
                              breed: breedCtrl.text.trim().isNotEmpty ? breedCtrl.text.trim() : 'SRD (Sem Raça Definida)',
                              size: 'Porte $size',
                              age: ageCtrl.text.trim().isNotEmpty ? ageCtrl.text.trim() : 'Idade não informada',
                              isVaccinated: vaccinated,
                              species: species,
                              photoUrl: species == 'Gato'
                                  ? 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&auto=format&fit=crop&q=80'
                                  : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400&auto=format&fit=crop&q=80',
                            ),
                          );
                        });
                        Navigator.pop(modalCtx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Pet ${nameCtrl.text.trim()} cadastrado com sucesso!'),
                            backgroundColor: AppColors.success,
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: const Text('Salvar Pet', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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
          'Pets da Unidade',
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
              'Pets cadastrados para circulação e acesso ao Pet Place do condomínio.',
              style: AppTextStyles.bodyMedium.copyWith(
                color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
              ),
            ),
            const SizedBox(height: 18),

            ..._pets.map((pet) {
              return FrostedGlassCard(
                borderRadius: 20,
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Image.network(
                        pet.photoUrl,
                        width: 60,
                        height: 60,
                        fit: BoxFit.cover,
                        errorBuilder: (_, error, stack) => Container(
                          width: 60,
                          height: 60,
                          color: AppColors.primarySoft,
                          child: const Icon(Icons.pets, color: AppColors.primary),
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
                                  pet.name,
                                  style: AppTextStyles.titleSmall.copyWith(fontWeight: FontWeight.bold),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                decoration: BoxDecoration(
                                  color: pet.isVaccinated ? const Color(0x2610B981) : const Color(0x26EF4444),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  pet.isVaccinated ? 'Vacinas OK' : 'Pendente',
                                  style: AppTextStyles.badge.copyWith(
                                    color: pet.isVaccinated ? AppColors.success : AppColors.danger,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '${pet.species} • ${pet.breed} • ${pet.age}',
                            style: AppTextStyles.labelSmall.copyWith(
                              color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            pet.size,
                            style: AppTextStyles.bodyMedium.copyWith(
                              fontSize: 12,
                              color: isDark ? Colors.white70 : Colors.black87,
                            ),
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
              onPressed: _openCreatePetModal,
              icon: const Icon(Icons.add_circle_outline_rounded, color: Colors.white),
              label: const Text('Cadastrar Novo Pet', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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
