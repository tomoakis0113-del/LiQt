#!/bin/bash
while true; do
  /usr/local/bin/php /var/www/html/api/group/auto_reply.php >> /var/log/apache2/error.log 2>&1 &
  echo "Cron job executed at $(date)" >> /var/log/apache2/access.log &
  sleep 10
done