FROM php:8.4-cli

# Set working directory
WORKDIR /app

# Copy the php.ini configuration
COPY php.ini /usr/local/etc/php/php.ini

# Copy the run-tests.sh script
COPY run-tests.sh /app/run-tests.sh

# Make the script executable
RUN chmod +x /app/run-tests.sh

CMD ["/app/run-tests.sh"]
