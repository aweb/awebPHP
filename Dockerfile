FROM  aweb/nginx-php-fpm
#FROM richarvey/nginx-php-fpm 

MAINTAINER kaiyanh 
ADD ./nginx-site.conf /etc/nginx/sites-available/default.conf 
ADD . /var/www/html

#EXPOSE 80


