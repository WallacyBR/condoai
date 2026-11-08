import { api } from '../utils/api.js';

export const imageUploadService = {
  /**
   * Faz upload de uma imagem em base64 para o servidor
   * @param {string} base64Image - Imagem em formato base64
   * @param {string} folder - Pasta onde salvar (residents, apartments, common_areas, pets)
   * @returns {Promise<Object>} - Retorna URL da imagem salva
   */
  async uploadImage(base64Image, folder = 'residents') {
    try {
      console.log('Iniciando upload da imagem...');
      console.log('Folder:', folder);
      console.log('Tamanho da imagem:', base64Image.length, 'caracteres');
      
      // TESTE TEMPORÁRIO: Testar rota sem autenticação
      console.log('Testando rota sem autenticação...');
      try {
        const testResponse = await api.post('/test-upload', {
          test: 'dados de teste',
          folder: folder
        });
        console.log('Teste sem autenticação funcionou:', testResponse.data);
      } catch (testError) {
        console.error('Teste sem autenticação falhou:', testError);
      }
      
      const requestData = {
        image: base64Image,
        folder: folder
      };

      console.log('Enviando requisição para /api/images/upload...');
      console.log('Token de autenticação:', localStorage.getItem('token') ? 'Presente' : 'Ausente');
      
      const response = await api.post('/images/upload', requestData);
      
      console.log('Resposta recebida:', response);
      console.log('Status da resposta:', response.status);
      console.log('Dados da resposta:', response.data);

      // Verificar se a resposta tem a estrutura esperada
      if (response.data && response.data.success) {
        console.log('Upload bem-sucedido:', response.data);
        return {
          success: true,
          url: response.data.url,
          filename: response.data.filename
        };
      } else {
        console.error('Resposta não tem sucesso:', response.data);
        const errorMessage = response.data?.error || response.data?.message || 'Erro desconhecido no upload';
        throw new Error(errorMessage);
      }
    } catch (error) {
      console.error('Erro detalhado no upload:', error);
      console.error('Tipo do erro:', error.constructor.name);
      console.error('Mensagem do erro:', error.message);
      
      if (error.response) {
        console.error('Resposta de erro do servidor:', error.response);
        console.error('Status do erro:', error.response.status);
        console.error('Dados do erro:', error.response.data);
        
        const serverError = error.response.data?.error || error.response.data?.message || 'Erro do servidor';
        throw new Error(`Erro do servidor (${error.response.status}): ${serverError}`);
      } else if (error.request) {
        console.error('Erro de rede:', error.request);
        throw new Error('Erro de conexão com o servidor. Verifique sua conexão com a internet.');
      } else {
        console.error('Erro de configuração:', error.message);
        throw new Error(`Erro de configuração: ${error.message}`);
      }
    }
  },

  /**
   * Deleta uma imagem do servidor
   * @param {string} imageUrl - URL da imagem a ser deletada
   * @returns {Promise<Object>} - Resultado da operação
   */
  async deleteImage(imageUrl) {
    try {
      console.log('Deletando imagem:', imageUrl);
      
      const response = await api.delete('/images/delete', {
        data: { url: imageUrl }
      });

      console.log('Resposta da deleção:', response);

      return {
        success: true,
        message: response.data?.message || 'Imagem deletada com sucesso'
      };
    } catch (error) {
      console.error('Erro ao deletar imagem:', error);
      
      if (error.response) {
        const serverError = error.response.data?.error || error.response.data?.message || 'Erro do servidor';
        throw new Error(`Erro do servidor (${error.response.status}): ${serverError}`);
      } else {
        throw new Error(error.message || 'Erro ao deletar imagem');
      }
    }
  },

  /**
   * Converte um arquivo para base64
   * @param {File} file - Arquivo a ser convertido
   * @returns {Promise<string>} - Arquivo em base64
   */
  fileToBase64(file) {
    return new Promise((resolve, reject) => {
      console.log('Convertendo arquivo para base64:', file.name, file.size, 'bytes');
      
      const reader = new FileReader();
      reader.readAsDataURL(file);
      
      reader.onload = () => {
        console.log('Arquivo convertido com sucesso, tamanho:', reader.result.length, 'caracteres');
        resolve(reader.result);
      };
      
      reader.onerror = error => {
        console.error('Erro ao converter arquivo:', error);
        reject(error);
      };
    });
  },

  /**
   * Redimensiona uma imagem para um tamanho máximo
   * @param {string} base64Image - Imagem em base64
   * @param {number} maxWidth - Largura máxima
   * @param {number} maxHeight - Altura máxima
   * @param {number} quality - Qualidade da imagem (0-1)
   * @returns {Promise<string>} - Imagem redimensionada em base64
   */
  resizeImage(base64Image, maxWidth = 800, maxHeight = 600, quality = 0.8) {
    return new Promise((resolve) => {
      console.log('Redimensionando imagem...');
      console.log('Dimensões máximas:', maxWidth, 'x', maxHeight);
      console.log('Qualidade:', quality);
      
      const img = new Image();
      
      img.onload = () => {
        console.log('Imagem carregada, dimensões originais:', img.width, 'x', img.height);
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        // Calcular novas dimensões mantendo proporção
        let { width, height } = img;
        
        if (width > height) {
          if (width > maxWidth) {
            height = (height * maxWidth) / width;
            width = maxWidth;
          }
        } else {
          if (height > maxHeight) {
            width = (width * maxHeight) / height;
            height = maxHeight;
          }
        }

        canvas.width = width;
        canvas.height = height;

        console.log('Novas dimensões:', width, 'x', height);

        // Desenhar imagem redimensionada
        ctx.drawImage(img, 0, 0, width, height);

        // Converter para base64 com qualidade especificada
        const resizedBase64 = canvas.toDataURL('image/jpeg', quality);
        console.log('Imagem redimensionada, tamanho:', resizedBase64.length, 'caracteres');
        
        resolve(resizedBase64);
      };

      img.onerror = (error) => {
        console.error('Erro ao carregar imagem para redimensionamento:', error);
        // Se falhar, retorna a imagem original
        console.log('Retornando imagem original devido ao erro');
        resolve(base64Image);
      };

      img.src = base64Image;
    });
  },

  /**
   * Valida se um arquivo é uma imagem válida
   * @param {File} file - Arquivo a ser validado
   * @param {number} maxSizeMB - Tamanho máximo em MB
   * @returns {Object} - Resultado da validação
   */
  validateImageFile(file, maxSizeMB = 5) {
    console.log('Validando arquivo:', file.name, file.type, file.size, 'bytes');
    
    const maxSize = maxSizeMB * 1024 * 1024; // Converter para bytes
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
      console.error('Tipo de arquivo não suportado:', file.type);
      return {
        valid: false,
        error: 'Tipo de arquivo não suportado. Use apenas JPG, PNG, GIF ou WebP.'
      };
    }

    if (file.size > maxSize) {
      console.error('Arquivo muito grande:', file.size, 'bytes (máximo:', maxSize, 'bytes)');
      return {
        valid: false,
        error: `Arquivo muito grande. Máximo ${maxSizeMB}MB.`
      };
    }

    console.log('Arquivo válido');
    return { valid: true };
  }
}; 