import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../data/models/package_item_model.dart';
import 'packages_state.dart';

/// Cubit responsável por gerenciar as encomendas recebidas na portaria
class PackagesCubit extends Cubit<PackagesState> {
  PackagesCubit() : super(PackagesInitial()) {
    loadMockPackages();
  }

  void loadMockPackages() {
    final mockList = [
      PackageItemModel(
        id: 101,
        company: 'Amazon Brasil',
        trackingCode: 'AMZ-BR-849102-SP',
        withdrawalCode: '#AMZ-8821',
        recipientName: 'Wallacy Silva',
        porterName: 'Rogério Medeiros (Portaria Principal)',
        photoUrl: 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=800&auto=format&fit=crop&q=80',
        receivedAt: DateTime.now().subtract(const Duration(hours: 2, minutes: 15)),
        isWithdrawn: false,
        storageLocation: 'Armário Encomendas A • Prateleira 2',
      ),
      PackageItemModel(
        id: 102,
        company: 'Mercado Livre',
        trackingCode: 'MELI-99218401-BR',
        withdrawalCode: '#MEL-3047',
        recipientName: 'Wallacy Silva',
        porterName: 'Rogério Medeiros (Portaria Principal)',
        photoUrl: 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&auto=format&fit=crop&q=80',
        receivedAt: DateTime.now().subtract(const Duration(hours: 4, minutes: 40)),
        isWithdrawn: false,
        storageLocation: 'Armário Encomendas B • Prateleira 1',
      ),
    ];

    emit(PackagesLoaded(mockList));
  }

  /// Registra a retirada da encomenda (baixa na portaria)
  void confirmWithdrawal(int packageId) {
    if (state is PackagesLoaded) {
      final current = (state as PackagesLoaded).packages;
      final updated = current.map((p) {
        if (p.id == packageId) {
          return p.copyWith(
            isWithdrawn: true,
            withdrawnAt: DateTime.now(),
          );
        }
        return p;
      }).toList();

      emit(PackagesLoaded(updated));
    }
  }
}
