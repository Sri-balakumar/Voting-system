# Use an official PHP runtime as a parent image
FROM php:8.0-cli

# Set the working directory in the container
WORKDIR /var/www/html

# Copy the current directory contents into the container at /var/www/html
COPY . .

# Expose port 10000
EXPOSE 10000

# Command to run PHP's built-in server
CMD ["php", "-S", "0.0.0.0:10000"]
