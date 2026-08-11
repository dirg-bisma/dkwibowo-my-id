#!/bin/sh
set -eu

php database/migrate.php
exec apache2-foreground
