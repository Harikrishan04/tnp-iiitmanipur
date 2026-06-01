#!/bin/bash
set -e

echo "Cleaning up Apache MPM modules..."
# Aggressively remove any event or worker MPM configuration
rm -f /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load

# Ensure prefork is enabled
a2enmod mpm_prefork

# Verify what's enabled
echo "Enabled Apache modules:"
ls -l /etc/apache2/mods-enabled/ | grep mpm

echo "Starting Apache..."
exec apache2-foreground
