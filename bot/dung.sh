#!/bin/sh
# ============================================================
#  DUNG BOT
#
#  Gui SIGTERM chu khong SIGKILL: bot co xu ly SIGTERM de ghi mot
#  dong vao bot.log truoc khi thoat. Nho dong do, sau vai ngay moi
#  phan biet duoc "bi tat co chu dich" voi "chet bat thuong".
#
#  Dung PID that trong bot.pid chu khong `pgrep -f bot.js`: pgrep so
#  voi toan bo dong lenh nen co the giet nham mot tien trinh dang MO
#  file bot.js (vi/nano), hoac bao "da dung" cho thu khong phai bot.
# ============================================================
cd "$(dirname "$0")" || exit 1
PIDFILE=bot.pid

la_bot() {   # $1 = PID; dung 0 neu that su la tien trinh bot cua minh
  [ -n "$1" ] || return 1
  kill -0 "$1" 2>/dev/null || return 1
  tr '\0' ' ' < "/proc/$1/cmdline" 2>/dev/null | grep -q "bot\.js"
}

PID=""
[ -f "$PIDFILE" ] && PID=$(cat "$PIDFILE" 2>/dev/null)

if ! la_bot "$PID"; then
  # Du phong cho truong hop mat bot.pid (vd bot bat bang tay khong qua chay.sh).
  for p in $(pgrep -f "[b]ot\.js" 2>/dev/null); do
    if la_bot "$p"; then PID="$p"; break; fi
  done
fi

if ! la_bot "$PID"; then
  echo "Bot khong chay."
  rm -f "$PIDFILE"
  exit 0
fi

echo "Dung PID $PID"
kill "$PID" 2>/dev/null
sleep 2

if la_bot "$PID"; then
  echo "Chua chiu tat, buoc dung."
  kill -9 "$PID" 2>/dev/null
  sleep 1
fi

rm -f "$PIDFILE"
echo "Da dung."
