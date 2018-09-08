FROM trafex/alpine-nginx-php7:cf28926c
CMD rm /var/www/html/*
COPY css /var/www/html/css
COPY images /var/www/html/images
COPY js /var/www/html/js
COPY posts /var/www/html/posts
COPY .htaccess footer.php header.html index.html posts.php posts_list.php /var/www/html/
