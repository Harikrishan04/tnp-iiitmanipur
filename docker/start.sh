#!/bin/bash
set -e

echo "Cleaning up Apache MPM modules via official a2dismod..."
# Disable all possible MPMs first
a2dismod mpm_event mpm_worker mpm_prefork || true

# Explicitly enable prefork (required for mod_php)
a2enmod mpm_prefork

echo "Starting Apache..."
# Execute the main process command
exec "$@"
