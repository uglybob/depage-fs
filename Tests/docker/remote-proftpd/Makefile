CONTAINER = depage-fs-proftpd
INSTANCE = depage-fs-proftpd0

.PHONY: build run kill enter clean

build:
	docker build -t $(CONTAINER) .

run: build kill
	docker run -d --name $(INSTANCE) -v $(CURDIR)/home:/home/testuser $(CONTAINER)
	docker cp $(INSTANCE):/etc/proftpd/ssl/ca.pem $(CURDIR)/../ssl

kill:
	-docker kill $(INSTANCE)
	-docker rm $(INSTANCE)

enter:
	docker exec -it $(INSTANCE) sh -c "export TERM=xterm && bash"

clean:
	-docker rm -f $(INSTANCE)
	-docker rmi -f $(CONTAINER):latest
