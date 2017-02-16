.PHONY: build run kill enter clean

build:
	docker build -t depage-fs-server-vsftpd .

run: build kill
	docker run -d --name server-vsftpd -v $(CURDIR)/home:/home/testuser depage-fs-server-vsftpd
	-docker cp server-vsftpd:/etc/ssl/private/ca.pem $(CURDIR)/ssl

kill:
	-docker kill server-vsftpd
	-docker rm server-vsftpd

enter:
	docker exec -it server-vsftpd sh -c "export TERM=xterm && bash"

clean:
	docker rm -f server-vsftpd
	docker rmi -f depage-fs-server-vsftpd:latest
