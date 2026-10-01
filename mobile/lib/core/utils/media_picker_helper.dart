import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

/// Anexo de mídia (foto local ou vídeo) selecionado pelo morador.
class MediaAttachment {
  final String path;
  final bool isVideo;

  const MediaAttachment({required this.path, this.isVideo = false});
}

/// Utilitário reutilizável para seleção de fotos e vídeos via image_picker.
class MediaPickerHelper {
  static final ImagePicker _picker = ImagePicker();

  static Future<MediaAttachment?> pickPhoto({ImageSource source = ImageSource.gallery}) async {
    final file = await _picker.pickImage(source: source, maxWidth: 1920, imageQuality: 85);
    if (file == null) return null;
    return MediaAttachment(path: file.path);
  }

  static Future<MediaAttachment?> pickVideo() async {
    final file = await _picker.pickVideo(source: ImageSource.gallery, maxDuration: const Duration(seconds: 30));
    if (file == null) return null;
    return MediaAttachment(path: file.path, isVideo: true);
  }

  static Future<void> showPhotoVideoPickerSheet(
    BuildContext context, {
    required void Function(MediaAttachment attachment) onSelected,
    int? maxItems,
    int currentCount = 0,
  }) async {
    if (maxItems != null && currentCount >= maxItems) return;

    final source = await showModalBottomSheet<ImageSource?>(
      context: context,
      backgroundColor: Colors.transparent,
      useSafeArea: true,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;
        return Container(
          margin: const EdgeInsets.fromLTRB(16, 0, 16, 24),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF1E293B) : Colors.white,
            borderRadius: BorderRadius.circular(20),
          ),
          child: SafeArea(
            top: false,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                ListTile(
                  leading: const Icon(Icons.photo_library_outlined),
                  title: const Text('Galeria de fotos'),
                  onTap: () => Navigator.pop(ctx, ImageSource.gallery),
                ),
                ListTile(
                  leading: const Icon(Icons.photo_camera_outlined),
                  title: const Text('Tirar foto'),
                  onTap: () => Navigator.pop(ctx, ImageSource.camera),
                ),
                ListTile(
                  leading: const Icon(Icons.videocam_outlined),
                  title: const Text('Anexar vídeo'),
                  onTap: () => Navigator.pop(ctx, null),
                ),
              ],
            ),
          ),
        );
      },
    );

    if (!context.mounted) return;

    MediaAttachment? attachment;
    if (source == null) {
      attachment = await pickVideo();
    } else {
      attachment = await pickPhoto(source: source);
    }

    if (attachment != null) {
      onSelected(attachment);
    }
  }
}
