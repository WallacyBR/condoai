import 'package:equatable/equatable.dart';

/// Modelo de Solicitação de Acesso / Chamada de Visitante Noknox
class AccessRequestModel extends Equatable {
  final int id;
  final String visitorName;
  final String? visitorDocument;
  final String? visitorPhone;
  final String? visitorPhotoUrl;
  final String? visitorType;
  final String? company;
  final String? vehiclePlate;
  final String? vehicleModel;
  final String? vehicleColor;
  final DateTime requestTime;
  final String status;
  final String? gatehouseObservation;

  const AccessRequestModel({
    required this.id,
    required this.visitorName,
    this.visitorDocument,
    this.visitorPhone,
    this.visitorPhotoUrl,
    this.visitorType,
    this.company,
    this.vehiclePlate,
    this.vehicleModel,
    this.vehicleColor,
    required this.requestTime,
    required this.status,
    this.gatehouseObservation,
  });

  String get subtitleInfo {
    if (company != null && company!.isNotEmpty) {
      return '$visitorType • $company';
    }
    return visitorType ?? 'Visitante';
  }

  factory AccessRequestModel.fromJson(Map<String, dynamic> json) {
    return AccessRequestModel(
      id: json['id'] as int,
      visitorName: json['visitor_name'] as String? ?? 'Visitante',
      visitorDocument: json['visitor_document'] as String?,
      visitorPhone: json['visitor_phone'] as String?,
      visitorPhotoUrl: json['visitor_photo_url'] as String? ??
          json['visitor']?['photo_url'] as String?,
      visitorType: json['purpose'] as String? ?? 'Visitante',
      company: json['company'] as String?,
      vehiclePlate: json['vehicle_plate'] as String?,
      vehicleModel: json['vehicle_model'] as String?,
      vehicleColor: json['vehicle_color'] as String?,
      requestTime: json['request_timestamp'] != null
          ? DateTime.tryParse(json['request_timestamp'] as String) ?? DateTime.now()
          : DateTime.now(),
      status: json['status'] as String? ?? 'pending',
      gatehouseObservation: json['gatehouse_observation'] as String?,
    );
  }

  @override
  List<Object?> get props => [
        id,
        visitorName,
        visitorDocument,
        visitorPhone,
        visitorPhotoUrl,
        visitorType,
        company,
        vehiclePlate,
        vehicleModel,
        vehicleColor,
        requestTime,
        status,
        gatehouseObservation,
      ];
}
