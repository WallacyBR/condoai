import 'package:flutter_test/flutter_test.dart';
import 'package:condoai_app/data/models/occurrence_model.dart';

void main() {
  test('OccurrenceItem serializa anexos e metadados completos', () {
    const item = OccurrenceItem(
      protocol: 'OC-123',
      title: 'Teste',
      category: 'Manutenção',
      date: 'Hoje',
      description: 'Descrição detalhada',
      location: 'Hall',
      urgency: 'Alta / Urgente',
      attachments: [
        OccurrenceAttachment(path: '/tmp/foto.jpg'),
        OccurrenceAttachment(path: '/tmp/video.mp4', isVideo: true),
      ],
    );

    final restored = OccurrenceItem.fromJson(item.toJson());

    expect(restored.description, 'Descrição detalhada');
    expect(restored.location, 'Hall');
    expect(restored.urgency, 'Alta / Urgente');
    expect(restored.attachments, hasLength(2));
    expect(restored.attachments.last.isVideo, isTrue);
  });
}
