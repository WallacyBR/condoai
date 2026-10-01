/// Modelos locais espelhando campos dos formulários web (ResidentForm, PetForm, VehicleForm).

class ResidentRegistry {
  final String id;
  final String name;
  final String email;
  final String rg;
  final String cpf;
  final String personType;
  final String cellPhone;
  final String whatsapp;
  final String address;
  final String notes;
  final String? photoPath;
  final String relationship;
  final bool hasFacialAccess;
  final bool canAuthorizeVisitors;
  final bool isOwner;

  const ResidentRegistry({
    required this.id,
    required this.name,
    this.email = '',
    this.rg = '',
    this.cpf = '',
    this.personType = 'fisica',
    this.cellPhone = '',
    this.whatsapp = '',
    this.address = '',
    this.notes = '',
    this.photoPath,
    this.relationship = 'Morador',
    this.hasFacialAccess = true,
    this.canAuthorizeVisitors = true,
    this.isOwner = false,
  });

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'rg': rg,
        'cpf': cpf,
        'person_type': personType,
        'cell_phone': cellPhone,
        'whatsapp': whatsapp,
        'address': address,
        'notes': notes,
        'photo_path': photoPath,
        'relationship': relationship,
        'has_facial_access': hasFacialAccess,
        'can_authorize_visitors': canAuthorizeVisitors,
        'is_owner': isOwner,
      };

  factory ResidentRegistry.fromJson(Map<String, dynamic> json) => ResidentRegistry(
        id: json['id'] as String,
        name: json['name'] as String? ?? '',
        email: json['email'] as String? ?? '',
        rg: json['rg'] as String? ?? '',
        cpf: json['cpf'] as String? ?? '',
        personType: json['person_type'] as String? ?? 'fisica',
        cellPhone: json['cell_phone'] as String? ?? '',
        whatsapp: json['whatsapp'] as String? ?? '',
        address: json['address'] as String? ?? '',
        notes: json['notes'] as String? ?? '',
        photoPath: json['photo_path'] as String?,
        relationship: json['relationship'] as String? ?? 'Morador',
        hasFacialAccess: json['has_facial_access'] as bool? ?? true,
        canAuthorizeVisitors: json['can_authorize_visitors'] as bool? ?? true,
        isOwner: json['is_owner'] as bool? ?? false,
      );

  ResidentRegistry copyWith({
    String? name,
    String? email,
    String? rg,
    String? cpf,
    String? personType,
    String? cellPhone,
    String? whatsapp,
    String? address,
    String? notes,
    String? photoPath,
    String? relationship,
    bool? hasFacialAccess,
    bool? canAuthorizeVisitors,
    bool? isOwner,
  }) =>
      ResidentRegistry(
        id: id,
        name: name ?? this.name,
        email: email ?? this.email,
        rg: rg ?? this.rg,
        cpf: cpf ?? this.cpf,
        personType: personType ?? this.personType,
        cellPhone: cellPhone ?? this.cellPhone,
        whatsapp: whatsapp ?? this.whatsapp,
        address: address ?? this.address,
        notes: notes ?? this.notes,
        photoPath: photoPath ?? this.photoPath,
        relationship: relationship ?? this.relationship,
        hasFacialAccess: hasFacialAccess ?? this.hasFacialAccess,
        canAuthorizeVisitors: canAuthorizeVisitors ?? this.canAuthorizeVisitors,
        isOwner: isOwner ?? this.isOwner,
      );
}

class PetRegistry {
  final String id;
  final String name;
  final String species;
  final String customSpecies;
  final String breed;
  final String gender;
  final String age;
  final String weight;
  final String healthInfo;
  final String notes;
  final String? photoPath;

  const PetRegistry({
    required this.id,
    required this.name,
    this.species = '',
    this.customSpecies = '',
    this.breed = '',
    this.gender = '',
    this.age = '',
    this.weight = '',
    this.healthInfo = '',
    this.notes = '',
    this.photoPath,
  });

  String get displaySpecies =>
      species == 'Outro' && customSpecies.isNotEmpty ? customSpecies : species;

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'species': species,
        'custom_species': customSpecies,
        'breed': breed,
        'gender': gender,
        'age': age,
        'weight': weight,
        'health_info': healthInfo,
        'notes': notes,
        'photo_path': photoPath,
      };

  factory PetRegistry.fromJson(Map<String, dynamic> json) => PetRegistry(
        id: json['id'] as String,
        name: json['name'] as String? ?? '',
        species: json['species'] as String? ?? '',
        customSpecies: json['custom_species'] as String? ?? '',
        breed: json['breed'] as String? ?? '',
        gender: json['gender'] as String? ?? '',
        age: json['age'] as String? ?? '',
        weight: json['weight'] as String? ?? '',
        healthInfo: json['health_info'] as String? ?? '',
        notes: json['notes'] as String? ?? '',
        photoPath: json['photo_path'] as String?,
      );
}

class VehicleRegistry {
  final String id;
  final String licensePlate;
  final String model;
  final String color;
  final String year;
  final String parkingSpot;
  final String notes;

  const VehicleRegistry({
    required this.id,
    required this.licensePlate,
    required this.model,
    this.color = '',
    this.year = '',
    this.parkingSpot = '',
    this.notes = '',
  });

  Map<String, dynamic> toJson() => {
        'id': id,
        'license_plate': licensePlate,
        'model': model,
        'color': color,
        'year': year,
        'parking_spot': parkingSpot,
        'notes': notes,
      };

  factory VehicleRegistry.fromJson(Map<String, dynamic> json) => VehicleRegistry(
        id: json['id'] as String,
        licensePlate: json['license_plate'] as String? ?? '',
        model: json['model'] as String? ?? '',
        color: json['color'] as String? ?? '',
        year: json['year'] as String? ?? '',
        parkingSpot: json['parking_spot'] as String? ?? '',
        notes: json['notes'] as String? ?? '',
      );
}
