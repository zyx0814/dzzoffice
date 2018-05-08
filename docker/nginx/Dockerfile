FROM nginx:alpine

RUN apk update && \
	apk add tzdata && \
	rm -fr /etc/localtime && \
	cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime && \
	apk del tzdata && \
	rm -fr /var/cache/apk/*
WORKDIR "/var/www/html"	
EXPOSE 80
