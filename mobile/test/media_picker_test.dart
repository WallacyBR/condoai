import 'package:flutter_test/flutter_test.dart';
import 'package:predihub_app/core/utils/media_picker_helper.dart';

void main() {
  group('MediaPickerHelper', () {
    test('cancelled é distinto de video — dismiss não deve acionar pickVideo', () {
      expect(MediaPickerChoice.cancelled, isNot(MediaPickerChoice.video));
      expect(MediaPickerChoice.gallery, isNot(MediaPickerChoice.cancelled));
      expect(MediaPickerChoice.camera, isNot(MediaPickerChoice.cancelled));
    });
  });
}
