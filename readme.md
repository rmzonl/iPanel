# iPanel

> Plesk / cPanel benzeri, açık kaynak Linux sunucu yönetim paneli.
> ZN Framework Custom Edition + Bootstrap 5 (Tabler) üzerine inşa edilmiştir.

## Repository layout

```
ipanel/
├── agent/                Root daemon (PHP CLI, Unix socket, JSON-RPC + HMAC)
│   ├── agent.php
│   ├── common/           Logger, Protocol, Command, BaseModule
│   └── modules/          NginxModule, PhpModule, DnsModule, MailModule,
│                         FtpModule, DatabaseModule, SslModule, FirewallModule,
│                         BackupModule, CronModule, SystemModule
│
├── web/                  Web UI (www-data) — ZN Framework CE
│   ├── iApp/
│   │   ├── Config/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Routes/
│   │   └── Libraries/
│   │       └── AgentClient.php   ← talks to the agent socket
│   ├── Views/             Wizard templates (Tabler/BS5)
│   ├── Packages/          Composer + ZN packages
│   └── zeroneed.php
│
├── bin/
│   └── ipanel             Admin CLI
│
├── install/
│   ├── install.sh         One-shot installer
│   ├── README.md          Installation guide
│   ├── schema.sql         Database schema
│   ├── systemd/           ipanel-agent.service, ipanel-cron.{service,timer}
│   ├── nginx/             panel.conf, site.conf.tpl
│   └── scripts/uninstall.sh
│
├── iPanel.md              Architecture manifesto
├── LICENSE
└── readme.md
```

## Architecture in one line

The Web UI runs unprivileged (`www-data`); root operations are dispatched to
the **iPanel Agent** over a Unix socket using HMAC-signed JSON-RPC.

```
Web UI (www-data)
    │   AgentClient::call('nginx.reload')
    ▼
Unix socket  /run/ipanel/agent.sock   (root:ipanel 660)
    │   Protocol verifies HMAC + rate-limits
    ▼
Agent daemon (root)  →  module.method()  →  shell exec
```

## Install

See [`install/README.md`](install/README.md). Short version:

```bash
curl -sSL https://raw.githubusercontent.com/rmzonl/ipanel/develop/install/install.sh | sudo bash
```

After install: `https://YOUR_IP:8443` — login `admin` / `password`, then run
`sudo ipanel passwd admin`.

## Manifesto

Full design document: [`iPanel.md`](iPanel.md).
