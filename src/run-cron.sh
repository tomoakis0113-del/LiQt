#!/bin/bash
while true; do
  echo "Cron job executed at $(date)" >> /var/log/apache2/access.log &
  /usr/local/bin/php /var/www/html/api/auto_reply.php >> /var/log/apache2/access.log 2>&1 &
  sleep 10
done