#!/bin/bash
# Ajusta permissões para as pastas utilizadas pelo Laravel
# Uso: ./scripts/set-permissions.sh [usuario] [grupo]
# O usuário padrão e o grupo padrão são www-data

set -e

TARGET_USER=${1:-www-data}
TARGET_GROUP=${2:-$TARGET_USER}

echo "Aplicando permissões para ${TARGET_USER}:${TARGET_GROUP}..."

sudo chown -R ${TARGET_USER}:${TARGET_GROUP} storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "Permissões aplicadas."
