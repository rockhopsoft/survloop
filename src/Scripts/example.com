server {
        listen 80;
        root /var/www/example.com/public;
        server_name example.com www.example.com;
        index index.php index.html index.htm index.nginx-debian.html;

        location / {
                try_files $uri $uri/ =404;
        }

        location ~ .php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        }

        location ~ /.ht {
                deny all;
        }
}