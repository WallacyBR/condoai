import fs from 'fs';
import path from 'path';

const jsRoot = path.resolve(process.cwd(), 'resources/js');

/**
 * Lê o conteúdo-fonte de um componente Vue para specs legados baseados em arquivo.
 *
 * @param {string} relativePath Caminho relativo a resources/js (ex.: components/LoginView.vue)
 * @returns {string}
 */
export function readLegacyComponentSource(relativePath) {
  return fs.readFileSync(path.resolve(jsRoot, relativePath), 'utf8');
}
