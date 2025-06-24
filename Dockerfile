FROM php:8.2-cli

# Install Apache & PHP module for Apache
RUN apt-get update && \
    apt-get install -y apache2 libapache2-mod-php && \
    apt-get clean

# Copy project files into web root
COPY . /var/www/html/

# Enable Apache and keep it running
CMD ["apache2ctl", "-D", "FOREGROUND"]