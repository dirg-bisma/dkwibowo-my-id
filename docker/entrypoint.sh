#!/bin/sh
set -eu

mkdir -p storage/cache/htmlpurifier storage/media/cover storage/media/inline storage/trash
chown -R www-data:www-data storage database content
php database/migrate.php
exec apache2-foreground
