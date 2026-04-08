#!/usr/bin/env bash
# =============================================================================
# RapidBistro — Worker & Scheduler runner
# =============================================================================
# Uso:
#   ./start-workers.sh             → inicia queue worker + scheduler en background
#   ./start-workers.sh worker      → solo el queue worker
#   ./start-workers.sh scheduler   → solo el scheduler
#   ./start-workers.sh stop        → detiene todos los procesos de este script
#   ./start-workers.sh status      → muestra si están corriendo
# =============================================================================

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP="${PHP_BIN:-php}"
ARTISAN="${APP_DIR}/artisan"
PID_DIR="${APP_DIR}/storage/app/pids"
WORKER_PID="${PID_DIR}/queue-worker.pid"
SCHEDULER_PID="${PID_DIR}/scheduler.pid"
LOG_DIR="${APP_DIR}/storage/logs"

# ── Colores ──────────────────────────────────────────────────────────────────
GREEN="\033[0;32m"
YELLOW="\033[1;33m"
RED="\033[0;31m"
CYAN="\033[0;36m"
RESET="\033[0m"

mkdir -p "${PID_DIR}"

# ── Funciones ────────────────────────────────────────────────────────────────

is_running() {
    local pidfile="$1"
    [ -f "$pidfile" ] && kill -0 "$(cat "$pidfile")" 2>/dev/null
}

start_worker() {
    if is_running "${WORKER_PID}"; then
        echo -e "${YELLOW}[worker]${RESET} Ya está corriendo (PID $(cat "${WORKER_PID}"))"
        return
    fi

    echo -e "${CYAN}[worker]${RESET} Iniciando queue:work --queue=default,mails..."
    nohup "${PHP}" "${ARTISAN}" queue:work \
        --queue=default,mails \
        --sleep=3 \
        --tries=3 \
        --max-time=3600 \
        >> "${LOG_DIR}/queue-worker.log" 2>&1 &

    echo $! > "${WORKER_PID}"
    echo -e "${GREEN}[worker]${RESET} Iniciado — PID $! | Log: storage/logs/queue-worker.log"
}

start_scheduler() {
    if is_running "${SCHEDULER_PID}"; then
        echo -e "${YELLOW}[scheduler]${RESET} Ya está corriendo (PID $(cat "${SCHEDULER_PID}"))"
        return
    fi

    echo -e "${CYAN}[scheduler]${RESET} Iniciando schedule:work (loop cada 60s)..."
    nohup "${PHP}" "${ARTISAN}" schedule:work \
        >> "${LOG_DIR}/scheduler.log" 2>&1 &

    echo $! > "${SCHEDULER_PID}"
    echo -e "${GREEN}[scheduler]${RESET} Iniciado — PID $! | Log: storage/logs/scheduler.log"
}

stop_all() {
    for entry in worker:${WORKER_PID} scheduler:${SCHEDULER_PID}; do
        name="${entry%%:*}"
        pidfile="${entry##*:}"
        if is_running "${pidfile}"; then
            kill "$(cat "${pidfile}")"
            rm -f "${pidfile}"
            echo -e "${RED}[${name}]${RESET} Detenido."
        else
            echo -e "${YELLOW}[${name}]${RESET} No estaba corriendo."
        fi
    done
}

show_status() {
    echo ""
    echo -e "  ${CYAN}Queue Worker${RESET}  →  $(is_running "${WORKER_PID}" && echo -e "${GREEN}Corriendo${RESET} (PID $(cat "${WORKER_PID}"))" || echo -e "${RED}Detenido${RESET}")"
    echo -e "  ${CYAN}Scheduler${RESET}     →  $(is_running "${SCHEDULER_PID}" && echo -e "${GREEN}Corriendo${RESET} (PID $(cat "${SCHEDULER_PID}"))" || echo -e "${RED}Detenido${RESET}")"
    echo ""
}

# ── Comando ──────────────────────────────────────────────────────────────────

CMD="${1:-all}"

echo ""
echo -e "  ${CYAN}RapidBistro Workers${RESET}"
echo -e "  Dir: ${APP_DIR}"
echo ""

case "$CMD" in
    worker)
        start_worker
        ;;
    scheduler)
        start_scheduler
        ;;
    stop)
        stop_all
        ;;
    status)
        show_status
        ;;
    all|"")
        start_worker
        start_scheduler
        echo ""
        show_status
        ;;
    *)
        echo "Uso: $0 [worker|scheduler|stop|status]"
        exit 1
        ;;
esac

echo ""
