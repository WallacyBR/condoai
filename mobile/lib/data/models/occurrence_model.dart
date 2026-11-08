/// Modelo completo de ocorrência com mídia e metadados do formulário.

class OccurrenceAttachment {
  final String path;
  final bool isVideo;

  const OccurrenceAttachment({required this.path, this.isVideo = false});

  Map<String, dynamic> toJson() => {'path': path, 'is_video': isVideo};

  factory OccurrenceAttachment.fromJson(Map<String, dynamic> json) =>
      OccurrenceAttachment(
        path: json['path'] as String,
        isVideo: json['is_video'] as bool? ?? false,
      );
}

class OccurrenceItem {
  final String protocol;
  final String title;
  final String category;
  final String date;
  final String status;
  final String description;
  final String location;
  final String urgency;
  final List<OccurrenceAttachment> attachments;

  const OccurrenceItem({
    required this.protocol,
    required this.title,
    required this.category,
    required this.date,
    this.status = 'Em Análise',
    this.description = '',
    this.location = '',
    this.urgency = 'Média',
    this.attachments = const [],
  });

  Map<String, dynamic> toJson() => {
        'protocol': protocol,
        'title': title,
        'category': category,
        'date': date,
        'status': status,
        'description': description,
        'location': location,
        'urgency': urgency,
        'attachments': attachments.map((a) => a.toJson()).toList(),
      };

  factory OccurrenceItem.fromJson(Map<String, dynamic> json) => OccurrenceItem(
        protocol: json['protocol'] as String,
        title: json['title'] as String? ?? '',
        category: json['category'] as String? ?? '',
        date: json['date'] as String? ?? '',
        status: json['status'] as String? ?? 'Em Análise',
        description: json['description'] as String? ?? '',
        location: json['location'] as String? ?? '',
        urgency: json['urgency'] as String? ?? 'Média',
        attachments: (json['attachments'] as List<dynamic>? ?? [])
            .map((e) => OccurrenceAttachment.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}
