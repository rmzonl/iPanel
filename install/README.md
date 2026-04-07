# iPanel — Installation

## Quick install (production)

```bash
curl -sSL https://raw.githubusercontent.com/rmzonl/ipanel/develop/install/install.sh | sudo bash
```

## Manual install

```bash
sudo git clone https://github.com/rmzonl/ipanel.git /usr/local/ipanel
cd /usr/local/ipanel
sudo bash install/install.sh
```

## Layout after install

```
/usr/local/ipanel/         (sources, owned by root, web/ chowned to www-data)
├── agent/                 root-privileged daemon
├── web/                   ZN Framework Web UI
├── bin/ipanel             admin CLI (symlinked to /usr/local/bin/ipanel)
└── install/               this folder

/etc/ipanel/
├── agent.conf.php         shared HMAC secret (root:ipanel 640)
├── db.env                 generated DB credentials (root 600)
└── ssl/                   panel TLS cert + key

/run/ipanel/agent.sock     Unix socket (root:ipanel 660)
/var/log/ipanel/           agent + panel logs
/var/backups/ipanel/       panel-level backups
```

## Services

| Unit                       | Purpose                                   |
|----------------------------|-------------------------------------------|
| `ipanel-agent.service`     | Root daemon listening on the Unix socket  |
| `ipanel-cron.timer`        | Daily certbot renew + housekeeping        |

```bash
systemctl status ipanel-agent
journalctl -u ipanel-agent -f
ipanel logs agent
```

## First login

After install completes, browse to `https://YOUR_IP:8443`.

```
username: admin
password: password
```

Change the password immediately:

```bash
sudo ipanel passwd admin
```

## Updating

```bash
sudo ipanel update
```

This pulls the latest commit on the configured branch, runs migrations, and
restarts `ipanel-agent`.

## Uninstalling

```bash
sudo bash /usr/local/ipanel/install/scripts/uninstall.sh
```

User sites under `/home/*` and managed databases are not removed.

## Supported operating systems

- Ubuntu 20.04 / 22.04 / 24.04
- Debian 11 / 12
- AlmaLinux / Rocky 8 / 9
- CentOS Stream 9
