# Building Production image

FROM nginx:1.15.5-alpine as prod

LABEL maintainer="FOLGER" description="FOLGER DAPClient Web"

WORKDIR /app

RUN mkdir -p web

COPY etc/docker/web/default.conf /etc/nginx/conf.d/default.conf
