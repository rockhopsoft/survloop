#!/bin/bash
set -x

# To ensure these are not run twice...
rm -f /tmp/survloop/production/01-ubuntu-create-user.sh
rm -f /tmp/survloop/production/02-laravel-ubuntu20.sh
rm -f /tmp/survloop/production/03-package-ubuntu20.sh
rm -f /tmp/survloop/production/03-survloop-ubuntu20.sh
rm -f /tmp/survloop/production/nginx-example.com

# To clean up...
rm -f /tmp/survloop/production/deploy-update-from-staging.sh
rm -f /tmp/survloop/production/maintenance-mode.sh
rm -f /tmp/survloop/production/maintenance-index.php
rm -f /tmp/survloop/production/jitsi-install-ubuntu20.sh
