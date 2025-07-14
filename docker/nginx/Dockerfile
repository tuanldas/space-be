FROM nginx:alpine

# Install curl for healthcheck
RUN apk add --no-cache curl

# Copy custom config
COPY ./docker/nginx/vhost.conf /etc/nginx/conf.d/default.conf

# Configure for non-root user
RUN set -eux; \
    addgroup nginx-user; \
    adduser -D -S -G nginx-user nginx-user; \
    mkdir -p /var/cache/nginx; \
    chown -R nginx-user:nginx-user /var/cache/nginx; \
    touch /var/run/nginx.pid; \
    chown -R nginx-user:nginx-user /var/run/nginx.pid

# Add healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Expose ports
EXPOSE 80 443

USER nginx-user

# Set proper permissions for config directories
USER root
RUN chown -R nginx-user:nginx-user /etc/nginx/conf.d

# Switch back to nginx user
USER nginx-user
