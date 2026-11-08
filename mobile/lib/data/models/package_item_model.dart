import 'package:equatable/equatable.dart';

/// Modelo de Encomenda recebida na Portaria
class PackageItemModel extends Equatable {
  final int id;
  final String company;
  final String trackingCode;
  final String withdrawalCode;
  final String recipientName;
  final String porterName;
  final String photoUrl;
  final DateTime receivedAt;
  final bool isWithdrawn;
  final DateTime? withdrawnAt;
  final String? storageLocation;

  const PackageItemModel({
    required this.id,
    required this.company,
    required this.trackingCode,
    required this.withdrawalCode,
    required this.recipientName,
    required this.porterName,
    required this.photoUrl,
    required this.receivedAt,
    this.isWithdrawn = false,
    this.withdrawnAt,
    this.storageLocation = 'Armário A - Prateleira 2',
  });

  PackageItemModel copyWith({
    bool? isWithdrawn,
    DateTime? withdrawnAt,
  }) {
    return PackageItemModel(
      id: id,
      company: company,
      trackingCode: trackingCode,
      withdrawalCode: withdrawalCode,
      recipientName: recipientName,
      porterName: porterName,
      photoUrl: photoUrl,
      receivedAt: receivedAt,
      isWithdrawn: isWithdrawn ?? this.isWithdrawn,
      withdrawnAt: withdrawnAt ?? this.withdrawnAt,
      storageLocation: storageLocation,
    );
  }

  @override
  List<Object?> get props => [
        id,
        company,
        trackingCode,
        withdrawalCode,
        recipientName,
        porterName,
        photoUrl,
        receivedAt,
        isWithdrawn,
        withdrawnAt,
        storageLocation,
      ];
}
