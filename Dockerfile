FROM trafex/alpine-nginx-php7:cf28926c
COPY css /usr/share/nginx/html/css
COPY images /usr/share/nginx/html/images
COPY js /usr/share/nginx/html/js
COPY posts /usr/share/nginx/html/posts
COPY .htaccess footer.php header.html index.html posts.php posts_list.php /usr/share/nginx/html/
