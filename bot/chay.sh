#!/bin/sh
# ============================================================
#  BAT BOT — chay nen 24/24, khong can trinh duyet nao mo.
#
#  Da kiem chung tren hosting nay (2026-08-10): tien trinh nen
#  song qua viec dong Terminal va tat trinh duyet.
#
#  Dung: ./dung.sh          Xem log: tail -f bot.log
# ============================================================
cd "$(dirname "$0")" || exit 1

# ---- KHOA THUC SU (flock) ----------------------------------
# Chi kiem bang pgrep roi moi khoi dong la co DUA TRANH: giua luc kiem va luc
# `nohup ... &` khong co gi chan, nen hai lan goi gan nhau (cron */5 trung voi
# lan chay tay) deu vuot qua roi CUNG khoi dong. Hai bot cung ghi la sinh keo
# TRUNG cho cung mot coin tu cung mot trang thai so lenh -> hai quan sat KHONG
# doc lap, pha dung gia dinh ma moi phep do ty le thang dua vao.
# flock giu khoa suot ca doan kiem + khoi dong nen xoa han dua tranh.
if command -v flock > /dev/null 2>&1; then
  exec 9> .lock
  flock -n 9 || { echo "Dang co lan chay khac (flock). Thoat."; exit 1; }
fi

PIDFILE=bot.pid

# ---- Bot da chay chua? Xac minh bang PID THAT --------------
# Khong dung `pgrep -f bot.js`: no so voi TOAN BO dong lenh nen bat ca
# `vi bot.js`, `nano bot.js`, hay vo boc `sh -c "... node bot.js"` cua cron ->
# script bao "dang chay roi" trong khi bot that da chet tu lau. Do la hong IM
# LANG, va te nhat la cron */5 lan nao cung bao thanh cong.
if [ -f "$PIDFILE" ]; then
  OLD=$(cat "$PIDFILE" 2>/dev/null)
  if [ -n "$OLD" ] && kill -0 "$OLD" 2>/dev/null \
     && tr '\0' ' ' < "/proc/$OLD/cmdline" 2>/dev/null | grep -q "bot\.js"; then
    echo "Bot DANG chay (PID $OLD). Muon chay lai thi ./dung.sh truoc."
    exit 1
  fi
  echo "(bot.pid cu tro vao PID $OLD khong con la bot — bo qua)"
  rm -f "$PIDFILE"
fi

NODE="$HOME/node-v20.20.0-linux-x64/bin/node"
[ -x "$NODE" ] || NODE="$(command -v node)"
if [ -z "$NODE" ]; then
  echo "KHONG tim thay node."
  echo "Tai ve: cd ~ && curl -sLO https://nodejs.org/dist/v20.20.0/node-v20.20.0-linux-x64.tar.gz && tar -xzf node-v20.20.0-linux-x64.tar.gz"
  exit 1
fi

# --experimental-websocket: Node 20 chua bat WebSocket toan cuc san.
nohup "$NODE" --experimental-websocket bot.js >> bot.log 2>&1 &
NEW=$!
echo "$NEW" > "$PIDFILE"
sleep 2

if kill -0 "$NEW" 2>/dev/null; then
  echo "Da bat bot. PID $NEW"
  echo "--- 15 dong log cuoi ---"
  tail -15 bot.log
else
  echo "!! Bot khong len duoc. Doc loi:"
  tail -30 bot.log
  rm -f "$PIDFILE"
  exit 1
fi
