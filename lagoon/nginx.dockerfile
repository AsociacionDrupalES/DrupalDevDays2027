FROM uselagoon/nginx:latest

COPY . /app

# Define where the Drupal Root is located
ENV WEBROOT=web
