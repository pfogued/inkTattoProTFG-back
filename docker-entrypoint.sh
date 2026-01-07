#!/bin/bash
set -e

a2dismod mpm_event 2>/dev/null || true

a2dismod mpm_worker 2>/dev/null || true

a2dismod mpm_prefork 2>/dev/null || true

# Enable only mpm_prefork

a2enmod mpm_prefork

# Start Apache in foreground

exec apache2-foreground