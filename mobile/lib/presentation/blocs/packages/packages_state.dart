import 'package:equatable/equatable.dart';
import '../../../data/models/package_item_model.dart';

abstract class PackagesState extends Equatable {
  const PackagesState();

  @override
  List<Object?> get props => [];
}

class PackagesInitial extends PackagesState {}

class PackagesLoaded extends PackagesState {
  final List<PackageItemModel> packages;

  const PackagesLoaded(this.packages);

  List<PackageItemModel> get pendingPackages =>
      packages.where((p) => !p.isWithdrawn).toList();

  List<PackageItemModel> get withdrawnPackages =>
      packages.where((p) => p.isWithdrawn).toList();

  int get pendingCount => pendingPackages.length;

  @override
  List<Object?> get props => [packages];
}
