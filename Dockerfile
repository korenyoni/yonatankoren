FROM nginx:1.14.0-alpine
COPY css/ images/ js/ parsedown/ posts/ .htaccess footer.php header.html index.html posts.php posts_list.php /usr/share/nginx/html/
