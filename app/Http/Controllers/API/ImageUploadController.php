<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|string', // Base64 da imagem
            'folder' => 'nullable|string|in:residents,apartments,common_areas,pets', // Pasta onde salvar
        ]);

        try {
            // Decodificar base64
            $imageData = $request->input('image');
            
            // Verificar se é uma imagem base64 válida
            if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                return response()->json(['error' => 'Formato de imagem inválido'], 400);
            }

            $imageType = $matches[1]; // jpeg, png, etc.
            $base64Data = substr($imageData, strpos($imageData, ',') + 1);
            $decodedData = base64_decode($base64Data);

            if ($decodedData === false) {
                return response()->json(['error' => 'Erro ao decodificar imagem'], 400);
            }

            // Verificar tamanho (máximo 5MB)
            if (strlen($decodedData) > 5 * 1024 * 1024) {
                return response()->json(['error' => 'Imagem muito grande. Máximo 5MB.'], 400);
            }

            // Gerar nome único para o arquivo
            $folder = $request->input('folder', 'residents');
            $filename = Str::uuid() . '.' . $imageType;
            $path = "public/images/{$folder}/{$filename}";

            // Salvar arquivo
            Storage::put($path, $decodedData);

            // Gerar URL pública
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'url' => $url,
                'filename' => $filename,
                'path' => $path
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao fazer upload da imagem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        try {
            $url = $request->input('url');
            
            // Extrair caminho do arquivo da URL
            $path = str_replace('/storage/', 'public/', $url);
            
            // Verificar se o arquivo existe
            if (Storage::exists($path)) {
                Storage::delete($path);
                return response()->json(['success' => true, 'message' => 'Imagem deletada com sucesso']);
            } else {
                return response()->json(['error' => 'Arquivo não encontrado'], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao deletar imagem: ' . $e->getMessage()
            ], 500);
        }
    }
} 