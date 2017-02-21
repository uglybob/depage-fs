RM = rm -rf

.PHONY: remote-vsftpd test clean

test: remote-vsftpd
	$(MAKE) run -C docker/local

remote-vsftpd:
	$(MAKE) run -C docker/remote-vsftpd

clean:
	${RM} Report Temp docker/remote-vsftpd/ssl/ca.pem
	$(MAKE) clean -C docker/local
	$(MAKE) clean -C docker/remote-vsftpd
