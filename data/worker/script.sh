#!/bin/sh

COOKIE=/tmp/cookies.txt

curl -s -c "$COOKIE" \
  -X POST \
  -d "username=admin&password=admin123" \
  http://lampboard-tt/admin/login.php

curl -s -b "$COOKIE" \
  http://lampboard-tt/send_monthly_report.php?lang=de

curl -s -b "$COOKIE" \
  http://lampboard-tt/admin/logout.php

rm -f "$COOKIE"
