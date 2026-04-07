# iPanel-managed site vhost template
# Variables substituted by NginxModule::createVhost():
#   {{USER}}    Linux user / pool name
#   {{DOMAIN}}  primary domain
#   {{ALIASES}} space-separated additional server names
#   {{PHPVER}}  e.g. 8.2

server {
    listen 80;
    listen [::]:80;
    server_name {{DOMAIN}} {{ALIASES}};

    root /home/{{USER}}/public_html;
    index index.php index.html;

    access_log /home/{{USER}}/logs/access.log;
    error_log  /home/{{USER}}/logs/error.log warn;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/{{USER}}.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
