/**
 * iPanel — Dashboard Canlı İstatistikler
 * SSE üzerinden gelen sistem verilerini Chart.js ile gösterir.
 *
 * Kullanım: dashboard view'ında <script src="/assets/js/stats.js"></script>
 * Gerekli: Chart.js (CDN üzerinden)
 */
(function () {
  'use strict';

  /* ---- Yapılandırma ---- */
  const SSE_URL      = '/stats/stream?interval=3';
  const HISTORY_LEN  = 60;   // grafiklerde tutulacak nokta sayısı

  /* ---- Zaman damgası yardımcısı ---- */
  function now() { return new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit', second: '2-digit' }); }

  /* ---- Veri tarihleri ---- */
  const history = {
    labels: [],
    cpu:    [],
    ram:    [],
    rxRate: [],
    txRate: [],
  };

  function push(arr, val) {
    arr.push(val);
    if (arr.length > HISTORY_LEN) arr.shift();
  }

  /* ---- Chart.js grafik fabrikası ---- */
  function makeChart(canvasId, label, color) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels:   history.labels,
        datasets: [{
          label,
          data:            [],
          borderColor:     color,
          backgroundColor: color + '1a',
          borderWidth:     2,
          fill:            true,
          tension:         0.4,
          pointRadius:     0,
        }],
      },
      options: {
        responsive:          true,
        maintainAspectRatio: false,
        animation:           false,
        plugins: { legend: { display: false } },
        scales: {
          x: { display: false },
          y: { min: 0, max: 100, ticks: { font: { size: 10 } }, grid: { color: '#f1f3f5' } },
        },
      },
    });
  }

  /* ---- Gösterge güncelleyiciler ---- */
  function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  function setBar(id, pct) {
    const el = document.getElementById(id);
    if (el) {
      el.style.width    = Math.max(0, Math.min(100, pct)) + '%';
      el.setAttribute('aria-valuenow', pct);
      el.className = 'progress-bar ' + (pct > 90 ? 'bg-danger' : pct > 70 ? 'bg-warning' : 'bg-primary');
    }
  }

  function fmtBytes(kb) {
    if (kb > 1048576) return (kb / 1048576).toFixed(1) + ' GB';
    if (kb > 1024)    return (kb / 1024).toFixed(1) + ' MB';
    return kb + ' KB';
  }

  function fmtUptime(sec) {
    const d = Math.floor(sec / 86400);
    const h = Math.floor((sec % 86400) / 3600);
    const m = Math.floor((sec % 3600) / 60);
    return (d > 0 ? d + 'g ' : '') + h + 's ' + m + 'd';
  }

  function fmtRate(bytes) {
    if (bytes > 1048576) return (bytes / 1048576).toFixed(1) + ' MB/s';
    if (bytes > 1024)    return (bytes / 1024).toFixed(1) + ' KB/s';
    return bytes + ' B/s';
  }

  /* ---- Servis durum kartları ---- */
  function renderServices(services) {
    const el = document.getElementById('service-status-grid');
    if (!el || !services) return;
    el.innerHTML = Object.entries(services).map(([name, active]) => `
      <div class="col-4 col-md-3 col-lg-2 mb-2">
        <div class="d-flex align-items-center gap-1">
          <span class="badge ${active ? 'bg-success' : 'bg-danger'}" style="width:8px;height:8px;border-radius:50%;padding:0"></span>
          <small class="text-${active ? 'success' : 'danger'} fw-medium">${name}</small>
        </div>
      </div>
    `).join('');
  }

  /* ---- Disk kartları ---- */
  function renderDisks(disks) {
    const el = document.getElementById('disk-info');
    if (!el || !disks) return;
    el.innerHTML = disks.map(d => `
      <div class="mb-2">
        <div class="d-flex justify-content-between mb-1">
          <small class="fw-medium">${d.mount}</small>
          <small class="text-muted">${fmtBytes(d.used_kb)} / ${fmtBytes(d.total_kb)}</small>
        </div>
        <div class="progress" style="height:6px">
          <div class="progress-bar ${d.usage_pct > 90 ? 'bg-danger' : d.usage_pct > 70 ? 'bg-warning' : 'bg-primary'}"
               style="width:${d.usage_pct}%"></div>
        </div>
      </div>
    `).join('');
  }

  /* ---- Ağ arayüzleri ---- */
  function renderNetwork(ifaces) {
    const el = document.getElementById('network-info');
    if (!el || !ifaces) return;
    el.innerHTML = ifaces.map(i => `
      <tr>
        <td><small>${i.iface}</small></td>
        <td><small>${fmtRate(i.rx_rate)}</small></td>
        <td><small>${fmtRate(i.tx_rate)}</small></td>
        <td><small>${fmtBytes(Math.floor(i.rx_b/1024))} / ${fmtBytes(Math.floor(i.tx_b/1024))}</small></td>
      </tr>
    `).join('');
  }

  /* ---- Grafik nesneleri ---- */
  let cpuChart, ramChart, rxChart, txChart;

  function initCharts() {
    cpuChart = makeChart('cpu-chart', 'CPU %',   '#206bc4');
    ramChart = makeChart('ram-chart', 'RAM %',   '#2fb344');
    rxChart  = makeChart('rx-chart',  'RX KB/s', '#f59f00');
    txChart  = makeChart('tx-chart',  'TX KB/s', '#d63939');
  }

  function updateChart(chart, val) {
    if (!chart) return;
    chart.data.labels = history.labels;
    chart.data.datasets[0].data.push(val);
    if (chart.data.datasets[0].data.length > HISTORY_LEN) {
      chart.data.datasets[0].data.shift();
    }
    chart.update('none');
  }

  /* ---- SSE bağlantısı ---- */
  function connect() {
    const es = new EventSource(SSE_URL);

    es.addEventListener('stats', e => {
      try {
        const d   = JSON.parse(e.data);
        const ts  = now();

        // Zaman ekseni
        push(history.labels, ts);

        // CPU
        const cpuArr = d.cpu || [];
        const totalCpu = cpuArr.find(c => c.core === 'cpu');
        const cpuPct   = totalCpu ? totalCpu.usage : 0;
        push(history.cpu, cpuPct);
        setText('cpu-pct', cpuPct.toFixed(1) + '%');
        setBar('cpu-bar', cpuPct);
        updateChart(cpuChart, cpuPct);

        // Load average
        if (d.load) {
          setText('load-avg', `${d.load['1m']} / ${d.load['5m']} / ${d.load['15m']}`);
        }

        // RAM
        if (d.memory) {
          const m = d.memory;
          setText('ram-used',  fmtBytes(m.used_kb));
          setText('ram-total', fmtBytes(m.total_kb));
          setText('ram-pct',   m.usage_pct.toFixed(1) + '%');
          setBar('ram-bar', m.usage_pct);
          push(history.ram, m.usage_pct);
          updateChart(ramChart, m.usage_pct);

          if (m.swap_total_kb > 0) {
            const swapPct = Math.round(m.swap_used_kb / m.swap_total_kb * 100);
            setText('swap-used',  fmtBytes(m.swap_used_kb));
            setText('swap-total', fmtBytes(m.swap_total_kb));
            setBar('swap-bar', swapPct);
          }
        }

        // Uptime
        if (d.uptime !== undefined) {
          setText('uptime', fmtUptime(d.uptime));
        }

        // Disk
        renderDisks(d.disks);

        // Ağ
        const net = d.network || [];
        const totalRx = net.reduce((s, i) => s + i.rx_rate, 0);
        const totalTx = net.reduce((s, i) => s + i.tx_rate, 0);
        push(history.rxRate, Math.floor(totalRx / 1024));
        push(history.txRate, Math.floor(totalTx / 1024));
        updateChart(rxChart, Math.floor(totalRx / 1024));
        updateChart(txChart, Math.floor(totalTx / 1024));
        renderNetwork(net);

        // Servisler
        renderServices(d.services);

        // Son güncelleme zamanı
        setText('stats-last-update', ts);

      } catch (err) {
        console.error('[Stats SSE parse]', err);
      }
    });

    es.addEventListener('error', () => {
      console.warn('[Stats SSE] bağlantı koptu, yeniden bağlanılıyor…');
      es.close();
      setTimeout(connect, 5000);
    });

    es.addEventListener('end', () => {
      es.close();
      setTimeout(connect, 1000);
    });
  }

  /* ---- Başlat ---- */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => { initCharts(); connect(); });
  } else {
    initCharts();
    connect();
  }

})();
